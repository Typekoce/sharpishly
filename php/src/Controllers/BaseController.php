<?php declare(strict_types=1);

namespace App\Controllers;

use App\Smarty;
use App\Db;

class BaseController {

    protected ?Smarty $smarty = null; // Make it nullable
    protected ?Db $db = null;

    public function __construct()
    {
        $this->initializeDependencies();
    }

    /**
     * Centralized initialization to prevent "Uninitialized" errors
     */
    protected function initializeDependencies(): void
    {
        if ($this->smarty === null) {
            $this->smarty = new Smarty();
        }
        if ($this->db === null) {
            $this->db = new Db();
        }
    }

    public function render($data, $views) {
        // Safety check: Ensure dependencies are loaded even if child forgot parent::__construct
        $this->initializeDependencies();

        $render = "";
        foreach($views as $name => $template){
            $render .= $this->getViewContent($template);
        }

        // Now safe to access
        echo $this->smarty->render($render, $data);
        die();
    }

    public function getViewContent(string $path): string
    {
        // Use the centralized storage path we defined earlier for views if necessary
        $file = dirname(__DIR__, 1) . "/views/$path.html"; 

        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return "View not found: $path";
    }
}