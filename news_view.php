<?php
$page = 'news';
include 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT n.*, a.name AS assoc_name FROM news n LEFT JOIN associations a ON n.association_id = a.id WHERE n.id = ?");
$st->execute([$id]);
$n = $st->fetch();

if (!$n) {
    echo "<div class='container' style='padding:120px 20px; text-align:center;'>
            <h2>خبر مورد نظر یافت نشد!</h2>
            <a href='news.php' class='btn-pill-dark' style='display:inline-flex; margin-top:20px;'>
                " . svgIcon('arrow-left', '', 16) . " بازگشت به لیست اخبار
            </a>
          </div>";
    include 'includes/footer.php';
    exit;
}

$rel = $pdo->prepare("SELECT * FROM news WHERE id <> ? ORDER BY (association_id = ?) DESC, created_at DESC, id DESC LIMIT 4");
$rel->execute([$id, (int)$n['association_id']]);
$related = $rel->fetchAll();
?>

<article class="news-single-page container">
    <div class="news-single-card">
        <div class="news-single-media">
            <?= newsImageHtml($n['image'], $n['title']) ?>
        </div>
        <div class="news-single-body">
            <h1 class="news-single-title"><?= e($n['title']) ?></h1>
            
            <div class="news-single-meta">
                <span class="meta-item">
                    <?= svgIcon('calendar', 'meta-icon', 16) ?>
                    <span><?= formatDate($n['created_at']) ?></span>
                </span>
                
                <?php if (!empty($n['assoc_name'])): ?>
                    <a href="association.php?id=<?= (int)$n['association_id'] ?>" class="meta-item meta-assoc">
                        <?= svgIcon('building', 'meta-icon', 16) ?>
                        <span><?= e($n['assoc_name']) ?></span>
                    </a>
                <?php else: ?>
                    <span class="meta-item meta-assoc">
                        <?= svgIcon('building', 'meta-icon', 16) ?>
                        <span>خبر عمومی دانشگاه</span>
                    </span>
                <?php endif; ?>
            </div>

            <div class="news-single-content">
                <?= !empty($n['content']) ? nl2br(e($n['content'])) : '<p class="text-muted">متن کامل برای این خبر ثبت نشده است.</p>' ?>
            </div>

            <div class="news-single-footer">
                <a href="news.php" class="btn-pill-dark">
                    <?= svgIcon('arrow-left', '', 15) ?>
                    <span>بازگشت به همه اخبار</span>
                </a>
            </div>
        </div>
    </div>
</article>

<?php if ($related): ?>
<section class="latest-news-section container" style="margin-top:60px;">
    <div class="section-top-bar">
        <div class="section-title-wrap">
            <h2 class="section-title">اخبار مرتبط</h2>
            <div class="title-underline"></div>
        </div>
    </div>
    <div class="news-cards-grid">
        <?php foreach ($related as $r): ?>
            <?= newsCard($r, false) ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
