<?php
namespace App;
class Vault {
    public static function getKey() { return base64_decode(file_get_contents(__DIR__ . '/../../storage/vault/master.key')); }
    public static function encrypt($data) {
        $key = self::getKey(); $iv = openssl_random_pseudo_bytes(16);
        $enc = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($enc . '::' . $iv);
    }
    public static function decrypt($data) {
        $key = self::getKey(); list($d, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($d, 'aes-256-cbc', $key, 0, $iv);
    }
}
