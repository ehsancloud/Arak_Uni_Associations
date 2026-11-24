<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

include 'inc/header.php';

// حذف فقط توسط مدیر ارشد مجاز است
if (isset($_GET['delete'])) {
    if ($isSuperAdmin) {
        $pdo->prepare("DELETE FROM associations WHERE id=?")->execute([(int)$_GET['delete']]);
    }
    header('Location: associations.php'); exit;
}

// افزودن/ویرایش
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $goals = trim($_POST['goals'] ?? '');
    $icon_key = trim($_POST['icon_key'] ?? 'general');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $membership_open = isset($_POST['membership_open']) ? 1 : 0;
    
    // اگر دبیر باشد، شناسه فقط انجمن خودش خواهد بود
    $id = $isSuperAdmin ? (int)($_POST['id'] ?? 0) : $adminAssocId;
    
    // آپلود کاور
    $cover = $_POST['current_cover'] ?? '';
    $up = uploadImage('cover', __DIR__ . '/../uploads/covers/');
    if ($up) $cover = $up;
    
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE associations SET 
            name=?, description=?, goals=?, icon_key=?, cover_image=?, 
            email=?, phone=?, telegram=?, instagram=?, linkedin=?, website=?, address=?, membership_open=? 
            WHERE id=?");
        $stmt->execute([
            $name, $desc, $goals, $icon_key, $cover,
            $email, $phone, $telegram, $instagram, $linkedin, $website, $address, $membership_open,
            $id
        ]);
        $msg = 'اطلاعات و کاور انجمن با موفقیت به‌روزرسانی شد.';
    } elseif ($isSuperAdmin) {
        $stmt = $pdo->prepare("INSERT INTO associations 
            (name, description, goals, icon_key, cover_image, email, phone, telegram, instagram, linkedin, website, address, membership_open) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $name, $desc, $goals, $icon_key, $cover,
            $email, $phone, $telegram, $instagram, $linkedin, $website, $address, $membership_open
        ]);
        $msg = 'انجمن جدید با موفقیت ایجاد شد.';
    }
}

// واکشی اطلاعات
$edit = null;
if (!$isSuperAdmin) {
    // دبیر همیشه انجمن خودش را ویرایش می‌کند
    $stmt = $pdo->prepare("SELECT * FROM associations WHERE id=?");
    $stmt->execute([$adminAssocId]);
    $edit = $stmt->fetch();
} elseif (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM associations WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$list = $isSuperAdmin ? $pdo->query("SELECT * FROM associations ORDER BY id DESC")->fetchAll() : [];
$catalog = assocIconCatalog();
?>

<div class="admin-header">
    <h2><?= $isSuperAdmin ? 'مدیریت انجمن‌های علمی دانشگاه' : ('مشخصات و هدر: ' . htmlspecialchars($edit['name'] ?? 'انجمن علمی')) ?></h2>
    <?php if (!$isSuperAdmin && $adminAssocId > 0): ?>
        <a href="../association.php?id=<?= $adminAssocId ?>" target="_blank" class="btn btn-sm btn-secondary">
            <?= svgIcon('eye', '', 14) ?> مشاهده صفحه در سایت
        </a>
    <?php endif; ?>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<div class="admin-card">
    <h3><?= $edit ? ($isSuperAdmin ? 'ویرایش اطلاعات انجمن' : 'ویرایش مشخصات و کاور هدر انجمن') : 'افزودن انجمن جدید' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="hidden" name="current_cover" value="<?= $edit['cover_image'] ?? '' ?>">
        
        <div class="form-group">
            <label>نام انجمن:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>آیکون موضوعی انجمن (نماد وکتور خطی):</label>
            <select name="icon_key" style="font-size:14px;">
                <?php foreach ($catalog as $key => $title): ?>
                    <option value="<?= $key ?>" <?= ($edit['icon_key'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($title) ?> (<?= $key ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:#64748b; display:block; margin-top:5px;">آیکون‌ها به صورت وکتور خطی استاندارد و امن درون صفحه رندر می‌شوند.</small>
        </div>

        <div class="form-group">
            <label>معرفی کوتاه انجمن:</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>اهداف و برنامه‌های انجمن (هر هدف در یک خط):</label>
            <textarea name="goals" rows="5" placeholder="هدف اول&#10;هدف دوم&#10;هدف سوم"><?= htmlspecialchars($edit['goals'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>تصویر کاور هدر انجمن:</label>
            <input type="file" name="cover" accept="image/*">
            <?php if (!empty($edit['cover_image'])): ?>
                <small style="display:block; margin-top:4px; color:#64748b;">تصویر فعلی: <?= htmlspecialchars($edit['cover_image']) ?></small>
            <?php endif; ?>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            <div class="form-group">
                <label>ایمیل انجمن:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>" placeholder="ai@araku.ac.ir">
            </div>
            <div class="form-group">
                <label>شماره تماس دفتر انجمن:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($edit['phone'] ?? '') ?>" placeholder="086-34173000">
            </div>
            <div class="form-group">
                <label>آیدی تلگرام (بدون @):</label>
                <input type="text" name="telegram" value="<?= htmlspecialchars($edit['telegram'] ?? '') ?>" placeholder="araku_ai">
            </div>
            <div class="form-group">
                <label>آیدی اینستاگرام:</label>
                <input type="text" name="instagram" value="<?= htmlspecialchars($edit['instagram'] ?? '') ?>" placeholder="araku_ai">
            </div>
            <div class="form-group">
                <label>لینکدین (نام کاربری یا شناسه):</label>
                <input type="text" name="linkedin" value="<?= htmlspecialchars($edit['linkedin'] ?? '') ?>" placeholder="araku-ai">
            </div>
            <div class="form-group">
                <label>وب‌سایت اختصاصی:</label>
                <input type="url" name="website" value="<?= htmlspecialchars($edit['website'] ?? '') ?>" placeholder="https://ai.araku.ac.ir">
            </div>
        </div>

        <div class="form-group">
            <label>آدرس فیزیکی دفتر انجمن در دانشگاه:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($edit['address'] ?? '') ?>" placeholder="دانشکده مهندسی، طبقه دوم، دفتر انجمن هوش مصنوعی">
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="membership_open" id="membership_open" style="width:auto;" <?= (!isset($edit['membership_open']) || $edit['membership_open']) ? 'checked' : '' ?>>
            <label for="membership_open" style="margin-bottom:0; cursor:pointer;">پذیرش عضو جدید باز است (نمایش دکمه عضویت در سایت)</label>
        </div>

        <button type="submit" class="btn"><?= $edit ? 'ذخیره تغییرات' : 'افزودن انجمن' ?></button>
        <?php if ($edit): ?><a href="associations.php" class="btn btn-danger">انصراف</a><?php endif; ?>
    </form>
</div>

<?php if ($isSuperAdmin): ?>
<div class="admin-card">
    <h3>لیست انجمن‌های علمی (<?= count($list) ?>)</h3>
    <table>
        <tr>
            <th>ردیف</th>
            <th>نماد</th>
            <th>نام انجمن</th>
            <th>پذیرش عضویت</th>
            <th>مشاهده صفحه</th>
            <th>عملیات</th>
        </tr>
        <?php foreach ($list as $i => $row): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td>
                    <div style="width:32px; height:32px; color:#1e3a5f; display:flex; align-items:center;">
                        <?= assocIconSvg(assocIconKey($row), '', 28) ?>
                    </div>
                </td>
                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                <td>
                    <?= !empty($row['membership_open']) 
                        ? '<span style="color:#059669; font-weight:600;">پذیرش باز</span>' 
                        : '<span style="color:#94a3b8;">بسته</span>' ?>
                </td>
                <td>
                    <a href="../association.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-success" style="padding:4px 12px; font-size:12px;">مشاهده</a>
                </td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn" style="padding:4px 12px; font-size:12px;">ویرایش</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger" style="padding:4px 12px; font-size:12px;" onclick="return confirm('آیا از حذف این انجمن اطمینان دارید؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<?php include 'inc/footer.php'; ?>
