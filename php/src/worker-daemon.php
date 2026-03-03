<?php
require_once __DIR__ . '/autoload.php';

use App\Services\CSVProcessor;
use App\Services\VideoOptimizerService;
use App\Models\VideoUploadModel;

echo "Worker daemon started at " . date('c') . "\n";

while (true) {
    try {
        $pdo = new PDO("mysql:host=db;dbname=sharpishly", "user", "pass");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // === CSV jobs ===
        $stmt = $pdo->query("SELECT * FROM jobs WHERE status = 'pending' LIMIT 1");
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $processor = new CSVProcessor($pdo);
            $processor->run($job['id'], $job['file_path']);
        }

        // === Video social posts ===
        $queueLines = file('php/queue.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        file_put_contents('php/queue.txt', ''); // clear queue

        foreach ($queueLines as $line) {
            $job = json_decode($line, true);
            if (!$job || empty($job['post_id'])) continue;

            $optimizer = new VideoOptimizerService();
            $model = new VideoUploadModel();

            $optimizedPath = $optimizer->optimizeForPlatform(
                $job['original_path'],
                $job['platform']
            );

            // TODO: Real posting logic here
            // $postUrl = postToPlatform($job['platform'], $optimizedPath, $job);
            $postUrl = "https://example.com/post/" . $job['post_id']; // placeholder

            $model->updatePostStatus($job['post_id'], 'posted', $postUrl);
        }
    } catch (Exception $e) {
        error_log("Worker error: " . $e->getMessage());
    }

    sleep(5); // avoid busy loop
}