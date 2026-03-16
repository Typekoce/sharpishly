<?php
declare(strict_types=1);

namespace App\Services;

class ProjectContextService 
{
    private string $rootDir = '/var/www/html/';

    /**
     * Scans logs and returns the last X lines
     */
    public function getLatestLogs(string $logFile = 'app.log', int $lines = 20): string 
    {
        $path = $this->rootDir . 'storage/logs/' . $logFile;
        if (!file_exists($path)) return "Log file $logFile not found.";
        
        $data = file($path);
        $lastLines = array_slice($data, -$lines);
        return implode("", $lastLines);
    }

    /**
     * Returns the project directory structure
     */
    public function getProjectMap(): string 
    {
        $it = new \RecursiveDirectoryIterator($this->rootDir);
        $display = "";
        foreach (new \RecursiveIteratorIterator($it) as $file) {
            if (str_contains($file->getPathname(), 'vendor') || str_contains($file->getPathname(), '.git')) continue;
            if ($file->isFile()) $display .= $file->getPathname() . "\n";
        }
        return substr($display, 0, 2000); // Cap to prevent context overflow
    }

    /**
     * Reads a specific file's content
     */
    public function getFileContent(string $relativePath): string 
    {
        $path = realpath($this->rootDir . $relativePath);
        if ($path && str_starts_with($path, $this->rootDir) && file_exists($path)) {
            return file_get_contents($path);
        }
        return "Access denied or file missing.";
    }
}