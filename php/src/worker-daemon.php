<?php
require_once __DIR__ . '/autoload.php';
use App\Db;

echo "🤖 OpenClaw Worker Daemon Online...\n";

// Handle DB Connection with retry logic for Docker
$db = null;
while ($db === null) {
    try {
        $db = new Db();
    } catch (\Exception $e) {
        echo "⏳ Waiting for Database... \n";
        sleep(5);
    }
}

while (true) {
    if (file_exists(__DIR__ . '/../../storage/queue/STOP')) { sleep(2); continue; }
    
    $jobs = glob(__DIR__ . '/../../storage/queue/*.job');
    foreach ($jobs as $f) {
        $job = json_decode(file_get_contents($f), true);
        $task = $job['task'];
        
        file_put_contents(__DIR__ . '/../../storage/queue/progress.json', json_encode(["event"=>"PROGRESS", "val"=>50, "msg"=>"Executing $task..."]));
        
        // ADD YOUR CUSTOM TASK LOGIC HERE
        sleep(2); 
        
        file_put_contents(__DIR__ . '/../../storage/queue/progress.json', json_encode(["event"=>"PROGRESS", "val"=>100, "msg"=>"Success: $task"]));
        unlink($f);
        @unlink(__DIR__ . '/../../storage/queue/progress.json');
    }
    sleep(1);
}
