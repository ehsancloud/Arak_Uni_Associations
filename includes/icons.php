<?php

function assocIconCatalog() {
    return [
        'ai'         => 'هوش مصنوعی',
        'humanities' => 'علوم انسانی',
        'biology'    => 'زیست‌شناسی',
        'physics'    => 'فیزیک',
        'chemistry'  => 'شیمی',
        'computer'   => 'کامپیوتر',
        'industrial' => 'مهندسی صنایع',
        'math'       => 'ریاضی',
        'general'    => 'عمومی',
    ];
}

function assocIconSvg($key, $class = '', $size = 40) {
    $attrs = 'width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="none"'
        . ' stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true" focusable="false"';
    if ($class !== '') {
        $attrs .= ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';
    }

    $paths = [
        // شبکه عصبی: ورودی، لایه میانی، خروجی
        'ai' => '<circle cx="4.5" cy="7" r="1.6"/><circle cx="4.5" cy="17" r="1.6"/>'
              . '<circle cx="12" cy="12" r="1.6"/><circle cx="19.5" cy="12" r="1.6"/>'
              . '<path d="M6.1 7.5 10.5 11.1M6.1 16.5 10.5 12.9M13.6 12h4.3"/>',

        // کتاب باز
        'humanities' => '<path d="M12 6.5C10.2 5 7.6 4.5 4 4.7v13c3.6-.2 6.2.3 8 1.8 1.8-1.5 4.4-2 8-1.8v-13c-3.6-.2-6.2.3-8 1.8Z"/>'
                      . '<path d="M12 6.5v13"/>',

        // مارپیچ DNA
        'biology' => '<path d="M8 3c0 4 8 4 8 8s-8 4-8 8"/>'
                   . '<path d="M16 3c0 4-8 4-8 8s8 4 8 8"/>'
                   . '<path d="M9 7h6M9 11h6M9 15h6"/>',

        // اتم با مدار
        'physics' => '<circle cx="12" cy="12" r="1.7"/>'
                   . '<ellipse cx="12" cy="12" rx="9" ry="4"/>'
                   . '<ellipse cx="12" cy="12" rx="9" ry="4" transform="rotate(60 12 12)"/>'
                   . '<ellipse cx="12" cy="12" rx="9" ry="4" transform="rotate(120 12 12)"/>',

        // ارلن آزمایشگاه
        'chemistry' => '<path d="M10 3h4"/><path d="M10.5 3v5.2L5.4 17.4A2.4 2.4 0 0 0 7.6 21h8.8a2.4 2.4 0 0 0 2.2-3.6L13.5 8.2V3"/>'
                     . '<path d="M7.4 15h9.2"/>',

        // براکت کد
        'computer' => '<path d="m8.5 8-4.5 4 4.5 4"/><path d="m15.5 8 4.5 4-4.5 4"/><path d="m13.5 5-3 14"/>',

        // چرخ‌دنده و روند صعودی
        'industrial' => '<circle cx="9.5" cy="9.5" r="3"/>'
                      . '<path d="M9.5 3.5v2M9.5 13.5v2M3.5 9.5h2M13.5 9.5h2M5.3 5.3l1.4 1.4M12.3 12.3l1.4 1.4M13.7 5.3l-1.4 1.4M6.7 12.3l-1.4 1.4"/>'
                      . '<path d="m3 20 4.5-4.5L11 19l4-5 6 6"/>',

        // نمادهای ریاضی
        'math' => '<path d="M4 7h7M4 17h7M17.5 7v10M14.5 12h6"/>',

        // ساختمان
        'general' => '<path d="M4 20h16"/><path d="M6 20V7l6-3 6 3v13"/>'
                   . '<path d="M10 20v-4h4v4"/><path d="M10 11h1M13 11h1"/>',
    ];

    $body = $paths[$key] ?? $paths['general'];
    return '<svg ' . $attrs . '>' . $body . '</svg>';
}

function assocIconKey($row) {
    if (!empty($row['icon_key']) && isset(assocIconCatalog()[$row['icon_key']])) {
        return $row['icon_key'];
    }

    $name = $row['name'] ?? '';
    $map = [
        'humanities' => ['انسانی', 'معارف'],
        'biology'    => ['زیست'],
        'physics'    => ['فیزیک', 'نجوم'],
        'chemistry'  => ['شیمی'],
        'computer'   => ['کامپیوتر', 'نرم‌افزار', 'امنیت'],
        'industrial' => ['صنایع'],
        'math'       => ['ریاضی', 'آمار'],
        'ai'         => ['هوش مصنوعی'],
    ];

    foreach ($map as $key => $words) {
        foreach ($words as $word) {
            if (mb_strpos($name, $word) !== false) {
                return $key;
            }
        }
    }

    return 'general';
}
