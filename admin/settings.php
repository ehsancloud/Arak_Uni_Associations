<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // تغییر تنظیمات عمومی
    if (isset($_POST['save_settings'])) {
        $title = $_POST['site_title'];
        $font = $_POST['font_family'];
        $about = $_POST['about_text'] ?? '';
        $stmt = $pdo->prepare("UPDATE settings SET site_title=?, font_family=?, about_text=? WHERE id=1");
        $stmt->execute([$title, $font, $about]);
        $msg = '<div class="alert alert-success">تنظیمات ذخیره شد.</div>';
    }
    // تغییر وضعیت جشنواره
    if (isset($_POST['toggle_festival'])) {
        $new_status = $_POST['current_status'] == 1 ? 0 : 1;
        $pdo->prepare("UPDATE settings SET festival_status=? WHERE id=1")->execute([$new_status]);
        $msg = '<div class="alert alert-success">وضعیت ثبت‌نام جشنواره تغییر کرد.</div>';
    }
}

$stmt = $pdo->query("SELECT * FROM settings WHERE id=1");
$s = $stmt->fetch();

include 'inc/header.php'; ?>

<div class="admin-header"><h2>تنظیمات سایت</h2></div>
<?= $msg ?>

<div class="admin-card">
    <h3>تنظیمات عمومی</h3>
    <form method="POST">
        <div class="form-group">
            <label>عنوان سایت:</label>
            <input type="text" name="site_title" value="<?= htmlspecialchars($s['site_title']) ?>">
        </div>
        <div class="form-group">
            <label>فونت سایت:</label>
            <select name="font_family">
                <option value="Vazirmatn" <?= $s['font_family']=='Vazirmatn'?'selected':'' ?>>وزیر متن</option>
                <option value="IRANSans" <?= $s['font_family']=='IRANSans'?'selected':'' ?>>ایران سنس</option>
                <option value="Yekan" <?= $s['font_family']=='Yekan'?'selected':'' ?>>یکان</option>
                <option value="Sahel" <?= $s['font_family']=='Sahel'?'selected':'' ?>>ساحل</option>
                <option value="Tahoma" <?= $s['font_family']=='Tahoma'?'selected':'' ?>>تاهوما</option>
            </select>
        </div>
        <div class="form-group">
            <label>متن صفحه «درباره ما»:</label>
            <textarea name="about_text" rows="8"><?= htmlspecialchars($s['about_text'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="save_settings" class="btn">ذخیره تغییرات</button>
    </form>
</div>

<div class="admin-card">
    <h3>مدیریت ثبت‌نام جشنواره حرکت</h3>
    <p>وضعیت فعلی: 
        <?php if ($s['festival_status']): ?>
            <strong style="color:#059669;">ثبت‌نام باز است</strong>
        <?php else: ?>
            <strong style="color:#dc2626;">ثبت‌نام بسته است</strong>
        <?php endif; ?>
    </p>
    <form method="POST" style="margin-top:15px;">
        <input type="hidden" name="current_status" value="<?= $s['festival_status'] ?>">
        <button type="submit" name="toggle_festival" class="btn <?= $s['festival_status'] ? 'btn-danger' : 'btn-success' ?>">
            <?= $s['festival_status'] ? 'توقف ثبت‌نام' : 'شروع مجدد ثبت‌نام' ?>
        </button>
    </form>
</div>

<?php include 'inc/footer.php'; ?>
