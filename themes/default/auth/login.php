<!DOCTYPE html>
<html>
<head>
    <title><?= __('login') ?> - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; text-align: center; }
        .footer-links { margin-top: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div style="text-align: right; margin-bottom: 10px;">
            <select onchange="window.location.href='/language/switch?code='+this.value">
                <?php foreach (getEnabledLanguages() as $lang): ?>
                    <option value="<?= $lang['code'] ?>" <?= (\App\Core\Translator::getInstance()->getLocale() == $lang['code']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <h2><?= __('login') ?></h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label><?= __('email') ?></label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label><?= __('password') ?></label>
                <input type="password" name="password" required>
            </div>
            <button type="submit"><?= __('login') ?></button>
        </form>
        <p style="text-align: center;"><a href="/register"><?= __('create_account') ?></a></p>
    </div>
</body>
</html>
