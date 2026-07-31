<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// すでにログインしている場合はホームへ移動
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'ユーザ名とパスワードを入力してください。';
    } else {
        $db = getDb();

        $stmt = $db->prepare(
            'SELECT id, username, password_hash
             FROM users
             WHERE username = :username'
        );

        $stmt->bindValue(
            ':username',
            $username,
            PDO::PARAM_STR
        );

        $stmt->execute();
        $user = $stmt->fetch();

        if (
            $user !== false
            && password_verify($password, $user['password_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];

            header('Location: index.php');
            exit;
        }

        $error = 'ユーザ名またはパスワードが違います。';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>ログイン</h2>

    <?php if (isset($_GET['registered'])): ?>
        <p class="success-message">
            ユーザ登録が完了しました。ログインしてください。
        </p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="error-message">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div>
            <label for="username">ユーザ名</label>

            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars(
                    $username,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >
        </div>

        <div>
            <label for="password">パスワード</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <button type="submit">ログイン</button>
    </form>

    <p>
        アカウントを持っていない方は
        <a href="register.php">新規登録</a>
    </p>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>