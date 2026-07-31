<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$styles = [
    'ストリート',
    'カジュアル',
    'モード',
    '古着',
    'きれいめ',
];

$error = '';
$currentAnswer = '';

$db = getDb();

// ログイン中なら、現在の回答を取得する
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare(
        'SELECT style_category
         FROM survey_answers
         WHERE user_id = :user_id'
    );

    $stmt->execute([
        ':user_id' => (int)$_SESSION['user_id'],
    ]);

    $answer = $stmt->fetch();

    if ($answer !== false) {
        $currentAnswer = $answer['style_category'];
    }
}

// アンケートが送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $selectedStyle = $_POST['style_category'] ?? '';

    if (!in_array($selectedStyle, $styles, true)) {
        $error = 'ファッションスタイルを選択してください。';
    } else {
        $userId = (int)$_SESSION['user_id'];

        // すでに回答済みか確認する
        $stmt = $db->prepare(
            'SELECT id
             FROM survey_answers
             WHERE user_id = :user_id'
        );

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        $existingAnswer = $stmt->fetch();

        if ($existingAnswer === false) {
            // 初回回答
            $stmt = $db->prepare(
                'INSERT INTO survey_answers (
                    user_id,
                    style_category
                ) VALUES (
                    :user_id,
                    :style_category
                )'
            );
        } else {
            // 回答の変更
            $stmt = $db->prepare(
                'UPDATE survey_answers
                 SET style_category = :style_category,
                     created_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id'
            );
        }

        $stmt->execute([
            ':user_id' => $userId,
            ':style_category' => $selectedStyle,
        ]);

        header('Location: survey_results.php?voted=1');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>ファッションアンケート</h2>

    <p>
        あなたが一番好きなファッションスタイルを
        教えてください。
    </p>

    <?php if ($error !== ''): ?>
        <p class="error-message">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($currentAnswer !== ''): ?>
            <p>
                現在の回答：
                <strong>
                    <?= htmlspecialchars(
                        $currentAnswer,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>
            </p>

            <p>回答は何度でも変更できます。</p>
        <?php endif; ?>

        <form action="survey.php" method="post">
            <input
            type="hidden"
            name="csrf_token"
            value="<?= h(getCsrfToken()) ?>"
            >
            <fieldset>
                <legend>
                    好きなファッションスタイル
                </legend>

                <?php foreach ($styles as $style): ?>
                    <label class="radio-option">
                        <input
                            type="radio"
                            name="style_category"
                            value="<?= htmlspecialchars(
                                $style,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            <?= $currentAnswer === $style
                                ? 'checked'
                                : '' ?>
                            required
                        >

                        <?= htmlspecialchars(
                            $style,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <button type="submit">回答する</button>
        </form>
    <?php else: ?>
        <div class="login-guide">
            <p>
                アンケートへの回答は登録ユーザ限定です。
            </p>

            <p>
                <a href="login.php">ログイン</a>
                または
                <a href="register.php">新規登録</a>
                してください。
            </p>
        </div>
    <?php endif; ?>

    <p>
        <a href="survey_result.php">
            アンケート結果を見る
        </a>
    </p>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
