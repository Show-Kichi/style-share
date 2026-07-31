<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
                <a href="favorite.php">お気に入り</a>
                <a href="profile.php">プロフィール</a>
                <a href="login.php">ログイン</a>
            <?php else: ?>
                <a href="logout.php">ログアウト</a>
                <a href="register.php">新規登録</a>
            <?php endif; ?>
        </nav>
    </header>
<main>

