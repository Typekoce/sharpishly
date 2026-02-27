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
        
        $this->save();    
    
        $this->db();

        $header = $this->view('home/header');
        $main = $this->view('home/main');
        $footer = $this->view('home/footer');

        $smarty = new Smarty();

        $arr = [
            'title' => 'Sharpishly',
            'dashboard'=>'Your Dashboard'
        ];

        // Option 1: render to string
        $main = $smarty->render($main, $arr);

        echo $header . $main . $footer;
        die();

    }

    public function about(string $name = 'Guest'): void
    {
        echo "<h1>About page</h1>";
        echo "<p>Hello, " . htmlspecialchars($name) . "!</p>";
    }

    public function view($folder="home"){
        
        $file = dirname(__DIR__) . "/views/" . $folder . ".html";

        if(file_exists($file)){
            return file_get_contents($file);
        }

        return false;

    }

    public function smarty(){
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
    }

    public function save(){

        $db = new Db();

        // ── INSERT new job ───────────────────────────────────────
        $save = [
            'tbl'           => 'jobs',
            'file_path'     => 'php/uploads/test.csv',
            'status'        => 'pending',
            'total_rows'    => 300000,
            'processed_rows'=> 12345,
            'created_at'    => date('Y-m-d H:i:s'),  // better format
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $newId = $db->save($save);
        echo "Created job with ID: $newId\n";

        // ── UPDATE existing job ──────────────────────────────────
        $update = [
            'tbl'           => 'jobs',
            'id'            => $newId,
            'status'        => 'processing',
            'processed_rows'=> 15000,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $affected = $db->save($update);
        echo "Updated $affected row(s)\n";    

    }

    public function db(){
        try {
            $db = new Db();

            $conditions = [
                'tbl'   => 'jobs',
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
}