<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$msg = '';

// حذف
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM associations WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: associations.php'); exit;
}

// افزودن/ویرایش
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $id = (int)($_POST['id'] ?? 0);
    
    // آپلود کاور
    $cover = $_POST['current_cover'] ?? '';
    $up = uploadImage('cover', __DIR__ . '/../uploads/covers/');
    if ($up) $cover = $up;
    
    // آپلود آیکون
    $icon = $_POST['current_icon'] ?? '';
    $up = uploadImage('icon', __DIR__ . '/../uploads/logos/');
    if ($up) $icon = $up;
    
    if ($id > 0) {
        $pdo->prepare("UPDATE associations SET name=?, description=?, cover_image=?, icon=? WHERE id=?")
            ->execute([$name, $desc, $cover, $icon, $id]);
    } else {
        $pdo->prepare("INSERT INTO associations (name, description, cover_image, icon) VALUES (?,?,?,?)")
            ->execute([$name, $desc, $cover, $icon]);
    }
    header('Location: associations.php'); exit;
}

// ویرایش
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM associations WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$list = $pdo->query("SELECT * FROM associations ORDER BY id DESC")->fetchAll();
include 'inc/header.php'; ?>

<div class="admin-header"><h2>مدیریت انجمن‌ها</h2></div>

<div class="admin-card">
    <h3><?= $edit ? 'ویرایش انجمن' : 'افزودن انجمن جدید' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="hidden" name="current_cover" value="<?= $edit['cover_image'] ?? '' ?>">
        <input type="hidden" name="current_icon" value="<?= $edit['icon'] ?? '' ?>">
        
        <div class="form-group">
            <label>نام انجمن:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>توضیحات:</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>تصویر هدر صفحه انجمن (Cover):</label>
            <input type="file" name="cover" accept="image/*">
        </div>
        <div class="form-group">
            <label>آیکون انجمن (برای نمایش در صفحه اصلی):</label>
            <input type="file" name="icon" accept="image/*">
        </div>
        <button type="submit" class="btn"><?= $edit ? 'ویرایش' : 'افزودن' ?></button>
        <?php if ($edit): ?><a href="associations.php" class="btn btn-danger">انصراف</a><?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>لیست انجمن‌ها</h3>
    <table>
        <tr><th>ردیف</th><th>نام</th><th>آیکون</th><th>عملیات</th></tr>
        <?php foreach ($list as $i => $row): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= $row['icon'] ? '<img src="../uploads/logos/'.htmlspecialchars($row['icon']).'" width="40">' : '-' ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn">ویرایش</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'inc/footer.php'; ?>
