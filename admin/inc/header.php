<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت سامانه | دانشگاه اراک</title>
    <link rel="stylesheet" href="inc/admin-style.css?v=<?= file_exists(__DIR__ . '/admin-style.css') ? filemtime(__DIR__ . '/admin-style.css') : '2.1' ?>">
</head>
<body>

<?php if (isset($_SESSION['admin_id'])): ?>
<!-- نوار بالای موبایل برای پنل ادمین -->
<header class="admin-mobile-topbar">
    <div style="display:flex; align-items:center; gap:10px;">
        <button type="button" class="admin-menu-toggle" id="adminSidebarToggle" aria-label="منوی مدیریت" aria-expanded="false">
            <?= svgIcon('menu', '', 22) ?>
        </button>
        <div class="admin-mobile-brand">
            <img src="../assets/images/logo-white.svg" alt="دانشگاه اراک" class="admin-mobile-logo">
            <div class="admin-mobile-titles">
                <span class="admin-mobile-title">پنل مدیریت</span>
                <?php if (!$isSuperAdmin && $currentAssocName): ?>
                    <span class="admin-mobile-assoc"><?= htmlspecialchars($currentAssocName) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="admin-mobile-actions">
        <a href="index.php?logout=1" class="admin-mobile-logout-btn" title="خروج از حساب" aria-label="خروج">
            <?= svgIcon('close', '', 18) ?>
        </a>
    </div>
</header>

<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-inner">
            <img src="../assets/images/logo-white.svg" alt="دانشگاه اراک" style="width:34px;height:34px;object-fit:contain;">
            <div>
                <h2 style="font-size:15px; margin:0; line-height:1.3;">پنل مدیریت</h2>
                <?php if (!$isSuperAdmin && $currentAssocName): ?>
                    <span style="font-size:11.5px; color:#93c5fd; display:block; margin-top:3px;"><?= htmlspecialchars($currentAssocName) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <button type="button" class="sidebar-close-btn" id="adminSidebarClose" aria-label="بستن منو">
            <?= svgIcon('close', '', 18) ?>
        </button>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">
                <?= svgIcon('grid', 'nav-svg', 18) ?>
                <span>داشبورد <?= $isSuperAdmin ? 'کل' : 'انجمن' ?></span>
            </a>
        </li>

        <?php if ($isSuperAdmin): ?>
            <li>
                <a href="users.php" class="<?= $current_page=='users.php'?'active':'' ?>">
                    <?= svgIcon('user-plus', 'nav-svg', 18) ?>
                    <span>مدیریت دبیران</span>
                </a>
            </li>
            <li>
                <a href="associations.php" class="<?= $current_page=='associations.php'?'active':'' ?>">
                    <?= svgIcon('building', 'nav-svg', 18) ?>
                    <span>مدیریت انجمن‌ها</span>
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="associations.php?edit=<?= $adminAssocId ?>" class="<?= $current_page=='associations.php'?'active':'' ?>">
                    <?= svgIcon('building', 'nav-svg', 18) ?>
                    <span>مشخصات و هدر انجمن</span>
                </a>
            </li>
        <?php endif; ?>

        <li>
            <a href="members.php" class="<?= $current_page=='members.php'?'active':'' ?>">
                <?= svgIcon('users', 'nav-svg', 18) ?>
                <span>اعضای شورای مرکزی</span>
            </a>
        </li>
        <li>
            <a href="events.php" class="<?= $current_page=='events.php'?'active':'' ?>">
                <?= svgIcon('calendar', 'nav-svg', 18) ?>
                <span>مدیریت رویدادها</span>
            </a>
        </li>
        <li>
            <a href="news.php" class="<?= $current_page=='news.php'?'active':'' ?>">
                <?= svgIcon('book-open', 'nav-svg', 18) ?>
                <span>مدیریت اخبار</span>
            </a>
        </li>
        <li>
            <a href="forms.php" class="<?= $current_page=='forms.php'?'active':'' ?>">
                <?= svgIcon('file-text', 'nav-svg', 18) ?>
                <span>فرم‌ها و فیلدها</span>
            </a>
        </li>
        <li>
            <a href="submissions.php" class="<?= $current_page=='submissions.php'?'active':'' ?>">
                <?= svgIcon('check', 'nav-svg', 18) ?>
                <span>ثبت‌نامی‌ها</span>
            </a>
        </li>
        <li>
            <a href="publications.php" class="<?= $current_page=='publications.php'?'active':'' ?>">
                <?= svgIcon('book', 'nav-svg', 18) ?>
                <span>نشریات علمی</span>
            </a>
        </li>
        <li>
            <a href="documents.php" class="<?= $current_page=='documents.php'?'active':'' ?>">
                <?= svgIcon('document', 'nav-svg', 18) ?>
                <span>اسناد و فایل‌ها</span>
            </a>
        </li>
        <li>
            <a href="gallery.php" class="<?= $current_page=='gallery.php'?'active':'' ?>">
                <?= svgIcon('image', 'nav-svg', 18) ?>
                <span>گالری تصاویر</span>
            </a>
        </li>

        <?php if ($isSuperAdmin): ?>
            <li>
                <a href="ticker.php" class="<?= $current_page=='ticker.php'?'active':'' ?>">
                    <?= svgIcon('megaphone', 'nav-svg', 18) ?>
                    <span>نوار آخرین اخبار</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?= $current_page=='settings.php'?'active':'' ?>">
                    <?= svgIcon('gear', 'nav-svg', 18) ?>
                    <span>تنظیمات سایت</span>
                </a>
            </li>
        <?php else: ?>
            <li style="margin-top:6px;">
                <a href="../association.php?id=<?= $adminAssocId ?>" target="_blank" style="color:#60a5fa;">
                    <?= svgIcon('eye', 'nav-svg', 18) ?>
                    <span>مشاهده انجمن در سایت</span>
                </a>
            </li>
        <?php endif; ?>

        <li style="margin-top:12px; padding-top:12px; border-top:1px solid rgba(255,255,255,0.1);">
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
