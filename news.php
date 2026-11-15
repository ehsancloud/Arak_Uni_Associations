<?php
$page = 'news';
include 'includes/header.php';

$q = trim($_GET['q'] ?? '');
$assoc = (int)($_GET['assoc'] ?? 0);
$p = max(1, (int)($_GET['p'] ?? 1));
$per = 9;

$where = []; $params = [];
if ($q !== '') { $where[] = "(title LIKE ? OR content LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($assoc > 0) { $where[] = "association_id = ?"; $params[] = $assoc; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$cnt = $pdo->prepare("SELECT COUNT(*) FROM news $w");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$p = min($p, $pages);
$offset = ($p - 1) * $per;

$st = $pdo->prepare("SELECT * FROM news $w ORDER BY created_at DESC, id DESC LIMIT $per OFFSET $offset");
$st->execute($params);
$list = $st->fetchAll();

$assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name")->fetchAll();

function pageUrl($n) { $g = $_GET; $g['p'] = $n; return '?' . http_build_query($g); }
?>

<section class="page-container container">
    <div class="page-header-box">
        <div class="section-title-wrap">
            <h1 class="page-title">اخبار و رویدادهای دانشگاه</h1>
            <div class="title-underline"></div>
        </div>
        <?php if ($q !== ''): ?>
            <p class="search-result-hint">نتایج جستجو برای عبارت «<?= e($q) ?>» — <?= $total ?> خبر یافت شد.</p>
        <?php endif; ?>
    </div>

    <!-- Filter and Search Bar -->
    <form class="news-filter-bar" method="get">
        <div class="filter-input-wrap">
            <?= svgIcon('search', 'filter-search-icon', 18) ?>
            <input type="text" name="q" placeholder="جستجو در عنوان و متن اخبار..." value="<?= e($q) ?>">
        </div>
        <div class="filter-select-wrap">
            <select name="assoc">
                <option value="0">همه انجمن‌ها</option>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $assoc == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-pill-dark btn-filter-submit">
            <span>اعمال فیلتر</span>
        </button>
    </form>

    <!-- News Grid -->
    <?php if ($list): ?>
        <div class="news-archive-grid">
            <?php foreach ($list as $n): ?>
                <?= newsCard($n, false) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state-box">
            <?= svgIcon('building', 'empty-icon', 48) ?>
            <p>خبری با این مشخصات یافت نشد.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="pagination-bar">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php if ($i == $p): ?>
                <span class="page-num active"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= e(pageUrl($i)) ?>" class="page-num"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
