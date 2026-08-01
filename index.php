<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$db = getDb();

$allowedCountries = [
    '日本',
    '韓国',
    'アメリカ',
    'フランス',
    'イギリス',
    'その他',
];

$allowedStyles = [
    'ストリート',
    'カジュアル',
    'モード',
    '古着',
    'きれいめ',
];

$selectedCountry = $_GET['country'] ?? '';
$selectedStyle = $_GET['style_category'] ?? '';

// 不正な値が送られた場合は空に戻す
if (
    $selectedCountry !== ''
    && !in_array($selectedCountry, $allowedCountries, true)
) {
    $selectedCountry = '';
}

if (
    $selectedStyle !== ''
    && !in_array($selectedStyle, $allowedStyles, true)
) {
    $selectedStyle = '';
}

$sql = '
    SELECT
        posts.id,
        posts.image_path,
        posts.caption,
        posts.style_category,
        posts.country,
        posts.city,
        posts.created_at,
        users.username
    FROM posts
    INNER JOIN users
        ON posts.user_id = users.id
';

$conditions = [];
$parameters = [];

if ($selectedCountry !== '') {
    $conditions[] = 'posts.country = :country';
    $parameters[':country'] = $selectedCountry;
}

if ($selectedStyle !== '') {
    $conditions[] = 'posts.style_category = :style_category';
    $parameters[':style_category'] = $selectedStyle;
}

if ($conditions !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY posts.created_at DESC, posts.id DESC';

$stmt = $db->prepare($sql);
$stmt->execute($parameters);

$posts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['posted'])): ?>
    <p class="success-message">
        コーディネートを投稿しました。
    </p>
<?php endif; ?>

<section class="hero">
    <div class="hero-shape" aria-hidden="true"></div>

    <div class="hero-content">
        <p class="hero-label">
            GLOBAL FASHION COMMUNITY
        </p>

        <h1>
            DISCOVER<br>
            YOUR NEXT STYLE
        </h1>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_post.php" class="hero-button">
                コーデを投稿する
            </a>
        <?php else: ?>
            <a href="register.php" class="hero-button">
                StyleShareに参加する
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="filter-section">
    <h2>コーデを絞り込む</h2>

    <form action="index.php" method="get" class="filter-form">
        <div>
            <label for="country">国</label>

            <select id="country" name="country">
                <option value="">すべての国</option>

                <?php foreach ($allowedCountries as $country): ?>
                    <option
                        value="<?= htmlspecialchars(
                            $country,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $selectedCountry === $country
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $country,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="style_category">ジャンル</label>

            <select
                id="style_category"
                name="style_category"
            >
                <option value="">すべてのジャンル</option>

                <?php foreach ($allowedStyles as $style): ?>
                    <option
                        value="<?= htmlspecialchars(
                            $style,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $selectedStyle === $style
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

        <div class="filter-buttons">
            <button type="submit">絞り込む</button>
            <a href="index.php">すべて表示</a>
        </div>
    </form>
</section>

<section>
    <h2>新着コーディネート</h2>
    <p>
        表示件数：
        <strong><?= count($posts) ?>件</strong>
    </p>
    <?php if ($posts === []): ?>
    <p>条件に合うコーディネートはありません。</p>
    <?php else: ?>
        <div class="post-grid">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <a href="post.php?id=<?= (int)$post['id'] ?>">
                        <img
                            src="<?= htmlspecialchars(
                                $post['image_path'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="コーディネート画像"
                        >
                    </a>

                    <div class="post-card-body">
                        <div class="post-meta">
                            <span>
                                <?= h($post['country']) ?>
                                ・
                                <?= h($post['city']) ?>
                            </span>

                            <span>
                                #<?= h($post['style_category']) ?>
                            </span>
                        </div>

                        <h3>
                            <?= h($post['caption']) ?>
                        </h3>

                        <p class="post-user">
                            by @<?= h($post['username']) ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script src="assets/js/hero-morph.js"></script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
