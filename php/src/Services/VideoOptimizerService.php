<?php
declare(strict_types=1);

namespace App\Services;

class VideoOptimizerService
{
    private string $uploadDir = 'php/uploads/optimized/';

    public function __construct()
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    public function optimizeForPlatform(string $originalPath, string $platform): string
    {
        $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
        $output = $this->uploadDir . uniqid($platform . '_') . '.' . $ext;

        $commands = [
            'tiktok'    => "-vf scale=1080:1920 -c:v libx264 -preset slow -crf 23",
            'instagram' => "-vf scale=1080:1080 -c:v libx264 -preset slow -crf 23",
            'youtube'   => "-vf scale=1920:1080 -c:v libx264 -preset slow -crf 23",
            'facebook'  => "-vf scale=1080:1920 -c:v libx264 -preset slow -crf 23",
        ];

        $cmd = $commands[$platform] ?? "-vf scale=1280:720 -c:v libx264 -preset slow -crf 23";

        exec("ffmpeg -i " . escapeshellarg($originalPath) . " $cmd -c:a aac " . escapeshellarg($output) . " 2>&1", $out, $ret);

        return $ret === 0 ? $output : $originalPath; // fallback
    }
}