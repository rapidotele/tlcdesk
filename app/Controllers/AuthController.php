<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Models\User;
use App\Core\Translator;
use PDO;

class AuthController extends Controller {

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();

            // Rate Limit
            $limiter = new RateLimiter();
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!$limiter->check($ip, 5, 60)) {
                $this->view('auth/login', ['error' => __('too_many_attempts')]);
                return;
            }

            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['tenant_id'] = $user['tenant_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_lang'] = $user['language'] ?? 'en';

                // Update Cookie
                setcookie('app_lang', $_SESSION['user_lang'], time() + (86400 * 30), "/");

                if (!$user['is_onboarding_complete']) {
                    $this->redirect('/onboarding');
                } else {
                    $this->redirect('/dashboard');
                }
            } else {
                $this->view('auth/login', ['error' => __('invalid_credentials')]);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function logout() {
        session_destroy();
        // clear cookie? No, keep lang preference
        $this->redirect('/login');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();

            $db = Database::getInstance()->getConnection();

            try {
                $db->beginTransaction();

                $type = $_POST['account_type'];
                $name = $_POST['name'];
                $email = $_POST['email'];
                $password = $_POST['password'];

                $tenantName = ($type === 'fleet_manager') ? $_POST['company_name'] : __("workspace_name", ['name' => $name]);
                $tenantType = ($type === 'fleet_manager') ? 'fleet' : 'driver';

                $stmt = $db->prepare("INSERT INTO tenants (name, type) VALUES (?, ?)");
                $stmt->execute([$tenantName, $tenantType]);
                $tenantId = $db->lastInsertId();

                // Get current lang from cookie/session to save as user preference
                $lang = $_COOKIE['app_lang'] ?? 'en';

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (tenant_id, name, email, password_hash, language) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$tenantId, $name, $email, $hash, $lang]);
                $userId = $db->lastInsertId();

                $roleId = ($type === 'fleet_manager') ? 2 : 3;
                $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                $stmt->execute([$userId, $roleId]);

                if ($type === 'driver') {
                    $tlc_license = $_POST['tlc_license'];
                    $tlc_expiration = $_POST['tlc_expiration'];
                    $dmv_license = $_POST['dmv_license'];
                    $dmv_expiration = $_POST['dmv_expiration'];

                    $plate = null;
                    if (!isset($_POST['no_vehicle']) && !empty($_POST['plate'])) {
                        $plate = $_POST['plate'];
                    }

                    $stmt = $db->prepare("INSERT INTO driver_profiles (user_id, tlc_license, tlc_expiration, dmv_license, dmv_expiration, plate) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $tlc_license, $tlc_expiration, $dmv_license, $dmv_expiration, $plate]);

                } elseif ($type === 'fleet_manager') {
                    $company_name = $_POST['company_name'];
                    $manager_name = $_POST['manager_name'];
                    $contact_email = $_POST['contact_email'];
                    $contact_phone = $_POST['contact_phone'];

                    $stmt = $db->prepare("INSERT INTO fleet_profiles (tenant_id, company_name, manager_name, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$tenantId, $company_name, $manager_name, $contact_email, $contact_phone]);
                }

                $db->commit();

                $_SESSION['user_id'] = $userId;
                $_SESSION['tenant_id'] = $tenantId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_lang'] = $lang;

                $this->redirect('/onboarding');

            } catch (\Exception $e) {
                $db->rollBack();
                $this->view('auth/register', ['error' => __('registration_failed', ['error' => $e->getMessage()])]);
            }
        } else {
            $this->view('auth/register');
        }
    }
}
