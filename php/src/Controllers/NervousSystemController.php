<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\Logger;
use App\Registry;
use App\Services\Location;

class NervousSystemController extends BaseController
{
    /**
     * Endpoint: /php/nervous_system/stream
     * This maintains a persistent connection to the browser HUD.
     */
    public function stream(): void
    {
        // 1. Mandatory SSE Headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Essential for Nginx/Proxy stability

        // Prevent PHP from timing out the script
        set_time_limit(0);
        ignore_user_abort(false);

        $loc = Registry::get(Location::class);
        $eventFile = $loc->queue('events.json');

        // 2. The Heartbeat Loop
        while (true) {
            // Check for new events written by background workers
            if (file_exists($eventFile)) {
                $data = file_get_contents($eventFile);
                echo "data: " . $data . "\n\n";
                unlink($eventFile); // Consume the event after sending
            } else {
                // Keep-alive "ping" to prevent Nginx/Browser timeouts
                echo ": ping\n\n";
            }

            // Force the output buffer to send data immediately
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            // 3. Structural Connection Guard
            // We use connection_status() as it is more robust across PHP builds.
            // 0 = CONNECTION_NORMAL
            if (function_exists('connection_status') && connection_status() !== 0) {
                break;
            }
            
            // Safety break if the user closes the tab
            if (function_exists('connection_abort') && connection_abort()) {
                break;
            }

            usleep(500000); // 0.5s delay to prevent CPU thrashing
        }
    }
}