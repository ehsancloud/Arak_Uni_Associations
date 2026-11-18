<?php
$page = 'cooperation';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/forms.php';

// جستجوی فرم همکاری عمومی
$st = $pdo->query("SELECT * FROM forms WHERE type = 'cooperation' AND association_id = 0 LIMIT 1");
$form = $st->fetch();

// اگر فرم نبود، به عنوان فالبک اولین فرم عمومی یا پیام نمایش داده می‌شود
if (!$form) {
    $st = $pdo->query("SELECT * FROM forms WHERE type = 'cooperation' LIMIT 1");
    $form = $st->fetch();
}

$fields = $form ? getFormFields($pdo, $form['id']) : [];
$errors = [];
$successMessage = '';

if ($form && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateFormSubmission($pdo, $form, $fields, $_POST);
    if (empty($errors)) {
        saveFormSubmission($pdo, $form['id'], $fields, $_POST);
        header('Location: cooperation.php?success=1');
        exit;
    }
}

if (isset($_GET['success'])) {
    $successMessage = 'درخواست همکاری شما با موفقیت ثبت شد. دبیران انجمن‌ها و کارشناسان مربوطه در اسرع وقت با شما تماس خواهند گرفت.';
}
?>

<section class="page-container container">
    <div style="max-width:720px; margin:0 auto; background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:36px; box-shadow:var(--shadow-sm);">
        <div style="text-align:center; margin-bottom:28px; padding-bottom:20px; border-bottom:1px solid var(--border-color);">
            <h1 style="font-size:22px; font-weight:700; color:var(--color-primary-navy); margin-bottom:10px;">
                درخواست همکاری با انجمن‌های علمی دانشگاه اراک
            </h1>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.8;">
                اساتید، پژوهشگران، شرکت‌های صنعتی، نهادهای علمی و دانشجویان می‌توانند پیشنهادات خود جهت برگزاری کارگاه‌های مشترک، پروژه‌های صنعتی، سخنرانی و حمایت مالی را از طریق این فرم ارسال نمایند.
            </p>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success" style="padding:16px 20px; border-radius:var(--radius-md); background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; text-align:center; font-weight:600; margin-bottom:24px;">
                <?= svgIcon('check', '', 20) ?> <?= e($successMessage) ?>
            </div>
            <div style="text-align:center;">
                <a href="index.php" class="btn-primary" style="display:inline-flex;">بازگشت به صفحه اصلی</a>
            </div>
        <?php elseif ($form): ?>
            <?= renderFormHtml($form, $fields, $errors, $_POST) ?>
        <?php else: ?>
            <div class="empty-state-box">
                <p>فرم درخواست همکاری در حال حاضر فعال نیست. لطفاً با دبیرخانه انجمن‌های علمی تماس بگیرید.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
