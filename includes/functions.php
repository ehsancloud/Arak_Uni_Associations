<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function getSettings($pdo) {
    $stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

// ---------- تاریخ شمسی ----------
function gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * ((int)($days / 12053)));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    if ($days > 365) { $jy += (int)(($days - 1) / 365); $days = ($days - 1) % 365; }
    if ($days < 186) { $jm = 1 + (int)($days / 31); $jd = 1 + ($days % 31); }
    else { $jm = 7 + (int)(($days - 186) / 30); $jd = 1 + (($days - 186) % 30); }
    return [$jy, $jm, $jd];
}

function formatDate($date) {
    $t = strtotime($date);
    if (!$t) return '';
    list($jy, $jm, $jd) = gregorian_to_jalali((int)date('Y', $t), (int)date('n', $t), (int)date('j', $t));
    $months = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
    return strtr("$jd {$months[$jm]} $jy", ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
}

// ---------- آپلود امن تصویر ----------
function uploadImage($field, $dir) {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return '';
    if (!@getimagesize($_FILES[$field]['tmp_name'])) return '';
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    return move_uploaded_file($_FILES[$field]['tmp_name'], rtrim($dir, '/') . '/' . $name) ? $name : '';
}

// ---------- کارت خبر ----------
function newsImageHtml($img) {
    if ($img && file_exists(__DIR__ . '/../uploads/news/' . $img)) {
        return '<img src="uploads/news/' . e($img) . '" alt="">';
    }
    return '<div class="no-img"></div>';
}

function newsCard($n, $big = false) {
    $url = 'news_view.php?id=' . (int)$n['id'];
    return '<div class="news-card' . ($big ? ' big-card' : '') . '">'
        . '<a href="' . $url . '" class="news-img">' . newsImageHtml($n['image']) . '</a>'
        . '<div class="news-content"><h4><a href="' . $url . '">' . e($n['title']) . '</a></h4>'
        . '<div class="news-meta"><span class="news-date">' . formatDate($n['created_at']) . '</span>'
        . '<a href="' . $url . '" class="news-more">مشاهده خبر ←</a></div></div></div>';
}

// ---------- کارت انجمن ----------
function assocCard($a) {
    $icon = '<div class="assoc-ph">🏛</div>';
    if (!empty($a['icon']) && file_exists(__DIR__ . '/../uploads/logos/' . $a['icon'])) {
        $icon = '<img src="uploads/logos/' . e($a['icon']) . '" alt="">';
    }
    return '<a href="association.php?id=' . (int)$a['id'] . '" class="assoc-card">' . $icon . '<span>' . e($a['name']) . '</span></a>';
}

// ---------- نوار آخرین اخبار (قابل کنترل از پنل ادمین) ----------
function renderTicker($pdo, $assoc_id = 0) {
    $items = [];
    if ($assoc_id > 0) {
        $st = $pdo->prepare("SELECT * FROM ticker_items WHERE active=1 AND association_id=? ORDER BY sort_order ASC, id DESC");
        $st->execute([$assoc_id]);
        $items = $st->fetchAll();
    }
    if (!$items) {
        $items = $pdo->query("SELECT * FROM ticker_items WHERE active=1 AND association_id=0 ORDER BY sort_order ASC, id DESC")->fetchAll();
    }
    if (!$items) return '';
    $parts = [];
    foreach ($items as $it) {
        $parts[] = !empty($it['link'])
            ? '<a href="' . e($it['link']) . '">' . e($it['text']) . '</a>'
            : '<span>' . e($it['text']) . '</span>';
    }
    return '<div class="news-ticker container"><div class="ticker-btn">آخرین اخبار:</div>'
        . '<marquee class="ticker-text" direction="right" scrollamount="5" onmouseover="this.stop()" onmouseout="this.start()">'
        . implode(' &nbsp;|&nbsp; ', $parts) . '</marquee></div>';
}
?>
