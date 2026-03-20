<?php

class CyberdeckController {

    public function cloudTail() {
        header('Content-Type: application/json');
        
        // Fetch recent events from Axiom API instead of local disk
        $dataset = "sharpishly-logs";
        $token = "YOUR_AXIOM_API_TOKEN";
        
        $ch = curl_init("https://api.axiom.co/v1/datasets/$dataset/query");
        // ... query for last 10 events ...
        
        echo $result; // Send cloud logs directly to the UI terminal
    }

    public function vpnStatus() {
        header('Content-Type: application/json');
        
        // Check if the wireguard interface (usually wg0) is up
        $status = shell_exec("ip addr show wg0");
        $isActive = strpos($status, 'UP') !== false;

        echo json_encode([
            'interface' => 'wg0',
            'status' => $isActive ? 'active' : 'inactive',
            'peers' => 2, // 'myphone' and 'mylaptop'
            'last_handshake' => date('H:i:s')
        ]);
    }

}

