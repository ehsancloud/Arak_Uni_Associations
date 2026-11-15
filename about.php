<?php
$page = 'about';
include 'includes/header.php';
?>

<section class="page-container container">
    <div class="page-header-box">
        <div class="section-title-wrap">
            <h1 class="page-title">درباره سامانه انجمن‌های علمی</h1>
            <div class="title-underline"></div>
        </div>
    </div>

    <div class="about-card-box">
        <div class="about-card-content">
            <?php if (!empty($settings['about_text'])): ?>
                <?= nl2br(e($settings['about_text'])) ?>
            <?php else: ?>
                <p>سامانه جامع انجمن‌های علمی دانشگاه اراک، بستری تخصصی جهت انتشار آخرین اخبار، دستاوردها، رویدادها، کارگاه‌های آموزشی و نشست‌های علمی انجمن‌های دانشجویی دانشگاه اراک می‌باشد.</p>
                <p style="margin-top:15px;">این بستر ارتباط میان پژوهشگران، اساتید و دانشجویان علاقه‌مند را تسهیل نموده و نقشی مؤثر در توانمندسازی علمی و ارتقای فعالیت‌های پژوهشی ایفا می‌کند.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
