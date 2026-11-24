include 'inc/header.php';

$message = '';
$error = '';
$assocFilter = $isSuperAdmin ? (int)($_GET['assoc'] ?? 0) : $adminAssocId;
$kindFilter = $_GET['kind'] ?? '';

// حذف سند با بررسی دسترسی
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT file FROM documents WHERE id = ?");
        $stmt->execute([$delId]);
    } else {
        $stmt = $pdo->prepare("SELECT file FROM documents WHERE id = ? AND association_id = ?");
        $stmt->execute([$delId, $adminAssocId]);
    }
    $doc = $stmt->fetch();
    if ($doc) {
        if (!empty($doc['file'])) {
            @unlink(__DIR__ . '/../uploads/documents/' . $doc['file']);
        }
        if ($isSuperAdmin) {
            $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$delId]);
        } else {
            $pdo->prepare("DELETE FROM documents WHERE id = ? AND association_id = ?")->execute([$delId, $adminAssocId]);
        }
    }
    header("Location: documents.php?assoc=$assocFilter&kind=$kindFilter&msg=deleted");
    exit;
}

// ثبت یا ویرایش
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_document'])) {
    $id = (int)($_POST['id'] ?? 0);
    $association_id = $isSuperAdmin ? (int)($_POST['association_id'] ?? 0) : $adminAssocId;
    $title = trim($_POST['title'] ?? '');
    $kind = in_array($_POST['kind'] ?? '', ['report', 'file']) ? $_POST['kind'] : 'file';
    $period_label = trim($_POST['period_label'] ?? '');

    $file = $_POST['current_file'] ?? '';
    $up = uploadDocument('file', __DIR__ . '/../uploads/documents/');
    if ($up) {
        $file = $up;
    }

    if (empty($title)) {
        $error = 'عنوان سند یا گزارش الزامی است.';
    } elseif ($association_id <= 0) {
        $error = 'انتخاب انجمن مربوطه الزامی است.';
    } elseif (empty($file) && $id === 0) {
        $error = 'انتخاب و آپلود فایل الزامی است.';
    } else {
        if ($id > 0) {
            if ($isSuperAdmin) {
                $stmt = $pdo->prepare("UPDATE documents SET association_id = ?, title = ?, kind = ?, file = ?, period_label = ? WHERE id = ?");
                $stmt->execute([$association_id, $title, $kind, $file, $period_label, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE documents SET association_id = ?, title = ?, kind = ?, file = ?, period_label = ? WHERE id = ? AND association_id = ?");
                $stmt->execute([$adminAssocId, $title, $kind, $file, $period_label, $id, $adminAssocId]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO documents (association_id, title, kind, file, period_label) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$association_id, $title, $kind, $file, $period_label]);
        }
        header("Location: documents.php?assoc=$association_id&kind=$kindFilter&msg=saved");
        exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$editId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND association_id = ?");
        $stmt->execute([$editId, $adminAssocId]);
    }
    $edit = $stmt->fetch();
}

if ($isSuperAdmin) {
    $assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();
    $where = ["1=1"];
    $params = [];
    if ($assocFilter > 0) {
        $where[] = "d.association_id = ?";
        $params[] = $assocFilter;
    }
} else {
    $assocs = $pdo->prepare("SELECT id, name FROM associations WHERE id = ?");
    $assocs->execute([$adminAssocId]);
    $assocs = $assocs->fetchAll();
    $where = ["d.association_id = ?"];
    $params = [$adminAssocId];
}

if (!empty($kindFilter) && in_array($kindFilter, ['report', 'file'])) {
    $where[] = "d.kind = ?";
    $params[] = $kindFilter;
}

$sql = "SELECT d.*, a.name AS assoc_name 
        FROM documents d 
        LEFT JOIN associations a ON d.association_id = a.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY d.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$docsList = $stmt->fetchAll();
?>

<div class="admin-header">
    <h2>مدیریت اسناد، آیین‌نامه‌ها و گزارش‌ها</h2>
    <a href="documents.php#editor" class="btn btn-sm">
        <?= svgIcon('plus', '', 14) ?> افزودن سند یا گزارش جدید
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'saved') echo 'سند با موفقیت ذخیره شد.';
            elseif ($_GET['msg'] === 'deleted') echo 'سند با موفقیت حذف گردید.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- فیلتر -->
<div class="admin-card">
    <form method="GET" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
        <?php if ($isSuperAdmin): ?>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:600;">انجمن:</label>
            <select name="assoc" onchange="this.form.submit()" style="padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px;">
                <option value="0">همه انجمن‌ها</option>
                <?php foreach ($assocs as $a): ?>
                    <option value="<?= (int)$a['id'] ?>" <?= $assocFilter == $a['id'] ? 'selected' : '' ?>>
                        <?= e($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:600;">نوع سند:</label>
            <select name="kind" onchange="this.form.submit()" style="padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px;">
                <option value="">همه انواع</option>
                <option value="report" <?= $kindFilter === 'report' ? 'selected' : '' ?>>گزارش عملکرد</option>
                <option value="file" <?= $kindFilter === 'file' ? 'selected' : '' ?>>فایل / اساسنامه</option>
            </select>
        </div>

        <?php if ($assocFilter > 0 || !empty($kindFilter)): ?>
            <div>
                <a href="documents.php" class="btn btn-sm btn-secondary" style="padding:8px 14px;">ریست فیلتر</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- جدول اسناد -->
<div class="admin-card">
    <h3>فهرست اسناد و گزارش‌ها (تعداد: <?= count($docsList) ?>)</h3>
    <?php if ($docsList): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>عنوان سند</th>
                        <th>انجمن</th>
                        <th>نوع</th>
                        <th>دوره زمانی</th>
                        <th>فایل پیوست</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($docsList as $doc): ?>
                        <tr>
                            <td><strong><?= e($doc['title']) ?></strong></td>
                            <td><?= e($doc['assoc_name']) ?></td>
                            <td>
                                <span class="badge <?= $doc['kind'] === 'report' ? 'badge-warning' : 'badge-secondary' ?>">
                                    <?= $doc['kind'] === 'report' ? 'گزارش عملکرد' : 'فایل / سند' ?>
                                </span>
                            </td>
                            <td><?= e($doc['period_label'] ?: '---') ?></td>
                            <td>
                                <?php if (!empty($doc['file'])): ?>
                                    <a href="../uploads/documents/<?= e($doc['file']) ?>" target="_blank" class="btn btn-sm btn-secondary" style="font-size:11.5px;">
                                        <?= svgIcon('download', '', 12) ?> دریافت فایل
                                    </a>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">بدون فایل</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12.5px;"><?= formatDate($doc['created_at']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="documents.php?assoc=<?= $assocFilter ?>&kind=<?= $kindFilter ?>&edit=<?= (int)$doc['id'] ?>#editor" class="btn btn-sm btn-secondary" title="ویرایش">
                                        <?= svgIcon('edit', '', 13) ?>
                                    </a>
                                    <a href="documents.php?assoc=<?= $assocFilter ?>&kind=<?= $kindFilter ?>&delete=<?= (int)$doc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این سند مطمئن هستید؟');" title="حذف">
                                        <?= svgIcon('trash', '', 13) ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color:#64748b; font-size:13.5px;">سندی در این بخش ثبت نشده است.</p>
    <?php endif; ?>
</div>

<!-- فرم ایجاد / ویرایش سند -->
<div class="admin-card" id="editor">
    <h3><?= $edit ? 'ویرایش سند: ' . e($edit['title']) : 'افزودن سند یا گزارش جدید' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
        <input type="hidden" name="current_file" value="<?= e($edit['file'] ?? '') ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:18px;">
            <div class="form-group">
                <label>عنوان سند یا گزارش: <span style="color:#dc2626;">*</span></label>
                <input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً: اساسنامه مصوب انجمن علمی" required>
            </div>

            <div class="form-group" <?= !$isSuperAdmin ? 'style="display:none;"' : '' ?>>
                <label>انجمن مربوطه: <span style="color:#dc2626;">*</span></label>
                <select name="association_id" required>
                    <option value="">-- انتخاب انجمن --</option>
                    <?php foreach ($assocs as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= (!empty($edit['association_id']) && $edit['association_id'] == $a['id']) ? 'selected' : ($assocFilter == $a['id'] ? 'selected' : '') ?>>
                            <?= e($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>نوع سند:</label>
                <select name="kind">
                    <option value="file" <?= (!empty($edit['kind']) && $edit['kind'] === 'file') ? 'selected' : '' ?>>فایل / اساسنامه / آیین‌نامه</option>
                    <option value="report" <?= (!empty($edit['kind']) && $edit['kind'] === 'report') ? 'selected' : '' ?>>گزارش عملکرد دوره‌ای</option>
                </select>
            </div>

            <div class="form-group">
                <label>برچسب دوره زمانی (اختیاری):</label>
                <input type="text" name="period_label" value="<?= e($edit['period_label'] ?? '') ?>" placeholder="مثلاً: دوره ۱۴۰۳-۱۴۰۴ یا نیمسال اول">
            </div>
        </div>

        <div class="form-group">
            <label>انتخاب فایل پیوست (PDF, Word, Zip): <?= $edit ? '(اختیاری در صورت تمایل به تغییر)' : '<span style="color:#dc2626;">*</span>' ?></label>
            <input type="file" name="file" accept=".pdf,.doc,.docx,.zip,.rar,.xlsx,.pptx" <?= $edit ? '' : 'required' ?>>
            <?php if (!empty($edit['file'])): ?>
                <div style="margin-top:6px; font-size:12.5px; color:#64748b;">
                    فایل فعلی: <a href="../uploads/documents/<?= e($edit['file']) ?>" target="_blank"><?= e($edit['file']) ?></a>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px;">
            <button type="submit" name="save_document" class="btn">
                <?= svgIcon('check', '', 16) ?> ذخیره سند
            </button>
            <?php if ($edit): ?>
                <a href="documents.php?assoc=<?= $assocFilter ?>" class="btn btn-secondary">انصراف</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include 'inc/footer.php'; ?>
