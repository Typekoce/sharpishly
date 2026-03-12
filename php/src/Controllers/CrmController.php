<?php
namespace App\Controllers;

use App\Models\TenantModel;
use App\Logger;

class CrmController extends BaseController {
    protected $tenantModel;

    public function __construct() {
        parent::__construct();
        $this->tenantModel = new TenantModel();
    }

    public function index(): void {
        try {
            // 1. Fetch data from the Model
            $tenants = $this->tenantModel.getAllTenants();

            // 2. Audit the access
            Logger::info("CrmController: Tenant records retrieved via TenantModel.");

            // 3. Dispatch JSON to the frontend script.js
            header('Content-Type: application/json');
            echo json_encode($tenants);
            
        } catch (\Exception $e) {
            Logger::error("CRM Failure: " . $e->getMessage());
            header('Content-Type: application/json', true, 500);
            echo json_encode(["error" => "Internal System Error"]);
        }
        exit;
    }
}