<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function getSettings($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
        $res = $stmt->fetch();
        return $res ?: ['site_title' => 'دانشگاه اراک', 'font_family' => 'Vazirmatn', 'festival_status' => 1, 'about_text' => ''];
    } catch (Exception $e) {
        return ['site_title' => 'دانشگاه اراک', 'font_family' => 'Vazirmatn', 'festival_status' => 1, 'about_text' => ''];
    }
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
    $persian = ["$jd", $months[$jm], "$jy"];
    return strtr(implode(' ', $persian), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
}

// ---------- آپلود امن تصویر ----------
function uploadImage($field, $dir) {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp','svg'])) return '';
    if ($ext !== 'svg' && !@getimagesize($_FILES[$field]['tmp_name'])) return '';
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    return move_uploaded_file($_FILES[$field]['tmp_name'], rtrim($dir, '/') . '/' . $name) ? $name : '';
}

// ---------- کتابخانه آیکون‌های وکتور برداری (بدون هیچ ایموجی) ----------
function svgIcon($name, $class = '', $size = null) {
    $szAttr = $size ? 'width="' . (int)$size . '" height="' . (int)$size . '"' : '';
    $cls = $class ? ' class="svg-icon ' . e($class) . '"' : ' class="svg-icon"';

    switch ($name) {
        case 'search':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';

        case 'calendar':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

        case 'arrow-left':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>';

        case 'megaphone':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>';

        case 'pause':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>';

        case 'play':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';

        case 'building':
        case 'association':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 9h1M9 13h1M9 17h1M14 9h1M14 13h1M14 17h1"/></svg>';

        case 'user':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';

        case 'check':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

        case 'close':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

        case 'menu':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';

        case 'telegram':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/></svg>';

        case 'instagram':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>';

        case 'linkedin':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v7.6h2.8v-7.6h-2.8M7.86 6.88a1.63 1.63 0 1 0 0 3.26a1.63 1.63 0 0 0 0-3.26z"/></svg>';

        case 'grid':
        case 'portal':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>';

        // آیکون‌های دسته‌بندی انجمن‌ها (مطابق طرح)
        case 'book':
        case 'humanity':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';

        case 'dna':
        case 'biology':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 15c6.667-6 13.333 0 20-6"/><path d="M9 22c1.798-1.998 2.518-3.995 2.807-5.993"/><path d="M15 2c-1.798 1.998-2.518 3.995-2.807 5.993"/><path d="M17 6l-2.5-2.5"/><path d="M14 8l-1-1"/><path d="M7 18l2.5 2.5"/><path d="M3.5 14.5l.5.5"/><path d="M20 9.5l.5.5"/><path d="M6.5 12.5l1 1"/><path d="M16.5 10.5l1 1"/><path d="M10 16l-1-1"/></svg>';

        case 'atom':
        case 'physics':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2.5" fill="currentColor"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(30 12 12)"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(-30 12 12)"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(90 12 12)"/></svg>';

        case 'flask':
        case 'chemistry':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v5l-6 11a2 2 0 0 0 1.7 3h12.6a2 2 0 0 0 1.7-3l-6-11V3"/><path d="M7 16h10"/></svg>';

        case 'computer':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><polyline points="8 21 12 17 16 21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';

        case 'gear':
        case 'industrial':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';

        case 'ai':
        case 'chip':
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="1" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="23" y2="12"/></svg>';

        default:
            return '<svg ' . $cls . ' ' . $szAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
    }
}

// ---------- نگاشت خودکار آیکون‌های انجمن‌ها بر اساس نام ----------
function getAssocIconSvg($name, $customIcon = '') {
    if (!empty($customIcon)) {
        if (file_exists(__DIR__ . '/../uploads/logos/' . $customIcon)) {
            $ext = pathinfo($customIcon, PATHINFO_EXTENSION);
            if ($ext === 'svg') {
                return '<div class="assoc-icon-wrap">' . file_get_contents(__DIR__ . '/../uploads/logos/' . $customIcon) . '</div>';
            }
            return '<div class="assoc-icon-wrap"><img src="uploads/logos/' . e($customIcon) . '" alt=""></div>';
        }
    }
    
    // تطبیق بر اساس واژگان نام انجمن مطابق طراحی ارسالی
    if (mb_strpos($name, 'انسانی') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('book', 'assoc-svg', 36) . '</div>';
    } elseif (mb_strpos($name, 'زیست') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('dna', 'assoc-svg', 36) . '</div>';
    } elseif (mb_strpos($name, 'فیزیک') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('atom', 'assoc-svg', 36) . '</div>';
    } elseif (mb_strpos($name, 'شیمی') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('flask', 'assoc-svg', 36) . '</div>';
    } elseif (mb_strpos($name, 'کامپیوتر') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('computer', 'assoc-svg', 36) . '</div>';
    } elseif (mb_strpos($name, 'صنایع') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('gear', 'assoc-svg', 36) . '</div>';
    } elseif (mb_strpos($name, 'هوش مصنوعی') !== false || mb_strpos($name, 'AI') !== false) {
        return '<div class="assoc-icon-wrap">' . svgIcon('ai', 'assoc-svg', 36) . '</div>';
    }
    return '<div class="assoc-icon-wrap">' . svgIcon('building', 'assoc-svg', 36) . '</div>';
}

// ---------- تصویر خبر ----------
function getNewsImageUrl($img) {
    if (!empty($img)) {
        if (file_exists(__DIR__ . '/../uploads/news/' . $img)) {
            return 'uploads/news/' . e($img);
        }
        if (file_exists(__DIR__ . '/../assets/images/' . $img)) {
            return 'assets/images/' . e($img);
        }
    }
    return 'assets/images/news-placeholder1.jpg';
}

function newsImageHtml($img, $alt = '') {
    $src = getNewsImageUrl($img);
    return '<img src="' . $src . '" alt="' . e($alt) . '" loading="lazy">';
}

// ---------- کارت خبر (گرید نامتقارن مطابق طرح) ----------
function newsCard($n, $big = false) {
    $url = 'news_view.php?id=' . (int)$n['id'];
    $imgSrc = getNewsImageUrl($n['image'] ?? '');
    $dateStr = formatDate($n['created_at']);
    $title = e($n['title']);

    if ($big) {
        return '<div class="news-card big-card">
            <a href="' . $url . '" class="big-card-link">
                <div class="big-card-img-wrap">
                    <img src="' . $imgSrc . '" alt="' . $title . '">
                    <div class="big-card-gradient"></div>
                </div>
                <div class="big-card-content">
                    <h3 class="big-card-title">' . $title . '</h3>
                    <div class="big-card-meta">
                        <span class="news-date">' . svgIcon('calendar', 'date-icon', 16) . ' ' . $dateStr . '</span>
                    </div>
                </div>
            </a>
        </div>';
    }

    return '<div class="news-card small-card">
        <a href="' . $url . '" class="small-card-img-wrap">
            <img src="' . $imgSrc . '" alt="' . $title . '" loading="lazy">
        </a>
        <div class="small-card-content">
            <h4 class="small-card-title"><a href="' . $url . '">' . $title . '</a></h4>
            <div class="small-card-meta">
                <span class="news-date">' . svgIcon('calendar', 'date-icon', 14) . ' ' . $dateStr . '</span>
            </div>
        </div>
    </div>';
}

// ---------- کارت انجمن ----------
function assocCard($a) {
    $iconHtml = getAssocIconSvg($a['name'], $a['icon'] ?? '');
    return '<a href="association.php?id=' . (int)$a['id'] . '" class="assoc-card">
        ' . $iconHtml . '
        <span class="assoc-name">' . e($a['name']) . '</span>
    </a>';
}

// ---------- نوار آخرین اخبار (تیکر روان مدرن با دکمه توقف/پخش) ----------
function renderTicker($pdo, $assoc_id = 0) {
    $items = [];
    if ($assoc_id > 0) {
        try {
            $st = $pdo->prepare("SELECT * FROM ticker_items WHERE active=1 AND association_id=? ORDER BY sort_order ASC, id DESC");
            $st->execute([$assoc_id]);
            $items = $st->fetchAll();
        } catch (Exception $e) {}
    }
    if (!$items) {
        try {
            $items = $pdo->query("SELECT * FROM ticker_items WHERE active=1 AND association_id=0 ORDER BY sort_order ASC, id DESC")->fetchAll();
        } catch (Exception $e) {}
    }
    if (!$items) {
        // فالبک از تیترهای اخبار در صورت خالی بودن جدول
        try {
            $fallbackNews = $pdo->query("SELECT id, title FROM news ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
            foreach ($fallbackNews as $fn) {
                $items[] = ['text' => $fn['title'], 'link' => 'news_view.php?id=' . $fn['id']];
            }
        } catch (Exception $e) {}
    }
    if (!$items) return '';

    $parts = [];
    foreach ($items as $it) {
        $link = !empty($it['link']) ? e($it['link']) : '#';
        $parts[] = '<a href="' . $link . '" class="ticker-item">' . e($it['text']) . '</a>';
    }

    $tickerContent = implode('<span class="ticker-sep">|</span>', $parts);

    return '<div class="news-ticker-wrapper container">
        <div class="news-ticker-container">
            <button type="button" class="ticker-ctrl-btn" id="tickerToggleBtn" title="توقف / پخش اخبار">
                <span class="btn-icon icon-pause">' . svgIcon('pause', '', 14) . '</span>
                <span class="btn-icon icon-play" style="display:none;">' . svgIcon('play', '', 14) . '</span>
            </button>
            <div class="ticker-track-wrap" id="tickerTrackWrap">
                <div class="ticker-track" id="tickerTrack">
                    ' . $tickerContent . '
                </div>
            </div>
            <div class="ticker-badge">
                <span class="badge-text">آخرین اخبار:</span>
                ' . svgIcon('megaphone', 'ticker-mega-icon', 18) . '
            </div>
        </div>
    </div>';
}
?>
