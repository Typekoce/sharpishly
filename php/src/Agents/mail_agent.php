<?php
/**
 * OpenClaw Mail Agent v1.0
 * Logic: Checks storage/queue/*.mail every 2 seconds.
 * Processes the mail and deletes the job file.
 */

require_once __DIR__ . '/../bootstrap.php';
use App\Services\Logger;

// Configuration - Use your Vault or Env variables here!
$smtp_host = 'smtp.mailtrap.io'; // Example: Use Mailtrap for testing
$admin_email = 'paul@sharpishly.com';

Logger::log("Mail Agent Started... Standing by for envelopes.");

while (true) {
    // 1. Look for mail jobs
    $mailJobs = glob(dirname(__DIR__, 2) . '/storage/queue/*.mail');

    foreach ($mailJobs as $jobFile) {
        Logger::log("New mail job detected: " . basename($jobFile));

        // 2. Read the mail instructions
        $data = json_decode(file_get_contents($jobFile), true);
        
        $subject = $data['subject'] ?? "Agent Alert";
        $message = $data['message'] ?? "No content provided.";

        // 3. Simple PHP Mail (Note: For production, PHPMailer is better)
        // We use the basic mail() function here to keep it "Simple" as requested.
        $headers = "From: agent@sharpishly.com";
        
        if (mail($admin_email, $subject, $message, $headers)) {
            Logger::log("Successfully sent email: $subject");
        } else {
            Logger::log("ERROR: PHP mail() failed. Ensure sendmail is installed in Docker.");
        }

        // 4. Cleanup - Delete the job so we don't send it twice!
        unlink($jobFile);
    }

    // Wait 2 seconds before checking again to save CPU
    sleep(2);
}