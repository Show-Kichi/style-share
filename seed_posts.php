<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

if (PHP_SAPI !== 'cli') {
    exit('seed_posts.phpはPowerShellから実行してください。');
}

$db = getDb();

try {
    $db->beginTransaction();

    // 初期投稿用ユーザを探す
    $stmt = $db->prepare(
        'SELECT id FROM users WHERE username = :username'
    );
    $stmt->execute([
        ':username' => 'styleshare_staff',
    ]);

    $user = $stmt->fetch();

    // 存在しなければ初期投稿用ユーザを作る
    if ($user === false) {
        $stmt = $db->prepare(
            'INSERT INTO users (
                username,
                password_hash,
                country
            ) VALUES (
                :username,
                :password_hash,
                :country
            )'
        );

        $stmt->execute([
            ':username' => 'styleshare_staff',
            ':password_hash' => password_hash(
                'StyleShare_2026!',
                PASSWORD_DEFAULT
            ),
            ':country' => '日本',
        ]);

        $userId = (int)$db->lastInsertId();
    } else {
        $userId = (int)$user['id'];
    }

    // すでに初期投稿があるか確認
    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM posts WHERE user_id = :user_id'
    );
    $stmt->execute([
        ':user_id' => $userId,
    ]);

    $postCount = (int)$stmt->fetchColumn();

    if ($postCount > 0) {
        throw new RuntimeException(
            '初期投稿はすでに登録されています。'
        );
    }

    $posts = [
        ['coordinate01.png', '東京のモノトーンストリートコーデ', 'ストリート', '日本', '東京'],
        ['coordinate02.png', '落ち着いた色を使ったカジュアルコーデ', 'カジュアル', '日本', '大阪'],
        ['coordinate03.png', '黒を中心にまとめたモードスタイル', 'モード', '日本', '東京'],
        ['coordinate04.png', '古着を組み合わせたレイヤードコーデ', '古着', '日本', '名古屋'],

        ['coordinate05.png', 'ソウルで人気のオーバーサイズコーデ', 'ストリート', '韓国', 'ソウル'],
        ['coordinate06.png', 'シンプルにまとめた韓国カジュアル', 'カジュアル', '韓国', '釜山'],
        ['coordinate07.png', 'シャープなシルエットの黒コーデ', 'モード', '韓国', 'ソウル'],
        ['coordinate08.png', '色使いを楽しむヴィンテージコーデ', '古着', '韓国', '仁川'],

        ['coordinate09.png', 'ニューヨークの自由なストリートスタイル', 'ストリート', 'アメリカ', 'ニューヨーク'],
        ['coordinate10.png', '休日に着たいリラックスコーデ', 'カジュアル', 'アメリカ', 'ロサンゼルス'],
        ['coordinate11.png', '都会的なオールブラックコーデ', 'モード', 'アメリカ', 'ニューヨーク'],
        ['coordinate12.png', 'デニムを使ったアメリカ古着スタイル', '古着', 'アメリカ', 'シカゴ'],

        ['coordinate13.png', 'パリの洗練されたストリートコーデ', 'ストリート', 'フランス', 'パリ'],
        ['coordinate14.png', '自然体で着られるフレンチカジュアル', 'カジュアル', 'フランス', 'リヨン'],
        ['coordinate15.png', '形の美しさを意識したモードコーデ', 'モード', 'フランス', 'パリ'],
    ];

    $stmt = $db->prepare(
        'INSERT INTO posts (
            user_id,
            image_path,
            caption,
            style_category,
            country,
            city
        ) VALUES (
            :user_id,
            :image_path,
            :caption,
            :style_category,
            :country,
            :city
        )'
    );

    foreach ($posts as $post) {
        $stmt->execute([
            ':user_id' => $userId,
            ':image_path' => 'assets/images/' . $post[0],
            ':caption' => $post[1],
            ':style_category' => $post[2],
            ':country' => $post[3],
            ':city' => $post[4],
        ]);
    }

    $db->commit();

    echo "初期投稿15件を登録しました。\n";
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo $e->getMessage() . "\n";
    exit(1);
}
