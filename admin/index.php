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
    $assoc_count = $pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn();
    $news_count = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
    $member_count = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
    $reg_count = $pdo->query("SELECT COUNT(*) FROM festival_registrations")->fetchColumn();
?>
    <div class="admin-header">
        <h2>داشبورد مدیریت</h2>
        <div>خوش آمدید، <?= htmlspecialchars($_SESSION['admin_user']) ?></div>
    </div>
    
    <div class="stat-grid">
        <div class="stat-box"><h4>تعداد انجمن‌ها</h4><div class="num"><?= $assoc_count ?></div></div>
        <div class="stat-box"><h4>تعداد اخبار</h4><div class="num"><?= $news_count ?></div></div>
        <div class="stat-box"><h4>تعداد اعضا</h4><div class="num"><?= $member_count ?></div></div>
        <div class="stat-box"><h4>ثبت‌نامی جشنواره</h4><div class="num"><?= $reg_count ?></div></div>
    </div>
<?php endif;
include 'inc/footer.php'; ?>
