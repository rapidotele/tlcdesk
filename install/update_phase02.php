<?php

// Migration Script for Phase 02
// Run this via CLI: php install/update_phase02.php
// Or access via browser if temporarily allowed (but CLI is safer).

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "Running Phase 02 Migration...\n";

    // 1. Add languages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS languages (
        code VARCHAR(5) PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        is_enabled TINYINT(1) DEFAULT 1
    )");
    echo "Checked/Created languages table.\n";

    // 2. Seed languages
    $pdo->exec("INSERT IGNORE INTO languages (code, name, is_enabled) VALUES ('en', 'English', 1), ('es', 'Español', 1)");
    echo "Seeded languages.\n";

    // 3. Add language column to users if not exists
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'language'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD language VARCHAR(5) DEFAULT 'en'");
        echo "Added language column to users.\n";
    } else {
        echo "language column already exists in users.\n";
    }

    echo "Migration Complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
