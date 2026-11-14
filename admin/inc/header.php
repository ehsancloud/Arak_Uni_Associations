<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/db.php';

// اگر لاگین نکرده بود و صفحه فعلی لاگین نبود، ریدایرکت کن
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
    <title>پنل مدیریت | دانشگاه اراک</title>
    <link rel="stylesheet" href="inc/admin-style.css">
</head>
<body>

<?php if (isset($_SESSION['admin_id'])): ?>
<aside class="admin-sidebar">
    <h2>پنل مدیریت</h2>
    <ul>
        <li><a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">🏠 داشبورد</a></li>
        <li><a href="settings.php" class="<?= $current_page=='settings.php'?'active':'' ?>">⚙️ تنظیمات سایت</a></li>
        <li><a href="associations.php" class="<?= $current_page=='associations.php'?'active':'' ?>">🏛 مدیریت انجمن‌ها</a></li>
        <li><a href="news.php" class="<?= $current_page=='news.php'?'active':'' ?>">📰 مدیریت اخبار</a></li>
        <li><a href="ticker.php" class="<?= $current_page=='ticker.php'?'active':'' ?>">📢 نوار آخرین اخبار</a></li>
        <li><a href="members.php" class="<?= $current_page=='members.php'?'active':'' ?>">👥 اعضای انجمن‌ها</a></li>
        <li><a href="festival_regs.php" class="<?= $current_page=='festival_regs.php'?'active':'' ?>">🎯 ثبت‌نامی‌های جشنواره</a></li>
        <li><a href="index.php?logout=1">🚪 خروج</a></li>
    </ul>
</aside>
<main class="admin-content">
<?php else: ?>
<main style="margin:auto;">
<?php endif; ?>
