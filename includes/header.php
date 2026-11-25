<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
$settings = getSettings($pdo);
$page = $page ?? '';
$nav = [
    'home'         => ['index.php', 'صفحه اصلی'],
    'associations' => ['associations.php', 'انجمن‌ها'],
    'events'       => ['events.php', 'رویدادها'],
    'membership'   => ['membership.php', 'عضویت'],
    'news'         => ['news.php', 'اخبار'],
    'cooperation'  => ['cooperation.php', 'همکاری'],
    'about'        => ['about.php', 'درباره ما'],
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title><?= e($settings['site_title']) ?></title>
    <meta name="description" content="سامانه جامع خبری و اطلاع‌رسانی انجمن‌های علمی دانشگاه اراک">
    <link rel="preload" href="assets/fonts/YekanBakhFaNum-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : '3.0' ?>">
<body>

<!-- اسکلتون لودینگ واکنش‌گرا و سبک صفحه (Responsive Skeleton Screen) -->
<div id="pageSkeleton" class="page-skeleton-overlay" aria-hidden="true">
    <div class="skeleton-header">
        <div class="container skeleton-header-inner">
            <div class="skeleton-mobile-btn skeleton-shimmer"></div>
            <div class="skeleton-brand">
                <div class="skeleton-logo skeleton-shimmer"></div>
                <div class="skeleton-brand-text">
                    <div class="skeleton-title-line skeleton-shimmer"></div>
                    <div class="skeleton-sub-line skeleton-shimmer"></div>
                </div>
            </div>
            <div class="skeleton-desktop-nav">
                <div class="skeleton-nav-item skeleton-shimmer"></div>
                <div class="skeleton-nav-item skeleton-shimmer"></div>
                <div class="skeleton-nav-item skeleton-shimmer"></div>
                <div class="skeleton-nav-item skeleton-shimmer"></div>
            </div>
            <div class="skeleton-search-wrap">
                <div class="skeleton-search-box skeleton-shimmer"></div>
            </div>
        </div>
    </div>
    <div class="skeleton-hero">
        <div class="container skeleton-hero-inner">
            <div class="skeleton-hero-title skeleton-shimmer"></div>
            <div class="skeleton-hero-desc skeleton-shimmer"></div>
            <div class="skeleton-hero-desc-short skeleton-shimmer"></div>
            <div class="skeleton-hero-buttons">
                <div class="skeleton-btn skeleton-shimmer"></div>
                <div class="skeleton-btn skeleton-shimmer"></div>
            </div>
        </div>
    </div>
    <div class="container skeleton-body">
        <div class="skeleton-cards-grid">
            <div class="skeleton-card">
                <div class="skeleton-card-img skeleton-shimmer"></div>
                <div class="skeleton-card-line skeleton-shimmer"></div>
                <div class="skeleton-card-sub skeleton-shimmer"></div>
            </div>
            <div class="skeleton-card">
                <div class="skeleton-card-img skeleton-shimmer"></div>
                <div class="skeleton-card-line skeleton-shimmer"></div>
                <div class="skeleton-card-sub skeleton-shimmer"></div>
            </div>
            <div class="skeleton-card">
                <div class="skeleton-card-img skeleton-shimmer"></div>
                <div class="skeleton-card-line skeleton-shimmer"></div>
                <div class="skeleton-card-sub skeleton-shimmer"></div>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var sk = document.getElementById('pageSkeleton');
    if (!sk) return;
    function hideSk() {
        if (!sk.classList.contains('hidden')) {
            sk.classList.add('hidden');
            setTimeout(function() {
                if (sk && sk.parentNode) sk.parentNode.removeChild(sk);
            }, 350);
        }
    }
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(hideSk, 100);
    } else {
        document.addEventListener('DOMContentLoaded', hideSk);
        window.addEventListener('load', hideSk);
    }
    setTimeout(hideSk, 450);
})();
</script>

<header class="main-header" id="mainHeader">
    <div class="container header-flex">
        <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="منوی سایت" aria-expanded="false">
            <?= svgIcon('menu', '', 24) ?>
        </button>

        <a href="index.php" class="logo-box">
            <div class="logo-text">
                <span class="logo-title">دانشگاه اراک</span>
                <span class="logo-sub">سامانه خبری انجمن‌های علمی</span>
            </div>
            <div class="logo-icon-wrap">
                <img src="assets/images/logo.svg" alt="دانشگاه اراک" class="logo-img">
            </div>
        </a>

        <nav class="main-nav" id="mainNav">
            <ul class="nav-list">
                <?php foreach ($nav as $key => $item): ?>
                    <li class="nav-item">
                        <a href="<?= $item[0] ?>" class="nav-link <?= $page === $key ? 'active' : '' ?>">
                            <?= $item[1] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <form class="search-box" action="news.php" method="get">
                <input type="text" name="q" placeholder="جستجو در اخبار..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="جستجو در اخبار">
                <button type="submit" class="search-btn" aria-label="جستجو">
                    <?= svgIcon('search', '', 18) ?>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- Mobile Navigation Drawer Overlay -->
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-header">
        <div class="logo-box">
            <img src="assets/images/logo.svg" alt="دانشگاه اراک" class="logo-img" style="width:36px;height:36px;">
            <div class="logo-text">
                <span class="logo-title">دانشگاه اراک</span>
                <span class="logo-sub">انجمن‌های علمی</span>
            </div>
        </div>
        <button type="button" class="drawer-close-btn" id="drawerCloseBtn" aria-label="بستن منو">
            <?= svgIcon('close', '', 20) ?>
        </button>
    </div>
    <form class="mobile-search-box" action="news.php" method="get">
        <input type="text" name="q" placeholder="جستجو در اخبار..." value="<?= e($_GET['q'] ?? '') ?>">
        <button type="submit"><?= svgIcon('search', '', 18) ?></button>
    </form>
    <ul class="mobile-nav-list">
        <?php foreach ($nav as $key => $item): ?>
            <li>
                <a href="<?= $item[0] ?>" class="<?= $page === $key ? 'active' : '' ?>">
                    <?= $item[1] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
