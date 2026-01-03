<!DOCTYPE html>
<html>
<head>
    <title><?= __('language_manager') ?> - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        button { cursor: pointer; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .upload-form { margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <a href="/dashboard"><?= __('back_dashboard') ?></a>
        <h1><?= __('language_manager') ?></h1>

        <table>
            <thead>
                <tr>
                    <th><?= __('code') ?></th>
                    <th><?= __('name') ?></th>
                    <th><?= __('enabled') ?></th>
                    <th><?= __('missing_keys') ?></th>
                    <th><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($languages as $lang): ?>
                <tr>
                    <td><?= htmlspecialchars($lang['code']) ?></td>
                    <td><?= htmlspecialchars($lang['name']) ?></td>
                    <td>
                        <form method="POST" action="/admin/languages/toggle" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="code" value="<?= $lang['code'] ?>">
                            <input type="hidden" name="enabled" value="<?= $lang['is_enabled'] ?>">
                            <button type="submit" class="btn-sm">
                                <?= $lang['is_enabled'] ? __('disable') : __('enable') ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <?= $lang['missing_count'] ?>
                    </td>
                    <td>
                        <a href="/admin/languages/export?code=<?= $lang['code'] ?>"><?= __('export') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="upload-form">
            <h3><?= __('import') ?> / <?= __('upload_json') ?></h3>
            <form method="POST" action="/admin/languages/import" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div style="margin-bottom: 10px;">
                    <label><?= __('code') ?>:</label>
                    <input type="text" name="code" placeholder="e.g. fr" required style="padding: 5px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <input type="file" name="json_file" accept=".json" required>
                </div>
                <button type="submit"><?= __('upload_json') ?></button>
            </form>
        </div>
    </div>
</body>
</html>
