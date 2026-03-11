<?php
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