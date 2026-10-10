<?php
require_once __DIR__ . '/config/db.php';

$health = Database::checkHealth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connectivity Status | SmartMove Transport</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0B0F17;
            color: #F8FAFC;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }
        .container {
            background: #161E2E;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        h1 {
            font-size: 1.5rem;
            margin-top: 0;
            color: #38BDF8;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.75rem;
        }
        .card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 0.5rem;
        }
        .badge-success { background: #10B981; color: #fff; }
        .badge-error { background: #EF4444; color: #fff; }
        .badge-warning { background: #F59E0B; color: #fff; }
        .details {
            font-size: 0.9rem;
            color: #94A3B8;
            margin-top: 0.5rem;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            background: #2563EB;
            color: white;
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            border-radius: 9999px;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SmartMove Hybrid Database Status</h1>

        <!-- Oracle XE Status -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Oracle Database 21c/19c XE</strong>
                <?php if ($health['oracle']['connected']): ?>
                    <span class="badge badge-success">Connected</span>
                <?php elseif (!$health['oracle']['enabled']): ?>
                    <span class="badge badge-warning">OCI8 Disabled</span>
                <?php else: ?>
                    <span class="badge badge-error">Offline</span>
                <?php endif; ?>
            </div>
            <div class="details">
                <?php echo htmlspecialchars($health['oracle']['message']); ?>
                <?php if ($health['oracle']['version']): ?>
                    <br><small>Version: <?php echo htmlspecialchars($health['oracle']['version']); ?></small>
                <?php endif; ?>
            </div>
        </div>

        <!-- MongoDB Status -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong>MongoDB Community Server (NoSQL)</strong>
                <?php if ($health['mongodb']['connected']): ?>
                    <span class="badge badge-success">Connected</span>
                <?php elseif (!$health['mongodb']['enabled']): ?>
                    <span class="badge badge-warning">MongoDB Ext Disabled</span>
                <?php else: ?>
                    <span class="badge badge-error">Offline</span>
                <?php endif; ?>
            </div>
            <div class="details">
                <?php echo htmlspecialchars($health['mongodb']['message']); ?>
            </div>
        </div>

        <a href="index.php" class="btn">← Return to SmartMove Application</a>
    </div>
</body>
</html>
