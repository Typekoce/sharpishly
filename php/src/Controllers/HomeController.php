<?php
// src/Controllers/HomeController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Db;          // ← important: import the Db class
use App\Smarty;
// or use \App\Db if you place it in root namespace

class HomeController
{
    public function index(): void
    {
        echo "<h1>Welcome to my tiny MVC</h1>";
        echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

        try {
            // Assuming autoloading is set up
            $smarty = new Smarty();

            $list = array(
                array('title'=>'Wolverine'),
                array('title'=>'Cyclops'),
                array('title'=>'Jean Grey')
            );

            $partial = "<li>{{{title}}}</li>";

            $ul = $smarty->partial($partial,$list);

            $file = "<b>{{{title}}}</b><i>{{{name}}}</i><ul>" . $ul . "</ul>";

            $arr = ['title' => 'foo'];

            // Option 1: render to string
            $output = $smarty->render($file, $arr);
            echo $output;   // → <b>foo</b><i></i>
        } catch(\Exception $e){
            echo "<div style=\"color: red; font-weight: bold;\">";
            echo "Smarty error: " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }

        try {
            $db = new Db();

            $conditions = [
                'tbl'   => 'students',
                'order' => ['id' => 'desc'],
                'limit' => '100',
                // You can add more later, e.g.:
                // 'where'  => ['age >' => 18],
                // 'fields' => ['id', 'name', 'grade'],
            ];

            $results = $db->find($conditions);

            echo "<pre>";
            print_r($results);
            echo "</pre>";
        } catch (\Exception $e) {
            echo "<div style=\"color: red; font-weight: bold;\">";
            echo "Database error: " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }

    public function about(string $name = 'Guest'): void
    {
        echo "<h1>About page</h1>";
        echo "<p>Hello, " . htmlspecialchars($name) . "!</p>";
    }
}