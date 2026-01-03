<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class LanguageController extends Controller {

    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) $this->redirect('/login');

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.name
            FROM roles r
            JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = ? AND r.name = 'admin'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            die(__("access_denied"));
        }
    }

    public function index() {
        $this->checkAdmin();
        $db = Database::getInstance()->getConnection();

        // Sync Files to DB (basic sync)
        $files = glob(ROOT_PATH . '/locales/*', GLOB_ONLYDIR);
        foreach ($files as $dir) {
            $code = basename($dir);
            // Sanitize code for DB (although basename is relatively safe from glob)
            if (preg_match('/^[a-z0-9-]{2,5}$/i', $code)) {
                $stmt = $db->prepare("INSERT IGNORE INTO languages (code, name, is_enabled) VALUES (?, ?, 1)");
                $stmt->execute([$code, ucfirst($code)]);
            }
        }

        $stmt = $db->query("SELECT * FROM languages");
        $languages = $stmt->fetchAll();

        // Calculate missing keys
        $enPath = ROOT_PATH . '/locales/en/messages.json';
        $enKeys = file_exists($enPath) ? array_keys(json_decode(file_get_contents($enPath), true)) : [];

        foreach ($languages as &$lang) {
            $path = ROOT_PATH . "/locales/{$lang['code']}/messages.json";
            if (file_exists($path)) {
                $keys = array_keys(json_decode(file_get_contents($path), true) ?? []);
                $missing = array_diff($enKeys, $keys);
                $lang['missing_count'] = count($missing);
            } else {
                $lang['missing_count'] = count($enKeys);
            }
        }

        $this->view('admin/languages/index', ['languages' => $languages]);
    }

    public function toggle() {
        $this->checkAdmin();
        $this->verifyCSRF();

        $code = $_POST['code'];
        // Sanitize code
        if (!preg_match('/^[a-z0-9-]{2,5}$/i', $code)) {
            die("Invalid language code");
        }

        $enabled = $_POST['enabled'] ? 0 : 1; // Toggle

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE languages SET is_enabled = ? WHERE code = ?");
        $stmt->execute([$enabled, $code]);

        $this->redirect('/admin/languages');
    }

    public function export() {
        $this->checkAdmin();
        $code = $_GET['code'] ?? 'en';

        // Sanitize code
        if (!preg_match('/^[a-z0-9-]{2,5}$/i', $code)) {
            die("Invalid language code");
        }

        $file = ROOT_PATH . "/locales/$code/messages.json";

        if (file_exists($file)) {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="messages_'.$code.'.json"');
            readfile($file);
            exit;
        }
        die("File not found");
    }

    public function import() {
        $this->checkAdmin();
        $this->verifyCSRF();

        if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] == 0) {
            $code = $_POST['code'];

            // Sanitize code
            if (!preg_match('/^[a-z0-9-]{2,5}$/i', $code)) {
                die("Invalid language code");
            }

            $content = file_get_contents($_FILES['json_file']['tmp_name']);

            // Validate JSON
            if (json_decode($content) !== null) {
                // Ensure directory exists
                if (!is_dir(ROOT_PATH . "/locales/$code")) {
                    mkdir(ROOT_PATH . "/locales/$code", 0755, true);
                }
                file_put_contents(ROOT_PATH . "/locales/$code/messages.json", $content);

                // Ensure DB entry
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("INSERT IGNORE INTO languages (code, name, is_enabled) VALUES (?, ?, 1)");
                $stmt->execute([$code, ucfirst($code)]);
            }
        }

        $this->redirect('/admin/languages');
    }
}
