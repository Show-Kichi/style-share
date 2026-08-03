<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$errors = [];

$caption = '';
$styleCategory = '';
$country = '';
$city = '';

$allowedStyles = [
    'ストリート',
    'カジュアル',
    'モード',
    '古着',
    'きれいめ',
];

$allowedCountries = [
    '日本',
    '韓国',
    'アメリカ',
    'フランス',
    'イギリス',
    'その他',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/functions.php';

    verifyCsrfToken($_POST['csrf_token'] ?? null);

    $caption = trim($_POST['caption'] ?? '');
    $styleCategory = $_POST['style_category'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = trim($_POST['city'] ?? '');

    if (preg_match('/\A.{0,50}\z/u', $city) !== 1) {
        $errors[] = '都市名は50文字以内で入力してください。';
    }

    if ($caption === '') {
        $errors[] = 'コーデの説明を入力してください。';
   } elseif (preg_match('/\A.{0,200}\z/u', $caption) !== 1) {
        $errors[] = '説明は200文字以内で入力してください。';
    }

    if (!in_array($styleCategory, $allowedStyles, true)) {
        $errors[] = 'ジャンルを選択してください。';
    }

    if (!in_array($country, $allowedCountries, true)) {
        $errors[] = '国を選択してください。';
    }

    if (
        !isset($_FILES['image'])
        || $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {
        $errors[] = '画像を選択してください。';
    }

    $imagePath = '';

    if ($errors === []) {
        $temporaryPath = $_FILES['image']['tmp_name'];

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mimeType])) {
            $errors[] = 'JPEG、PNG、WebP画像のみ投稿できます。';
        }

        if ($_FILES['image']['size'] <= 0) {
            $errors[] = '画像ファイルが空です。';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = '画像サイズは5MB以下にしてください。';
        }

        if ($errors === []) {
            $extension = $extensions[$mimeType];
            $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

            $uploadDirectory = __DIR__ . '/uploads/';
            $savePath = $uploadDirectory . $fileName;

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            if (!move_uploaded_file($temporaryPath, $savePath)) {
                $errors[] = '画像の保存に失敗しました。';
            } else {
                $imagePath = 'uploads/' . $fileName;
            }
        }
    }

    if ($errors === []) {
        $db = getDb();

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

        $stmt->execute([
            ':user_id' => (int)$_SESSION['user_id'],
            ':image_path' => $imagePath,
            ':caption' => $caption,
            ':style_category' => $styleCategory,
            ':country' => $country,
            ':city' => $city,
        ]);

        header('Location: index.php?posted=1');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>コーデを投稿する</h2>

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

    <form
        action="create_post.php"
        method="post"
        enctype="multipart/form-data"
    >
    <input
    type="hidden"
    name="csrf_token"
    value="<?= h(getCsrfToken()) ?>"
>
        <div>
            <label for="image">コーデ画像</label>
            <input
                type="file"
                id="image"
                name="image"
                accept="image/jpeg,image/png,image/webp"
                required
            >
        </div>

        <div>
            <label for="caption">コーデの説明</label>
            <textarea
                id="caption"
                name="caption"
                rows="4"
                maxlength="200"
                required
            ><?= htmlspecialchars(
                $caption,
                ENT_QUOTES,
                'UTF-8'
            ) ?></textarea>
        </div>

        <div>
            <label for="style_category">ジャンル</label>

            <select
                id="style_category"
                name="style_category"
                required
            >
                <option value="">選択してください</option>

                <?php foreach ($allowedStyles as $style): ?>
                    <option
                        value="<?= htmlspecialchars(
                            $style,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $styleCategory === $style
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $style,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
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

        <div>
            <label for="city">都市</label>
            <input
                type="text"
                id="city"
                name="city"
                value="<?= htmlspecialchars(
                    $city,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                maxlength="50"
            >
        </div>

        <button type="submit">投稿する</button>
    </form>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
