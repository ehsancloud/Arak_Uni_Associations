include 'inc/header.php';

$message = '';
$error = '';

// حذف رویداد با بررسی دسترسی
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($isSuperAdmin) {
        $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$delId]);
    } else {
        $pdo->prepare("DELETE FROM events WHERE id = ? AND association_id = ?")->execute([$delId, $adminAssocId]);
    }
    header('Location: events.php?msg=deleted');
    exit;
}

// تغییر وضعیت انتشار
if (isset($_GET['toggle_publish'])) {
    $pId = (int)$_GET['toggle_publish'];
    if ($isSuperAdmin) {
        $pdo->prepare("UPDATE events SET is_published = 1 - is_published WHERE id = ?")->execute([$pId]);
    } else {
        $pdo->prepare("UPDATE events SET is_published = 1 - is_published WHERE id = ? AND association_id = ?")->execute([$pId, $adminAssocId]);
    }
    header('Location: events.php?msg=status_updated');
    exit;
}

// ثبت یا ویرایش رویداد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $association_id = $isSuperAdmin ? (int)($_POST['association_id'] ?? 0) : $adminAssocId;
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $starts_at = trim($_POST['starts_at'] ?? '');
    $ends_at = !empty($_POST['ends_at']) ? trim($_POST['ends_at']) : null;
    $form_id = (int)($_POST['form_id'] ?? 0);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $auto_create_form = isset($_POST['auto_create_form']) ? 1 : 0;

    $image = $_POST['current_image'] ?? '';
    $up = uploadImage('image', __DIR__ . '/../uploads/news/');
    if ($up) {
        $image = $up;
    }

    if (empty($title) || empty($starts_at)) {
        $error = 'عنوان رویداد و تاریخ شروع الزامی هستند.';
    } else {
        // اگر گزینه ایجاد فرم ثبت‌نام پیش‌فرض فعال باشد و هنوز فرمی انتخاب نشده
        if ($auto_create_form && $form_id === 0) {
            $fTitle = 'ثبت‌نام در: ' . $title;
            $fDesc = 'لطفاً فرم زیر را جهت رزرو و ثبت‌نام در رویداد «' . $title . '» تکمیل نمایید.';
            $fStmt = $pdo->prepare("INSERT INTO forms (association_id, type, title, description, is_open, capacity) VALUES (?, 'event', ?, ?, 1, 0)");
            $fStmt->execute([$association_id, $fTitle, $fDesc]);
            $form_id = (int)$pdo->lastInsertId();

            // درج فیلدهای استاندارد
            $fIns = $pdo->prepare("INSERT INTO form_fields (form_id, label, type, required, is_unique, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $fIns->execute([$form_id, 'نام و نام خانوادگی', 'text', 1, 0, 1]);
            $fIns->execute([$form_id, 'شماره دانشجویی', 'student_id', 1, 1, 2]);
            $fIns->execute([$form_id, 'شماره تماس (موبایل)', 'tel', 1, 0, 3]);
        }

        if ($id > 0) {
            if ($isSuperAdmin) {
                $stmt = $pdo->prepare("UPDATE events SET title = ?, association_id = ?, description = ?, location = ?, starts_at = ?, ends_at = ?, image = ?, form_id = ?, is_published = ? WHERE id = ?");
                $stmt->execute([$title, $association_id, $description, $location, $starts_at, $ends_at, $image, $form_id, $is_published, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE events SET title = ?, association_id = ?, description = ?, location = ?, starts_at = ?, ends_at = ?, image = ?, form_id = ?, is_published = ? WHERE id = ? AND association_id = ?");
                $stmt->execute([$title, $adminAssocId, $description, $location, $starts_at, $ends_at, $image, $form_id, $is_published, $id, $adminAssocId]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO events (title, association_id, description, location, starts_at, ends_at, image, form_id, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $association_id, $description, $location, $starts_at, $ends_at, $image, $form_id, $is_published]);
        }

        header('Location: events.php?msg=saved');
        exit;
    }
}

// بارگذاری داده‌ها برای ویرایش
$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$editId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND association_id = ?");
        $stmt->execute([$editId, $adminAssocId]);
    }
    $edit = $stmt->fetch();
}

if ($isSuperAdmin) {
    $assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();
    $forms = $pdo->query("SELECT id, title FROM forms ORDER BY id DESC")->fetchAll();
    $events = $pdo->query("SELECT e.*, a.name AS assoc_name, f.title AS form_title 
                           FROM events e 
                           LEFT JOIN associations a ON e.association_id = a.id 
                           LEFT JOIN forms f ON e.form_id = f.id 
                           ORDER BY e.starts_at DESC")->fetchAll();
} else {
    $assocs = $pdo->prepare("SELECT id, name FROM associations WHERE id = ?");
    $assocs->execute([$adminAssocId]);
    $assocs = $assocs->fetchAll();

    $fStmt = $pdo->prepare("SELECT id, title FROM forms WHERE association_id = ? OR association_id = 0 ORDER BY id DESC");
    $fStmt->execute([$adminAssocId]);
    $forms = $fStmt->fetchAll();

    $evStmt = $pdo->prepare("SELECT e.*, a.name AS assoc_name, f.title AS form_title 
                             FROM events e 
                             LEFT JOIN associations a ON e.association_id = a.id 
                             LEFT JOIN forms f ON e.form_id = f.id 
                             WHERE e.association_id = ? 
                             ORDER BY e.starts_at DESC");
    $evStmt->execute([$adminAssocId]);
    $events = $evStmt->fetchAll();
}
?>

<div class="admin-header">
    <h2>مدیریت رویدادها و کارگاه‌های علمی</h2>
    <a href="events.php#event-editor" class="btn btn-sm">
        <?= svgIcon('plus', '', 14) ?> تعریف رویداد جدید
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'saved') echo 'اطلاعات رویداد با موفقیت ذخیره شد.';
            elseif ($_GET['msg'] === 'deleted') echo 'رویداد با موفقیت حذف گردید.';
            elseif ($_GET['msg'] === 'status_updated') echo 'وضعیت انتشار تغییر یافت.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- لیست رویدادها -->
<div class="admin-card">
    <h3>فهرست رویدادها</h3>
    <?php if ($events): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>تصویر</th>
                        <th>عنوان رویداد</th>
                        <th>انجمن</th>
                        <th>تاریخ و ساعت</th>
                        <th>مکان</th>
                        <th>فرم ثبت‌نام</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): 
                        $imgSrc = !empty($ev['image']) ? getNewsImageUrl($ev['image']) : '../assets/images/news-placeholder1.jpg';
                    ?>
                        <tr>
                            <td style="width:60px;">
                                <img src="../<?= e($imgSrc) ?>" alt="" style="width:50px; height:36px; object-fit:cover; border-radius:4px;">
                            </td>
                            <td>
                                <strong><a href="../event.php?id=<?= (int)$ev['id'] ?>" target="_blank"><?= e($ev['title']) ?></a></strong>
                            </td>
                            <td><?= e($ev['assoc_name'] ?: 'عمومی (دانشگاه اراک)') ?></td>
                            <td style="font-size:12.5px;"><?= formatDate($ev['starts_at']) ?><br><span style="color:#64748b;"><?= date('H:i', strtotime($ev['starts_at'])) ?></span></td>
                            <td style="font-size:13px;"><?= e($ev['location'] ?: '---') ?></td>
                            <td>
                                <?php if (!empty($ev['form_id']) && !empty($ev['form_title'])): ?>
                                    <a href="submissions.php?form_id=<?= (int)$ev['form_id'] ?>" class="badge badge-secondary" title="مشاهده ثبت‌نامی‌ها">
                                        <?= e($ev['form_title']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-size:12px;">بدون فرم</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="events.php?toggle_publish=<?= (int)$ev['id'] ?>" class="badge <?= $ev['is_published'] ? 'badge-success' : 'badge-danger' ?>" style="text-decoration:none;">
                                    <?= $ev['is_published'] ? 'منتشر شده' : 'پیش‌نویس' ?>
                                </a>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="events.php?edit=<?= (int)$ev['id'] ?>#event-editor" class="btn btn-sm btn-secondary" title="ویرایش">
                                        <?= svgIcon('edit', '', 14) ?>
                                    </a>
                                    <a href="../event.php?id=<?= (int)$ev['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="مشاهده در سایت">
                                        <?= svgIcon('eye', '', 14) ?>
                                    </a>
                                    <a href="events.php?delete=<?= (int)$ev['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این رویداد مطمئن هستید؟');" title="حذف">
                                        <?= svgIcon('trash', '', 14) ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color:#64748b; font-size:13.5px;">هنوز رویدادی ثبت نشده است.</p>
    <?php endif; ?>
</div>

<!-- فرم ایجاد / ویرایش رویداد -->
<div class="admin-card" id="event-editor">
    <h3><?= $edit ? 'ویرایش رویداد: ' . e($edit['title']) : 'تعریف رویداد جدید' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
        <input type="hidden" name="current_image" value="<?= e($edit['image'] ?? '') ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:18px;">
            <div class="form-group">
                <label>عنوان رویداد: <span style="color:#dc2626;">*</span></label>
                <input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً: کارگاه کاربردی یادگیری ماشین" required>
            </div>

            <div class="form-group" <?= !$isSuperAdmin ? 'style="display:none;"' : '' ?>>
                <label>انجمن برگزارکننده:</label>
                <select name="association_id">
                    <?php if ($isSuperAdmin): ?>
                        <option value="0">دانشگاه اراک (عمومی / بدون انجمن خاص)</option>
                    <?php endif; ?>
                    <?php foreach ($assocs as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= (!empty($edit['association_id']) && $edit['association_id'] == $a['id']) ? 'selected' : (!$isSuperAdmin ? 'selected' : '') ?>>
                            <?= e($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>تاریخ و زمان شروع: <span style="color:#dc2626;">*</span></label>
                <input type="datetime-local" name="starts_at" value="<?= !empty($edit['starts_at']) ? date('Y-m-d\TH:i', strtotime($edit['starts_at'])) : date('Y-m-d\T10:00') ?>" required>
            </div>

            <div class="form-group">
                <label>تاریخ و زمان پایان (اختیاری):</label>
                <input type="datetime-local" name="ends_at" value="<?= !empty($edit['ends_at']) ? date('Y-m-d\TH:i', strtotime($edit['ends_at'])) : '' ?>">
            </div>

            <div class="form-group">
                <label>مکان برگزاری:</label>
                <input type="text" name="location" value="<?= e($edit['location'] ?? '') ?>" placeholder="مثال: سالن آمفی‌تئاتر دکتر قریب / اسکای‌روم">
            </div>

            <div class="form-group">
                <label>فرم ثبت‌نام اختصاصی:</label>
                <select name="form_id" id="form_id_select">
                    <option value="0">بدون فرم ثبت‌نام (فقط اطلاع‌رسانی)</option>
                    <?php foreach ($forms as $f): ?>
                        <option value="<?= (int)$f['id'] ?>" <?= (!empty($edit['form_id']) && $edit['form_id'] == $f['id']) ? 'selected' : '' ?>>
                            <?= e($f['title']) ?> (کد: <?= (int)$f['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$edit): ?>
            <div class="form-group" style="background:#f0f7ff; padding:12px 16px; border-radius:8px; border:1px solid #bfdbfe; display:flex; align-items:center; gap:8px;">
                <input type="checkbox" id="auto_create_form" name="auto_create_form" value="1" style="width:auto;">
                <label for="auto_create_form" style="margin:0; cursor:pointer; font-weight:600; color:#1e3a8a;">
                    ایجاد خودکار فرم ثبت‌نام پیش‌فرض برای این رویداد (با فیلدهای نام، شماره دانشجویی و شماره تماس)
                </label>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>توضیحات و سرفصل‌های رویداد:</label>
            <textarea name="description" rows="5" placeholder="توضیحات کامل درباره موضوع رویداد، سخنرانان، نحوه ارائه و پیش‌نیازها"><?= e($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>تصویر رویداد (پوستر):</label>
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($edit['image'])): ?>
                <div style="margin-top:8px;">
                    <img src="../<?= e(getNewsImageUrl($edit['image'])) ?>" alt="" style="height:60px; border-radius:6px;">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" id="is_published" name="is_published" value="1" <?= (!isset($edit) || !empty($edit['is_published'])) ? 'checked' : '' ?> style="width:auto;">
            <label for="is_published" style="margin-bottom:0; cursor:pointer;">رویداد منتشر شده و در تقویم عمومی نمایش داده شود</label>
        </div>

        <div style="margin-top:24px; display:flex; gap:12px;">
            <button type="submit" name="save_event" class="btn">
                <?= svgIcon('check', '', 16) ?> ذخیره اطلاعات رویداد
            </button>
            <?php if ($edit): ?>
                <a href="events.php" class="btn btn-secondary">انصراف</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include 'inc/footer.php'; ?>
