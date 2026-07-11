<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FfmpegService
{
    public function convertToOgg(string $inputPath, ?string $outputDir = null): ?string
    {
        return $this->convert($inputPath, 'ogg', 'libopus', '128k', $outputDir);
    }

    public function convertToMp3(string $inputPath, ?string $outputDir = null): ?string
    {
        return $this->convert($inputPath, 'mp3', 'libmp3lame', '128k', $outputDir);
    }

    protected function convert(string $inputPath, string $format, string $codec, string $bitrate, ?string $outputDir = null): ?string
    {
        if (!file_exists($inputPath)) return null;

        $outputDir = $outputDir ?: dirname($inputPath);
        $filename = pathinfo($inputPath, PATHINFO_FILENAME) . '_converted.' . $format;
        $outputPath = $outputDir . '/' . $filename;

        $ffmpeg = config('services.ffmpeg.path', 'ffmpeg');

        $cmd = escapeshellcmd($ffmpeg) . ' -y -i ' . escapeshellarg($inputPath)
            . ' -acodec ' . escapeshellarg($codec)
            . ' -b:a ' . escapeshellarg($bitrate)
            . ' -ar 16000 -ac 1 '
            . escapeshellarg($outputPath)
            . ' 2>&1';

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath)) {
            Log::error("FFmpeg conversion failed", ['cmd' => $cmd, 'output' => $output]);
            return null;
        }

        return $outputPath;
    }

    public function isAvailable(): bool
    {
        $ffmpeg = config('services.ffmpeg.path', 'ffmpeg');
        exec(escapeshellcmd($ffmpeg) . ' -version 2>&1', $output, $exitCode);
        return $exitCode === 0;
    }

    public function getDuration(string $audioPath): ?float
    {
        if (!file_exists($audioPath)) return null;

        $ffmpeg = config('services.ffmpeg.path', 'ffmpeg');
        $cmd = escapeshellcmd($ffmpeg) . ' -i ' . escapeshellarg($audioPath) . ' 2>&1';

        exec($cmd, $output);
        $output = implode("\n", $output);

        if (preg_match('/Duration: (\d+):(\d+):(\d+\.\d+)/', $output, $matches)) {
            return ($matches[1] * 3600) + ($matches[2] * 60) + (float) $matches[3];
        }

        return null;
    }
}
