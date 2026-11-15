<?php 
$page = 'home';
include 'includes/header.php';

try {
    $latest = $pdo->query("SELECT * FROM news ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $latest = [];
}

try {
    $assocs = $pdo->query("SELECT * FROM associations ORDER BY id ASC LIMIT 12")->fetchAll();
} catch (Exception $e) {
    $assocs = [];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg-layer" style="background-image: url('assets/images/main-hero.jpg');"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                سامانه خبری انجمن‌های علمی<br>
                <span class="hero-title-sub">دانشگاه اراک</span>
            </h1>
            <p class="hero-desc">با ما در جریان تازه‌ترین اخبار و رویدادهای علمی دانشگاه باشید.</p>
            <div class="hero-accent-dash"></div>
        </div>
    </div>
</section>

<!-- News Ticker -->
<?= renderTicker($pdo, 0) ?>

<!-- Latest News Section (Asymmetrical Grid) -->
<section class="latest-news-section container">
    <div class="section-top-bar">
        <div class="section-title-wrap">
            <h2 class="section-title">جدیدترین اخبار</h2>
            <div class="title-underline"></div>
        </div>
        <a href="news.php" class="btn-pill-dark">
            <span>مشاهده همه اخبار</span>
            <?= svgIcon('arrow-left', 'btn-icon-arrow', 15) ?>
        </a>
    </div>
    
    <?php if ($latest): ?>
    <div class="featured-news-layout">
        <!-- Big Card on Right in RTL -->
        <div class="featured-col-big">
            <?= newsCard($latest[0], true) ?>
        </div>
        <!-- 2x2 Small Grid on Left in RTL -->
        <div class="featured-col-grid">
            <?php for ($i = 1; $i < count($latest); $i++): ?>
                <?= newsCard($latest[$i], false) ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="empty-state-box">
            <?= svgIcon('building', 'empty-icon', 48) ?>
            <p>هنوز خبری در سامانه ثبت نشده است.</p>
        </div>
    <?php endif; ?>
</section>

<!-- Associations Section (Soft rounded box with white cards) -->
<section class="associations-section container">
    <div class="associations-inner-box">
        <div class="section-top-bar">
            <div class="section-title-wrap">
                <h2 class="section-title">انجمن‌های علمی دانشگاه اراک</h2>
                <div class="title-underline"></div>
            </div>
            <a href="associations.php" class="btn-pill-dark">
                <span>مشاهده همه انجمن‌ها</span>
                <?= svgIcon('arrow-left', 'btn-icon-arrow', 15) ?>
            </a>
        </div>

        <div class="associations-grid-row">
            <?php if ($assocs): foreach ($assocs as $a): ?>
                <?= assocCard($a) ?>
            <?php endforeach; else: ?>
                <div class="empty-state-box">
                    <?= svgIcon('building', 'empty-icon', 48) ?>
                    <p>هنوز انجمنی ثبت نشده است.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
