<?php

namespace App\Services;
use Exception;

class Security {

    public function __construct(){
        
    }

    public static function verifyHardwareKey(): bool {
        $allowedSerial = "VID_1234&PID_5678"; // Your specific thumb drive
        $usbList = shell_exec("lsusb");
        
        if (strpos($usbList, $allowedSerial) === false) {
            Logger::log("🚨 SECURITY: Hardware Key Missing during sensitive operation!", "CRITICAL");
            return false;
        }
        return true;
    }
}