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
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="site-logo">
            StyleShare
        </a>

        <nav class="site-nav">
            <a href="index.php">Discover</a>
            <a href="survey.php">Survey</a>
            <a href="survey_results.php">Results</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="login-user">
                    <?= h((string)$_SESSION['username']) ?>
                </span>

                <a href="create_post.php">Post</a>
                <a href="favorites.php">Favorites</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="nav-register">
                    Join
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>

