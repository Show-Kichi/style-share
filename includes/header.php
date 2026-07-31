<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

require_once __DIR__ . '/functions.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StyleShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header>
        <a href="index.php"><h1>StyleShare</h1></a>
        <nav>
            <a href="index.php">ホーム</a>
            <a href="map.php">コーデページ</a>
            <a href="ranking">ランキング</a>
            <a href="survey.php">アンケート</a>
            <a href="survey_results.php">アンケート結果</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="login-user">
                    <?= h((string)$_SESSION['username']) ?> さん
                </span>

                <a href="create_post.php">投稿</a>
                <a href="favorites.php">お気に入り</a>
                <a href="logout.php">ログアウト</a>
            <?php else: ?>
                <a href="login.php">ログイン</a>
                <a href="register.php">新規登録</a>
            <?php endif; ?>
        </nav>
    </header>
<main>

