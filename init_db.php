<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

// ブラウザから誤って何度も実行されないようにする
if (PHP_SAPI !== 'cli') {
    exit('init_db.phpはPowerShellから実行してください。');
}

try {
    $db = getDb();

    // ユーザ
    $db->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            country TEXT NOT NULL DEFAULT "",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    // コーディネート投稿
    $db->exec(
        'CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            image_path TEXT NOT NULL,
            caption TEXT NOT NULL,
            style_category TEXT NOT NULL,
            country TEXT NOT NULL,
            city TEXT NOT NULL DEFAULT "",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        )'
    );

    // コメント
    $db->exec(
        'CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (post_id)
                REFERENCES posts(id)
                ON DELETE CASCADE,

            FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        )'
    );

    // お気に入り
    $db->exec(
        'CREATE TABLE IF NOT EXISTS favorites (
            user_id INTEGER NOT NULL,
            post_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY (user_id, post_id),

            FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE,

            FOREIGN KEY (post_id)
                REFERENCES posts(id)
                ON DELETE CASCADE
        )'
    );

    // アンケート回答
    $db->exec(
        'CREATE TABLE IF NOT EXISTS survey_answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            style_category TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        )'
    );

    echo "データベースとテーブルを作成しました。\n";
    echo "保存場所: " . __DIR__ . "/data/style_share.db\n";
} catch (PDOException $e) {
    echo "データベースの作成に失敗しました。\n";
    echo $e->getMessage() . "\n";
    exit(1);
}