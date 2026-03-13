<?php
// Location: /var/www/html/php/src/Services/Location.php

namespace App\Services;

class Location {
    private string $base = '/var/www/html/storage/';

    public function uploads(string $file = ''): string {
        return $this->base . 'uploads/' . ltrim($file, '/');
    }

    public function queue(string $file = ''): string {
        return $this->base . 'queue/' . ltrim($file, '/');
    }

    public function logs(string $file = ''): string {
        return $this->base . 'logs/' . ltrim($file, '/');
    }
    
    public function relative(string $absolutePath): string {
        return str_replace($this->base, '', $absolutePath);
    }
}