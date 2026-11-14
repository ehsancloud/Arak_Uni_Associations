<?php 
$page = 'festival';
include 'includes/header.php'; 

// بررسی وضعیت باز یا بسته بودن ثبت نام
if ($settings['festival_status'] == 0) {
    echo "<div class='container' style='text-align:center; padding: 100px 0;'>
            <h2 style='color:red;'>مهلت ثبت‌نام در کادر اجرایی جشنواره حرکت به پایان رسیده است.</h2>
          </div>";
    include 'includes/footer.php';
    exit;
}

// ذخیره فرم در دیتابیس
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_reg'])) {
    $name = $_POST['full_name'];
    $student_id = $_POST['student_id'];
    $phone = $_POST['phone'];
    
    $stmt = $pdo->prepare("INSERT INTO festival_registrations (full_name, student_id, phone) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $student_id, $phone])) {
        $message = "<p style='color:green; text-align:center;'>ثبت نام شما با موفقیت انجام شد!</p>";
    }
}
?>

<section class="festival-form container" style="max-width: 600px; margin: 50px auto; background:#fff; padding:30px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
    <h2 style="text-align:center; margin-bottom:20px;">ثبت‌نام کادر اجرایی جشنواره حرکت</h2>
    <?= $message ?>
    <form method="POST" action="">
        <div style="margin-bottom: 15px;">
            <label>نام و نام خانوادگی:</label>
            <input type="text" name="full_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>شماره دانشجویی:</label>
            <input type="text" name="student_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>شماره تماس:</label>
            <input type="text" name="phone" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
        </div>
        <button type="submit" name="submit_reg" class="btn-primary" style="width:100%; padding:12px; border:none; cursor:pointer; text-align:center;">ثبت اطلاعات</button>
    </form>
</section>

<?php include 'includes/footer.php'; ?>
