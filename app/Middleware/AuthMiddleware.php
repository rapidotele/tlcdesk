<?php

namespace App\Middleware;

use App\Core\Model;

class AuthMiddleware {
    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Set Multi-tenancy context
        if (isset($_SESSION['tenant_id'])) {
            Model::setTenantId($_SESSION['tenant_id']);
        }
    }
}
