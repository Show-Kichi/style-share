<?php
declare(strict_types=1);

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
        posts.created_at,
        users.username
     FROM posts
     INNER JOIN users
        ON posts.user_id = users.id
     ORDER BY posts.created_at DESC, posts.id DESC'
);

$stmt->execute();
$posts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>世界のコーディネート</h2>

    <p>
        国や地域を越えて、さまざまなファッションを
        発見できるコーデ掲示板です。
    </p>
</section>

<section>
    <h2>新着コーディネート</h2>

    <?php if ($posts === []): ?>
        <p>まだ投稿がありません。</p>
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