<?php
namespace App\Actions;

class CsvProcessAction {
    public function handle(array $payload): void {
        $file = $payload['file_path'];
        echo "📊 Processing CSV: {$file}\n";
    }
}