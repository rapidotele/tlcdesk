<?php
session_start();

// Need to handle Translator manually here since autoloader and config might not be fully ready if install/index.php is run directly.
// But we can include the class manually.
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Translator.php';

use App\Core\Translator;

// Helper function
if (!function_exists('__')) {
    function __($key, $params = []) {
        return Translator::t($key, $params);
    }
}

// Minimal version of getEnabledLanguages for installer
function getInstallerLanguages() {
    $langs = [];
    $files = glob(ROOT_PATH . '/locales/*', GLOB_ONLYDIR);
    foreach ($files as $dir) {
        $code = basename($dir);
        $langs[] = ['code' => $code, 'name' => strtoupper($code)];
    }
    return $langs;
}


// Set Locale for Installer (Simple detection or default EN)
$locale = 'en';
if (isset($_GET['lang'])) {
    $locale = $_GET['lang'];
    $_SESSION['install_lang'] = $locale;
} elseif (isset($_SESSION['install_lang'])) {
    $locale = $_SESSION['install_lang'];
}
Translator::getInstance()->setLocale($locale);

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

function checkRequirements() {
    $results = [];
    $results['php_version'] = [
        'label' => __('req_php_version'),
        'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'current' => PHP_VERSION
    ];
    $results['pdo'] = [
        'label' => __('req_pdo'),
        'status' => extension_loaded('pdo')
    ];
    $results['mysql'] = [
        'label' => __('req_mysql'),
        'status' => extension_loaded('pdo_mysql')
    ];
    $results['json'] = [
        'label' => __('req_json'),
        'status' => extension_loaded('json')
    ];

    $folders = ['storage', 'storage/logs', 'storage/uploads', 'public/assets'];
    foreach ($folders as $folder) {
        $path = ROOT_PATH . '/' . $folder;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $results[$folder] = [
            'label' => __('req_writable', ['folder' => $folder]),
            'status' => is_writable($path)
        ];
    }

    return $results;
}

if ($step == 1) {
    $requirements = checkRequirements();
    $allPass = true;
    foreach ($requirements as $req) {
        if (!$req['status']) $allPass = false;
    }

    if ($allPass && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Location: ?step=2');
        exit;
    }
}

if ($step == 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['db_host'] ?? 'localhost';
        $name = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';

        try {
            $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $configContent = "<?php\n\n";
            $configContent .= "define('DB_HOST', '$host');\n";
            $configContent .= "define('DB_NAME', '$name');\n";
            $configContent .= "define('DB_USER', '$user');\n";
            $configContent .= "define('DB_PASS', '$pass');\n";

            file_put_contents(ROOT_PATH . '/config.php', $configContent);

            $sql = file_get_contents(ROOT_PATH . '/install/schema.sql');
            $pdo->exec($sql);

            header('Location: ?step=3');
            exit;
        } catch (Exception $e) {
            $error = "Connection Failed: " . $e->getMessage();
        }
    }
}

if ($step == 3) {
    if (file_exists(ROOT_PATH . '/config.php')) {
        require_once ROOT_PATH . '/config.php';
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (Exception $e) {
            die("DB Error: " . $e->getMessage());
        }
    } else {
        header('Location: ?step=2');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $name = $_POST['name'];

        if ($email && $password) {
            $stmt = $pdo->prepare("INSERT INTO tenants (name, type) VALUES (?, 'fleet')");
            $stmt->execute(['System Admin']);
            $tenantId = $pdo->lastInsertId();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, name, email, password_hash, is_onboarding_complete) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$tenantId, $name, $email, $hash]);
            $userId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 1)");
            $stmt->execute([$userId]);

            file_put_contents(ROOT_PATH . '/storage/install.lock', date('Y-m-d H:i:s'));

            $success = __('install_success');
        } else {
            $error = __('error_fill_fields');
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= __('install_title') ?></title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
        .card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; }
        .error { color: red; }
        .success { color: green; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #007bff; color: #fff; border: none; cursor: pointer; }
        button:disabled { background: #ccc; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .lang-switch { float: right; }
    </style>
</head>
<body>
    <div class="card">
        <div class="lang-switch">
             <select onchange="window.location.href='?step=<?= $step ?>&lang='+this.value">
                <?php foreach (getInstallerLanguages() as $lang): ?>
                    <option value="<?= $lang['code'] ?>" <?= ($locale == $lang['code']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <h1><?= __('install_title') ?> - <?= __('step', ['step' => $step]) ?></h1>

        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php else: ?>

            <?php if ($step == 1): ?>
                <form method="POST">
                    <h3><?= __('system_requirements') ?></h3>
                    <table>
                        <?php foreach ($requirements as $req): ?>
                        <tr>
                            <td><?= $req['label'] ?></td>
                            <td>
                                <?php if ($req['status']): ?>
                                    <span style="color: green;">OK</span>
                                <?php else: ?>
                                    <span style="color: red;">FAIL</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <br>
                    <?php if ($allPass): ?>
                        <button type="submit"><?= __('next') ?>: <?= __('db_config') ?></button>
                    <?php else: ?>
                        <p class="error"><?= __('fix_issues') ?></p>
                        <button type="button" onclick="window.location.reload()"><?= __('check_again') ?></button>
                    <?php endif; ?>
                </form>
            <?php elseif ($step == 2): ?>
                <form method="POST">
                    <h3><?= __('db_config') ?></h3>
                    <div class="form-group">
                        <label><?= __('host') ?></label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label><?= __('db_name') ?></label>
                        <input type="text" name="db_name" required>
                    </div>
                    <div class="form-group">
                        <label><?= __('user') ?></label>
                        <input type="text" name="db_user" required>
                    </div>
                    <div class="form-group">
                        <label><?= __('password') ?></label>
                        <input type="password" name="db_pass">
                    </div>
                    <button type="submit"><?= __('install_db') ?></button>
                </form>
            <?php elseif ($step == 3): ?>
                <form method="POST">
                    <h3><?= __('create_admin') ?></h3>
                    <div class="form-group">
                        <label><?= __('name') ?></label>
                        <input type="text" name="name" value="System Admin" required>
                    </div>
                    <div class="form-group">
                        <label><?= __('email') ?></label>
                        <input type="text" name="email" required>
                    </div>
                    <div class="form-group">
                        <label><?= __('password') ?></label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit"><?= __('complete_install') ?></button>
                </form>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
