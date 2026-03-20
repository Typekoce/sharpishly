<?php
header('Access-Control-Allow-Origin: *');
$action = $_GET['action'] ?? '';

if ($action === 'queue') {
    $task = $_GET['task'];
    file_put_contents("../storage/queue/".uniqid().".job", json_encode(["task" => $task]));
    exit;
}
if ($action === 'safety') {
    $sig = $_GET['sig'];
    if ($sig === 'stop') file_put_contents('../storage/queue/STOP', '1');
    else @unlink('../storage/queue/STOP');
    exit;
}
if ($action === 'stream') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    while (true) {
        if (file_exists('../storage/queue/progress.json')) {
            echo "data: " . file_get_contents('../storage/queue/progress.json') . "\n\n";
        }
        ob_flush(); flush(); sleep(1);
    }
}
