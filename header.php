<?php if (isset($_SESSION['user_id'])): ?>
    <a href="create_post.php">投稿</a>
    <a href="favorites.php">お気に入り</a>
    <a href="logout.php">ログアウト</a>
<?php else: ?>
    <a href="login.php">ログイン</a>
    <a href="register.php">新規登録</a>
<?php endif; ?>