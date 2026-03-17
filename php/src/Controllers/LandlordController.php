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

    // In App\Controllers\LandlordController.php
    public function dashboard(): void 
    {
        $mockData = [
            'properties' => [
                ['id' => 1, 'address' => '221B Baker St', 'rent' => 1200, 'status' => 'Paid'],
                ['id' => 2, 'address' => '742 Evergreen Terrace', 'rent' => 850, 'status' => 'Overdue'],
            ],
            'total_revenue' => 2050
        ];
        $this->json($mockData);
    }    
}