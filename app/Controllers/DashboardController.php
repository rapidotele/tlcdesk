<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class DashboardController extends Controller {

    public function index() {
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);

        if (!$user) {
            $this->redirect('/login');
        }

        if (!$user['is_onboarding_complete']) {
            $this->redirect('/onboarding');
        }

        $this->view('dashboard/index', ['name' => $user['name']]);
    }

    public function admin() {
        $userModel = new User();
        $role = $userModel->getRole($_SESSION['user_id']);

        if ($role !== 'admin') {
            die(__("access_denied"));
        }

        $this->view('admin/dashboard/index');
    }
}
