<?php declare(strict_types=1);

namespace App\Services;

class CrmProcessor {
    public function processRentCsv(string $filePath): array {
        $stats = ['updated' => 0, 'total_value' => 0];
        $handle = fopen($filePath, 'r');
        
        // Skip header
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            // Mapping: 0: TenantID, 1: Amount, 2: Date
            $tenantId = (int)$row[0];
            $amount = (float)$row[1];

            if ($this->updateBalance($tenantId, $amount)) {
                $stats['updated']++;
                $stats['total_value'] += $amount;
                
                // Cloud Log: Offload audit trail to Axiom
                Logger::log("Payment of $$amount applied to Tenant #$tenantId", "FINANCE");
            }
        }
        fclose($handle);

        // Notify: Send summary to your phone
        $this->notifyCompletion($stats);

        return $stats;
    }

    private function updateBalance(int $id, float $amt): bool {
        $db = \App\Db::connect();
        $stmt = $db->prepare("UPDATE tenants SET balance = balance + ?, last_payment = NOW() WHERE id = ?");
        return $stmt->execute([$amt, $id]);
    }

    private function notifyCompletion(array $stats): void {
        $msg = "💰 *Rent Processing Complete*\n";
        $msg .= "Records Updated: {$stats['updated']}\n";
        $msg .= "Total Volume: $" . number_format($stats['total_value'], 2);
        Logger::log($msg, "NOTIFY"); // Triggers Telegram push
    }
}