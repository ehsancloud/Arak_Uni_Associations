<?php
require_once __DIR__ . '/inc/auth.php';
requireSuperAdmin();

$message = '';
$error = '';

// عملیات حذف کاربر
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId === 1 || $delId === (int)$_SESSION['admin_id']) {
        $error = 'امکان حذف حساب مدیر ارشد یا حساب فعال وجود ندارد!';
    } else {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$delId]);
        header('Location: users.php?msg=deleted');
        exit;
    }
}

// ثبت یا ویرایش دبیر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $association_id = (int)($_POST['association_id'] ?? 0);
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($full_name)) {
        $error = 'نام کاربری و نام دبیر الزامی است.';
    } elseif ($association_id <= 0) {
        $error = 'انتخاب انجمن مربوطه الزامی است.';
    } else {
        // بررسی یکتایی نام کاربری
        $chkStmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $chkStmt->execute([$username, $id]);
        if ($chkStmt->fetch()) {
            $error = 'این نام کاربری قبلاً ثبت شده است. لطفاً نام دیگری انتخاب کنید.';
        } else {
            if ($id > 0) {
                if (!empty($password)) {
                    $passHash = md5($password);
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ?, full_name = ?, association_id = ?, role = 'secretary' WHERE id = ?");
                    $stmt->execute([$username, $passHash, $full_name, $association_id, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, full_name = ?, association_id = ?, role = 'secretary' WHERE id = ?");
                    $stmt->execute([$username, $full_name, $association_id, $id]);
                }
            } else {
                if (empty($password)) {
                    $error = 'تعیین رمز عبور برای حساب جدید الزامی است.';
                } else {
                    $passHash = md5($password);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, password, role, association_id, full_name) VALUES (?, ?, 'secretary', ?, ?)");
                    $stmt->execute([$username, $passHash, $association_id, $full_name]);
                }
            }

            if (empty($error)) {
                header('Location: users.php?msg=saved');
                exit;
            }
        }
    }
}

// واکشی برای ویرایش
$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();

$users = $pdo->query("SELECT u.*, a.name AS assoc_name 
                      FROM admins u 
                      LEFT JOIN associations a ON u.association_id = a.id 
                      ORDER BY u.id ASC")->fetchAll();

include 'inc/header.php';
?>

<div class="admin-header">
    <h2>مدیریت دبیران و دسترسی‌های اختصاصی</h2>
    <a href="users.php#user-editor" class="btn btn-sm">
        <?= svgIcon('user-plus', '', 14) ?> ایجاد حساب دبیر جدید
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'saved') echo 'اطلاعات دبیر با موفقیت ذخیره شد.';
            elseif ($_GET['msg'] === 'deleted') echo 'حساب کاربری با موفقیت حذف گردید.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- جدول دبیران و کاربران -->
<div class="admin-card">
    <h3>فهرست دبیران و مدیران سامانه</h3>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>نام و نام خانوادگی</th>
                    <th>نام کاربری</th>
                    <th>نقش</th>
                    <th>انجمن منتسب</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= (int)$u['id'] ?></td>
                        <td><strong><?= e($u['full_name'] ?: $u['username']) ?></strong></td>
                        <td dir="ltr" style="text-align:right;"><code><?= e($u['username']) ?></code></td>
                        <td>
                            <?php if ($u['role'] === 'superadmin'): ?>
                                <span class="badge badge-warning">مدیر ارشد (Superadmin)</span>
                            <?php else: ?>
                                <span class="badge badge-success">دبیر انجمن</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($u['assoc_name'] ?: 'مدیریت کل دانشگاه') ?></td>
                        <td style="font-size:12.5px;"><?= !empty($u['created_at']) ? formatDate($u['created_at']) : '---' ?></td>
                        <td>
                            <div class="actions-cell">
                                <?php if ($u['id'] != 1): ?>
                                    <a href="users.php?edit=<?= (int)$u['id'] ?>#user-editor" class="btn btn-sm btn-secondary" title="ویرایش">
                                        <?= svgIcon('edit', '', 13) ?> ویرایش
                                    </a>
                                    <a href="users.php?delete=<?= (int)$u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف حساب این دبیر مطمئن هستید؟');" title="حذف">
                                        <?= svgIcon('trash', '', 13) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-size:12px;">حساب اصلی سیستم</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- فرم ایجاد / ویرایش حساب دبیر -->
<div class="admin-card" id="user-editor">
    <h3><?= $edit ? 'ویرایش اطلاعات دبیر: ' . e($edit['full_name'] ?: $edit['username']) : 'تعریف دسترسی دبیر جدید' ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:18px;">
            <div class="form-group">
                <label>نام و نام خانوادگی دبیر: <span style="color:#dc2626;">*</span></label>
                <input type="text" name="full_name" value="<?= e($edit['full_name'] ?? '') ?>" placeholder="مثلاً: علیرضا محمدی" required>
            </div>

            <div class="form-group">
                <label>انجمن مربوطه: <span style="color:#dc2626;">*</span></label>
                <select name="association_id" required>
                    <option value="">-- انتخاب انجمن --</option>
                    <?php foreach ($assocs as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= (!empty($edit['association_id']) && $edit['association_id'] == $a['id']) ? 'selected' : '' ?>>
                            <?= e($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>نام کاربری اختصاصی برای ورود: <span style="color:#dc2626;">*</span></label>
                <input type="text" name="username" value="<?= e($edit['username'] ?? '') ?>" placeholder="مثلاً: sec_ai یا anjoman_chem" required dir="ltr">
            </div>

            <div class="form-group">
                <label>رمز عبور: <?= $edit ? '<span style="font-size:11.5px; color:#64748b;">(تنها در صورت نیاز به تغییر وارد شود)</span>' : '<span style="color:#dc2626;">*</span>' ?></label>
                <input type="password" name="password" placeholder="<?= $edit ? 'بدون تغییر' : 'حداقل ۶ کاراکتر' ?>" <?= $edit ? '' : 'required' ?> dir="ltr">
            </div>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px;">
            <button type="submit" name="save_user" class="btn">
                <?= svgIcon('check', '', 16) ?> ذخیره حساب دبیر
            </button>
            <?php if ($edit): ?>
                <a href="users.php" class="btn btn-secondary">انصراف</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include 'inc/footer.php'; ?>
