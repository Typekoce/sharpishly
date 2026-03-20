<?php
declare(strict_types=1);
namespace App\Tests;
use App\Controllers\BaseController;

class MockController extends BaseController {
    public function testJson() { $this->json(['test' => true]); }
}

class BaseControllerTest {
    private $t;
    public function __construct($t) { $this->t = $t; }
    public function run() {
        $this->t->assert(class_exists('App\Controllers\BaseController'), "BaseController class exists.");
        $controller = new MockController();
        $this->t->assert($controller instanceof \App\Controllers\BaseController, "Inheritance works.");
    }
}
