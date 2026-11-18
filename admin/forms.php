<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$message = '';
$error = '';

// حذف فرم
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM form_fields WHERE form_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM form_submissions WHERE form_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM forms WHERE id = ?")->execute([$delId]);
    header('Location: forms.php?msg=deleted');
    exit;
}

// تغییر وضعیت باز/بسته
if (isset($_GET['toggle'])) {
    $tId = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE forms SET is_open = 1 - is_open WHERE id = ?")->execute([$tId]);
    header('Location: forms.php?msg=toggled');
    exit;
}

// ثبت یا ویرایش فرم به همراه فیلدها
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_form'])) {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $association_id = (int)($_POST['association_id'] ?? 0);
    $type = $_POST['type'] ?? 'custom';
    $description = trim($_POST['description'] ?? '');
    $is_open = isset($_POST['is_open']) ? 1 : 0;
    $capacity = (int)($_POST['capacity'] ?? 0);

    if (empty($title)) {
        $error = 'عنوان فرم الزامی است.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE forms SET title = ?, association_id = ?, type = ?, description = ?, is_open = ?, capacity = ? WHERE id = ?");
            $stmt->execute([$title, $association_id, $type, $description, $is_open, $capacity, $id]);
            $formId = $id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO forms (title, association_id, type, description, is_open, capacity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $association_id, $type, $description, $is_open, $capacity]);
            $formId = (int)$pdo->lastInsertId();
        }

        // ذخیره فیلدهای پویا
        $fieldIdsToKeep = [];
        if (!empty($_POST['fields']) && is_array($_POST['fields'])) {
            $sort = 0;
            foreach ($_POST['fields'] as $f) {
                $sort++;
                $fLabel = trim($f['label'] ?? '');
                if (empty($fLabel)) continue;

                $fType = $f['type'] ?? 'text';
                $fRequired = !empty($f['required']) ? 1 : 0;
                $fUnique = !empty($f['is_unique']) ? 1 : 0;
                $fPlaceholder = trim($f['placeholder'] ?? '');
                $fHelp = trim($f['help'] ?? '');
                
                // فرمت گزینه‌ها به عنوان JSON آرایه
                $fOptions = null;
                if (in_array($fType, ['select', 'radio', 'checkbox'])) {
                    $rawOpts = trim($f['options'] ?? '');
                    if (!empty($rawOpts)) {
                        $optArr = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $rawOpts)))));
                        if (!empty($optArr)) {
                            $fOptions = json_encode($optArr, JSON_UNESCAPED_UNICODE);
                        }
                    }
                }

                $fieldId = (int)($f['id'] ?? 0);
                if ($fieldId > 0) {
                    $upStmt = $pdo->prepare("UPDATE form_fields SET label = ?, type = ?, options = ?, required = ?, is_unique = ?, placeholder = ?, help = ?, sort_order = ? WHERE id = ? AND form_id = ?");
                    $upStmt->execute([$fLabel, $fType, $fOptions, $fRequired, $fUnique, $fPlaceholder, $fHelp, $sort, $fieldId, $formId]);
                    $fieldIdsToKeep[] = $fieldId;
                } else {
                    $insStmt = $pdo->prepare("INSERT INTO form_fields (form_id, label, type, options, required, is_unique, placeholder, help, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $insStmt->execute([$formId, $fLabel, $fType, $fOptions, $fRequired, $fUnique, $fPlaceholder, $fHelp, $sort]);
                    $fieldIdsToKeep[] = (int)$pdo->lastInsertId();
                }
            }
        }

        // حذف فیلدهای حذف شده
        if (!empty($fieldIdsToKeep)) {
            $inClause = implode(',', array_map('intval', $fieldIdsToKeep));
            $pdo->query("DELETE FROM form_fields WHERE form_id = $formId AND id NOT IN ($inClause)");
        } else {
            $pdo->prepare("DELETE FROM form_fields WHERE form_id = ?")->execute([$formId]);
        }

        header('Location: forms.php?msg=saved');
        exit;
    }
}

// واکشی داده فرم برای ویرایش
$edit = null;
$editFields = [];
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
    if ($edit) {
        $fStmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order ASC, id ASC");
        $fStmt->execute([$editId]);
        $editFields = $fStmt->fetchAll();
    }
}

// لیست انجمن‌ها برای دراپ‌داون
$assocs = $pdo->query("SELECT id, name FROM associations ORDER BY name ASC")->fetchAll();

// لیست تمام فرم‌ها به همراه شمارش فیلدها و ثبت‌نامی‌ها
$forms = $pdo->query("SELECT f.*, a.name AS assoc_name,
                        (SELECT COUNT(*) FROM form_fields WHERE form_id = f.id) AS field_count,
                        (SELECT COUNT(*) FROM form_submissions WHERE form_id = f.id) AS sub_count
                      FROM forms f 
                      LEFT JOIN associations a ON f.association_id = a.id 
                      ORDER BY f.id DESC")->fetchAll();

include 'inc/header.php';
?>

<div class="admin-header">
    <h2>مدیریت فرم‌ها و فیلدها</h2>
    <a href="forms.php#form-editor" class="btn btn-sm">
        <?= svgIcon('plus', '', 14) ?> ایجاد فرم جدید
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'saved') echo 'فرم و فیلدها با موفقیت ذخیره شدند.';
            elseif ($_GET['msg'] === 'deleted') echo 'فرم و تمام فیلدها و پاسخ‌های مربوطه حذف شدند.';
            elseif ($_GET['msg'] === 'toggled') echo 'وضعیت فرم با موفقیت تغییر یافت.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- جدول فرم‌ها -->
<div class="admin-card">
    <h3>فهرست فرم‌های سامانه</h3>
    <?php if ($forms): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>عنوان فرم</th>
                        <th>انجمن مربوطه</th>
                        <th>نوع فرم</th>
                        <th>ظرفیت</th>
                        <th>فیلدها</th>
                        <th>ثبت‌نامی‌ها</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $f): 
                        $typeNames = [
                            'membership' => 'عضویت',
                            'event'      => 'رویداد',
                            'cooperation'=> 'همکاری',
                            'custom'     => 'سفارشی'
                        ];
                    ?>
                        <tr>
                            <td>#<?= (int)$f['id'] ?></td>
                            <td>
                                <strong><a href="../form.php?id=<?= $f['id'] ?>" target="_blank"><?= e($f['title']) ?></a></strong>
                            </td>
                            <td><?= e($f['assoc_name'] ?: 'عمومی (دانشگاه اراک)') ?></td>
                            <td><span class="badge badge-secondary"><?= $typeNames[$f['type']] ?? 'سفارشی' ?></span></td>
                            <td><?= $f['capacity'] > 0 ? (int)$f['capacity'] : 'نامحدود' ?></td>
                            <td><?= (int)$f['field_count'] ?> فیلد</td>
                            <td>
                                <a href="submissions.php?form_id=<?= (int)$f['id'] ?>" class="badge badge-secondary" style="text-decoration:none;">
                                    <?= (int)$f['sub_count'] ?> پاسخ
                                </a>
                            </td>
                            <td>
                                <a href="forms.php?toggle=<?= (int)$f['id'] ?>" class="badge <?= $f['is_open'] ? 'badge-success' : 'badge-danger' ?>" style="text-decoration:none;" title="کلیک برای تغییر وضعیت">
                                    <?= $f['is_open'] ? 'فعال / باز' : 'بسته' ?>
                                </a>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="forms.php?edit=<?= (int)$f['id'] ?>#form-editor" class="btn btn-sm btn-secondary" title="ویرایش فرم">
                                        <?= svgIcon('edit', '', 14) ?> ویرایش
                                    </a>
                                    <a href="submissions.php?form_id=<?= (int)$f['id'] ?>" class="btn btn-sm btn-secondary" title="مشاهده پاسخ‌ها">
                                        <?= svgIcon('eye', '', 14) ?> پاسخ‌ها
                                    </a>
                                    <a href="forms.php?delete=<?= (int)$f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این فرم و تمام داده‌های آن مطمئن هستید؟');" title="حذف">
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
        <p style="color:#64748b; font-size:13.5px;">هنوز فرمی ایجاد نشده است.</p>
    <?php endif; ?>
</div>

<!-- فرم ایجاد / ویرایش -->
<div class="admin-card" id="form-editor">
    <h3><?= $edit ? 'ویرایش فرم: ' . e($edit['title']) : 'ایجاد فرم جدید' ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:18px;">
            <div class="form-group">
                <label>عنوان فرم: <span style="color:#dc2626;">*</span></label>
                <input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً: فرم ثبت‌نام وبینار هوش مصنوعی" required>
            </div>

            <div class="form-group">
                <label>انجمن مربوطه:</label>
                <select name="association_id">
                    <option value="0">دانشگاه اراک (عمومی / بدون انجمن خاص)</option>
                    <?php foreach ($assocs as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= (!empty($edit['association_id']) && $edit['association_id'] == $a['id']) ? 'selected' : '' ?>>
                            <?= e($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>نوع فرم:</label>
                <select name="type">
                    <option value="custom" <?= (!empty($edit['type']) && $edit['type'] === 'custom') ? 'selected' : '' ?>>سفارشی / عمومی</option>
                    <option value="membership" <?= (!empty($edit['type']) && $edit['type'] === 'membership') ? 'selected' : '' ?>>عضویت در انجمن</option>
                    <option value="event" <?= (!empty($edit['type']) && $edit['type'] === 'event') ? 'selected' : '' ?>>ثبت‌نام رویداد یا کارگاه</option>
                    <option value="cooperation" <?= (!empty($edit['type']) && $edit['type'] === 'cooperation') ? 'selected' : '' ?>>درخواست همکاری</option>
                </select>
            </div>

            <div class="form-group">
                <label>ظرفیت پذیرش (۰ برای نامحدود):</label>
                <input type="number" name="capacity" value="<?= isset($edit['capacity']) ? (int)$edit['capacity'] : 0 ?>" min="0">
            </div>
        </div>

        <div class="form-group">
            <label>توضیحات و راهنمای فرم:</label>
            <textarea name="description" rows="3" placeholder="توضیحاتی که بالای فیلدهای فرم به کاربر نمایش داده می‌شود"><?= e($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" id="is_open" name="is_open" value="1" <?= (!isset($edit) || !empty($edit['is_open'])) ? 'checked' : '' ?> style="width:auto;">
            <label for="is_open" style="margin-bottom:0; cursor:pointer;">فرم فعال و آماده دریافت پاسخ باشد</label>
        </div>

        <!-- بخش فیلدهای داینامیک -->
        <div style="margin-top:32px; border-top:1px solid #e2e8f0; padding-top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h4 style="font-size:16px; color:#0e1f3d; margin:0;">فیلدهای فرم</h4>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addFieldRow()">
                    <?= svgIcon('plus', '', 14) ?> افزودن فیلد جدید
                </button>
            </div>

            <div id="fields-container" style="display:flex; flex-direction:column; gap:12px;">
                <!-- فیلدها اینجا توسط جاوااسکریپت و PHP رندر می‌شوند -->
            </div>
        </div>

        <div style="margin-top:28px; display:flex; gap:12px;">
            <button type="submit" name="save_form" class="btn">
                <?= svgIcon('check', '', 16) ?> ذخیره فرم و فیلدها
            </button>
            <?php if ($edit): ?>
                <a href="forms.php" class="btn btn-secondary">انصراف</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
let fieldCounter = 0;

// داده‌های اولیه فیلدها برای ویرایش
const initialFields = <?= json_encode($editFields, JSON_UNESCAPED_UNICODE) ?: '[]' ?>;

function addFieldRow(data = {}) {
    fieldCounter++;
    const id = data.id || '';
    const label = data.label || '';
    const type = data.type || 'text';
    const required = (data.required == 1 || data.required === true);
    const isUnique = (data.is_unique == 1 || data.is_unique === true);
    const placeholder = data.placeholder || '';
    const help = data.help || '';
    
    // تبدیل گزینه‌ها از JSON به متن خطی
    let optionsText = '';
    if (data.options) {
        try {
            const parsed = JSON.parse(data.options);
            if (Array.isArray(parsed)) {
                optionsText = parsed.join('\n');
            }
        } catch(e) {
            optionsText = data.options;
        }
    }

    const row = document.createElement('div');
    row.className = 'field-item-card';
    row.style.background = '#f8fafc';
    row.style.border = '1px solid #cbd5e1';
    row.style.borderRadius = '8px';
    row.style.padding = '16px';
    row.id = 'field_row_' + fieldCounter;

    row.innerHTML = `
        <input type="hidden" name="fields[${fieldCounter}][id]" value="${id}">
        <div style="display:grid; grid-template-columns: 2fr 1.5fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:12.5px;">عنوان فیلد (Label):</label>
                <input type="text" name="fields[${fieldCounter}][label]" value="${escapeHtml(label)}" placeholder="مثال: نام و نام خانوادگی" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:12.5px;">نوع فیلد:</label>
                <select name="fields[${fieldCounter}][type]" onchange="toggleOptionsBox(${fieldCounter}, this.value)">
                    <option value="text" ${type === 'text' ? 'selected' : ''}>متن کوتاه (Text)</option>
                    <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>متن چندخطی (Textarea)</option>
                    <option value="tel" ${type === 'tel' ? 'selected' : ''}>شماره موبایل (Tel)</option>
                    <option value="student_id" ${type === 'student_id' ? 'selected' : ''}>شماره دانشجویی</option>
                    <option value="email" ${type === 'email' ? 'selected' : ''}>ایمیل (Email)</option>
                    <option value="number" ${type === 'number' ? 'selected' : ''}>عدد (Number)</option>
                    <option value="select" ${type === 'select' ? 'selected' : ''}>منوی کشویی (Select)</option>
                    <option value="radio" ${type === 'radio' ? 'selected' : ''}>تک‌انتخابی (Radio)</option>
                    <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>چندانتخابی (Checkbox)</option>
                    <option value="date" ${type === 'date' ? 'selected' : ''}>تاریخ (Date)</option>
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:6px; padding-bottom:10px;">
                <input type="checkbox" id="req_${fieldCounter}" name="fields[${fieldCounter}][required]" value="1" ${required ? 'checked' : ''} style="width:auto;">
                <label for="req_${fieldCounter}" style="font-size:12.5px; margin:0; cursor:pointer;">اجباری</label>
            </div>
            <div style="display:flex; align-items:center; gap:6px; padding-bottom:10px;">
                <input type="checkbox" id="uniq_${fieldCounter}" name="fields[${fieldCounter}][is_unique]" value="1" ${isUnique ? 'checked' : ''} style="width:auto;">
                <label for="uniq_${fieldCounter}" style="font-size:12.5px; margin:0; cursor:pointer;">یکتا (بدون تکرار)</label>
            </div>
            <div style="padding-bottom:6px;">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFieldRow(${fieldCounter})" title="حذف فیلد">
                    حذف
                </button>
            </div>
        </div>

        <div id="options_box_${fieldCounter}" style="margin-top:12px; display:${['select', 'radio', 'checkbox'].includes(type) ? 'block' : 'none'};">
            <label style="font-size:12px; color:#475569;">گزینه‌ها (هر گزینه در یک خط مجزا):</label>
            <textarea name="fields[${fieldCounter}][options]" rows="3" placeholder="گزینه ۱&#10;گزینه ۲&#10;گزینه ۳">${escapeHtml(optionsText)}</textarea>
        </div>
    `;

    document.getElementById('fields-container').appendChild(row);
}

function removeFieldRow(num) {
    const el = document.getElementById('field_row_' + num);
    if (el) el.remove();
}

function toggleOptionsBox(num, val) {
    const box = document.getElementById('options_box_' + num);
    if (box) {
        box.style.display = ['select', 'radio', 'checkbox'].includes(val) ? 'block' : 'none';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// بارگذاری فیلدهای موجود یا یک فیلد پیش‌فرض
if (initialFields.length > 0) {
    initialFields.forEach(f => addFieldRow(f));
} else {
    // ۳ فیلد استاندارد پیش‌فرض برای سهولت
    addFieldRow({ label: 'نام و نام خانوادگی', type: 'text', required: true });
    addFieldRow({ label: 'شماره دانشجویی', type: 'student_id', required: true, is_unique: true });
    addFieldRow({ label: 'شماره تماس', type: 'tel', required: true });
}
</script>

<?php include 'inc/footer.php'; ?>
