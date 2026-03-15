<?php
declare(strict_types=1);

namespace App\Tests;

use App\Vault;

class VaultTest {
    private $tester;

    public function __construct($tester) {
        $this->tester = $tester;
    }

    public function run(): void {
        $this->testEncryptionRoundTrip();
    }

    private function testEncryptionRoundTrip(): void {
        $originalSecret = "sk-ant-api03-sharpishly-2026";
        
        try {
            // 1. Encrypt
            $encrypted = Vault::encrypt($originalSecret);
            $this->tester->assert(
                $encrypted !== $originalSecret, 
                "Vault: Data was successfully obfuscated (Encrypted != Plaintext)."
            );

            // 2. Decrypt
            $decrypted = Vault::decrypt($encrypted);
            $this->tester->assert(
                $decrypted === $originalSecret, 
                "Vault: Round-trip successful. Decrypted data matches original secret."
            );

            // 3. Integrity Check
            $tampered = $encrypted . "extra_junk";
            $failedDecryption = Vault::decrypt($tampered);
            $this->tester->assert(
                $failedDecryption === null || $failedDecryption === false,
                "Vault: Correctly fails or returns null when data is tampered with."
            );

        } catch (\Exception $e) {
            $this->tester->assert(false, "Vault: Critical error during security test: " . $e->getMessage());
        }
    }
}
