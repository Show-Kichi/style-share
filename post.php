<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

require_once __DIR__ . '/includes/session.php';

$postId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if ($postId === false || $postId === null || $postId < 1) {
    exit('投稿IDが正しくありません。');
}

$db = getDb();

// 投稿情報を取得
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
     WHERE posts.id = :post_id'
);

$stmt->execute([
    ':post_id' => $postId,
]);

$post = $stmt->fetch();

if ($post === false) {
    http_response_code(404);
    exit('投稿が見つかりません。');
}
// お気に入り数を取得
$stmt = $db->prepare(
    'SELECT COUNT(*)
     FROM favorites
     WHERE post_id = :post_id'
);

$stmt->execute([
    ':post_id' => $postId,
]);

$favoriteCount = (int)$stmt->fetchColumn();

// ログインユーザがお気に入り済みか確認
$isFavorite = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare(
        'SELECT 1
         FROM favorites
         WHERE user_id = :user_id
           AND post_id = :post_id'
    );

    $stmt->execute([
        ':user_id' => (int)$_SESSION['user_id'],
        ':post_id' => $postId,
    ]);

    $isFavorite = $stmt->fetch() !== false;
}

// コメント一覧を取得
$stmt = $db->prepare(
    'SELECT
        comments.content,
        comments.created_at,
        users.username
     FROM comments
     INNER JOIN users
        ON comments.user_id = users.id
     WHERE comments.post_id = :post_id
     ORDER BY comments.created_at ASC, comments.id ASC'
);

$stmt->execute([
    ':post_id' => $postId,
]);

$comments = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <p>
        <a href="index.php">← 投稿一覧へ戻る</a>
    </p>

    <article class="post-detail">
        <img
            src="<?= htmlspecialchars(
                $post['image_path'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            alt="コーディネート画像"
        >

        <div class="post-detail-body">
            <p class="post-user">
                @<?= htmlspecialchars(
                    $post['username'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <h2>
                <?= htmlspecialchars(
                    $post['caption'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h2>

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
            <p>
                お気に入り数：<?= $favoriteCount ?>
            </p>
            <?php if (isset($_SESSION['user_id'])): ?>
    <form
        action="favorite.php"
        method="post"
        class="favorite-form"
    >
    <input
    type="hidden"
    name="csrf_token"
    value="<?= h(getCsrfToken()) ?>"
>
        <input
            type="hidden"
            name="post_id"
            value="<?= (int)$post['id'] ?>"
        >

        <button type="submit">
            <?= $isFavorite
                ? 'お気に入りを解除'
                : 'お気に入りに追加' ?>
        </button>
    </form>
<?php else: ?>
    <p>
        お気に入り登録には
        <a href="login.php">ログイン</a>
        が必要です。
    </p>
<?php endif; ?>
        </div>
    </article>
</section>

<section class="comment-section">
    <h2>コメント</h2>

    <?php if (isset($_GET['commented'])): ?>
        <p class="success-message">
            コメントを投稿しました。
        </p>
    <?php endif; ?>

    <?php if ($comments === []): ?>
        <p>まだコメントはありません。</p>
    <?php else: ?>
        <div class="comment-list">
            <?php foreach ($comments as $comment): ?>
                <article class="comment-card">
                    <p class="comment-user">
                        @<?= htmlspecialchars(
                            $comment['username'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p>
                        <?= nl2br(
                            htmlspecialchars(
                                $comment['content'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        ) ?>
                    </p>

                    <time>
                        <?= htmlspecialchars(
                            $comment['created_at'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </time>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <h3>コメントを投稿する</h3>

        <form
            action="post_comment.php"
            method="post"
            class="comment-form"
        >
        <input
    type="hidden"
    name="csrf_token"
    value="<?= h(getCsrfToken()) ?>"
>

            <input
                type="hidden"
                name="post_id"
                value="<?= (int)$post['id'] ?>"
            >

            <div>
                <label for="content">コメント内容</label>

                <textarea
                    id="content"
                    name="content"
                    rows="4"
                    maxlength="300"
                    required
                ></textarea>
            </div>

            <button type="submit">
                コメントする
            </button>
        </form>
    <?php else: ?>
        <p>
            コメントするには
            <a href="login.php">ログイン</a>
            が必要です。
        </p>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>