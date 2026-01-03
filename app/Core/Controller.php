<?php

namespace App\Core;

class Controller {
    protected function view($view, $data = []) {
        // Auto-inject CSRF token
        $data['csrf_token'] = CSRF::generate();

        extract($data);
        $viewFile = ROOT_PATH . "/themes/default/$view.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View $view not found.");
        }
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    protected function verifyCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!CSRF::verify($token)) {
                die("CSRF Token Validation Failed");
            }
        }
    }
}
