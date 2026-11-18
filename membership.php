<?php
$page = 'membership';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/icons.php';

$stmt = $pdo->query("SELECT a.*, f.id AS form_id 
                     FROM associations a 
                     LEFT JOIN forms f ON f.association_id = a.id AND f.type = 'membership' AND f.is_open = 1
                     WHERE a.membership_open = 1 
                     ORDER BY a.name ASC");
$assocs = $stmt->fetchAll();
?>

<section class="page-container container">
    <div class="page-header-box" style="text-align:center; max-width:680px; margin:0 auto 36px;">
        <h1 class="page-title">عضویت در انجمن‌های علمی دانشگاه اراک</h1>
        <p style="font-size:15px; color:var(--text-secondary); margin-top:8px;">
            انجمن‌های علمی دانشجویی بستری پویا برای کسب تجربه عملی، توسعه مهارت‌های تخصصی و کار تیمی در دانشگاه هستند. برای عضویت، انجمن مورد نظر خود را انتخاب نمایید.
        </p>
    </div>

    <?php if ($assocs): ?>
        <div class="membership-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:20px;">
            <?php foreach ($assocs as $a): 
                $link = !empty($a['form_id']) ? 'form.php?id=' . (int)$a['form_id'] : 'association.php?id=' . (int)$a['id'];
            ?>
                <div class="membership-card" style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px; display:flex; flex-direction:column; justify-content:space-between; transition:border-color 0.15s ease;">
                    <div>
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                            <div style="width:36px; height:36px; color:var(--color-primary-navy); flex-shrink:0;">
                                <?= assocIconSvg(assocIconKey($a), '', 32) ?>
                            </div>
                            <h3 style="font-size:16px; font-weight:700; color:var(--color-primary-navy);">
                                <?= e($a['name']) ?>
                            </h3>
                        </div>
                        <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.7; margin-bottom:18px;">
                            <?= e($a['description'] ?: 'پذیرش عضو جدید در کارگروه‌های تخصصی، رویدادها و روابط عمومی.') ?>
                        </p>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding-top:16px; border-top:1px solid var(--border-subtle);">
                        <a href="association.php?id=<?= $a['id'] ?>" class="btn-secondary" style="font-size:12.5px; padding:6px 14px;">
                            معرفی انجمن
                        </a>
                        <a href="<?= $link ?>" class="btn-primary" style="font-size:12.5px; padding:6px 16px;">
                            فرم ثبت‌نام عضویت <?= svgIcon('arrow-left', '', 14) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state-box">
            <?= svgIcon('building', 'empty-icon', 48) ?>
            <p>در حال حاضر فراخوان عضویت فعالی ثبت نشده است.</p>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
