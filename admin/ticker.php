<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM ticker_items WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: ticker.php'); exit;
}
if (isset($_GET['toggle'])) {
    $pdo->prepare("UPDATE ticker_items SET active = 1 - active WHERE id=?")->execute([(int)$_GET['toggle']]);
    header('Location: ticker.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $text = trim($_POST['text']);
    $link = trim($_POST['link'] ?? '');
    $assoc = (int)$_POST['association_id'];
    $sort = (int)$_POST['sort_order'];
    $active = isset($_POST['active']) ? 1 : 0;
    $id = (int)($_POST['id'] ?? 0);

    if ($text !== '') {
        if ($id > 0) {
            $pdo->prepare("UPDATE ticker_items SET text=?, link=?, association_id=?, sort_order=?, active=? WHERE id=?")
                ->execute([$text, $link, $assoc, $sort, $active, $id]);
        } else {
            $pdo->prepare("INSERT INTO ticker_items (text, link, association_id, sort_order, active) VALUES (?,?,?,?,?)")
                ->execute([$text, $link, $assoc, $sort, $active]);
        }
    }
    header('Location: ticker.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM ticker_items WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$assocs = $pdo->query("SELECT * FROM associations ORDER BY name")->fetchAll();
$list = $pdo->query("SELECT t.*, a.name AS assoc_name FROM ticker_items t LEFT JOIN associations a ON t.association_id=a.id ORDER BY t.association_id, t.sort_order, t.id DESC")->fetchAll();

include 'inc/header.php';
requireSuperAdmin();
?>

<div class="admin-header"><h2>مدیریت نوار «آخرین اخبار»</h2></div>

<div class="admin-card">
    <h3><?= $edit ? 'ویرایش آیتم' : 'افزودن آیتم جدید' ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <div class="form-group">
            <label>متن:</label>
            <input type="text" name="text" value="<?= e($edit['text'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>لینک (اختیاری، مثلاً news_view.php?id=3):</label>
            <input type="text" name="link" value="<?= e($edit['link'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>نمایش در:</label>
            <select name="association_id">
                <option value="0">صفحه اصلی (و انجمن‌هایی که نوار اختصاصی ندارند)</option>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($edit['association_id'] ?? 0)==$a['id']?'selected':'' ?>>فقط صفحه انجمن: <?= e($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>ترتیب (عدد کمتر = زودتر):</label>
            <input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="active" style="width:auto;" <?= (!$edit || $edit['active']) ? 'checked' : '' ?>> فعال</label>
        </div>
        <button type="submit" class="btn"><?= $edit ? 'ویرایش' : 'افزودن' ?></button>
        <?php if ($edit): ?><a href="ticker.php" class="btn btn-danger">انصراف</a><?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>آیتم‌های نوار</h3>
    <table>
        <tr><th>متن</th><th>محل نمایش</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr>
        <?php if (!$list): ?><tr><td colspan="5" style="text-align:center;">آیتمی ثبت نشده است.</td></tr><?php endif; ?>
        <?php foreach ($list as $t): ?>
            <tr>
                <td><?= e($t['text']) ?></td>
                <td><?= $t['association_id'] ? e($t['assoc_name']) : 'صفحه اصلی' ?></td>
                <td><?= (int)$t['sort_order'] ?></td>
                <td><?= $t['active'] ? '<span style="color:#059669; font-weight:600;">فعال</span>' : '<span style="color:#94a3b8;">غیرفعال</span>' ?></td>
                <td>
                    <a href="?toggle=<?= $t['id'] ?>" class="btn btn-success"><?= $t['active'] ? 'غیرفعال' : 'فعال' ?></a>
                    <a href="?edit=<?= $t['id'] ?>" class="btn">ویرایش</a>
                    <a href="?delete=<?= $t['id'] ?>" class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'inc/footer.php'; ?>
