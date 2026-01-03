<!DOCTYPE html>
<html>
<head>
    <title><?= __('onboarding') ?> - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; text-align: center; }
        .card { background: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; margin: 0 auto; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; font-size: 16px; }
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
        <h1><?= __('welcome', ['name' => '']) ?> TLCDesk!</h1>
        <p><?= __('you_are_registered') ?> <strong><?= __($role) ?></strong></p>

        <?php if ($role == 'driver'): ?>
            <h3><?= __('driver_checklist') ?></h3>
            <ul style="text-align: left;">
                <li>[ ] <?= __('verify_tlc') ?></li>
                <li>[ ] <?= __('upload_insurance') ?></li>
                <li>[ ] <?= __('setup_payment') ?></li>
            </ul>
        <?php elseif ($role == 'fleet_manager'): ?>
            <h3><?= __('fleet_setup') ?></h3>
            <ul style="text-align: left;">
                <li>[ ] <?= __('add_vehicles') ?></li>
                <li>[ ] <?= __('invite_drivers') ?></li>
                <li>[ ] <?= __('config_billing') ?></li>
            </ul>
        <?php else: ?>
            <p><?= __('admin_setup') ?></p>
        <?php endif; ?>

        <form method="POST" action="/onboarding">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <p><em><?= __('placeholder_onboarding') ?></em></p>
            <button type="submit"><?= __('complete_onboarding') ?></button>
        </form>
    </div>
</body>
</html>
