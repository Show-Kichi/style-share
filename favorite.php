<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$postId = filter_input(
    INPUT_POST,
    'post_id',
    FILTER_VALIDATE_INT
);

if ($postId === false || $postId === null || $postId < 1) {
    exit('投稿IDが正しくありません。');
}

$userId = (int)$_SESSION['user_id'];
$db = getDb();

// 投稿が存在するか確認
$stmt = $db->prepare(
    'SELECT id
     FROM posts
     WHERE id = :post_id'
);

$stmt->execute([
    ':post_id' => $postId,
]);

if ($stmt->fetch() === false) {
    exit('投稿が見つかりません。');
}

// すでにお気に入り済みか確認
$stmt = $db->prepare(
    'SELECT 1
     FROM favorites
     WHERE user_id = :user_id
       AND post_id = :post_id'
);

$stmt->execute([
    ':user_id' => $userId,
    ':post_id' => $postId,
]);

$isFavorite = $stmt->fetch() !== false;

if ($isFavorite) {
    // 登録済みなら解除
    $stmt = $db->prepare(
        'DELETE FROM favorites
         WHERE user_id = :user_id
           AND post_id = :post_id'
    );
} else {
    // 未登録なら追加
    $stmt = $db->prepare(
        'INSERT INTO favorites (
            user_id,
            post_id
        ) VALUES (
            :user_id,
            :post_id
        )'
    );
}

$stmt->execute([
    ':user_id' => $userId,
    ':post_id' => $postId,
]);

header('Location: post.php?id=' . $postId);
exit;