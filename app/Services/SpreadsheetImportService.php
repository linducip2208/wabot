<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use ZipArchive;

class SpreadsheetImportService
{
    /**
     * Parse CSV/TXT/XLSX file into array of rows (array of string cells).
     */
    public function parse(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'xlsx') {
            return $this->parseXlsx($file->getRealPath());
        }

        return $this->parseCsv($file->getRealPath());
    }

    protected function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return $rows;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return $rows;
        }

        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $first = true;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($first) {
                $first = false;
                if (isset($row[0])) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
                }
            }
            $rows[] = array_map(fn ($c) => trim((string) $c), $row);
        }

        fclose($handle);
        return $rows;
    }

    protected function parseXlsx(string $path): array
    {
        $rows = [];
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return $rows;
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $this->readFirstSheetXml($zip);
        $zip->close();

        if ($sheetXml === null) {
            return $rows;
        }

        $xml = @simplexml_load_string($sheetXml);
        if ($xml === false || !isset($xml->sheetData)) {
            return $rows;
        }

        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $colIndex = $this->columnIndex(preg_replace('/\d+/', '', $ref));
                $type = (string) $c['t'];

                $value = '';
                if ($type === 's') {
                    $idx = (int) $c->v;
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($c->is) ? $this->extractText($c->is) : '';
                } elseif (isset($c->v)) {
                    $value = (string) $c->v;
                    if (is_numeric($value) && stripos($value, 'e') !== false) {
                        $value = sprintf('%.0f', (float) $value);
                    }
                }

                $cells[$colIndex] = trim($value);
            }

            if (empty($cells)) {
                continue;
            }

            $max = max(array_keys($cells));
            $ordered = [];
            for ($i = 0; $i <= $max; $i++) {
                $ordered[] = $cells[$i] ?? '';
            }
            $rows[] = $ordered;
        }

        return $rows;
    }

    protected function readSharedStrings(ZipArchive $zip): array
    {
        $strings = [];
        $content = $zip->getFromName('xl/sharedStrings.xml');
        if ($content === false) {
            return $strings;
        }

        $xml = @simplexml_load_string($content);
        if ($xml === false) {
            return $strings;
        }

        foreach ($xml->si as $si) {
            $strings[] = $this->extractText($si);
        }

        return $strings;
    }

    protected function extractText(\SimpleXMLElement $node): string
    {
        if (isset($node->t)) {
            return (string) $node->t;
        }

        $text = '';
        if (isset($node->r)) {
            foreach ($node->r as $r) {
                $text .= (string) $r->t;
            }
        }

        return $text;
    }

    protected function readFirstSheetXml(ZipArchive $zip): ?string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');
        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbook !== false && $rels !== false) {
            $wbXml = @simplexml_load_string($workbook);
            $relsXml = @simplexml_load_string($rels);

            if ($wbXml !== false && $relsXml !== false && isset($wbXml->sheets->sheet[0])) {
                $sheet = $wbXml->sheets->sheet[0];
                $rid = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

                foreach ($relsXml->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rid) {
                        $target = ltrim((string) $rel['Target'], '/');
                        if (!str_starts_with($target, 'xl/')) {
                            $target = 'xl/' . $target;
                        }
                        $content = $zip->getFromName($target);
                        if ($content !== false) {
                            return $content;
                        }
                    }
                }
            }
        }

        $fallback = $zip->getFromName('xl/worksheets/sheet1.xml');
        return $fallback === false ? null : $fallback;
    }

    protected function columnIndex(string $letters): int
    {
        $index = 0;
        $letters = strtoupper($letters);
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }
}
