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

<!-- اسکلتون لودینگ صفحه (Skeleton Screen Loader) -->
<div id="pageSkeleton" class="page-skeleton-overlay" aria-hidden="true">
    <div class="skeleton-header-wrap">
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="skeleton-shimmer" style="width:40px; height:40px; border-radius:50%;"></div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <div class="skeleton-shimmer" style="width:110px; height:14px;"></div>
                <div class="skeleton-shimmer" style="width:75px; height:10px;"></div>
            </div>
        </div>
        <div style="display:flex; gap:16px;">
            <div class="skeleton-shimmer" style="width:65px; height:12px;"></div>
            <div class="skeleton-shimmer" style="width:65px; height:12px;"></div>
            <div class="skeleton-shimmer" style="width:65px; height:12px;"></div>
            <div class="skeleton-shimmer" style="width:65px; height:12px;"></div>
        </div>
        <div class="skeleton-shimmer" style="width:140px; height:36px; border-radius:20px;"></div>
    </div>
    <div class="skeleton-hero-wrap">
        <div class="skeleton-shimmer" style="width:280px; height:28px;"></div>
        <div class="skeleton-shimmer" style="width:400px; height:16px;"></div>
        <div class="skeleton-shimmer" style="width:340px; height:16px;"></div>
        <div style="display:flex; gap:12px; margin-top:12px;">
            <div class="skeleton-shimmer" style="width:120px; height:38px; border-radius:8px;"></div>
            <div class="skeleton-shimmer" style="width:120px; height:38px; border-radius:8px;"></div>
        </div>
    </div>
    <div class="skeleton-content-grid">
        <div class="skeleton-shimmer" style="height:180px; border-radius:12px;"></div>
        <div class="skeleton-shimmer" style="height:180px; border-radius:12px;"></div>
        <div class="skeleton-shimmer" style="height:180px; border-radius:12px;"></div>
        <div class="skeleton-shimmer" style="height:180px; border-radius:12px;"></div>
    </div>
</div>

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
