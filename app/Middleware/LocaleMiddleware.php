<?php

namespace App\Middleware;

use App\Core\Translator;
use App\Core\Database;
use PDO;

class LocaleMiddleware {
    public function handle() {
        $locale = 'en'; // Default

        // 1. Check Cookie
        if (isset($_COOKIE['app_lang'])) {
            $locale = $_COOKIE['app_lang'];
        }

        // 2. Check Auth User (Overrides cookie)
        if (isset($_SESSION['user_id'])) {
            if (isset($_SESSION['user_lang'])) {
                $locale = $_SESSION['user_lang'];
            } else {
                if (file_exists(ROOT_PATH . '/storage/install.lock')) {
                    try {
                        $db = Database::getInstance()->getConnection();
                        $stmt = $db->prepare("SELECT language FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $lang = $stmt->fetchColumn();
                        if ($lang) {
                            $locale = $lang;
                            $_SESSION['user_lang'] = $lang;
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
        }
        // 3. Check Browser Header
        elseif (!isset($_COOKIE['app_lang'])) {
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                foreach ($langs as $lang) {
                    $lang = substr($lang, 0, 2);
                    if (file_exists(ROOT_PATH . "/locales/$lang/messages.json")) {
                        $locale = $lang;
                        break;
                    }
                }
            }
        }

        // 4. Validate locale exists AND is enabled (if DB is ready)
        if (file_exists(ROOT_PATH . '/storage/install.lock')) {
             try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT is_enabled FROM languages WHERE code = ?");
                $stmt->execute([$locale]);
                $enabled = $stmt->fetchColumn();

                // If not found (e.g. file exists but not in DB yet) or explicitly disabled
                if ($enabled === false || $enabled == 0) {
                     // If scanning dir found it but DB says disabled, revert to EN
                     // Exception: if 'en' is somehow disabled, we might have issues.
                     // Assuming 'en' is always enabled or fallback exists.
                     if ($locale !== 'en') {
                         $locale = 'en';
                     }
                }
            } catch (\Exception $e) {
                // DB error, rely on file existence
            }
        }

        if (!file_exists(ROOT_PATH . "/locales/$locale/messages.json")) {
            $locale = 'en';
        }

        // Set Translator
        Translator::getInstance()->setLocale($locale);

        // Update Cookie if it changed effectively
        if (!isset($_COOKIE['app_lang']) || $_COOKIE['app_lang'] !== $locale) {
             setcookie('app_lang', $locale, time() + (86400 * 30), "/");
        }
    }
}
