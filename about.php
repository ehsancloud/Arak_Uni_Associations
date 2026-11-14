<?php
$page = 'about';
include 'includes/header.php';
?>

<section class="container">
    <div class="page-title"><h2>درباره ما</h2></div>
    <div class="about-box">
        <?= !empty($settings['about_text']) ? nl2br(e($settings['about_text'])) : '<span style="color:#888;">متن این صفحه از پنل مدیریت (تنظیمات سایت) قابل ویرایش است.</span>' ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
