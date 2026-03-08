<?php
use PHPUnit\Framework\TestCase;

class IntegrityTest extends TestCase {
    public function testRequiredDirectories() {
        $this->assertDirectoryExists('php/src/Controllers');
        $this->assertDirectoryExists('php/src/Services');
    }
    
    public function testCoreFilesExist() {
        // These will fail if the files aren't created yet, which is perfect for diagnostics
        $this->assertFileExists('php/src/Controllers/ShopController.php');
        $this->assertFileExists('php/src/Services/OllamaService.php');
    }
}
