<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class LanguageController extends Controller {

    public function switch() {
        $code = $_GET['code'] ?? 'en';

        // Validate code exists in DB or files
        if (file_exists(ROOT_PATH . "/locales/$code/messages.json")) {
            // Set Cookie
            setcookie('app_lang', $code, time() + (86400 * 30), "/");

            // If logged in, update user preference
            if (isset($_SESSION['user_id'])) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE users SET language = ? WHERE id = ?");
                $stmt->execute([$code, $_SESSION['user_id']]);
                $_SESSION['user_lang'] = $code;
            }
        }

        // Redirect back
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }
}
