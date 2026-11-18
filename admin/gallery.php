<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$message = '';
$error = '';
$assocFilter = (int)($_GET['assoc'] ?? 0);

// حذف تصویر
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM gallery_images WHERE id = ?")->execute([$delId]);
    header("Location: gallery.php?assoc=$assocFilter&msg=deleted");
    exit;
}

// ثبت یا ویرایش
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery'])) {
    $id = (int)($_POST['id'] ?? 0);
    $association_id = (int)($_POST['association_id'] ?? 0);
    $caption = trim($_POST['caption'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $image = $_POST['current_image'] ?? '';
    $up = uploadImage('image', __DIR__ . '/../uploads/news/');
    if ($up) {
        $image = $up;
    }

    if (empty($image) && $id === 0) {
        $error = 'انتخاب فایل تصویر الزامی است.';
    } elseif ($association_id <= 0) {
        $error = 'انتخاب انجمن مربوطه الزامی است.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE gallery_images SET association_id = ?, image = ?, caption = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$association_id, $image, $caption, $sort_order, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO gallery_images (association_id, image, caption, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$association_id, $image, $caption, $sort_order]);
        }
        header("Location: gallery.php?assoc=$association_id&msg=saved");
        exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE id = ?");
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();

$where = ["1=1"];
$params = [];
if ($assocFilter > 0) {
    $where[] = "g.association_id = ?";
    $params[] = $assocFilter;
}

$sql = "SELECT g.*, a.name AS assoc_name 
        FROM gallery_images g 
        LEFT JOIN associations a ON g.association_id = a.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY g.sort_order ASC, g.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$galleryList = $stmt->fetchAll();

include 'inc/header.php';
?>

<div class="admin-header">
    <h2>مدیریت گالری تصاویر انجمن‌ها</h2>
    <a href="gallery.php#editor" class="btn btn-sm">
        <?= svgIcon('plus', '', 14) ?> افزودن تصویر جدید
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'saved') echo 'تصویر با موفقیت در گالری ذخیره شد.';
            elseif ($_GET['msg'] === 'deleted') echo 'تصویر با موفقیت حذف گردید.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

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

<!-- لیست تصاویر گالری -->
<div class="admin-card">
    <h3>فهرست تصاویر (تعداد: <?= count($galleryList) ?>)</h3>
    <?php if ($galleryList): ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:18px;">
            <?php foreach ($galleryList as $item): 
                $imgSrc = getNewsImageUrl($item['image']);
            ?>
                <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; overflow:hidden; display:flex; flex-direction:column;">
                    <div style="height:140px; overflow:hidden; background:#000;">
                        <img src="../<?= e($imgSrc) ?>" alt="<?= e($item['caption'] ?? '') ?>" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="padding:12px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <span style="font-size:11.5px; color:#64748b; font-weight:600;"><?= e($item['assoc_name']) ?></span>
                            <p style="font-size:13px; font-weight:600; margin:4px 0 8px; color:#0e1f3d; line-height:1.4;">
                                <?= e($item['caption'] ?: 'بدون عنوان') ?>
                            </p>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:8px; margin-top:8px;">
                            <span style="font-size:11.5px; color:#94a3b8;">ترتیب: <?= (int)$item['sort_order'] ?></span>
                            <div class="actions-cell">
                                <a href="gallery.php?assoc=<?= $assocFilter ?>&edit=<?= (int)$item['id'] ?>#editor" class="btn btn-sm btn-secondary" title="ویرایش">
                                    <?= svgIcon('edit', '', 13) ?>
                                </a>
                                <a href="gallery.php?assoc=<?= $assocFilter ?>&delete=<?= (int)$item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این تصویر مطمئن هستید؟');" title="حذف">
                                    <?= svgIcon('trash', '', 13) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:#64748b; font-size:13.5px;">تصویری در این بخش ثبت نشده است.</p>
    <?php endif; ?>
</div>

<!-- فرم ایجاد / ویرایش تصویر -->
<div class="admin-card" id="editor">
    <h3><?= $edit ? 'ویرایش تصویر گالری' : 'افزودن تصویر جدید به گالری' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
        <input type="hidden" name="current_image" value="<?= e($edit['image'] ?? '') ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:18px;">
            <div class="form-group">
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
                <label>کپشن / عنوان تصویر:</label>
                <input type="text" name="caption" value="<?= e($edit['caption'] ?? '') ?>" placeholder="مثلاً: کارگاه یادگیری عمیق - اسفند ۱۴۰۴">
            </div>

            <div class="form-group">
                <label>ترتیب نمایش (اعداد کوچکتر بالاتر):</label>
                <input type="number" name="sort_order" value="<?= isset($edit['sort_order']) ? (int)$edit['sort_order'] : 0 ?>">
            </div>
        </div>

        <div class="form-group">
            <label>انتخاب فایل تصویر: <?= $edit ? '(اختیاری در صورت تمایل به تغییر)' : '<span style="color:#dc2626;">*</span>' ?></label>
            <input type="file" name="image" accept="image/*" <?= $edit ? '' : 'required' ?>>
            <?php if (!empty($edit['image'])): ?>
                <div style="margin-top:8px;">
                    <img src="../<?= e(getNewsImageUrl($edit['image'])) ?>" alt="" style="height:70px; border-radius:6px;">
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px;">
            <button type="submit" name="save_gallery" class="btn">
                <?= svgIcon('check', '', 16) ?> ذخیره تصویر
            </button>
            <?php if ($edit): ?>
                <a href="gallery.php?assoc=<?= $assocFilter ?>" class="btn btn-secondary">انصراف</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include 'inc/footer.php'; ?>
