<!DOCTYPE html>
<html>
<head>
    <title>Onboarding - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; text-align: center; }
        .card { background: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; margin: 0 auto; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome to TLCDesk!</h1>
        <p>You are registered as: <strong><?= ucfirst(str_replace('_', ' ', htmlspecialchars($role))) ?></strong></p>

        <?php if ($role == 'driver'): ?>
            <h3>Driver Onboarding Checklist</h3>
            <ul style="text-align: left;">
                <li>[ ] Verify TLC License (Automated check pending)</li>
                <li>[ ] Upload Insurance Documents</li>
                <li>[ ] Set up Payment Method</li>
            </ul>
        <?php elseif ($role == 'fleet_manager'): ?>
            <h3>Fleet Manager Setup</h3>
            <ul style="text-align: left;">
                <li>[ ] Add Vehicles</li>
                <li>[ ] Invite Drivers</li>
                <li>[ ] Configure Billing</li>
            </ul>
        <?php else: ?>
            <p>Admin Setup...</p>
        <?php endif; ?>

        <form method="POST" action="/onboarding">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <p><em>(This is a placeholder for the actual onboarding wizard)</em></p>
            <button type="submit">Complete Onboarding</button>
        </form>
    </div>
</body>
</html>
