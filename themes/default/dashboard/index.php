<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        a { color: #fff; text-decoration: none; margin-left: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div>TLCDesk</div>
        <div>
            <span>Welcome, <?= htmlspecialchars($name) ?></span>
            <a href="/logout">Logout</a>
        </div>
    </div>
    <div class="content">
        <div class="card">
            <h2>Dashboard</h2>
            <p>You have successfully logged in and completed onboarding.</p>
            <p>Tenant ID: <?= $_SESSION['tenant_id'] ?></p>

            <?php if ($_SESSION['user_id'] == 1): // Simple check for first user/admin ?>
                <p><a href="/admin" style="color: blue;">Go to Admin Panel</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
