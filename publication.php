<?php
$page = 'publications';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT p.*, a.name AS assoc_name 
                     FROM publications p 
                     LEFT JOIN associations a ON p.association_id = a.id 
                     WHERE p.id = ?");
$st->execute([$id]);
$pub = $st->fetch();

if (!$pub) {
    echo "<section class='page-container container' style='text-align:center; padding:100px 20px;'>
            <h2>نشریه مورد نظر یافت نشد!</h2>
            <a href='index.php' class='btn-primary' style='display:inline-flex; margin-top:20px;'>بازگشت به صفحه اصلی</a>
          </section>";
    include 'includes/footer.php';
    exit;
}

$pdfPath = 'uploads/publications/' . $pub['file'];
$hasPdf = file_exists(__DIR__ . '/' . $pdfPath);
$cover = !empty($pub['cover_image']) ? getNewsImageUrl($pub['cover_image']) : 'assets/images/default-cover.jpg';
?>

<section class="page-container container">
    <div style="max-width:880px; margin:0 auto; background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:36px; box-shadow:var(--shadow-sm);">
        <div style="display:grid; grid-template-columns: 240px 1fr; gap:30px; align-items:start;" class="publication-detail-grid">
            <div style="border-radius:var(--radius-md); overflow:hidden; border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
                <img src="<?= e($cover) ?>" alt="<?= e($pub['title']) ?>" style="width:100%; display:block;">
            </div>

            <div>
                <span style="font-size:13px; color:var(--text-muted); font-weight:600;">
                    <?= e($pub['assoc_name']) ?>
                </span>
                <h1 style="font-size:22px; font-weight:700; color:var(--color-primary-navy); margin:8px 0 12px; line-height:1.45;">
                    <?= e($pub['title']) ?>
                </h1>

                <?php if (!empty($pub['issue_no'])): ?>
                    <p style="font-size:14px; color:var(--color-accent); font-weight:600; margin-bottom:12px;">
                        <?= e($pub['issue_no']) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($pub['published_at'])): ?>
                    <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:16px;">
                        <?= svgIcon('calendar', '', 14) ?> تاریخ انتشار: <?= formatDate($pub['published_at']) ?>
                    </p>
                <?php endif; ?>

                <div style="font-size:14.5px; line-height:2; color:var(--text-secondary); margin-bottom:24px;">
                    <?= !empty($pub['summary']) ? nl2br(e($pub['summary'])) : '<p class="text-muted">توضیحاتی برای این شماره ثبت نشده است.</p>' ?>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <?php if ($hasPdf): ?>
                        <a href="<?= e($pdfPath) ?>" target="_blank" class="btn-primary">
                            مشاهده و دانلود فایل PDF
                        </a>
                    <?php else: ?>
                        <span class="btn-secondary" style="opacity:0.7; cursor:not-allowed;">فایل الکترونیکی در دسترس نیست</span>
                    <?php endif; ?>

                    <?php if (!empty($pub['association_id'])): ?>
                        <a href="association.php?id=<?= $pub['association_id'] ?>" class="btn-secondary">
                            صفحه انجمن <?= svgIcon('arrow-left', '', 14) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
