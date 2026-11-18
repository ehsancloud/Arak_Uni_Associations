<?php
$page = 'events';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/forms.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT e.*, a.name AS assoc_name 
                     FROM events e 
                     LEFT JOIN associations a ON e.association_id = a.id 
                     WHERE e.id = ? AND e.is_published = 1");
$st->execute([$id]);
$ev = $st->fetch();

if (!$ev) {
    echo "<section class='page-container container' style='text-align:center; padding:100px 20px;'>
            <h2>رویداد مورد نظر یافت نشد!</h2>
            <a href='events.php' class='btn-primary' style='display:inline-flex; margin-top:20px;'>بازگشت به تقویم رویدادها</a>
          </section>";
    include 'includes/footer.php';
    exit;
}

// فرم ثبت‌نام رویداد
$form = null;
$fields = [];
$errors = [];
$successMessage = '';

if (!empty($ev['form_id'])) {
    $form = getForm($pdo, $ev['form_id']);
    if ($form) {
        $fields = getFormFields($pdo, $ev['form_id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = validateFormSubmission($pdo, $form, $fields, $_POST);
            if (empty($errors)) {
                saveFormSubmission($pdo, $form['id'], $fields, $_POST);
                header('Location: event.php?id=' . $id . '&success=1#register-section');
                exit;
            }
        }
    }
}

if (isset($_GET['success'])) {
    $successMessage = 'ثبت‌نام شما در این رویداد با موفقیت انجام شد.';
}

$dateObj = strtotime($ev['starts_at']);
$dateStr = formatDate($ev['starts_at']);
$timeStr = date('H:i', $dateObj);
$img = !empty($ev['image']) ? getNewsImageUrl($ev['image']) : 'assets/images/news-placeholder1.jpg';
?>

<section class="page-container container">
    <div style="max-width:880px; margin:0 auto;">
        <article style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); margin-bottom:36px;">
            <div style="width:100%; max-height:420px; overflow:hidden;">
                <img src="<?= e($img) ?>" alt="<?= e($ev['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>

            <div style="padding:32px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                    <span style="font-size:13px; color:var(--text-muted); font-weight:600;">
                        <?= e($ev['assoc_name'] ?: 'دانشگاه اراک (عمومی)') ?>
                    </span>
                    <span style="font-size:12.5px; background:var(--color-soft-bg); color:var(--color-primary-navy); padding:4px 12px; border-radius:var(--radius-sm); font-weight:600;">
                        <?= (strtotime($ev['starts_at']) >= time()) ? 'رویداد پیش‌رو' : 'برگزار شده' ?>
                    </span>
                </div>

                <h1 style="font-size:24px; font-weight:700; color:var(--color-primary-navy); line-height:1.45; margin-bottom:18px;">
                    <?= e($ev['title']) ?>
                </h1>

                <!-- متای رویداد: تاریخ، زمان، مکان -->
                <div style="display:flex; flex-wrap:wrap; gap:20px; padding:16px; background:var(--color-body-bg); border-radius:var(--radius-md); margin-bottom:24px; font-size:13.5px; color:var(--text-primary);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <?= svgIcon('calendar', '', 18) ?>
                        <span><strong>تاریخ:</strong> <?= $dateStr ?></span>
                    </div>
                    <?php if ($timeStr !== '00:00'): ?>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span><strong>ساعت:</strong> <?= $timeStr ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($ev['location'])): ?>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <?= svgIcon('building', '', 18) ?>
                            <span><strong>مکان:</strong> <?= e($ev['location']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- شرح رویداد -->
                <div style="font-size:15px; line-height:2.1; color:var(--text-secondary); margin-bottom:28px;">
                    <?= !empty($ev['description']) ? nl2br(e($ev['description'])) : '<p class="text-muted">شرح تفصیلی برای این رویداد درج نشده است.</p>' ?>
                </div>

                <a href="events.php" class="btn-secondary" style="font-size:13px; padding:6px 16px;">
                    <?= svgIcon('arrow-left', '', 14) ?> بازگشت به لیست رویدادها
                </a>
            </div>
        </article>

        <!-- بخش ثبت‌نام در رویداد (در صورت اتصال فرم) -->
        <?php if ($form): ?>
            <div id="register-section" style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:32px; box-shadow:var(--shadow-sm);">
                <div style="margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid var(--border-color);">
                    <h2 style="font-size:19px; font-weight:700; color:var(--color-primary-navy);">
                        <?= e($form['title']) ?>
                    </h2>
                    <?php if (!empty($form['description'])): ?>
                        <p style="font-size:13.5px; color:var(--text-secondary); margin-top:6px;">
                            <?= e($form['description']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success" style="padding:16px 20px; border-radius:var(--radius-md); background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; text-align:center; font-weight:600;">
                        <?= svgIcon('check', '', 20) ?> <?= e($successMessage) ?>
                    </div>
                <?php else: ?>
                    <?php
                    $availErr = checkFormAvailability($pdo, $form);
                    if ($availErr): ?>
                        <div class="alert alert-danger" style="padding:16px 20px; border-radius:var(--radius-md); background:#fef2f2; color:#991b1b; border:1px solid #fecaca; text-align:center; font-weight:600;">
                            <?= e($availErr) ?>
                        </div>
                    <?php else: ?>
                        <?= renderFormHtml($form, $fields, $errors, $_POST) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
