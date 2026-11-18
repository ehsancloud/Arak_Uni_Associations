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
        header('Location: index.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز اشتباه است!';
    }
}

include 'inc/header.php';

if (!isset($_SESSION['admin_id'])): ?>
    <div class="login-box">
        <h2>ورود به پنل مدیریت</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>نام کاربری:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>رمز عبور:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn" style="width:100%;">ورود</button>
        </form>
        <p style="text-align:center; margin-top:15px; font-size:12px; color:#999;">پیش‌فرض: admin / 123456</p>
    </div>
<?php else: 
    // داشبورد
    try {
        $assoc_count = (int)$pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn();
        $events_count = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $submissions_count = (int)$pdo->query("SELECT COUNT(*) FROM form_submissions")->fetchColumn();
        $active_forms_count = (int)$pdo->query("SELECT COUNT(*) FROM forms WHERE is_open = 1")->fetchColumn();
        $news_count = (int)$pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
        $publications_count = (int)$pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn();

        $latest_submissions = $pdo->query("SELECT s.*, f.title AS form_title 
                                           FROM form_submissions s 
                                           LEFT JOIN forms f ON s.form_id = f.id 
                                           ORDER BY s.id DESC LIMIT 5")->fetchAll();
    } catch (Exception $e) {
        $assoc_count = 0;
        $events_count = 0;
        $submissions_count = 0;
        $active_forms_count = 0;
        $news_count = 0;
        $publications_count = 0;
        $latest_submissions = [];
    }
?>
    <div class="admin-header">
        <h2>داشبورد مدیریت</h2>
        <div>خوش آمدید، <?= htmlspecialchars($_SESSION['admin_user']) ?></div>
    </div>
    
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="stat-box"><h4>انجمن‌های علمی</h4><div class="num"><?= $assoc_count ?></div></div>
        <div class="stat-box"><h4>رویدادها</h4><div class="num"><?= $events_count ?></div></div>
        <div class="stat-box"><h4>کل ثبت‌نامی‌ها</h4><div class="num"><?= $submissions_count ?></div></div>
        <div class="stat-box"><h4>فرم‌های فعال</h4><div class="num"><?= $active_forms_count ?></div></div>
        <div class="stat-box"><h4>اخبار و اطلاعیه‌ها</h4><div class="num"><?= $news_count ?></div></div>
        <div class="stat-box"><h4>نشریات علمی</h4><div class="num"><?= $publications_count ?></div></div>
    </div>

    <div class="admin-card" style="margin-top: 25px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0;">آخرین درخواست‌ها و ثبت‌نام‌ها</h3>
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
            <p style="color:#64748b; font-size:13.5px;">تاکنون درخواستی ثبت نشده است.</p>
        <?php endif; ?>
    </div>
<?php endif;
include 'inc/footer.php'; ?>
