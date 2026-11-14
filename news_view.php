<?php
$page = 'news';
include 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT n.*, a.name AS assoc_name FROM news n LEFT JOIN associations a ON n.association_id = a.id WHERE n.id = ?");
$st->execute([$id]);
$n = $st->fetch();

if (!$n) {
    echo "<div class='container' style='padding:100px 0; text-align:center;'><h2>خبر مورد نظر یافت نشد!</h2><a href='news.php' class='btn-primary back-btn'>بازگشت به اخبار</a></div>";
    include 'includes/footer.php';
    exit;
}

$rel = $pdo->prepare("SELECT * FROM news WHERE id <> ? ORDER BY (association_id = ?) DESC, created_at DESC, id DESC LIMIT 4");
$rel->execute([$id, (int)$n['association_id']]);
$related = $rel->fetchAll();
?>

<article class="news-detail container">
    <?= newsImageHtml($n['image']) ?>
    <div class="news-detail-body">
        <h1><?= e($n['title']) ?></h1>
        <div class="news-detail-meta">
            <span>📅 <?= formatDate($n['created_at']) ?></span>
            <?php if ($n['assoc_name']): ?>
                <a href="association.php?id=<?= (int)$n['association_id'] ?>">🏛 <?= e($n['assoc_name']) ?></a>
            <?php else: ?>
                <span>🏛 خبر عمومی</span>
            <?php endif; ?>
        </div>
        <div class="news-text">
            <?= !empty($n['content']) ? nl2br(e($n['content'])) : '<p style="color:#888;">متن کامل برای این خبر ثبت نشده است.</p>' ?>
        </div>
        <a href="news.php" class="btn-primary back-btn">← بازگشت به اخبار</a>
    </div>
</article>

<?php if ($related): ?>
<section class="latest-news container">
    <div class="section-header"><h3>اخبار مرتبط</h3></div>
    <div class="news-list-grid">
        <?php foreach ($related as $r): ?><?= newsCard($r) ?><?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
