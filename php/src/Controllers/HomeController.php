<?php declare(strict_types=1);

namespace App\Controllers;

use App\Smarty;
use Exception;
use App\Models\HomeModel; // add this if needed

class HomeController
{
    private Smarty $smarty;
    private HomeModel $home;

    public function __construct()
    {
        $this->smarty = new Smarty();
        $this->home = new HomeModel();
    }

    public function render()
    {
        $data = [
            'title'     => 'Sharpishly Dashboard',
            'dashboard' => 'Your Dashboard',
            //'jobs'      => $jobs,
        ];

        $header = $this->getViewContent('layouts/header');
        $footer = $this->getViewContent('layouts/footer');
        $main   = $this->getViewContent('home/main');

        $renderedMain = $this->smarty->render($main, $data);

        echo $header . $renderedMain . $footer;
    }

    public function index(): void
    {
        $this->render();
    }

    public function about(string $name = 'Guest'): void
    {
        echo "<h1>About page</h1>";
        echo "<p>Hello, " . htmlspecialchars($name) . "!</p>";
    }

    public function csv(): void
    {

        $result = $this->home->csv();
        var_dump($result);
        $this->render();

    }

    public function migrate(): void
    {
        try {
            $model = new HomeModel();
            echo $model->migrate();
        } catch (Exception $e) {
            http_response_code(500);
            echo "<h1>Migration Error</h1>";
            echo "<pre style=\"color:red;\">" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    }

    private function getViewContent(string $path): string
    {
        $file = dirname(__DIR__, 1) . "/views/$path.html"; // fixed path (up 2 levels)

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return "<!-- View not found: $path -->";
    }
}