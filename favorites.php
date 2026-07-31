<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$db = getDb();

$stmt = $db->prepare(
    'SELECT
        posts.id,
        posts.image_path,
        posts.caption,
        posts.style_category,
        posts.country,
        posts.city,
        users.username
     FROM favorites
     INNER JOIN posts
        ON favorites.post_id = posts.id
     INNER JOIN users
        ON posts.user_id = users.id
     WHERE favorites.user_id = :user_id
     ORDER BY favorites.created_at DESC'
);

$stmt->execute([
    ':user_id' => (int)$_SESSION['user_id'],
]);

$posts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>お気に入り一覧</h2>

    <?php if ($posts === []): ?>
        <p>お気に入りに登録した投稿はありません。</p>
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
                        <p class="post-user">
                            @<?= htmlspecialchars(
                                $post['username'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <h3>
                            <?= htmlspecialchars(
                                $post['caption'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars(
                                $post['country'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                            ・
                            <?= htmlspecialchars(
                                $post['city'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <p>
                            #<?= htmlspecialchars(
                                $post['style_category'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>