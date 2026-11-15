<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['admin_id']) && $current_page != 'index.php') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت سامانه | دانشگاه اراک</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="inc/admin-style.css">
</head>
<body>

<?php if (isset($_SESSION['admin_id'])): ?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/logo-white.svg" alt="دانشگاه اراک" style="width:36px;height:36px;">
        <h2>پنل مدیریت</h2>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">
                <?= svgIcon('building', 'nav-svg', 18) ?>
                <span>داشبورد</span>
            </a>
        </li>
        <li>
            <a href="settings.php" class="<?= $current_page=='settings.php'?'active':'' ?>">
                <?= svgIcon('gear', 'nav-svg', 18) ?>
                <span>تنظیمات سایت</span>
            </a>
        </li>
        <li>
            <a href="associations.php" class="<?= $current_page=='associations.php'?'active':'' ?>">
                <?= svgIcon('association', 'nav-svg', 18) ?>
                <span>مدیریت انجمن‌ها</span>
            </a>
        </li>
        <li>
            <a href="news.php" class="<?= $current_page=='news.php'?'active':'' ?>">
                <?= svgIcon('book', 'nav-svg', 18) ?>
                <span>مدیریت اخبار</span>
            </a>
        </li>
        <li>
            <a href="ticker.php" class="<?= $current_page=='ticker.php'?'active':'' ?>">
                <?= svgIcon('megaphone', 'nav-svg', 18) ?>
                <span>نوار آخرین اخبار</span>
            </a>
        </li>
        <li>
            <a href="members.php" class="<?= $current_page=='members.php'?'active':'' ?>">
                <?= svgIcon('user', 'nav-svg', 18) ?>
                <span>اعضای انجمن‌ها</span>
            </a>
        </li>
        <li>
            <a href="festival_regs.php" class="<?= $current_page=='festival_regs.php'?'active':'' ?>">
                <?= svgIcon('check', 'nav-svg', 18) ?>
                <span>ثبت‌نامی‌های جشنواره</span>
            </a>
        </li>
        <li style="margin-top:auto;">
            <a href="index.php?logout=1" class="logout-link">
                <?= svgIcon('close', 'nav-svg', 18) ?>
                <span>خروج از حساب</span>
            </a>
        </li>
    </ul>
</aside>
<main class="admin-content">
<?php else: ?>
<main style="margin:auto; width:100%;">
<?php endif; ?>
