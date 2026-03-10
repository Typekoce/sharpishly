<?php

namespace App\Controllers;

use App\Services\Logger;
use App\Models\OllamaModel;
use App\dBug;

class OllamaController extends BaseController {

    public OllamaModel $ollama;

    private $ollama_url = "http://host.docker.internal:11434/api/tags";

    public function __construct(){

        $this->ollama = new OllamaModel;

    }

    public function index(){
        $data = [
            'title'     => 'Ollama',
            'dashboard' => 'Data Engine',
            'jobs'      => [],
        ];

        $views = [
           'header' => 'layouts/header',
           'main'   => 'csv/upload', 
           'footer' => 'layouts/footer'
        ];

        new dBug(array_merge($data,$views));

        $this->render($data, $views);
    }

    public function response() {

        $debug = TRUE;

        if($debug){

            $question = $argv[1] ?? readline("Ask me anything: ");

            $answer = $this->ollama->ragAsk($question);

            echo "\n\033[1;32mGod Mode:\033[0m $answer\n\n";

        } else {

            // Old code

                $ch = curl_init($this->ollama_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Don't let a down LLM hang the UI
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                header('Content-Type: application/json');

                if ($http_code === 200) {
                    $data = json_decode($response, true);
                    echo json_encode([
                        'status' => 'online',
                        'models' => $data['models'] ?? [],
                        'message' => 'Ollama is responding'
                    ]);
                } else {
                    Logger::log("Ollama connection failed with code: " . $http_code);
                    echo json_encode([
                        'status' => 'offline',
                        'message' => 'Could not connect to Ollama server at ' . $this->ollama_url
                    ]);
                }
            // End old code

        }
    }
}