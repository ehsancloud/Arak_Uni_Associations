<?php
$page = 'forms';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/forms.php';

$formId = (int)($_GET['id'] ?? 0);
$form = getForm($pdo, $formId);

if (!$form) {
    echo "<section class='page-container container' style='text-align:center; padding:100px 20px;'>
            <h2>فرم مورد نظر یافت نشد یا غیرفعال شده است.</h2>
            <a href='index.php' class='btn-primary' style='display:inline-flex; margin-top:20px;'>بازگشت به صفحه اصلی</a>
          </section>";
    include 'includes/footer.php';
    exit;
}

$fields = getFormFields($pdo, $formId);
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateFormSubmission($pdo, $form, $fields, $_POST);
    if (empty($errors)) {
        saveFormSubmission($pdo, $formId, $fields, $_POST);
        // الگوی PRG برای جلوگیری از ارسال مجدد با رفرش
        header('Location: form.php?id=' . $formId . '&success=1');
        exit;
    }
}

if (isset($_GET['success'])) {
    $successMessage = 'اطلاعات شما با موفقیت در سامانه ثبت شد. از همراهی شما سپاسگزاریم.';
}
?>

<section class="page-container container">
    <div class="form-page-wrapper" style="max-width:680px; margin:0 auto; background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:36px; box-shadow:var(--shadow-sm);">
        <div class="form-page-header" style="text-align:center; margin-bottom:28px; padding-bottom:20px; border-bottom:1px solid var(--border-color);">
            <?php if (!empty($form['assoc_name'])): ?>
                <span style="font-size:13px; color:var(--text-muted); font-weight:500; display:block; margin-bottom:6px;">
                    <?= e($form['assoc_name']) ?>
                </span>
            <?php endif; ?>
            <h1 style="font-size:22px; font-weight:700; color:var(--color-primary-navy); margin-bottom:12px;">
                <?= e($form['title']) ?>
            </h1>
            <?php if (!empty($form['description'])): ?>
                <p style="font-size:14px; color:var(--text-secondary); line-height:1.8;">
                    <?= nl2br(e($form['description'])) ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success" style="padding:16px 20px; border-radius:var(--radius-md); background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; text-align:center; font-weight:600; margin-bottom:24px;">
                <?= svgIcon('check', '', 20) ?> <?= e($successMessage) ?>
            </div>
            <div style="text-align:center;">
                <a href="index.php" class="btn-primary" style="display:inline-flex;">بازگشت به صفحه اصلی</a>
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
</section>

<?php include 'includes/footer.php'; ?>
