<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        echo "<h1>Welcome to my tiny MVC</h1>";
        echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
    }

    public function about(string $name = 'Guest'): void
    {
        echo "<h1>About page</h1>";
        echo "<p>Hello, " . htmlspecialchars($name) . "!</p>";
    }
}
