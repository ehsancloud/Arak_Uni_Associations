<?php
$page = 'events';
require_once __DIR__ . '/includes/header.php';

$tab = $_GET['tab'] ?? 'upcoming';
$assocId = (int)($_GET['assoc'] ?? 0);

$where = ["is_published = 1"];
$params = [];

if ($tab === 'past') {
    $where[] = "starts_at < NOW()";
    $orderBy = "starts_at DESC";
} else {
    $tab = 'upcoming';
    $where[] = "starts_at >= NOW()";
    $orderBy = "starts_at ASC";
}

if ($assocId > 0) {
    $where[] = "association_id = ?";
    $params[] = $assocId;
}

$sql = "SELECT e.*, a.name AS assoc_name 
        FROM events e 
        LEFT JOIN associations a ON e.association_id = a.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();
?>

<section class="page-container container">
    <div class="page-header-box" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 class="page-title">رویدادها و کارگاه‌های علمی</h1>
            <p style="font-size:14px; color:var(--text-secondary); margin-top:6px;">تقویم سمینارها، کارگاه‌های تخصصی، مسابقات و همایش‌های دانشگاه اراک</p>
        </div>
        
        <!-- فیلتر انجمن -->
        <form method="GET" action="events.php" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <select name="assoc" onchange="this.form.submit()" class="form-control" style="font-size:13.5px; padding:6px 12px; border-radius:var(--radius-md);">
                <option value="0">همه انجمن‌ها</option>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $assocId == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- تب‌های پیش‌رو و برگزار شده -->
    <div class="events-tabs" style="display:flex; gap:12px; margin-bottom:28px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
        <a href="events.php?tab=upcoming&assoc=<?= $assocId ?>" class="<?= $tab === 'upcoming' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13.5px; padding:6px 18px;">
            رویدادهای پیش‌رو
        </a>
        <a href="events.php?tab=past&assoc=<?= $assocId ?>" class="<?= $tab === 'past' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13.5px; padding:6px 18px;">
            رویدادهای برگزارشده
        </a>
    </div>

    <!-- لیست رویدادها -->
    <?php if ($events): ?>
        <div class="events-list-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:22px;">
            <?php foreach ($events as $ev): 
                $dateObj = strtotime($ev['starts_at']);
                $jalali = gregorian_to_jalali((int)date('Y', $dateObj), (int)date('n', $dateObj), (int)date('j', $dateObj));
                $months = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
                $dayNum = strtr((string)$jalali[2], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
                $monthName = $months[$jalali[1]];
                $img = !empty($ev['image']) ? getNewsImageUrl($ev['image']) : 'assets/images/news-placeholder1.jpg';
            ?>
                <article class="event-card" style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; display:flex; flex-direction:column; transition:border-color 0.15s ease;">
                    <div style="height:150px; position:relative; overflow:hidden;">
                        <img src="<?= e($img) ?>" alt="<?= e($ev['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <div style="position:absolute; top:12px; right:12px; background:var(--color-primary-navy); color:#fff; border-radius:var(--radius-md); padding:6px 12px; text-align:center; min-width:54px;">
                            <span style="font-size:17px; font-weight:700; display:block; line-height:1;"><?= $dayNum ?></span>
                            <span style="font-size:11px; opacity:0.9;"><?= $monthName ?></span>
                        </div>
                    </div>
                    <div style="padding:18px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <span style="font-size:12px; color:var(--text-muted); font-weight:500;">
                                <?= e($ev['assoc_name'] ?: 'دانشگاه اراک (عمومی)') ?>
                            </span>
                            <h3 style="font-size:15px; font-weight:700; color:var(--color-primary-navy); margin:6px 0 10px; line-height:1.5;">
                                <a href="event.php?id=<?= $ev['id'] ?>"><?= e($ev['title']) ?></a>
                            </h3>
                            <?php if (!empty($ev['location'])): ?>
                                <p style="font-size:12.5px; color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                                    <?= svgIcon('building', '', 14) ?> <?= e($ev['location']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:16px; padding-top:12px; border-top:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                            <a href="event.php?id=<?= $ev['id'] ?>" class="btn-primary" style="font-size:12px; padding:6px 14px;">
                                مشاهده و ثبت‌نام <?= svgIcon('arrow-left', '', 12) ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state-box">
            <?= svgIcon('calendar', 'empty-icon', 48) ?>
            <p>رویدادی در این دسته‌بندی یافت نشد.</p>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
