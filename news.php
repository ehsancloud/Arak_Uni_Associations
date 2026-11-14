<?php
$page = 'news';
include 'includes/header.php';

$q = trim($_GET['q'] ?? '');
$assoc = (int)($_GET['assoc'] ?? 0);
$p = max(1, (int)($_GET['p'] ?? 1));
$per = 12;

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

<section class="container">
    <div class="page-title">
        <h2>اخبار</h2>
        <?php if ($q !== ''): ?><p>نتایج جستجو برای «<?= e($q) ?>» — <?= $total ?> خبر</p><?php endif; ?>
    </div>

    <form class="filter-bar" method="get">
        <input type="text" name="q" placeholder="جستجو در عنوان و متن اخبار..." value="<?= e($q) ?>">
        <select name="assoc">
            <option value="0">همه انجمن‌ها</option>
            <?php foreach ($assocs as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $assoc == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">جستجو</button>
    </form>

    <div class="news-list-grid">
        <?php if ($list): foreach ($list as $n): ?>
            <?= newsCard($n) ?>
        <?php endforeach; else: ?>
            <div class="empty-box">خبری یافت نشد.</div>
        <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php if ($i == $p): ?><span class="current"><?= $i ?></span>
            <?php else: ?><a href="<?= e(pageUrl($i)) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
