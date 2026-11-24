include 'inc/header.php';

// حذف عضو با بررسی دسترسی
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($isSuperAdmin) {
        $pdo->prepare("DELETE FROM members WHERE id=?")->execute([$delId]);
    } else {
        $pdo->prepare("DELETE FROM members WHERE id=? AND association_id=?")->execute([$delId, $adminAssocId]);
    }
    header('Location: members.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $assoc = $isSuperAdmin ? (int)($_POST['association_id'] ?? 0) : $adminAssocId;
    
    $image = $_POST['current_image'] ?? '';
    $up = uploadImage('image', __DIR__ . '/../uploads/profiles/');
    if ($up) $image = $up;
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($isSuperAdmin) {
            $pdo->prepare("UPDATE members SET full_name=?, role=?, association_id=?, image=? WHERE id=?")
                ->execute([$name, $role, $assoc, $image, $id]);
        } else {
            $pdo->prepare("UPDATE members SET full_name=?, role=?, association_id=?, image=? WHERE id=? AND association_id=?")
                ->execute([$name, $role, $adminAssocId, $image, $id, $adminAssocId]);
        }
    } else {
        $pdo->prepare("INSERT INTO members (full_name, role, association_id, image) VALUES (?,?,?,?)")
            ->execute([$name, $role, $assoc, $image]);
    }
    header('Location: members.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE id=?");
        $stmt->execute([$editId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE id=? AND association_id=?");
        $stmt->execute([$editId, $adminAssocId]);
    }
    $edit = $stmt->fetch();
}

if ($isSuperAdmin) {
    $assocs = $pdo->query("SELECT * FROM associations ORDER BY name ASC")->fetchAll();
    $list = $pdo->query("SELECT m.*, a.name as assoc_name FROM members m LEFT JOIN associations a ON m.association_id=a.id ORDER BY m.id DESC")->fetchAll();
} else {
    $assocs = $pdo->prepare("SELECT * FROM associations WHERE id=?");
    $assocs->execute([$adminAssocId]);
    $assocs = $assocs->fetchAll();
    $listStmt = $pdo->prepare("SELECT m.*, a.name as assoc_name FROM members m LEFT JOIN associations a ON m.association_id=a.id WHERE m.association_id=? ORDER BY m.id DESC");
    $listStmt->execute([$adminAssocId]);
    $list = $listStmt->fetchAll();
}
?>

<div class="admin-header">
    <h2><?= $isSuperAdmin ? 'مدیریت اعضای شورای مرکزی انجمن‌ها' : ('اعضای شورای مرکزی: ' . htmlspecialchars($currentAssocName ?: 'انجمن')) ?></h2>
</div>

<div class="admin-card">
    <h3><?= $edit ? 'ویرایش اطلاعات عضو شورا' : 'افزودن عضو جدید به شورای مرکزی' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="hidden" name="current_image" value="<?= $edit['image'] ?? '' ?>">
        
        <div class="form-group">
            <label>نام و نام خانوادگی:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($edit['full_name'] ?? '') ?>" placeholder="مثلاً: امیرحسین زمانی" required>
        </div>
        <div class="form-group">
            <label>سمت در شورا (دبیر، نایب دبیر، روابط عمومی، عضو شورا...):</label>
            <input type="text" name="role" value="<?= htmlspecialchars($edit['role'] ?? '') ?>" placeholder="مثلاً: دبیر انجمن" required>
        </div>
        <div class="form-group" <?= !$isSuperAdmin ? 'style="display:none;"' : '' ?>>
            <label>انجمن مربوطه:</label>
            <select name="association_id" required>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= (($edit['association_id'] ?? ($isSuperAdmin ? 0 : $adminAssocId)) == $a['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>تصویر پرتره عضو (اختیاری):</label>
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($edit['image'])): ?>
                <div style="margin-top:8px;">
                    <img src="../uploads/profiles/<?= htmlspecialchars($edit['image']) ?>" width="60" height="60" style="border-radius:50%; object-fit:cover;">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn"><?= $edit ? 'ذخیره تغییرات' : 'افزودن عضو' ?></button>
        <?php if ($edit): ?><a href="members.php" class="btn btn-secondary">انصراف</a><?php endif; ?>
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
