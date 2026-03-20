<?php
namespace App\Services;

use App\Actions\CsvProcessAction;
use App\Actions\OllamaRagAction;
use App\Actions\SocialPostAction;

class Scheduler {
    private $map = [
        'csv_process' => CsvProcessAction::class,
        'ollama_rag'  => OllamaRagAction::class,
        'social_post' => SocialPostAction::class,
    ];

    public function dispatch(array $task): void {
        $actionType = $task['action_type'];

        if (!isset($this->map[$actionType])) {
            throw new \Exception("Unknown action type: {$actionType}");
        }

        $actionClass = $this->map[$actionType];
        $handler = new $actionClass();
        
        // Pass the JSON payload into the handle method
        $payload = json_decode($task['payload'], true);
        $handler->handle($payload);
    }
}