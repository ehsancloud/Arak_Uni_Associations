<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';

// خروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$error = '';
// پردازش لاگین
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_user'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'] ?? 'superadmin';
        $_SESSION['admin_assoc_id'] = (int)($user['association_id'] ?? 0);
        $_SESSION['admin_fullname'] = $user['full_name'] ?? $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز اشتباه است!';
    }
}

include 'inc/header.php';

if (!isset($_SESSION['admin_id'])): ?>
    <div class="login-box">
        <div style="text-align:center; margin-bottom:16px;">
            <img src="../assets/images/logo.svg" alt="دانشگاه اراک" style="width:54px; height:54px; object-fit:contain;">
        </div>
        <h2>ورود به پنل مدیریت سامانه</h2>
        <p style="text-align:center; color:#64748b; font-size:13px; margin-top:-14px; margin-bottom:20px;">مدیران کل و دبیران انجمن‌های علمی دانشگاه اراک</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>نام کاربری:</label>
                <input type="text" name="username" required dir="ltr">
            </div>
            <div class="form-group">
                <label>رمز عبور:</label>
                <input type="password" name="password" required dir="ltr">
            </div>
            <button type="submit" name="login" class="btn" style="width:100%;">ورود به حساب</button>
        </form>
    </div>
<?php else: 
    $isSec = (!empty($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'secretary');
    $assocId = (int)($_SESSION['admin_assoc_id'] ?? 0);

    try {
        if ($isSec) {
            $assocRow = $pdo->prepare("SELECT * FROM associations WHERE id = ?");
            $assocRow->execute([$assocId]);
            $currentAssoc = $assocRow->fetch();

            $mem_count = (int)$pdo->query("SELECT COUNT(*) FROM members WHERE association_id = $assocId")->fetchColumn();
            $events_count = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE association_id = $assocId")->fetchColumn();
            $news_count = (int)$pdo->query("SELECT COUNT(*) FROM news WHERE association_id = $assocId")->fetchColumn();
            $pubs_count = (int)$pdo->query("SELECT COUNT(*) FROM publications WHERE association_id = $assocId")->fetchColumn();
            $gallery_count = (int)$pdo->query("SELECT COUNT(*) FROM gallery_images WHERE association_id = $assocId")->fetchColumn();
            $sub_count = (int)$pdo->query("SELECT COUNT(*) FROM form_submissions s JOIN forms f ON s.form_id = f.id WHERE f.association_id = $assocId")->fetchColumn();

            $latest_submissions = $pdo->query("SELECT s.*, f.title AS form_title 
                                               FROM form_submissions s 
                                               JOIN forms f ON s.form_id = f.id 
                                               WHERE f.association_id = $assocId 
                                               ORDER BY s.id DESC LIMIT 5")->fetchAll();
        } else {
            $assoc_count = (int)$pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn();
            $events_count = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
            $submissions_count = (int)$pdo->query("SELECT COUNT(*) FROM form_submissions")->fetchColumn();
            $active_forms_count = (int)$pdo->query("SELECT COUNT(*) FROM forms WHERE is_open = 1")->fetchColumn();
            $news_count = (int)$pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
            $publications_count = (int)$pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn();
            $secretaries_count = (int)$pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'secretary'")->fetchColumn();

            $latest_submissions = $pdo->query("SELECT s.*, f.title AS form_title 
                                               FROM form_submissions s 
                                               LEFT JOIN forms f ON s.form_id = f.id 
                                               ORDER BY s.id DESC LIMIT 5")->fetchAll();
        }
    } catch (Exception $e) {
        $latest_submissions = [];
    }
?>
    <div class="admin-header">
        <div>
            <h2><?= $isSec ? ('داشبورد مدیریت: ' . htmlspecialchars($currentAssoc['name'] ?? 'انجمن علمی')) : 'داشبورد مدیریت کل سامانه' ?></h2>
            <span style="font-size:13px; color:#64748b;">خوش آمدید، <?= htmlspecialchars($_SESSION['admin_fullname'] ?? $_SESSION['admin_user']) ?> (<?= $isSec ? 'دبیر انجمن' : 'مدیر ارشد سامانه' ?>)</span>
        </div>
        <div>
            <?php if ($isSec && $assocId > 0): ?>
                <a href="../association.php?id=<?= $assocId ?>" target="_blank" class="btn btn-sm btn-secondary">
                    <?= svgIcon('eye', '', 14) ?> مشاهده صفحه انجمن در سایت
                </a>
            <?php elseif (!$isSec): ?>
                <a href="users.php" class="btn btn-sm btn-secondary">
                    <?= svgIcon('user-plus', '', 14) ?> مدیریت دسترسی دبیران
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($isSec): ?>
        <!-- آمار اختصاصی دبیر انجمن -->
        <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
            <div class="stat-box"><h4>اعضای شورای مرکزی</h4><div class="num"><?= $mem_count ?></div></div>
            <div class="stat-box"><h4>رویدادهای انجمن</h4><div class="num"><?= $events_count ?></div></div>
            <div class="stat-box"><h4>اخبار ثبت‌شده</h4><div class="num"><?= $news_count ?></div></div>
            <div class="stat-box"><h4>نشریات علمی</h4><div class="num"><?= $pubs_count ?></div></div>
            <div class="stat-box"><h4>تصاویر گالری</h4><div class="num"><?= $gallery_count ?></div></div>
            <div class="stat-box"><h4>کل ثبت‌نامی‌ها</h4><div class="num"><?= $sub_count ?></div></div>
        </div>

        <!-- دسترسی‌های سریع برای دبیر انجمن -->
        <div class="admin-card" style="margin-top: 25px;">
            <h3>دسترسی‌های سریع به بخش‌های انجمن</h3>
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:14px;">
                <a href="associations.php?edit=<?= $assocId ?>" class="btn btn-sm">
                    <?= svgIcon('edit', '', 14) ?> ویرایش مشخصات و کاور هدر
                </a>
                <a href="members.php" class="btn btn-sm btn-secondary">
                    <?= svgIcon('users', '', 14) ?> اعضای شورا
                </a>
                <a href="events.php#event-editor" class="btn btn-sm btn-secondary">
                    <?= svgIcon('calendar', '', 14) ?> ثبت رویداد جدید
                </a>
                <a href="news.php" class="btn btn-sm btn-secondary">
                    <?= svgIcon('book-open', '', 14) ?> ارسال خبر
                </a>
                <a href="gallery.php#editor" class="btn btn-sm btn-secondary">
                    <?= svgIcon('image', '', 14) ?> آپلود تصویر گالری
                </a>
                <a href="publications.php#editor" class="btn btn-sm btn-secondary">
                    <?= svgIcon('book', '', 14) ?> ثبت شماره نشریه
                </a>
                <a href="documents.php#editor" class="btn btn-sm btn-secondary">
                    <?= svgIcon('document', '', 14) ?> آپلود سند یا گزارش
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- آمار کل برای مدیر ارشد -->
        <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
            <div class="stat-box"><h4>انجمن‌های علمی</h4><div class="num"><?= $assoc_count ?></div></div>
            <div class="stat-box"><h4>دبیران فعال</h4><div class="num"><?= $secretaries_count ?></div></div>
            <div class="stat-box"><h4>رویدادها</h4><div class="num"><?= $events_count ?></div></div>
            <div class="stat-box"><h4>کل ثبت‌نامی‌ها</h4><div class="num"><?= $submissions_count ?></div></div>
            <div class="stat-box"><h4>اخبار و اطلاعیه‌ها</h4><div class="num"><?= $news_count ?></div></div>
            <div class="stat-box"><h4>نشریات علمی</h4><div class="num"><?= $publications_count ?></div></div>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="margin-top: 25px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0;"><?= $isSec ? 'آخرین درخواست‌ها و ثبت‌نامی‌های این انجمن' : 'آخرین درخواست‌ها و ثبت‌نام‌ها در کل سامانه' ?></h3>
            <a href="submissions.php" class="btn btn-sm btn-secondary">مشاهده همه</a>
        </div>
        <?php if (!empty($latest_submissions)): ?>
            <table>
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>عنوان فرم</th>
                        <th>تاریخ ثبت</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest_submissions as $sub): 
                        $badgeClass = 'badge-secondary';
                        $statusFa = 'جدید';
                        if ($sub['status'] === 'approved') { $badgeClass = 'badge-success'; $statusFa = 'تأیید شده'; }
                        elseif ($sub['status'] === 'rejected') { $badgeClass = 'badge-danger'; $statusFa = 'رد شده'; }
                        elseif ($sub['status'] === 'pending') { $badgeClass = 'badge-warning'; $statusFa = 'در حال بررسی'; }
                    ?>
                        <tr>
                            <td>#<?= (int)$sub['id'] ?></td>
                            <td><?= htmlspecialchars($sub['form_title'] ?? 'فرم ناشناخته') ?></td>
                            <td><?= formatDate($sub['created_at']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $statusFa ?></span></td>
                            <td>
                                <a href="submissions.php?form_id=<?= (int)$sub['form_id'] ?>&view=<?= (int)$sub['id'] ?>" class="btn btn-sm btn-secondary">مشاهده جزئیات</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#64748b; font-size:13.5px;">تاکنون پاسخی ثبت نشده است.</p>
        <?php endif; ?>
    </div>
<?php endif;
include 'inc/footer.php'; ?>
