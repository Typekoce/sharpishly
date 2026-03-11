<?php
// php/bin/ai-worker.php
while (true) {
    $job = $db->query("SELECT * FROM ai_jobs WHERE status = 'pending' LIMIT 1")->fetch();
    if ($job) {
        $result = (new AiManager())->ask($job['prompt'], $job['provider']);
        $db->prepare("UPDATE ai_jobs SET result = ?, status = 'completed' WHERE id = ?")
           ->execute([$result, $job['id']]);
           
        Logger::log("AI Job #{$job['id']} finished via Telegram.");
    }
    sleep(2); // Save CPU cycles
}