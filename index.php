<?php 
$page = 'home';
include 'includes/header.php';

try {
    $latest = $pdo->query("SELECT * FROM news ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $latest = [];
}

try {
    $upcomingEvents = $pdo->query("SELECT e.*, a.name AS assoc_name 
                                    FROM events e 
                                    LEFT JOIN associations a ON e.association_id = a.id 
                                    WHERE e.is_published = 1 AND e.starts_at >= NOW() 
                                    ORDER BY e.starts_at ASC LIMIT 4")->fetchAll();
} catch (Exception $e) {
    $upcomingEvents = [];
}

try {
    $assocs = $pdo->query("SELECT * FROM associations ORDER BY id ASC LIMIT 12")->fetchAll();
} catch (Exception $e) {
    $assocs = [];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg-layer" style="background-image: url('assets/images/science-faculty.jpg');"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                سامانه خبری انجمن‌های علمی<br>
                <span class="hero-title-sub">دانشگاه اراک</span>
            </h1>
            <p class="hero-desc">با ما در جریان تازه‌ترین اخبار و رویدادهای علمی دانشگاه باشید.</p>
            <div class="hero-actions">
                <a href="membership.php" class="btn-primary">
                    <?= svgIcon('user-plus', '', 16) ?>
                    <span>عضویت در انجمن</span>
                </a>
                <a href="events.php" class="btn-secondary">
                    <?= svgIcon('calendar', '', 16) ?>
                    <span>مشاهده رویدادها</span>
                </a>
                <a href="cooperation.php" class="btn-secondary">
                    <?= svgIcon('send', '', 15) ?>
                    <span>ثبت درخواست همکاری</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- News Ticker -->
<?= renderTicker($pdo, 0) ?>

<?php if (!empty($upcomingEvents)): ?>
<!-- Upcoming Events Section -->
<section class="upcoming-events-section container">
    <div class="section-top-bar">
        <div class="section-title-wrap">
            <h2 class="section-title">تقویم رویدادهای پیش‌رو</h2>
        </div>
        <a href="events.php" class="btn-primary">
            <span>همه رویدادها</span>
            <?= svgIcon('arrow-left', 'btn-icon-arrow', 15) ?>
        </a>
    </div>

    <div class="home-events-grid">
        <?php foreach ($upcomingEvents as $ev): 
            $dateObj = strtotime($ev['starts_at']);
            $jalali = gregorian_to_jalali((int)date('Y', $dateObj), (int)date('n', $dateObj), (int)date('j', $dateObj));
            $months = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
            $dayNum = strtr((string)$jalali[2], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
            $monthName = $months[$jalali[1]];
        ?>
            <article class="home-event-card">
                <div class="home-event-badge">
                    <span class="home-event-day"><?= $dayNum ?></span>
                    <span class="home-event-month"><?= $monthName ?></span>
                </div>
                <div class="home-event-info">
                    <span class="home-event-assoc"><?= e($ev['assoc_name'] ?: 'دانشگاه اراک (عمومی)') ?></span>
                    <h3 class="home-event-title">
                        <a href="event.php?id=<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a>
                    </h3>
                    <?php if (!empty($ev['location'])): ?>
                        <div class="home-event-meta">
                            <?= svgIcon('building', '', 13) ?>
                            <span><?= e($ev['location']) ?></span>
                        </div>
                    <?php endif; ?>
                    <a href="event.php?id=<?= (int)$ev['id'] ?>" class="home-event-action">
                        <span>مشاهده و ثبت‌نام</span>
                        <?= svgIcon('arrow-left', '', 12) ?>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Latest News Section (Asymmetrical Grid) -->
<section class="latest-news-section container">
    <div class="section-top-bar">
        <div class="section-title-wrap">
            <h2 class="section-title">جدیدترین اخبار</h2>
        </div>
        <a href="news.php" class="btn-primary">
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
            </div>
            <a href="associations.php" class="btn-primary">
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
