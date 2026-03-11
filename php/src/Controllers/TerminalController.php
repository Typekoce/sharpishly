<?php declare(strict_types=1);

namespace App\Controllers;

use App\Services\Logger;

class TerminalController extends BaseController {
    
    public function execute(): void {
        $cmd = $_POST['command'] ?? 'whoami';
        
        // Safety: Never run raw POST data in shell_exec in production.
        // For your local Cyberdeck:
        $connection = @ssh2_connect('192.168.0.5', 22);
        
        if (!$connection) {
            Logger::error("SSH Connection Refused to MacBook.");
            echo json_encode(['output' => 'Error: Connection Refused. Check MacBook Sharing settings.']);
            return;
        }

        // Use keys or password from .env
        ssh2_auth_password($connection, getenv('SSH_USER'), getenv('SSH_PASS'));
        $stream = ssh2_exec($connection, $cmd);
        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);

        echo json_encode(['output' => $output]);
    }
}