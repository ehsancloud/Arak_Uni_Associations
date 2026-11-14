<?php
$page = 'associations';
include 'includes/header.php';
$list = $pdo->query("SELECT * FROM associations ORDER BY id ASC")->fetchAll();
?>

<section class="container">
    <div class="page-title">
        <h2>انجمن‌های علمی دانشگاه اراک</h2>
        <p><?= count($list) ?> انجمن</p>
    </div>
    <div class="associations-grid big">
        <?php if ($list): foreach ($list as $a): ?>
            <?= assocCard($a) ?>
        <?php endforeach; else: ?>
            <div class="empty-box">هنوز انجمنی ثبت نشده است.</div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
