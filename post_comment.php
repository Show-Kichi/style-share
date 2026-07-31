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

$content = trim($_POST['content'] ?? '');

if ($postId === false || $postId === null || $postId < 1) {
    exit('投稿IDが正しくありません。');
}

if ($content === '') {
    exit('コメントを入力してください。');
}

if (mb_strlen($content) > 300) {
    exit('コメントは300文字以内で入力してください。');
}

$db = getDb();

// コメント先の投稿が存在するか確認
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

// コメントを保存
$stmt = $db->prepare(
    'INSERT INTO comments (
        post_id,
        user_id,
        content
    ) VALUES (
        :post_id,
        :user_id,
        :content
    )'
);

$stmt->execute([
    ':post_id' => $postId,
    ':user_id' => (int)$_SESSION['user_id'],
    ':content' => $content,
]);

header(
    'Location: post.php?id='
    . $postId
    . '&commented=1'
);

exit;