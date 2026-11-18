<?php 
$page = 'associations';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/icons.php';

$assoc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM associations WHERE id = ?");
$stmt->execute([$assoc_id]);
$assoc = $stmt->fetch();

if (!$assoc) {
    $fallbackAssoc = $pdo->query("SELECT * FROM associations ORDER BY id ASC LIMIT 1")->fetch();
    if ($fallbackAssoc) {
        $assoc = $fallbackAssoc;
        $assoc_id = (int)$assoc['id'];
    } else {
        echo "<div class='container' style='padding:120px 20px; text-align:center;'>
                <h2>انجمن مورد نظر یافت نشد!</h2>
                <a href='associations.php' class='btn-primary' style='display:inline-flex; margin-top:20px;'>مشاهده همه انجمن‌ها</a>
              </div>";
        include 'includes/footer.php';
        exit;
    }
}

// ۱. اعضای شورای مرکزی
$mem_stmt = $pdo->prepare("SELECT * FROM members WHERE association_id = ? ORDER BY id ASC");
$mem_stmt->execute([$assoc_id]);
$members = $mem_stmt->fetchAll();

// ۲. رویدادهای اختصاصی انجمن
$events_stmt = $pdo->prepare("SELECT * FROM events WHERE association_id = ? AND is_published = 1 ORDER BY starts_at ASC");
$events_stmt->execute([$assoc_id]);
$assoc_events = $events_stmt->fetchAll();

// ۳. اخبار اختصاصی انجمن (بدون فالبک به عمومی)
$news_stmt = $pdo->prepare("SELECT * FROM news WHERE association_id = ? ORDER BY created_at DESC, id DESC LIMIT 6");
$news_stmt->execute([$assoc_id]);
$news_list = $news_stmt->fetchAll();

// ۴. نشریات علمی انجمن
$pub_stmt = $pdo->prepare("SELECT * FROM publications WHERE association_id = ? ORDER BY published_at DESC, id DESC");
$pub_stmt->execute([$assoc_id]);
$publications = $pub_stmt->fetchAll();

// ۵. تصاویر گالری
$gallery_stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE association_id = ? ORDER BY sort_order ASC, id DESC");
$gallery_stmt->execute([$assoc_id]);
$gallery = $gallery_stmt->fetchAll();

// ۶. اسناد و گزارش‌ها
$docs_stmt = $pdo->prepare("SELECT * FROM documents WHERE association_id = ? ORDER BY id DESC");
$docs_stmt->execute([$assoc_id]);
$documents = $docs_stmt->fetchAll();

// ۷. فرم عضویت فعال انجمن
$membership_form = null;
if (!empty($assoc['membership_open'])) {
    $f_stmt = $pdo->prepare("SELECT id FROM forms WHERE association_id = ? AND type = 'membership' AND is_open = 1 LIMIT 1");
    $f_stmt->execute([$assoc_id]);
    $membership_form = $f_stmt->fetch();
}

// تصویر کاور
$cover = 'assets/images/default-cover.jpg';
if (!empty($assoc['cover_image'])) {
    if (file_exists(__DIR__ . '/uploads/covers/' . $assoc['cover_image'])) {
        $cover = 'uploads/covers/' . $assoc['cover_image'];
    } elseif (file_exists(__DIR__ . '/assets/images/' . $assoc['cover_image'])) {
        $cover = 'assets/images/' . $assoc['cover_image'];
    }
}

// تفکیک خطوط اهداف
$goalsList = [];
if (!empty($assoc['goals'])) {
    $goalsList = array_filter(array_map('trim', explode("\n", $assoc['goals'])));
}
?>

<!-- هیرو اختصاصی انجمن بدون بج -->
<section class="assoc-hero-section">
    <div class="assoc-hero-bg-layer" style="background-image: url('<?= e($cover) ?>');"></div>
    <div class="assoc-hero-overlay"></div>
    <div class="container assoc-hero-container">
        <div class="assoc-hero-content">
            <h1 class="assoc-hero-title">
                <?= e($assoc['name']) ?><br>
                <span class="assoc-hero-sub">دانشگاه اراک</span>
            </h1>
            <p class="assoc-hero-desc"><?= e($assoc['description']) ?></p>

            <!-- دکمه‌های اقدام هیرو -->
            <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
                <?php if (!empty($assoc['membership_open'])): ?>
                    <?php $memUrl = $membership_form ? 'form.php?id=' . $membership_form['id'] : '#contact'; ?>
                    <a href="<?= $memUrl ?>" class="btn-primary" style="padding:10px 22px;">
                        عضویت در انجمن
                    </a>
                <?php endif; ?>
                <a href="#events" class="btn-secondary" style="padding:10px 20px;">
                    مشاهده رویدادها
                </a>
            </div>
        </div>
    </div>
</section>

<!-- نوار آخرین اخبار اختصاصی انجمن -->
<?= renderTicker($pdo, $assoc_id) ?>

<!-- ناوبری چسبان درون‌صفحه‌ای (In-Page Sub-navigation) -->
<nav class="assoc-subnav-sticky" id="assocSubNav">
    <div class="container">
        <ul class="assoc-subnav-list">
            <li><a href="#about" class="subnav-link">معرفی و اهداف</a></li>
            <li><a href="#council" class="subnav-link">شورای مرکزی</a></li>
            <li><a href="#events" class="subnav-link">رویدادها</a></li>
            <li><a href="#news" class="subnav-link">اخبار</a></li>
            <?php if ($publications): ?><li><a href="#publications" class="subnav-link">نشریات</a></li><?php endif; ?>
            <?php if ($gallery): ?><li><a href="#gallery" class="subnav-link">گالری</a></li><?php endif; ?>
            <?php if ($documents): ?><li><a href="#documents" class="subnav-link">اسناد و گزارش‌ها</a></li><?php endif; ?>
            <li><a href="#contact" class="subnav-link">راه‌های ارتباطی</a></li>
        </ul>
    </div>
</nav>

<div class="assoc-sections-wrapper container">

    <!-- ۱. معرفی و اهداف -->
    <section id="about" class="assoc-content-section">
        <h2 class="section-title">معرفی و اهداف انجمن</h2>
        <div class="about-text-content" style="font-size:15.5px; line-height:2.1; color:var(--text-secondary); margin-bottom:20px;">
            <?= nl2br(e($assoc['description'])) ?>
        </div>

        <?php if ($goalsList): ?>
            <div class="goals-box" style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px 28px;">
                <h3 style="font-size:16px; font-weight:700; color:var(--color-primary-navy); margin-bottom:14px;">اهداف و چشم‌اندازها:</h3>
                <ul style="display:flex; flex-direction:column; gap:10px;">
                    <?php foreach ($goalsList as $goal): ?>
                        <li style="display:flex; align-items:flex-start; gap:10px; font-size:14.5px; color:var(--text-primary);">
                            <span style="color:var(--color-primary-navy); margin-top:4px; flex-shrink:0;">
                                <?= svgIcon('check', '', 16) ?>
                            </span>
                            <span><?= e($goal) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>

    <!-- ۲. شورای مرکزی (اعضا) -->
    <section id="council" class="assoc-content-section">
        <h2 class="section-title">شورای مرکزی <?= e($assoc['name']) ?></h2>
        <?php if ($members): ?>
            <div class="council-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:24px; text-align:center;">
                <?php foreach ($members as $m): 
                    $hasImg = false;
                    $img = 'assets/images/default-user.png';
                    if (!empty($m['image'])) {
                        if (file_exists(__DIR__ . '/uploads/profiles/' . $m['image'])) {
                            $img = 'uploads/profiles/' . $m['image'];
                            $hasImg = true;
                        } elseif (file_exists(__DIR__ . '/assets/images/' . $m['image'])) {
                            $img = 'assets/images/' . $m['image'];
                            $hasImg = true;
                        }
                    }
                ?>
                    <div class="council-member-item">
                        <div style="width:110px; height:110px; border-radius:50%; margin:0 auto 12px; overflow:hidden; border:2px solid var(--border-color); background:var(--color-soft-bg);">
                            <?php if ($hasImg): ?>
                                <img src="<?= e($img) ?>" alt="<?= e($m['full_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
                                    <?= svgIcon('user', '', 48) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h3 style="font-size:15px; font-weight:700; color:var(--color-primary-navy); margin-bottom:4px;">
                            <?= e($m['full_name']) ?>
                        </h3>
                        <span style="font-size:13px; color:var(--text-muted);">
                            <?= e($m['role']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-box"><p>اسامی اعضای شورای مرکزی ثبت نشده است.</p></div>
        <?php endif; ?>
    </section>

    <!-- ۳. رویدادهای اختصاصی انجمن -->
    <section id="events" class="assoc-content-section">
        <h2 class="section-title">رویدادها و کارگاه‌های انجمن</h2>
        <?php if ($assoc_events): ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:20px;">
                <?php foreach ($assoc_events as $ev): 
                    $dateObj = strtotime($ev['starts_at']);
                    $jalali = gregorian_to_jalali((int)date('Y', $dateObj), (int)date('n', $dateObj), (int)date('j', $dateObj));
                    $months = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
                    $dayNum = strtr((string)$jalali[2], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
                    $monthName = $months[$jalali[1]];
                ?>
                    <article style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; display:flex; gap:16px;">
                        <div style="background:var(--color-primary-navy); color:#fff; border-radius:var(--radius-md); padding:10px 14px; text-align:center; height:fit-content; min-width:60px;">
                            <span style="font-size:18px; font-weight:700; display:block;"><?= $dayNum ?></span>
                            <span style="font-size:11px;"><?= $monthName ?></span>
                        </div>
                        <div style="flex:1;">
                            <h3 style="font-size:15px; font-weight:700; margin-bottom:8px;">
                                <a href="event.php?id=<?= $ev['id'] ?>"><?= e($ev['title']) ?></a>
                            </h3>
                            <?php if (!empty($ev['location'])): ?>
                                <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">
                                    <?= svgIcon('building', '', 14) ?> <?= e($ev['location']) ?>
                                </p>
                            <?php endif; ?>
                            <a href="event.php?id=<?= $ev['id'] ?>" class="btn-text" style="font-size:12.5px;">
                                ثبت‌نام و جزئیات <?= svgIcon('arrow-left', '', 12) ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-box"><p>در حال حاضر رویداد فعالی برای این انجمن ثبت نشده است.</p></div>
        <?php endif; ?>
    </section>

    <!-- ۴. اخبار اختصاصی انجمن -->
    <section id="news" class="assoc-content-section">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 class="section-title" style="margin-bottom:0;">اخبار و اطلاعیه‌های انجمن</h2>
            <a href="news.php?assoc=<?= $assoc_id ?>" class="btn-secondary" style="font-size:12.5px; padding:6px 14px;">
                آرشیو کامل اخبار <?= svgIcon('arrow-left', '', 12) ?>
            </a>
        </div>
        
        <?php if ($news_list): ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                <?php foreach ($news_list as $n): ?>
                    <?= newsCard($n, false) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-box">
                <p>هنوز خبری برای این انجمن ثبت نشده است.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- ۵. نشریات علمی -->
    <?php if ($publications): ?>
        <section id="publications" class="assoc-content-section">
            <h2 class="section-title">نشریات و ویژه‌نامه‌ها</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:22px;">
                <?php foreach ($publications as $pub): 
                    $coverImg = !empty($pub['cover_image']) ? getNewsImageUrl($pub['cover_image']) : 'assets/images/default-cover.jpg';
                ?>
                    <div style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
                        <a href="publication.php?id=<?= $pub['id'] ?>" style="display:block; height:200px; overflow:hidden;">
                            <img src="<?= e($coverImg) ?>" alt="<?= e($pub['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        </a>
                        <div style="padding:16px;">
                            <span style="font-size:12px; color:var(--color-accent); font-weight:600;"><?= e($pub['issue_no'] ?? '') ?></span>
                            <h3 style="font-size:14.5px; font-weight:700; margin:4px 0 10px;">
                                <a href="publication.php?id=<?= $pub['id'] ?>"><?= e($pub['title']) ?></a>
                            </h3>
                            <a href="publication.php?id=<?= $pub['id'] ?>" class="btn-text" style="font-size:12.5px;">
                                دانلود نسخه PDF <?= svgIcon('arrow-left', '', 12) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- ۶. گالری تصاویر با دیالوگ مدال بومی -->
    <?php if ($gallery): ?>
        <section id="gallery" class="assoc-content-section">
            <h2 class="section-title">گالری تصاویر رویدادها</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px;">
                <?php foreach ($gallery as $g): 
                    $imgSrc = getNewsImageUrl($g['image']);
                ?>
                    <div class="gallery-thumb" style="border-radius:var(--radius-md); overflow:hidden; height:150px; cursor:pointer;" onclick="openLightbox('<?= e($imgSrc) ?>', '<?= e($g['caption'] ?? '') ?>')">
                        <img src="<?= e($imgSrc) ?>" alt="<?= e($g['caption'] ?? '') ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- مدال لایت‌باکس با دیالوگ استاندارد HTML5 -->
            <dialog id="imageLightbox" style="border:none; border-radius:var(--radius-lg); padding:20px; max-width:85vw; max-height:85vh; background:#0c1a35; color:#fff; box-shadow:var(--shadow-lg);" onclick="if(event.target===this) this.close()">
                <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
                    <button type="button" onclick="document.getElementById('imageLightbox').close()" style="background:transparent; border:none; color:#fff; cursor:pointer; font-size:20px;">
                        <?= svgIcon('close', '', 20) ?>
                    </button>
                </div>
                <img id="lightboxImg" src="" alt="" style="max-width:100%; max-height:65vh; border-radius:var(--radius-md); margin:0 auto; display:block;">
                <p id="lightboxCaption" style="text-align:center; margin-top:12px; font-size:14px; opacity:0.9;"></p>
            </dialog>

            <script>
                function openLightbox(src, caption) {
                    const dlg = document.getElementById('imageLightbox');
                    document.getElementById('lightboxImg').src = src;
                    document.getElementById('lightboxCaption').innerText = caption;
                    dlg.showModal();
                }
            </script>
        </section>
    <?php endif; ?>

    <!-- ۷. اسناد و فایل‌ها -->
    <?php if ($documents): ?>
        <section id="documents" class="assoc-content-section">
            <h2 class="section-title">اسناد، اساسنامه و گزارش‌های عملکرد</h2>
            <div style="background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
                <ul style="display:flex; flex-direction:column;">
                    <?php foreach ($documents as $doc): 
                        $docUrl = 'uploads/documents/' . e($doc['file']);
                    ?>
                        <li style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid var(--border-subtle); flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span style="color:var(--color-primary-navy);">
                                    <?= svgIcon('book', '', 20) ?>
                                </span>
                                <div>
                                    <strong style="font-size:14px; color:var(--text-primary); display:block;"><?= e($doc['title']) ?></strong>
                                    <?php if (!empty($doc['period_label'])): ?>
                                        <small style="color:var(--text-muted);"><?= e($doc['period_label']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= $docUrl ?>" target="_blank" class="btn-secondary" style="font-size:12.5px; padding:6px 14px;">
                                دریافت فایل <?= svgIcon('arrow-left', '', 12) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    <!-- ۸. راه‌های ارتباطی -->
    <section id="contact" class="assoc-content-section" style="margin-bottom:60px;">
        <h2 class="section-title">راه‌های ارتباط با انجمن</h2>
        <div class="contact-links-row" style="display:flex; flex-wrap:wrap; gap:20px; background:var(--color-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:28px;">
            <?php if (!empty($assoc['email'])): ?>
                <div style="display:flex; align-items:center; gap:10px; font-size:14px;">
                    <span style="color:var(--color-primary-navy);"><?= svgIcon('mail', '', 18) ?></span>
                    <span><strong>ایمیل:</strong> <a href="mailto:<?= e($assoc['email']) ?>" style="color:var(--color-primary-navy);"><?= e($assoc['email']) ?></a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($assoc['phone'])): ?>
                <div style="display:flex; align-items:center; gap:10px; font-size:14px;">
                    <span style="color:var(--color-primary-navy);"><?= svgIcon('phone', '', 18) ?></span>
                    <span><strong>تلفن:</strong> <a href="tel:<?= e($assoc['phone']) ?>"><?= e($assoc['phone']) ?></a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($assoc['telegram'])): ?>
                <div style="display:flex; align-items:center; gap:10px; font-size:14px;">
                    <span style="color:var(--color-primary-navy);"><?= svgIcon('telegram', '', 18) ?></span>
                    <span><strong>تلگرام:</strong> <a href="https://t.me/<?= e($assoc['telegram']) ?>" target="_blank">@<?= e($assoc['telegram']) ?></a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($assoc['instagram'])): ?>
                <div style="display:flex; align-items:center; gap:10px; font-size:14px;">
                    <span style="color:var(--color-primary-navy);"><?= svgIcon('instagram', '', 18) ?></span>
                    <span><strong>اینستاگرام:</strong> <a href="https://instagram.com/<?= e($assoc['instagram']) ?>" target="_blank">@<?= e($assoc['instagram']) ?></a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($assoc['linkedin'])): ?>
                <div style="display:flex; align-items:center; gap:10px; font-size:14px;">
                    <span style="color:var(--color-primary-navy);"><?= svgIcon('linkedin', '', 18) ?></span>
                    <span><strong>لینکدین:</strong> <a href="https://linkedin.com/in/<?= e($assoc['linkedin']) ?>" target="_blank"><?= e($assoc['linkedin']) ?></a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($assoc['website'])): ?>
                <div style="display:flex; align-items:center; gap:10px; font-size:14px;">
                    <span style="color:var(--color-primary-navy);"><?= svgIcon('grid', '', 18) ?></span>
                    <span><strong>وب‌سایت:</strong> <a href="<?= e($assoc['website']) ?>" target="_blank"><?= e($assoc['website']) ?></a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($assoc['address'])): ?>
                <div style="width:100%; display:flex; align-items:center; gap:10px; font-size:14px; margin-top:8px; padding-top:14px; border-top:1px solid var(--border-subtle);">
                    <span style="color:var(--color-primary-navy); flex-shrink:0;"><?= svgIcon('building', '', 18) ?></span>
                    <span><strong>آدرس دفتر:</strong> <?= e($assoc['address']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>
