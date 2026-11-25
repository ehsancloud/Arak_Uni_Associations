<?php
require_once __DIR__ . '/inc/auth.php';
requireSuperAdmin();

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM festival_registrations WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: festival_regs.php'); exit;
}

// خروجی اکسل (CSV)
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=festival_registrations.csv');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM برای فارسی
    fputcsv($out, ['ردیف', 'نام', 'شماره دانشجویی', 'تلفن', 'تاریخ']);
    $rows = $pdo->query("SELECT * FROM festival_registrations")->fetchAll();
    foreach ($rows as $i => $r) {
        fputcsv($out, [$i+1, $r['full_name'], $r['student_id'], $r['phone'], $r['register_date']]);
    }
    fclose($out); exit;
}

$list = $pdo->query("SELECT * FROM festival_registrations ORDER BY id DESC")->fetchAll();
$status = $pdo->query("SELECT festival_status FROM settings WHERE id=1")->fetchColumn();

include 'inc/header.php';
?>

<div class="admin-header">
    <h2>ثبت‌نامی‌های کادر اجرایی جشنواره حرکت</h2>
    <div>
        <a href="?export=1" class="btn btn-success">خروجی فایل اکسل (CSV)</a>
        <a href="settings.php" class="btn">تنظیمات ثبت‌نام</a>
    </div>
</div>

<div class="admin-card">
    <h3>
        وضعیت فعلی فرم ثبت‌نام: 
        <?php if ($status): ?>
            <span style="color:#059669;">فعال و باز</span>
        <?php else: ?>
            <span style="color:#dc2626;">بسته شده</span>
        <?php endif; ?>
    </h3>
    <p>برای تغییر وضعیت ثبت‌نام، به صفحه <a href="settings.php">تنظیمات</a> یا <a href="forms.php?edit=1">ویرایش فرم جشنواره</a> مراجعه کنید.</p>
    <div style="margin-top:10px; background:#eff6ff; padding:10px 14px; border-radius:6px; border:1px solid #bfdbfe; font-size:13px; color:#1e40af;">
        همچنین می‌توانید از بخش جدید <a href="submissions.php?form_id=1" style="font-weight:700; text-decoration:underline;">مدیریت ثبت‌نامی‌ها</a> پاسخ‌های تفکیک‌شده را مدیریت کنید.
    </div>
</div>

<div class="admin-card">
    <h3>لیست ثبت‌نام شدگان (تعداد کل: <?= count($list) ?> نفر)</h3>
    <table>
        <tr><th>ردیف</th><th>نام و نام خانوادگی</th><th>شماره دانشجویی</th><th>تلفن</th><th>تاریخ ثبت‌نام</th><th>عملیات</th></tr>
        <?php if (count($list) == 0): ?>
            <tr><td colspan="6" style="text-align:center;">هنوز ثبت‌نامی ثبت نشده است.</td></tr>
        <?php else: foreach ($list as $i => $r): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($r['full_name']) ?></td>
                <td><?= htmlspecialchars($r['student_id']) ?></td>
                <td><?= htmlspecialchars($r['phone']) ?></td>
                <td><?= $r['register_date'] ?></td>
                <td><a href="?delete=<?= $r['id'] ?>" class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a></td>
            </tr>
        <?php endforeach; endif; ?>
    </table>
</div>

<?php include 'inc/footer.php'; ?>
