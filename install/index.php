<?php
session_start();

define('ROOT_PATH', dirname(__DIR__));
$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Helper to check requirements
function checkRequirements() {
    $results = [];
    $results['php_version'] = [
        'label' => 'PHP Version >= 7.4',
        'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'current' => PHP_VERSION
    ];
    $results['pdo'] = [
        'label' => 'PDO Extension',
        'status' => extension_loaded('pdo')
    ];
    $results['mysql'] = [
        'label' => 'MySQL Extension (pdo_mysql)',
        'status' => extension_loaded('pdo_mysql')
    ];
    $results['json'] = [
        'label' => 'JSON Extension',
        'status' => extension_loaded('json')
    ];

    // Writable folders
    $folders = ['storage', 'storage/logs', 'storage/uploads', 'public/assets'];
    foreach ($folders as $folder) {
        $path = ROOT_PATH . '/' . $folder;
        // ensure dir exists
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $results[$folder] = [
            'label' => "Writable: $folder",
            'status' => is_writable($path)
        ];
    }

    return $results;
}

if ($step == 1) {
    // Requirements Check
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
    // DB Configuration
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['db_host'] ?? 'localhost';
        $name = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';

        // Test Connection
        try {
            $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Save Config
            $configContent = "<?php\n\n";
            $configContent .= "define('DB_HOST', '$host');\n";
            $configContent .= "define('DB_NAME', '$name');\n";
            $configContent .= "define('DB_USER', '$user');\n";
            $configContent .= "define('DB_PASS', '$pass');\n";

            file_put_contents(ROOT_PATH . '/config.php', $configContent);

            // Run Schema
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
    // Create Admin User
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
        $name = $_POST['name']; // "System Admin"

        if ($email && $password) {
            // Create Admin Tenant
            $stmt = $pdo->prepare("INSERT INTO tenants (name, type) VALUES (?, 'fleet')");
            $stmt->execute(['System Admin']);
            $tenantId = $pdo->lastInsertId();

            // Create User
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, name, email, password_hash, is_onboarding_complete) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$tenantId, $name, $email, $hash]);
            $userId = $pdo->lastInsertId();

            // Assign Admin Role (ID 1)
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 1)");
            $stmt->execute([$userId]);

            // Lock Installer
            file_put_contents(ROOT_PATH . '/storage/install.lock', date('Y-m-d H:i:s'));

            $success = "Installation Complete! <a href='/'>Go to Login</a>";
            // Wait, if I go to /, I need to be able to login.
        } else {
            $error = "Please fill all fields.";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>TLCDesk Installer</title>
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
    </style>
</head>
<body>
    <div class="card">
        <h1>TLCDesk Installation - Step <?= $step ?></h1>

        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php else: ?>

            <?php if ($step == 1): ?>
                <form method="POST">
                    <h3>System Requirements</h3>
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
                        <button type="submit">Next: Database Config</button>
                    <?php else: ?>
                        <p class="error">Please fix the issues above to continue.</p>
                        <button type="button" onclick="window.location.reload()">Check Again</button>
                    <?php endif; ?>
                </form>
            <?php elseif ($step == 2): ?>
                <form method="POST">
                    <h3>Database Configuration</h3>
                    <div class="form-group">
                        <label>Host</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label>Database Name</label>
                        <input type="text" name="db_name" required>
                    </div>
                    <div class="form-group">
                        <label>User</label>
                        <input type="text" name="db_user" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="db_pass">
                    </div>
                    <button type="submit">Install Database</button>
                </form>
            <?php elseif ($step == 3): ?>
                <form method="POST">
                    <h3>Create Admin Account</h3>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="System Admin" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">Complete Installation</button>
                </form>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
