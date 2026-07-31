<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$styles = [
    'ストリート',
    'カジュアル',
    'モード',
    '古着',
    'きれいめ',
];

$db = getDb();

$stmt = $db->query(
    'SELECT
        style_category,
        COUNT(*) AS vote_count
     FROM survey_answers
     GROUP BY style_category'
);

$databaseResults = $stmt->fetchAll();

// 回答が0件のスタイルも表示するため、最初に0を設定
$results = [];

foreach ($styles as $style) {
    $results[$style] = 0;
}

// データベースの集計結果を入れる
foreach ($databaseResults as $row) {
    $style = $row['style_category'];

    if (array_key_exists($style, $results)) {
        $results[$style] = (int)$row['vote_count'];
    }
}

$totalVotes = array_sum($results);
$maximumVotes = max($results);

require_once __DIR__ . '/includes/header.php';
?>

<section>
    <h2>アンケート集計結果</h2>

    <?php if (isset($_GET['voted'])): ?>
        <p class="success-message">
            アンケートへの回答を保存しました。
        </p>
    <?php endif; ?>

    <p>
        総回答数：
        <strong><?= $totalVotes ?></strong>
    </p>

    <?php if ($totalVotes === 0): ?>
        <p>まだアンケートへの回答がありません。</p>
    <?php else: ?>
        <div class="survey-chart">
            <?php foreach ($results as $style => $voteCount): ?>
                <?php
                $percentage = $totalVotes > 0
                    ? round(($voteCount / $totalVotes) * 100, 1)
                    : 0;

                $barWidth = $maximumVotes > 0
                    ? ($voteCount / $maximumVotes) * 100
                    : 0;
                ?>

                <div class="chart-row">
                    <div class="chart-label">
                        <?= htmlspecialchars(
                            $style,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </div>

                    <div class="chart-area">
                        <div
                            class="chart-bar"
                            style="width: <?= $barWidth ?>%;"
                        >
                            <?php if ($voteCount > 0): ?>
                                <?= $voteCount ?>票
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="chart-value">
                        <?= $voteCount ?>票
                        （<?= $percentage ?>%）
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p>
        <a href="survey.php">
            アンケートへ戻る
        </a>
    </p>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>