<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

require_once __DIR__ . '/includes/session.php';

$errors = [];
$username = '';
$country = '';

$allowedCountries = [
    '日本',
    '韓国',
    'アメリカ',
    'フランス',
    'イギリス',
    'その他',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $country = $_POST['country'] ?? '';

    // ユーザ名の確認
    if ($username === '') {
        $errors[] = 'ユーザ名を入力してください。';
    } elseif (preg_match('/\A.{2,30}\z/u', $username) !== 1) {
        $errors[] = 'ユーザ名は2文字以上30文字以内で入力してください。';
    }

    // パスワードの確認
    if ($password === '') {
        $errors[] = 'パスワードを入力してください。';
    } elseif (strlen($password) < 8) {
        $errors[] = 'パスワードは8文字以上で入力してください。';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = '確認用パスワードが一致していません。';
    }

    // 国の確認
    if (!in_array($country, $allowedCountries, true)) {
        $errors[] = '国を選択してください。';
    }

    if ($errors === []) {
        $db = getDb();

        // 同じユーザ名が登録済みか確認する
        $stmt = $db->prepare(
            'SELECT id FROM users WHERE username = :username'
        );
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch() !== false) {
            $errors[] = 'そのユーザ名はすでに使用されています。';
        } else {
            // パスワードを安全な形式に変換する
            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // usersテーブルへ登録する
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

            $stmt->bindValue(
                ':username',
                $username,
                PDO::PARAM_STR
            );
            $stmt->bindValue(
                ':password_hash',
                $passwordHash,
                PDO::PARAM_STR
            );
            $stmt->bindValue(
                ':country',
                $country,
                PDO::PARAM_STR
            );

            $stmt->execute();

            header('Location: login.php?registered=1');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>新規ユーザ登録</h2>

    <?php if ($errors !== []): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="post">
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
                maxlength="30"
                required
            >
        </div>

        <div>
            <label for="password">パスワード</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required
            >
            <p>8文字以上で入力してください。</p>
        </div>

        <div>
            <label for="password_confirm">
                パスワード（確認）
            </label>
            <input
                type="password"
                id="password_confirm"
                name="password_confirm"
                minlength="8"
                required
            >
        </div>

        <div>
            <label for="country">国</label>
            <select id="country" name="country" required>
                <option value="">選択してください</option>

                <?php foreach ($allowedCountries as $countryOption): ?>
                    <option
                        value="<?= htmlspecialchars(
                            $countryOption,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $country === $countryOption
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $countryOption,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">登録する</button>
    </form>

    <p>
        すでに登録済みの方は
        <a href="login.php">ログイン</a>
    </p>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>