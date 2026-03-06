<?php declare(strict_types=1);

namespace App\Controllers;

use App\Smarty;
use App\Db;

class BaseController {

    protected Smarty $smarty;
    protected Db $db;

    public function __construct()
    {
        $this->smarty = new Smarty();
        $this->db = new Db();
    }

    public function getViewContent(string $path): string
    {
        $file = dirname(__DIR__, 1) . "/views/$path.html"; 

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return "";
    }

    public function render($data, $views)
    {
        $render = "";
        foreach($views as $name => $template){
            $render .= $this->getViewContent($template);
        }

        // Now accessible because it's protected in this class
        echo $this->smarty->render($render, $data);
        die();
    }
}