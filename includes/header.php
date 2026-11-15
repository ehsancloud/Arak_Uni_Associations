<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
$settings = getSettings($pdo);
$page = $page ?? '';
$nav = [
    'home'         => ['index.php', 'صفحه اصلی'],
    'news'         => ['news.php', 'اخبار'],
    'associations' => ['associations.php', 'انجمن‌ها'],
    'festival'     => ['festival.php', 'جشنواره حرکت'],
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --font-family: '<?= e($settings['font_family'] ?: 'Vazirmatn') ?>', 'Vazirmatn', -apple-system, BlinkMacSystemFont, Tahoma, sans-serif;
        }
    </style>
</head>
<body>

<header class="main-header" id="mainHeader">
    <div class="container header-flex">
        <!-- Logo on Right (in RTL) -->
        <a href="index.php" class="logo-box">
            <div class="logo-text">
                <span class="logo-title">دانشگاه اراک</span>
                <span class="logo-sub">سامانه خبری انجمن‌های علمی</span>
            </div>
            <div class="logo-icon-wrap">
                <img src="assets/images/logo.svg" alt="دانشگاه اراک" class="logo-img">
            </div>
        </a>
        
        <!-- Navigation in Center -->
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

        <!-- Search and Mobile Toggle on Left -->
        <div class="header-actions">
            <form class="search-box" action="news.php" method="get">
                <input type="text" name="q" placeholder="جستجو در اخبار..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="جستجو در اخبار">
                <button type="submit" class="search-btn" aria-label="جستجو">
                    <?= svgIcon('search', '', 18) ?>
                </button>
            </form>
            <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="منوی سایت">
                <?= svgIcon('menu', '', 24) ?>
            </button>
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
