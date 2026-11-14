<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM members WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: members.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['full_name'];
    $role = $_POST['role'];
    $assoc = (int)$_POST['association_id'];
    
    $image = $_POST['current_image'] ?? '';
    $up = uploadImage('image', __DIR__ . '/../uploads/profiles/');
    if ($up) $image = $up;
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE members SET full_name=?, role=?, association_id=?, image=? WHERE id=?")
            ->execute([$name, $role, $assoc, $image, $id]);
    } else {
        $pdo->prepare("INSERT INTO members (full_name, role, association_id, image) VALUES (?,?,?,?)")
            ->execute([$name, $role, $assoc, $image]);
    }
    header('Location: members.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$assocs = $pdo->query("SELECT * FROM associations")->fetchAll();
$list = $pdo->query("SELECT m.*, a.name as assoc_name FROM members m LEFT JOIN associations a ON m.association_id=a.id ORDER BY m.id DESC")->fetchAll();

include 'inc/header.php'; ?>

<div class="admin-header"><h2>مدیریت اعضای انجمن‌ها</h2></div>

<div class="admin-card">
    <h3><?= $edit ? 'ویرایش عضو' : 'افزودن عضو جدید' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="hidden" name="current_image" value="<?= $edit['image'] ?? '' ?>">
        
        <div class="form-group">
            <label>نام و نام خانوادگی:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($edit['full_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>سمت (دبیر، نایب دبیر، عضو...):</label>
            <input type="text" name="role" value="<?= htmlspecialchars($edit['role'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>انجمن:</label>
            <select name="association_id" required>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($edit['association_id'] ?? 0)==$a['id']?'selected':'' ?>>
                        <?= htmlspecialchars($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>تصویر عضو:</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn"><?= $edit ? 'ویرایش' : 'افزودن' ?></button>
    </form>
</div>

<div class="admin-card">
    <h3>لیست اعضا</h3>
    <table>
        <tr><th>تصویر</th><th>نام</th><th>سمت</th><th>انجمن</th><th>عملیات</th></tr>
        <?php foreach ($list as $m): ?>
            <tr>
                <td><?= $m['image'] ? '<img src="../uploads/profiles/'.htmlspecialchars($m['image']).'" width="50" style="border-radius:50%;">' : '-' ?></td>
                <td><?= htmlspecialchars($m['full_name']) ?></td>
                <td><?= htmlspecialchars($m['role']) ?></td>
                <td><?= htmlspecialchars($m['assoc_name']) ?></td>
                <td>
                    <a href="?edit=<?= $m['id'] ?>" class="btn">ویرایش</a>
                    <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'inc/footer.php'; ?>
