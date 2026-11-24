<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$isSuperAdmin = (!empty($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin') || empty($_SESSION['admin_role']);
$adminAssocId = (int)($_SESSION['admin_assoc_id'] ?? 0);

$formId = (int)($_GET['form_id'] ?? 0);
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

// عملیات تغییر وضعیت
if (isset($_GET['action']) && isset($_GET['sub_id'])) {
    $subId = (int)$_GET['sub_id'];
    $act = $_GET['action'];
    if (in_array($act, ['approve', 'reject', 'new'])) {
        $st = ($act === 'approve') ? 'approved' : (($act === 'reject') ? 'rejected' : 'new');
        if ($isSuperAdmin) {
            $pdo->prepare("UPDATE form_submissions SET status = ? WHERE id = ?")->execute([$st, $subId]);
        } else {
            $chk = $pdo->prepare("SELECT s.id FROM form_submissions s JOIN forms f ON s.form_id = f.id WHERE s.id = ? AND f.association_id = ?");
            $chk->execute([$subId, $adminAssocId]);
            if ($chk->fetch()) {
                $pdo->prepare("UPDATE form_submissions SET status = ? WHERE id = ?")->execute([$st, $subId]);
            }
        }
        header("Location: submissions.php?form_id=$formId&status=$statusFilter&q=" . urlencode($search) . "&msg=status_updated");
        exit;
    }
}

// عملیات حذف
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($isSuperAdmin) {
        $pdo->prepare("DELETE FROM form_submissions WHERE id = ?")->execute([$delId]);
    } else {
        $chk = $pdo->prepare("SELECT s.id FROM form_submissions s JOIN forms f ON s.form_id = f.id WHERE s.id = ? AND f.association_id = ?");
        $chk->execute([$delId, $adminAssocId]);
        if ($chk->fetch()) {
            $pdo->prepare("DELETE FROM form_submissions WHERE id = ?")->execute([$delId]);
        }
    }
    header("Location: submissions.php?form_id=$formId&status=$statusFilter&q=" . urlencode($search) . "&msg=deleted");
    exit;
}

// خروجی CSV برای اکسل با پشتیبانی کامل از حروف فارسی (UTF-8 BOM)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $where = ["1=1"];
    $params = [];
    if (!$isSuperAdmin) {
        $where[] = "f.association_id = ?";
        $params[] = $adminAssocId;
    }
    if ($formId > 0) {
        $where[] = "s.form_id = ?";
        $params[] = $formId;
    }
    if (!empty($statusFilter)) {
        $where[] = "s.status = ?";
        $params[] = $statusFilter;
    }
    if (!empty($search)) {
        $where[] = "s.data LIKE ?";
        $params[] = '%' . $search . '%';
    }

    $sql = "SELECT s.*, f.title AS form_title 
            FROM form_submissions s 
            LEFT JOIN forms f ON s.form_id = f.id 
            WHERE " . implode(' AND ', $where) . " 
            ORDER BY s.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // تنظیم هدرهای دانلود فایل CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=submissions_' . date('Y-m-d_H-i') . '.csv');
    
    // خروجی UTF-8 BOM برای نمایش درست حروف فارسی در نرم‌افزار اکسل
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // استخراج تمام کلیدهای یکتا برای ستون‌های جدول
    $allKeys = ['شناسه', 'عنوان فرم', 'وضعیت', 'تاریخ ثبت'];
    $dynamicKeys = [];
    foreach ($rows as $r) {
        $data = json_decode($r['data'], true);
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (!in_array($k, $dynamicKeys)) {
                    $dynamicKeys[] = $k;
                }
            }
        }
    }
    $headers = array_merge($allKeys, $dynamicKeys);
    fputcsv($output, $headers);

    foreach ($rows as $r) {
        $data = json_decode($r['data'], true) ?: [];
        $statusFa = ($r['status'] === 'approved') ? 'تأیید شده' : (($r['status'] === 'rejected') ? 'رد شده' : 'جدید');
        $rowOutput = [
            $r['id'],
            $r['form_title'],
            $statusFa,
            formatDate($r['created_at'])
        ];
        foreach ($dynamicKeys as $k) {
            $val = $data[$k] ?? '';
            if (is_array($val)) {
                $val = implode('، ', $val);
            }
            $rowOutput[] = $val;
        }
        fputcsv($output, $rowOutput);
    }
    fclose($output);
    exit;
}

// ساخت کوئری فیلتر
$where = ["1=1"];
$params = [];

if (!$isSuperAdmin) {
    $where[] = "f.association_id = ?";
    $params[] = $adminAssocId;
}
if ($formId > 0) {
    $where[] = "s.form_id = ?";
    $params[] = $formId;
}
if (!empty($statusFilter)) {
    $where[] = "s.status = ?";
    $params[] = $statusFilter;
}
if (!empty($search)) {
    $where[] = "s.data LIKE ?";
    $params[] = '%' . $search . '%';
}

$sql = "SELECT s.*, f.title AS form_title 
        FROM form_submissions s 
        LEFT JOIN forms f ON s.form_id = f.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY s.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// لیست فرم‌ها برای فیلتر
if ($isSuperAdmin) {
    $allForms = $pdo->query("SELECT id, title FROM forms ORDER BY id DESC")->fetchAll();
} else {
    $fStmt = $pdo->prepare("SELECT id, title FROM forms WHERE association_id = ? ORDER BY id DESC");
    $fStmt->execute([$adminAssocId]);
    $allForms = $fStmt->fetchAll();
}

// مشاهده جزئیات یک پاسخ خاص
$viewSub = null;
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    if ($isSuperAdmin) {
        $vStmt = $pdo->prepare("SELECT s.*, f.title AS form_title FROM form_submissions s LEFT JOIN forms f ON s.form_id = f.id WHERE s.id = ?");
        $vStmt->execute([$viewId]);
    } else {
        $vStmt = $pdo->prepare("SELECT s.*, f.title AS form_title FROM form_submissions s LEFT JOIN forms f ON s.form_id = f.id WHERE s.id = ? AND f.association_id = ?");
        $vStmt->execute([$viewId, $adminAssocId]);
    }
    $viewSub = $vStmt->fetch();
}

include 'inc/header.php';
?>

<div class="admin-header">
    <h2>مدیریت ثبت‌نامی‌ها و پاسخ‌های دریافتی</h2>
    <div style="display:flex; gap:8px;">
        <a href="submissions.php?form_id=<?= $formId ?>&status=<?= e($statusFilter) ?>&q=<?= urlencode($search) ?>&export=csv" class="btn btn-sm btn-secondary">
            <?= svgIcon('download', '', 14) ?> خروجی اکسل (CSV)
        </a>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary">
            چاپ لیست
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?php
            if ($_GET['msg'] === 'status_updated') echo 'وضعیت پاسخ با موفقیت به‌روزرسانی شد.';
            elseif ($_GET['msg'] === 'deleted') echo 'پاسخ با موفقیت حذف گردید.';
        ?>
    </div>
<?php endif; ?>

<!-- فیلتر و جستجو -->
<div class="admin-card">
    <form method="GET" style="display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:1; min-width:200px;">
            <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:600;">انتخاب فرم:</label>
            <select name="form_id" class="form-control" onchange="this.form.submit()" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px;">
                <option value="0">همه فرم‌ها</option>
                <?php foreach ($allForms as $af): ?>
                    <option value="<?= (int)$af['id'] ?>" <?= $formId == $af['id'] ? 'selected' : '' ?>>
                        <?= e($af['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:160px;">
            <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:600;">وضعیت:</label>
            <select name="status" class="form-control" onchange="this.form.submit()" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px;">
                <option value="">همه وضعیت‌ها</option>
                <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>جدید / بررسی‌نشده</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>تأیید شده</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>رد شده</option>
            </select>
        </div>

        <div style="flex:1; min-width:220px;">
            <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:600;">جستجو در متن پاسخ‌ها:</label>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="نام، شماره دانشجویی، شماره تماس..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px;">
        </div>

        <div>
            <button type="submit" class="btn btn-sm" style="padding:9px 18px;">جستجو</button>
            <?php if ($formId > 0 || !empty($statusFilter) || !empty($search)): ?>
                <a href="submissions.php" class="btn btn-sm btn-secondary" style="padding:9px 14px;">ریست</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- مودال یا بخش مشاهده جزئیات پاسخ -->
<?php if ($viewSub): 
    $viewData = json_decode($viewSub['data'], true) ?: [];
?>
    <div class="admin-card" style="border:2px solid #2563eb; background:#f8fafc;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0; border-right-color:#2563eb;">جزئیات پاسخ شماره #<?= (int)$viewSub['id'] ?> (<?= e($viewSub['form_title']) ?>)</h3>
            <a href="submissions.php?form_id=<?= $formId ?>&status=<?= e($statusFilter) ?>&q=<?= urlencode($search) ?>" class="btn btn-sm btn-secondary">بستن پنجره جزئیات</a>
        </div>

        <table style="background:#fff; border-radius:8px; overflow:hidden;">
            <tbody>
                <tr>
                    <th style="width:220px;">شناسه فرم:</th>
                    <td><?= e($viewSub['form_title']) ?> (کد: <?= (int)$viewSub['form_id'] ?>)</td>
                </tr>
                <tr>
                    <th>تاریخ و ساعت ارسال:</th>
                    <td><?= formatDate($viewSub['created_at']) ?> (<?= e($viewSub['created_at']) ?>)</td>
                </tr>
                <tr>
                    <th>وضعیت فعلی:</th>
                    <td>
                        <?php
                            $vBadge = 'badge-secondary';
                            $vFa = 'جدید';
                            if ($viewSub['status'] === 'approved') { $vBadge = 'badge-success'; $vFa = 'تأیید شده'; }
                            elseif ($viewSub['status'] === 'rejected') { $vBadge = 'badge-danger'; $vFa = 'رد شده'; }
                        ?>
                        <span class="badge <?= $vBadge ?>"><?= $vFa ?></span>
                    </td>
                </tr>
                <?php foreach ($viewData as $key => $val): ?>
                    <tr>
                        <th style="color:#0e1f3d;"><?= e($key) ?>:</th>
                        <td>
                            <?php 
                                if (is_array($val)) {
                                    echo e(implode('، ', $val));
                                } else {
                                    echo nl2br(e($val));
                                }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:16px; display:flex; gap:10px;">
            <a href="submissions.php?form_id=<?= $formId ?>&sub_id=<?= (int)$viewSub['id'] ?>&action=approve" class="btn btn-sm btn-success">
                <?= svgIcon('check', '', 14) ?> تأیید درخواست
            </a>
            <a href="submissions.php?form_id=<?= $formId ?>&sub_id=<?= (int)$viewSub['id'] ?>&action=reject" class="btn btn-sm btn-danger">
                <?= svgIcon('close', '', 14) ?> رد درخواست
            </a>
            <a href="submissions.php?form_id=<?= $formId ?>&sub_id=<?= (int)$viewSub['id'] ?>&action=new" class="btn btn-sm btn-secondary">
                تنظیم به عنوان جدید
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- جدول پاسخ‌ها -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="margin:0;">لیست پاسخ‌ها (تعداد کل: <?= count($submissions) ?>)</h3>
    </div>

    <?php if ($submissions): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>عنوان فرم</th>
                        <th>خلاصه اطلاعات</th>
                        <th>وضعیت</th>
                        <th>تاریخ ارسال</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): 
                        $data = json_decode($sub['data'], true) ?: [];
                        // استخراج ۲ یا ۳ فیلد اول به عنوان خلاصه
                        $summaryParts = [];
                        $count = 0;
                        foreach ($data as $k => $v) {
                            if ($count >= 3) break;
                            $strVal = is_array($v) ? implode('، ', $v) : (string)$v;
                            if (mb_strlen($strVal) > 30) {
                                $strVal = mb_substr($strVal, 0, 30) . '...';
                            }
                            $summaryParts[] = '<span style="color:#64748b;">' . e($k) . ':</span> ' . e($strVal);
                            $count++;
                        }
                        $summaryHtml = implode(' | ', $summaryParts);

                        $badge = 'badge-secondary';
                        $statusName = 'جدید';
                        if ($sub['status'] === 'approved') { $badge = 'badge-success'; $statusName = 'تأیید شده'; }
                        elseif ($sub['status'] === 'rejected') { $badge = 'badge-danger'; $statusName = 'رد شده'; }
                    ?>
                        <tr>
                            <td>#<?= (int)$sub['id'] ?></td>
                            <td><strong><?= e($sub['form_title'] ?? 'فرم #' . $sub['form_id']) ?></strong></td>
                            <td style="font-size:13px;"><?= $summaryHtml ?: '---' ?></td>
                            <td><span class="badge <?= $badge ?>"><?= $statusName ?></span></td>
                            <td style="font-size:13px;"><?= formatDate($sub['created_at']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="submissions.php?form_id=<?= $formId ?>&status=<?= e($statusFilter) ?>&q=<?= urlencode($search) ?>&view=<?= (int)$sub['id'] ?>" class="btn btn-sm btn-secondary" title="مشاهده کامل">
                                        <?= svgIcon('eye', '', 14) ?> جزئیات
                                    </a>
                                    <?php if ($sub['status'] !== 'approved'): ?>
                                        <a href="submissions.php?form_id=<?= $formId ?>&sub_id=<?= (int)$sub['id'] ?>&action=approve" class="btn btn-sm btn-success" title="تأیید سریع">
                                            <?= svgIcon('check', '', 14) ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($sub['status'] !== 'rejected'): ?>
                                        <a href="submissions.php?form_id=<?= $formId ?>&sub_id=<?= (int)$sub['id'] ?>&action=reject" class="btn btn-sm btn-danger" title="رد سریع">
                                            <?= svgIcon('close', '', 14) ?>
                                        </a>
                                    <?php endif; ?>
                                    <a href="submissions.php?form_id=<?= $formId ?>&delete=<?= (int)$sub['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('آیا از حذف این رکورد مطمئن هستید؟');" title="حذف">
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
        <p style="color:#64748b; font-size:13.5px;">هیچ پاسخی با شرایط انتخابی یافت نشد.</p>
    <?php endif; ?>
</div>

<style>
@media print {
    .admin-sidebar, .admin-header, form, .actions-cell, a.btn, button.btn {
        display: none !important;
    }
    .admin-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    body {
        background: #fff !important;
    }
}
</style>

<?php include 'inc/footer.php'; ?>
