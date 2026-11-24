include 'inc/header.php';

$message = '';
$error = '';
$assocFilter = $isSuperAdmin ? (int)($_GET['assoc'] ?? 0) : $adminAssocId;

// حذف نشریه با بررسی دسترسی
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT file FROM publications WHERE id = ?");
        $stmt->execute([$delId]);
    } else {
        $stmt = $pdo->prepare("SELECT file FROM publications WHERE id = ? AND association_id = ?");
        $stmt->execute([$delId, $adminAssocId]);
    }
    $pub = $stmt->fetch();
    if ($pub) {
        if (!empty($pub['file'])) {
            @unlink(__DIR__ . '/../uploads/publications/' . $pub['file']);
        }
        if ($isSuperAdmin) {
            $pdo->prepare("DELETE FROM publications WHERE id = ?")->execute([$delId]);
        } else {
            $pdo->prepare("DELETE FROM publications WHERE id = ? AND association_id = ?")->execute([$delId, $adminAssocId]);
        }
    }
    header("Location: publications.php?assoc=$assocFilter&msg=deleted");
    exit;
}

// ثبت یا ویرایش
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_publication'])) {
    $id = (int)($_POST['id'] ?? 0);
    $association_id = $isSuperAdmin ? (int)($_POST['association_id'] ?? 0) : $adminAssocId;
    $title = trim($_POST['title'] ?? '');
    $issue_no = trim($_POST['issue_no'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $published_at = !empty($_POST['published_at']) ? trim($_POST['published_at']) : date('Y-m-d');

    // آپلود جلد
    $cover_image = $_POST['current_cover'] ?? '';
    $upCover = uploadImage('cover_image', __DIR__ . '/../uploads/news/');
    if ($upCover) {
        $cover_image = $upCover;
    }

    // آپلود فایل PDF
    $file = $_POST['current_file'] ?? '';
    $upFile = uploadDocument('file', __DIR__ . '/../uploads/publications/');
    if ($upFile) {
        $file = $upFile;
    }

    if (empty($title)) {
        $error = 'عنوان نشریه الزامی است.';
    } elseif ($association_id <= 0) {
        $error = 'انتخاب انجمن الزامی است.';
    } elseif (empty($file) && $id === 0) {
        $error = 'آپلود فایل PDF نشریه الزامی است.';
    } else {
        if ($id > 0) {
            if ($isSuperAdmin) {
                $stmt = $pdo->prepare("UPDATE publications SET association_id = ?, title = ?, issue_no = ?, summary = ?, cover_image = ?, file = ?, published_at = ? WHERE id = ?");
                $stmt->execute([$association_id, $title, $issue_no, $summary, $cover_image, $file, $published_at, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE publications SET association_id = ?, title = ?, issue_no = ?, summary = ?, cover_image = ?, file = ?, published_at = ? WHERE id = ? AND association_id = ?");
                $stmt->execute([$adminAssocId, $title, $issue_no, $summary, $cover_image, $file, $published_at, $id, $adminAssocId]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO publications (association_id, title, issue_no, summary, cover_image, file, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$association_id, $title, $issue_no, $summary, $cover_image, $file, $published_at]);
        }
        header("Location: publications.php?assoc=$association_id&msg=saved");
        exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ?");
        $stmt->execute([$editId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ? AND association_id = ?");
        $stmt->execute([$editId, $adminAssocId]);
    }
    $edit = $stmt->fetch();
}

if ($isSuperAdmin) {
    $assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();
    $where = ["1=1"];
    $params = [];
    if ($assocFilter > 0) {
        $where[] = "p.association_id = ?";
        $params[] = $assocFilter;
    }
} else {
    $assocs = $pdo->prepare("SELECT id, name FROM associations WHERE id = ?");
    $assocs->execute([$adminAssocId]);
    $assocs = $assocs->fetchAll();
    $where = ["p.association_id = ?"];
    $params = [$adminAssocId];
}

$sql = "SELECT p.*, a.name AS assoc_name 
        FROM publications p 
        LEFT JOIN associations a ON p.association_id = a.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY p.published_at DESC, p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pubsList = $stmt->fetchAll();
?>

<div class="admin-header">
    <h2>مدیریت نشریات و گاهنامه‌های علمی</h2>
    <a href="publications.php#editor" class="btn btn-sm">
        <?= svgIcon('plus', '', 14) ?> ثبت شماره جدید نشریه
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'saved') echo 'شماره نشریه با موفقیت ذخیره شد.';
            elseif ($_GET['msg'] === 'deleted') echo 'نشریه با موفقیت حذف گردید.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($isSuperAdmin): ?>
<!-- فیلتر انجمن -->
<div class="admin-card">
    <form method="GET" style="display:flex; gap:12px; align-items:center;">
        <label style="font-weight:600; font-size:13.5px;">فیلتر بر اساس انجمن:</label>
        <select name="assoc" onchange="this.form.submit()" style="padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px;">
            <option value="0">همه انجمن‌ها</option>
            <?php foreach ($assocs as $a): ?>
                <option value="<?= (int)$a['id'] ?>" <?= $assocFilter == $a['id'] ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php endif; ?>

<!-- جدول نشریات -->
<div class="admin-card">
    <h3>فهرست نشریات (تعداد: <?= count($pubsList) ?>)</h3>
    <?php if ($pubsList): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>جلد</th>
                        <th>عنوان نشریه</th>
                        <th>شماره / دوره</th>
                        <th>انجمن مربوطه</th>
                        <th>تاریخ انتشار</th>
                        <th>فایل PDF</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pubsList as $pub): 
                        $cover = !empty($pub['cover_image']) ? getNewsImageUrl($pub['cover_image']) : '../assets/images/default-cover.jpg';
                    ?>
                        <tr>
                            <td style="width:50px;">
                                <img src="../<?= e($cover) ?>" alt="" style="width:40px; height:52px; object-fit:cover; border-radius:4px; border:1px solid #e2e8f0;">
                            </td>
                            <td>
                                <strong><a href="../publication.php?id=<?= (int)$pub['id'] ?>" target="_blank"><?= e($pub['title']) ?></a></strong>
                            </td>
                            <td><?= e($pub['issue_no'] ?: '---') ?></td>
                            <td><?= e($pub['assoc_name']) ?></td>
                            <td style="font-size:12.5px;"><?= !empty($pub['published_at']) ? formatDate($pub['published_at']) : '---' ?></td>
                            <td>
                                <?php if (!empty($pub['file'])): ?>
                                    <a href="../uploads/publications/<?= e($pub['file']) ?>" target="_blank" class="btn btn-sm btn-secondary" style="font-size:11.5px;">
                                        <?= svgIcon('download', '', 12) ?> دانلود PDF
                                    </a>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">بدون فایل</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="publications.php?assoc=<?= $assocFilter ?>&edit=<?= (int)$pub['id'] ?>#editor" class="btn btn-sm btn-secondary" title="ویرایش">
                                        <?= svgIcon('edit', '', 13) ?>
                                    </a>
                                    <a href="../publication.php?id=<?= (int)$pub['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="مشاهده">
                                        <?= svgIcon('eye', '', 13) ?>
                                    </a>
                                    <a href="publications.php?assoc=<?= $assocFilter ?>&delete=<?= (int)$pub['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این نشریه مطمئن هستید؟');" title="حذف">
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
        <p style="color:#64748b; font-size:13.5px;">نشریه‌ای در این بخش ثبت نشده است.</p>
    <?php endif; ?>
</div>

<!-- فرم ایجاد / ویرایش نشریه -->
<div class="admin-card" id="editor">
    <h3><?= $edit ? 'ویرایش نشریه: ' . e($edit['title']) : 'ثبت شماره جدید نشریه' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
        <input type="hidden" name="current_cover" value="<?= e($edit['cover_image'] ?? '') ?>">
        <input type="hidden" name="current_file" value="<?= e($edit['file'] ?? '') ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:18px;">
            <div class="form-group">
                <label>عنوان نشریه: <span style="color:#dc2626;">*</span></label>
                <input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً: نشریه پردازش هوشمند" required>
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
                <label>شماره یا دوره انتشار:</label>
                <input type="text" name="issue_no" value="<?= e($edit['issue_no'] ?? '') ?>" placeholder="مثلاً: شماره ۱۰ - پاییز ۱۴۰۴">
            </div>

            <div class="form-group">
                <label>تاریخ انتشار:</label>
                <input type="date" name="published_at" value="<?= !empty($edit['published_at']) ? date('Y-m-d', strtotime($edit['published_at'])) : date('Y-m-d') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>خلاصه، فهرست مقالات یا سخن سردبیر:</label>
            <textarea name="summary" rows="4" placeholder="خلاصه‌ای از مباحث و مقالات مطرح‌شده در این شماره"><?= e($edit['summary'] ?? '') ?></textarea>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
            <div class="form-group">
                <label>تصویر روی جلد نشریه (JPG, PNG):</label>
                <input type="file" name="cover_image" accept="image/*">
                <?php if (!empty($edit['cover_image'])): ?>
                    <div style="margin-top:6px;">
                        <img src="../<?= e(getNewsImageUrl($edit['cover_image'])) ?>" alt="" style="height:60px; border-radius:4px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>فایل کامل نشریه (PDF): <?= $edit ? '(اختیاری در صورت تمایل به تغییر)' : '<span style="color:#dc2626;">*</span>' ?></label>
                <input type="file" name="file" accept=".pdf" <?= $edit ? '' : 'required' ?>>
                <?php if (!empty($edit['file'])): ?>
                    <div style="margin-top:6px; font-size:12.5px; color:#64748b;">
                        فایل فعلی: <a href="../uploads/publications/<?= e($edit['file']) ?>" target="_blank"><?= e($edit['file']) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px;">
            <button type="submit" name="save_publication" class="btn">
                <?= svgIcon('check', '', 16) ?> ذخیره نشریه
            </button>
            <?php if ($edit): ?>
                <a href="publications.php?assoc=<?= $assocFilter ?>" class="btn btn-secondary">انصراف</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include 'inc/footer.php'; ?>
