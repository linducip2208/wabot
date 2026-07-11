<?php

namespace App\Services;

use App\Models\WaSheetsIntegration;
use App\Models\WaContact;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected ?\Google\Client $client = null;

    protected function getClient(WaSheetsIntegration $integration): ?\Google\Client
    {
        $serviceAccountJson = $integration->service_account_json;
        if (!$serviceAccountJson) return null;

        try {
            $client = new \Google\Client();
            $client->setApplicationName(config('app.name'));
            $client->setScopes([\Google\Service\Sheets::SPREADSHEETS]);
            $client->setAuthConfig(json_decode($serviceAccountJson, true));
            $client->setAccessType('offline');

            return $client;
        } catch (\Throwable $e) {
            Log::error('Google Sheets client init failed: ' . $e->getMessage());
            return null;
        }
    }

    public function testConnection(WaSheetsIntegration $integration): array
    {
        $client = $this->getClient($integration);
        if (!$client) {
            return ['success' => false, 'message' => 'Invalid service account credentials'];
        }

        try {
            $service = new \Google\Service\Sheets($client);
            $range = "'{$integration->sheet_name}'!A1:A1";
            $service->spreadsheets_values->get($integration->spreadsheet_id, $range);

            return ['success' => true, 'message' => 'Connected successfully'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function readSheet(WaSheetsIntegration $integration, string $range = null): array
    {
        $client = $this->getClient($integration);
        if (!$client) return [];

        try {
            $service = new \Google\Service\Sheets($client);
            $range = $range ?: "'{$integration->sheet_name}'!A:Z";
            $response = $service->spreadsheets_values->get($integration->spreadsheet_id, $range);

            return $response->getValues() ?? [];
        } catch (\Throwable $e) {
            Log::error('Google Sheets read failed: ' . $e->getMessage());
            return [];
        }
    }

    public function appendRow(WaSheetsIntegration $integration, array $values): bool
    {
        $client = $this->getClient($integration);
        if (!$client) return false;

        try {
            $service = new \Google\Service\Sheets($client);
            $range = "'{$integration->sheet_name}'!A:Z";

            $body = new \Google\Service\Sheets\ValueRange([
                'values' => [$values],
            ]);

            $params = ['valueInputOption' => 'RAW'];
            $service->spreadsheets_values->append(
                $integration->spreadsheet_id,
                $range,
                $body,
                $params
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('Google Sheets append failed: ' . $e->getMessage());
            return false;
        }
    }

    public function syncContacts(WaSheetsIntegration $integration): array
    {
        $integration->update(['sync_status' => 'syncing']);

        $stats = ['imported' => 0, 'exported' => 0, 'errors' => 0];

        try {
            if (in_array($integration->sync_direction, ['import', 'both'])) {
                $stats['imported'] = $this->importContacts($integration);
            }

            if (in_array($integration->sync_direction, ['export', 'both'])) {
                $stats['exported'] = $this->exportContacts($integration);
            }

            $integration->update([
                'sync_status' => 'synced',
                'last_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Google Sheets sync failed: ' . $e->getMessage());
            $integration->update(['sync_status' => 'failed']);
            $stats['errors'] = 1;
        }

        return $stats;
    }

    protected function importContacts(WaSheetsIntegration $integration): int
    {
        $rows = $this->readSheet($integration);
        if (empty($rows)) return 0;

        $header = array_shift($rows);
        if (empty($header)) return 0;

        $nameCol = $this->findColumn($header, ['name', 'nama', 'contact name']);
        $phoneCol = $this->findColumn($header, ['phone', 'nomor', 'phone number', 'mobile', 'whatsapp', 'wa']);
        $emailCol = $this->findColumn($header, ['email', 'e-mail']);

        if ($phoneCol === null) return 0;

        $count = 0;
        foreach ($rows as $row) {
            $phone = $row[$phoneCol] ?? null;
            if (!$phone) continue;

            $displayPhone = preg_replace('/[^0-9]/', '', $phone);
            $name = ($nameCol !== null && isset($row[$nameCol])) ? $row[$nameCol] : $displayPhone;
            $email = ($emailCol !== null && isset($row[$emailCol])) ? $row[$emailCol] : null;

            $contact = WaContact::firstOrCreate(
                ['user_id' => $integration->user_id, 'phone' => $phone],
                ['name' => $name, 'display_phone' => $displayPhone]
            );

            if ($contact->wasRecentlyCreated) $count++;
        }

        return $count;
    }

    protected function exportContacts(WaSheetsIntegration $integration): int
    {
        $contacts = WaContact::where('user_id', $integration->user_id)->get();
        if ($contacts->isEmpty()) return 0;

        $header = ['Name', 'Phone', 'Display Phone'];
        $existing = $this->readSheet($integration);

        $client = $this->getClient($integration);
        if (!$client) return 0;

        $service = new \Google\Service\Sheets($client);

        $allRows = array_merge($existing ?: [], [$header]);
        foreach ($contacts as $contact) {
            $allRows[] = [$contact->name, $contact->phone, $contact->display_phone];
        }

        $range = "'{$integration->sheet_name}'!A:Z";

        $body = new \Google\Service\Sheets\ValueRange(['values' => $allRows]);
        $params = ['valueInputOption' => 'RAW'];

        $service->spreadsheets_values->update(
            $integration->spreadsheet_id,
            $range,
            $body,
            $params
        );

        return $contacts->count();
    }

    protected function findColumn(array $header, array $possibleNames): ?int
    {
        foreach ($header as $i => $col) {
            $clean = strtolower(trim((string) $col));
            foreach ($possibleNames as $name) {
                if ($clean === $name) return $i;
            }
        }
        return null;
    }
}
