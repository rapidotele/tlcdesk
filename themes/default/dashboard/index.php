<!DOCTYPE html>
<html>
<head>
    <title><?= __('dashboard') ?> - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        a { color: #fff; text-decoration: none; margin-left: 15px; }
        .lang-switch a { color: #ccc; }
        .lang-switch a:hover { color: #fff; }
        select { padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div><?= __('app_name') ?></div>
        <div>
            <span class="lang-switch">
                <select onchange="window.location.href='/language/switch?code='+this.value">
                    <?php foreach (getEnabledLanguages() as $lang): ?>
                        <option value="<?= $lang['code'] ?>" <?= (\App\Core\Translator::getInstance()->getLocale() == $lang['code']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lang['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </span>
            <span style="margin-left: 20px;"><?= __('welcome', ['name' => htmlspecialchars($name)]) ?></span>
            <a href="/logout"><?= __('logout') ?></a>
        </div>
    </div>
    <div class="content">
        <div class="card">
            <h2><?= __('dashboard') ?></h2>
            <p><?= __('welcome_message') ?></p>
            <p><?= __('tenant_id', ['id' => $_SESSION['tenant_id']]) ?></p>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): // Simple check for first user/admin ?>
                <p><a href="/admin" style="color: blue;"><?= __('admin_panel') ?></a></p>
                <p><a href="/admin/languages" style="color: blue;"><?= __('language_manager') ?></a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
