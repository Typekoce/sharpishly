<?php
require_once 'agents.php';
echo "🤖 Master Worker Online...\n";
while (true) {
    if (file_exists('../storage/queue/STOP')) { sleep(2); continue; }
    $jobs = glob('../storage/queue/*.job');
    foreach ($jobs as $f) {
        $job = json_decode(file_get_contents($f), true);
        $task = $job['task'];
        
        // Agent Reasoning Logic
        file_put_contents('../storage/queue/progress.json', json_encode(["event"=>"PROGRESS", "val"=>50, "msg"=>"Agent Reasoning..."]));
        sleep(2);
        $result = ($task === 'SCOUT_TRENDS') ? scoutWeb($task) : socialDispatch('MultiChannel', $task);
        
        file_put_contents('../storage/queue/progress.json', json_encode(["event"=>"PROGRESS", "val"=>100, "msg"=>$result]));
        sleep(1);
        unlink($f); @unlink('../storage/queue/progress.json');
    }
    sleep(1);
}
