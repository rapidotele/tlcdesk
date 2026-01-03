<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class OnboardingController extends Controller {

    public function index() {
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);

        if (!$user) {
            $this->redirect('/login');
        }

        if ($user['is_onboarding_complete']) {
            $this->redirect('/dashboard');
        }

        $role = $userModel->getRole($_SESSION['user_id']);

        $this->view('onboarding/index', ['role' => $role]);
    }

    public function complete() {
        $this->verifyCSRF();

        $userModel = new User();
        $userModel->completeOnboarding($_SESSION['user_id']);

        $this->redirect('/dashboard');
    }
}
