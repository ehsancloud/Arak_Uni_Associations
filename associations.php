<?php
$page = 'associations';
include 'includes/header.php';
try {
    $list = $pdo->query("SELECT * FROM associations ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $list = [];
}
?>

<section class="page-container container">
    <div class="page-header-box">
        <div class="section-title-wrap">
            <h1 class="page-title">انجمن‌های علمی دانشگاه اراک</h1>
            <div class="title-underline"></div>
        </div>
        <p class="search-result-hint"><?= count($list) ?> انجمن فعال علمی و دانشجویی</p>
    </div>

    <div class="associations-inner-box" style="margin-top:20px;">
        <div class="associations-grid-row grid-large">
            <?php if ($list): foreach ($list as $a): ?>
                <?= assocCard($a) ?>
            <?php endforeach; else: ?>
                <div class="empty-state-box">
                    <?= svgIcon('building', 'empty-icon', 48) ?>
                    <p>هنوز انجمنی در سامانه ثبت نشده است.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
