<?php
require_once __DIR__ . '/inc/auth.php';

// حذف خبر با کنترل دسترسی
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($isSuperAdmin) {
        $pdo->prepare("DELETE FROM news WHERE id=?")->execute([$delId]);
    } else {
        $pdo->prepare("DELETE FROM news WHERE id=? AND association_id=?")->execute([$delId, $adminAssocId]);
    }
    header('Location: news.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $assoc = $isSuperAdmin ? (int)($_POST['association_id'] ?? 0) : $adminAssocId;
    $date = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d');
    
    $image = $_POST['current_image'] ?? '';
    $up = uploadImage('image', __DIR__ . '/../uploads/news/');
    if ($up) $image = $up;
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($isSuperAdmin) {
            $pdo->prepare("UPDATE news SET title=?, content=?, association_id=?, image=?, created_at=? WHERE id=?")
                ->execute([$title, $content, $assoc, $image, $date, $id]);
        } else {
            $pdo->prepare("UPDATE news SET title=?, content=?, association_id=?, image=?, created_at=? WHERE id=? AND association_id=?")
                ->execute([$title, $content, $adminAssocId, $image, $date, $id, $adminAssocId]);
        }
    } else {
        $pdo->prepare("INSERT INTO news (title, content, association_id, image, created_at) VALUES (?,?,?,?,?)")
            ->execute([$title, $content, $assoc, $image, $date]);
    }
    header('Location: news.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id=?");
        $stmt->execute([$editId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id=? AND association_id=?");
        $stmt->execute([$editId, $adminAssocId]);
    }
    $edit = $stmt->fetch();
}

if ($isSuperAdmin) {
    $assocs = $pdo->query("SELECT * FROM associations ORDER BY name ASC")->fetchAll();
    $news_list = $pdo->query("SELECT n.*, a.name as assoc_name FROM news n LEFT JOIN associations a ON n.association_id=a.id ORDER BY n.id DESC")->fetchAll();
} else {
    $assocs = $pdo->prepare("SELECT * FROM associations WHERE id=?");
    $assocs->execute([$adminAssocId]);
    $assocs = $assocs->fetchAll();
    $nStmt = $pdo->prepare("SELECT n.*, a.name as assoc_name FROM news n LEFT JOIN associations a ON n.association_id=a.id WHERE n.association_id=? ORDER BY n.id DESC");
    $nStmt->execute([$adminAssocId]);
    $news_list = $nStmt->fetchAll();
}

include 'inc/header.php';
?>

<div class="admin-header">
    <h2><?= $isSuperAdmin ? 'مدیریت اخبار و اطلاعیه‌ها' : ('اخبار و اطلاعیه‌ها: ' . htmlspecialchars($currentAssocName ?: 'انجمن')) ?></h2>
</div>

<div class="admin-card">
    <h3><?= $edit ? 'ویرایش خبر' : 'افزودن خبر جدید' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="hidden" name="current_image" value="<?= e($edit['image'] ?? '') ?>">
        
        <div class="form-group">
            <label>عنوان خبر:</label>
            <input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>متن کامل خبر:</label>
            <textarea name="content" rows="8"><?= e($edit['content'] ?? '') ?></textarea>
        </div>
        <div class="form-group" <?= !$isSuperAdmin ? 'style="display:none;"' : '' ?>>
            <label>انجمن مربوطه:</label>
            <select name="association_id">
                <?php if ($isSuperAdmin): ?>
                    <option value="0">دانشگاه اراک (عمومی / بدون انجمن)</option>
                <?php endif; ?>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= (($edit['association_id'] ?? ($isSuperAdmin ? 0 : $adminAssocId)) == $a['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>تاریخ خبر:</label>
            <input type="date" name="created_at" value="<?= e($edit['created_at'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="form-group">
            <label>تصویر خبر (jpg, png, webp, gif):</label>
            <input type="file" name="image" accept="image/*" <?= !$edit?'required':'' ?>>
        </div>
        <button type="submit" class="btn"><?= $edit ? 'ویرایش' : 'افزودن' ?></button>
        <?php if ($edit): ?><a href="news.php" class="btn btn-danger">انصراف</a><?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>لیست اخبار</h3>
    <table>
        <tr><th>تصویر</th><th>عنوان</th><th>انجمن</th><th>تاریخ</th><th>عملیات</th></tr>
        <?php foreach ($news_list as $n): ?>
            <tr>
                <td><?= $n['image'] ? '<img src="../uploads/news/'.e($n['image']).'" width="60">' : '-' ?></td>
                <td><?= e($n['title']) ?></td>
                <td><?= e($n['assoc_name'] ?: 'عمومی') ?></td>
                <td><?= e($n['created_at']) ?></td>
                <td>
                    <a href="../news_view.php?id=<?= $n['id'] ?>" target="_blank" class="btn btn-success">مشاهده</a>
                    <a href="?edit=<?= $n['id'] ?>" class="btn">ویرایش</a>
                    <a href="?delete=<?= $n['id'] ?>" class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'inc/footer.php'; ?>
