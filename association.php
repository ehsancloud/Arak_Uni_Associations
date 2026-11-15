<?php 
$page = 'associations';
include 'includes/header.php'; 

$assoc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM associations WHERE id = ?");
$stmt->execute([$assoc_id]);
$assoc = $stmt->fetch();

if (!$assoc) {
    // اگر شناسه پیدا نشد، اولین انجمن را به عنوان نمونه نمایش بده یا پیام بده
    $fallbackAssoc = $pdo->query("SELECT * FROM associations ORDER BY id ASC LIMIT 1")->fetch();
    if ($fallbackAssoc) {
        $assoc = $fallbackAssoc;
        $assoc_id = (int)$assoc['id'];
    } else {
        echo "<div class='container' style='padding:120px 20px; text-align:center;'>
                <h2>انجمن مورد نظر یافت نشد!</h2>
                <a href='associations.php' class='btn-pill-dark' style='display:inline-flex; margin-top:20px;'>مشاهده همه انجمن‌ها</a>
              </div>";
        include 'includes/footer.php';
        exit;
    }
}

// اخبار اختصاصی انجمن
$news_stmt = $pdo->prepare("SELECT * FROM news WHERE association_id = ? ORDER BY created_at DESC, id DESC LIMIT 5");
$news_stmt->execute([$assoc_id]);
$news_list = $news_stmt->fetchAll();

// اگر برای این انجمن هنوز خبری ثبت نشده، از آخرین اخبار عمومی استفاده کن
if (!$news_list) {
    $news_list = $pdo->query("SELECT * FROM news ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
}

// اعضای انجمن
$mem_stmt = $pdo->prepare("SELECT * FROM members WHERE association_id = ? ORDER BY id ASC");
$mem_stmt->execute([$assoc_id]);
$members = $mem_stmt->fetchAll();

// کاور هیرو
$cover = 'assets/images/default-cover.jpg';
if (!empty($assoc['cover_image'])) {
    if (file_exists(__DIR__ . '/uploads/covers/' . $assoc['cover_image'])) {
        $cover = 'uploads/covers/' . $assoc['cover_image'];
    } elseif (file_exists(__DIR__ . '/assets/images/' . $assoc['cover_image'])) {
        $cover = 'assets/images/' . $assoc['cover_image'];
    }
}
?>

<!-- Association Hero Section -->
<section class="assoc-hero-section">
    <div class="assoc-hero-bg-layer" style="background-image: url('<?= e($cover) ?>');"></div>
    <div class="assoc-hero-overlay"></div>
    <div class="container assoc-hero-container">
        <div class="assoc-hero-content">
            <span class="assoc-pill-badge">انجمن علمی</span>
            <h1 class="assoc-hero-title">
                <?= e($assoc['name']) ?><br>
                <span class="assoc-hero-sub">دانشگاه اراک</span>
            </h1>
            <p class="assoc-hero-desc"><?= e($assoc['description']) ?></p>
        </div>
    </div>
</section>

<!-- News Ticker -->
<?= renderTicker($pdo, $assoc_id) ?>

<!-- Association News Section (Asymmetrical Grid) -->
<section class="latest-news-section container">
    <div class="section-top-bar">
        <div class="section-title-wrap">
            <h2 class="section-title">اخبار انجمن</h2>
            <div class="title-underline"></div>
        </div>
        <a href="news.php?assoc=<?= $assoc_id ?>" class="btn-pill-dark">
            <span>مشاهده همه اخبار</span>
            <?= svgIcon('arrow-left', 'btn-icon-arrow', 15) ?>
        </a>
    </div>
    
    <?php if ($news_list): ?>
    <div class="featured-news-layout">
        <div class="featured-col-big">
            <?= newsCard($news_list[0], true) ?>
        </div>
        <div class="featured-col-grid">
            <?php for ($i = 1; $i < count($news_list); $i++): ?>
                <?= newsCard($news_list[$i], false) ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="empty-state-box">
            <?= svgIcon('building', 'empty-icon', 48) ?>
            <p>هنوز خبری برای این انجمن ثبت نشده است.</p>
        </div>
    <?php endif; ?>
</section>

<!-- Members Section with Decorative Dot Patterns -->
<section class="members-section-wrapper container">
    <div class="members-inner-box">
        <!-- Decorative Dot Pattern Right -->
        <div class="dots-decoration dots-right" aria-hidden="true"></div>

        <div class="members-header-center">
            <h2 class="section-title">اعضای <?= e($assoc['name']) ?></h2>
            <div class="title-underline-center"></div>
        </div>

        <div class="members-row">
            <?php if ($members): foreach ($members as $m): 
                $hasImg = false;
                $img = 'assets/images/default-user.png';
                if (!empty($m['image'])) {
                    if (file_exists(__DIR__ . '/uploads/profiles/' . $m['image'])) {
                        $img = 'uploads/profiles/' . $m['image'];
                        $hasImg = true;
                    } elseif (file_exists(__DIR__ . '/assets/images/' . $m['image'])) {
                        $img = 'assets/images/' . $m['image'];
                        $hasImg = true;
                    }
                }
            ?>
                <div class="member-card">
                    <div class="member-avatar-wrap <?= $hasImg ? 'has-photo' : 'is-fallback' ?>">
                        <?php if ($hasImg): ?>
                            <img src="<?= e($img) ?>" alt="<?= e($m['full_name']) ?>" class="member-avatar-img" loading="lazy">
                        <?php else: ?>
                            <div class="member-avatar-svg">
                                <?= svgIcon('user', '', 48) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="member-name"><?= e($m['full_name']) ?></h3>
                    <span class="member-role"><?= e($m['role']) ?></span>
                </div>
            <?php endforeach; else: ?>
                <div class="empty-state-box">
                    <?= svgIcon('user', 'empty-icon', 48) ?>
                    <p>هنوز عضوی برای این انجمن ثبت نشده است.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Decorative Dot Pattern Left -->
        <div class="dots-decoration dots-left" aria-hidden="true"></div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
