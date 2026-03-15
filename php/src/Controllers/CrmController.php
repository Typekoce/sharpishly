<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\TenantModel;
use App\Services\Logger;
use Exception;

class CrmController extends BaseController {
    
    protected TenantModel $tenantModel;

    public function __construct() {
        parent::__construct();
        // In the future, we will pull this from a Dependency Injection Container
        $this->tenantModel = new TenantModel();
    }

    public function index(): void {
        try {
            // 1. Fetch data from the Model
            $tenants = $this->tenantModel->getAllTenants();

            // 2. Audit the access
            Logger::info("CrmController: Tenant records retrieved.");

            // 3. Leverage BaseController's json method (Standardized & Testable)
            $this->json($tenants);
            
        } catch (Exception $e) {
            Logger::error("CRM Failure: " . $e->getMessage());
            
            // Standardized error response
            $this->json(["error" => "Internal System Error"], 500);
        }
        // REMOVED: exit; (BaseController::json handles response termination gracefully)
    }
}