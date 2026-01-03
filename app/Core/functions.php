<?php

use App\Core\Translator;
use App\Core\Database;

if (!function_exists('__')) {
    function __($key, $params = []) {
        return Translator::t($key, $params);
    }
}

if (!function_exists('getEnabledLanguages')) {
    function getEnabledLanguages() {
        // Try DB first
        try {
            // Check if DB config exists
            if (file_exists(ROOT_PATH . '/config.php')) {
                // Ensure we don't crash if called before full bootstrap, but usually this is called in Views
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT code, name FROM languages WHERE is_enabled = 1 ORDER BY code ASC");
                $langs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($langs) {
                    return $langs;
                }
            }
        } catch (Exception $e) {
            // DB might not be ready or table missing
        }

        // Fallback to scanning directories (e.g. for Installer or DB failure)
        $langs = [];
        $files = glob(ROOT_PATH . '/locales/*', GLOB_ONLYDIR);
        foreach ($files as $dir) {
            $code = basename($dir);
            $langs[] = ['code' => $code, 'name' => strtoupper($code)];
        }
        return $langs;
    }
}
