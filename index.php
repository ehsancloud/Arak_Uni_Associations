<?php 
$page = 'home';
include 'includes/header.php';

$latest = $pdo->query("SELECT * FROM news ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
$assocs = $pdo->query("SELECT * FROM associations ORDER BY id ASC LIMIT 10")->fetchAll();
?>

<section class="hero-section" style="background-image: url('assets/images/main-hero.jpg');">
    <div class="container">
        <div class="hero-text">
            <h2>سامانه خبری انجمن‌های علمی<br>دانشگاه اراک</h2>
            <p>با ما در جریان تازه‌ترین اخبار و رویدادهای علمی دانشگاه باشید.</p>
        </div>
    </div>
</section>

<?= renderTicker($pdo, 0) ?>

<section class="latest-news container">
    <div class="section-header">
        <h3>جدیدترین اخبار</h3>
        <a href="news.php" class="btn-primary">مشاهده همه اخبار ←</a>
    </div>
    
    <?php if ($latest): ?>
    <div class="news-grid">
        <?= newsCard($latest[0], true) ?>
        <div class="small-news-wrapper">
            <?php for ($i = 1; $i < count($latest); $i++): ?>
                <?= newsCard($latest[$i]) ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="empty-box">هنوز خبری ثبت نشده است.</div>
    <?php endif; ?>
</section>

<section class="associations-list container">
    <div class="section-header">
        <h3>انجمن‌های علمی دانشگاه اراک</h3>
        <a href="associations.php" class="btn-primary">مشاهده همه انجمن‌ها ←</a>
    </div>
    <div class="associations-grid">
        <?php if ($assocs): foreach ($assocs as $a): ?>
            <?= assocCard($a) ?>
        <?php endforeach; else: ?>
            <div class="empty-box">هنوز انجمنی ثبت نشده است.</div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
