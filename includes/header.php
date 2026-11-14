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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($settings['site_title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: '<?= e($settings['font_family']) ?>', Tahoma, sans-serif !important; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="container header-flex">
        <div class="logo-box">
            <img src="assets/images/logo.png" alt="دانشگاه اراک">
            <div>
                <h1>دانشگاه اراک</h1>
                <span>سامانه خبری انجمن‌های علمی</span>
            </div>
        </div>
        
        <nav class="main-nav">
            <ul>
                <?php foreach ($nav as $key => $item): ?>
                    <li><a href="<?= $item[0] ?>" class="<?= $page === $key ? 'active' : '' ?>"><?= $item[1] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <form class="search-box" action="news.php" method="get">
            <input type="text" name="q" placeholder="جستجو در اخبار..." value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit">🔍</button>
        </form>
    </div>
</header>
