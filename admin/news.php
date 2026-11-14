<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM news WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: news.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'] ?? '';
    $assoc = (int)$_POST['association_id'];
    $date = $_POST['created_at'];
    
    $image = $_POST['current_image'] ?? '';
    $up = uploadImage('image', __DIR__ . '/../uploads/news/');
    if ($up) $image = $up;
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE news SET title=?, content=?, association_id=?, image=?, created_at=? WHERE id=?")
            ->execute([$title, $content, $assoc, $image, $date, $id]);
    } else {
        $pdo->prepare("INSERT INTO news (title, content, association_id, image, created_at) VALUES (?,?,?,?,?)")
            ->execute([$title, $content, $assoc, $image, $date]);
    }
    header('Location: news.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$assocs = $pdo->query("SELECT * FROM associations")->fetchAll();
$news_list = $pdo->query("SELECT n.*, a.name as assoc_name FROM news n LEFT JOIN associations a ON n.association_id=a.id ORDER BY n.id DESC")->fetchAll();

include 'inc/header.php'; ?>

<div class="admin-header"><h2>مدیریت اخبار</h2></div>

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
        <div class="form-group">
            <label>مربوط به انجمن:</label>
            <select name="association_id">
                <option value="0">خبر عمومی</option>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($edit['association_id'] ?? 0)==$a['id']?'selected':'' ?>>
                        <?= e($a['name']) ?>
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
