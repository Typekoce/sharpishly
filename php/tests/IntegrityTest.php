<?php
use PHPUnit\Framework\TestCase;

class IntegrityTest extends TestCase {
    
    public function testOllamaServiceExists() {
        $this->assertTrue(class_exists('App\Services\OllamaService'), "OllamaService is missing from the structure.");
    }

    public function testShopControllerStructure() {
        $this->assertTrue(method_exists('App\Controllers\ShopController', 'placeOrder'), "ShopController missing placeOrder method.");
    }

    public function testNervousSystemIntegrity() {
        // Checking if the core files are reachable
        $this->assertFileExists('php/src/nervous_system.php');
    }
}