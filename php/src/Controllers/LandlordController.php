<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\LandlordModel;

class LandlordController extends BaseController
{
    public function index(): string
    {
        $model = new LandlordModel();
        $data = $model->getAll();

        return $this->json([
            'status' => 'success',
            'count'  => count($data),
            'properties' => $data
        ]);
    }
}