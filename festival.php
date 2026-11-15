<?php 
$page = 'festival';
include 'includes/header.php'; 

// بررسی وضعیت باز یا بسته بودن ثبت نام
if ($settings['festival_status'] == 0) {
    echo "<div class='page-container container'>
            <div class='festival-closed-box'>
                " . svgIcon('close', 'closed-icon', 48) . "
                <h2>مهلت ثبت‌نام در کادر اجرایی جشنواره حرکت به پایان رسیده است.</h2>
                <p>جهت اطلاع از دوره‌های آتی اخبار سایت را دنبال کنید.</p>
                <a href='index.php' class='btn-pill-dark' style='margin-top:20px; display:inline-flex;'>بازگشت به صفحه اصلی</a>
            </div>
          </div>";
    include 'includes/footer.php';
    exit;
}

// ذخیره فرم در دیتابیس
$message = '';
$isSuccess = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_reg'])) {
    $name = trim($_POST['full_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if ($name && $student_id && $phone) {
        $stmt = $pdo->prepare("INSERT INTO festival_registrations (full_name, student_id, phone) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $student_id, $phone])) {
            $isSuccess = true;
            $message = "ثبت‌نام شما با موفقیت در سامانه ذخیره شد. به‌زودی با شما تماس گرفته خواهد شد.";
        }
    } else {
        $message = "لطفاً تمامی فیلدهای الزامی را تکمیل نمایید.";
    }
}
?>

<section class="page-container container">
    <div class="festival-form-card">
        <div class="section-title-wrap" style="text-align:center; margin-bottom:25px;">
            <h1 class="page-title" style="display:inline-block;">ثبت‌نام کادر اجرایی جشنواره حرکت</h1>
            <div class="title-underline-center"></div>
        </div>

        <?php if ($message): ?>
            <div class="alert-box <?= $isSuccess ? 'alert-success' : 'alert-danger' ?>">
                <?= $isSuccess ? svgIcon('check', '', 18) : svgIcon('close', '', 18) ?>
                <span><?= e($message) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-grid">
            <div class="form-group">
                <label for="full_name">نام و نام خانوادگی:</label>
                <input type="text" id="full_name" name="full_name" required placeholder="مثال: امیرحسین رضایی">
            </div>
            <div class="form-group">
                <label for="student_id">شماره دانشجویی:</label>
                <input type="text" id="student_id" name="student_id" required placeholder="مثال: 99123456">
            </div>
            <div class="form-group">
                <label for="phone">شماره تماس (همراه):</label>
                <input type="tel" id="phone" name="phone" required placeholder="مثال: 09123456789">
            </div>
            <button type="submit" name="submit_reg" class="btn-pill-dark btn-submit-full">
                <span>ثبت اطلاعات در سامانه</span>
                <?= svgIcon('check', '', 16) ?>
            </button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
