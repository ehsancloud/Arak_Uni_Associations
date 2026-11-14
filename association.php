<?php 
$page = 'associations';
include 'includes/header.php'; 

$assoc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM associations WHERE id = ?");
$stmt->execute([$assoc_id]);
$assoc = $stmt->fetch();

if (!$assoc) {
    echo "<div class='container' style='padding:100px 0; text-align:center;'><h2>انجمن مورد نظر یافت نشد!</h2></div>";
    include 'includes/footer.php';
    exit;
}

$news_stmt = $pdo->prepare("SELECT * FROM news WHERE association_id = ? ORDER BY created_at DESC, id DESC LIMIT 5");
$news_stmt->execute([$assoc_id]);
$news_list = $news_stmt->fetchAll();

$mem_stmt = $pdo->prepare("SELECT * FROM members WHERE association_id = ?");
$mem_stmt->execute([$assoc_id]);
$members = $mem_stmt->fetchAll();

$cover = (!empty($assoc['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $assoc['cover_image']))
    ? 'uploads/covers/' . $assoc['cover_image'] : 'assets/images/default-cover.jpg';
?>

<section class="assoc-hero" style="background-image: url('<?= e($cover) ?>');">
    <div class="container">
        <div class="assoc-hero-text">
            <span class="badge">انجمن علمی</span>
            <h2><?= e($assoc['name']) ?><br>دانشگاه اراک</h2>
            <p><?= e($assoc['description']) ?></p>
        </div>
    </div>
</section>

<?= renderTicker($pdo, $assoc_id) ?>

<section class="latest-news container">
    <div class="section-header">
        <h3>اخبار انجمن</h3>
        <a href="news.php?assoc=<?= $assoc_id ?>" class="btn-primary">مشاهده همه اخبار ←</a>
    </div>
    
    <?php if ($news_list): ?>
    <div class="news-grid">
        <?= newsCard($news_list[0], true) ?>
        <div class="small-news-wrapper">
            <?php for ($i = 1; $i < count($news_list); $i++): ?>
                <?= newsCard($news_list[$i]) ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="empty-box">هنوز خبری برای این انجمن ثبت نشده است.</div>
    <?php endif; ?>
</section>

<section class="members-section container">
    <h3 class="section-title">اعضای <?= e($assoc['name']) ?></h3>
    <div class="members-grid">
        <?php if ($members): foreach ($members as $m): 
            $img = (!empty($m['image']) && file_exists(__DIR__ . '/uploads/profiles/' . $m['image']))
                ? 'uploads/profiles/' . $m['image'] : 'assets/images/default-user.png';
        ?>
            <div class="member-card">
                <img src="<?= e($img) ?>" alt="<?= e($m['full_name']) ?>">
                <h5><?= e($m['full_name']) ?></h5>
                <span><?= e($m['role']) ?></span>
            </div>
        <?php endforeach; else: ?>
            <div class="empty-box">هنوز عضوی ثبت نشده است.</div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
