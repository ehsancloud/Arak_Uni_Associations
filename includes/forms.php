<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/**
 * تبدیل ارقام فارسی و عربی به ارقام انگلیسی
 */
function toEnglishDigits($str) {
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];
    $str = str_replace($persian, $english, (string)$str);
    return str_replace($arabic, $english, $str);
}

/**
 * مدیریت توکن امنیتی CSRF
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * دریافت اطلاعات یک فرم
 */
function getForm($pdo, $id) {
    $stmt = $pdo->prepare("SELECT f.*, a.name AS assoc_name 
                           FROM forms f 
                           LEFT JOIN associations a ON f.association_id = a.id 
                           WHERE f.id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

/**
 * دریافت لیست فیلدهای یک فرم به ترتیب
 */
function getFormFields($pdo, $formId) {
    $stmt = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([(int)$formId]);
    return $stmt->fetchAll();
}

/**
 * بررسی امکان ارسال فرم (باز بودن، تاریخ، ظرفیت)
 */
function checkFormAvailability($pdo, $form) {
    if (empty($form['is_open'])) {
        return 'این فرم در حال حاضر بسته شده است و امکان ارسال پاسخ جدید وجود ندارد.';
    }

    $now = time();
    if (!empty($form['opens_at']) && strtotime($form['opens_at']) > $now) {
        return 'مهلت ثبت‌نام در این فرم هنوز آغاز نشده است.';
    }
    if (!empty($form['closes_at']) && strtotime($form['closes_at']) < $now) {
        return 'مهلت ثبت‌نام در این فرم به پایان رسیده است.';
    }

    if (!empty($form['capacity']) && (int)$form['capacity'] > 0) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM form_submissions WHERE form_id = ?");
        $st->execute([(int)$form['id']]);
        $count = (int)$st->fetchColumn();
        if ($count >= (int)$form['capacity']) {
            return 'ظرفیت ثبت‌نام در این فرم تکمیل شده است.';
        }
    }

    return null;
}

/**
 * اعتبارسنجی ارسال فرم
 */
function validateFormSubmission($pdo, $form, $fields, $post) {
    $errors = [];

    // ۱. بررسی تله اسپم (Honeypot)
    if (!empty($post['website_hp'])) {
        $errors['global'] = 'درخواست نامعتبر تشخیص داده شد.';
        return $errors;
    }

    // ۲. بررسی توکن امنیتی CSRF
    if (!verifyCsrfToken($post['csrf_token'] ?? '')) {
        $errors['global'] = 'نشست امنیتی شما منقضی شده است. لطفاً صفحه را مجدداً باز کرده و تلاش کنید.';
        return $errors;
    }

    // ۳. بررسی در دسترس بودن فرم
    $availErr = checkFormAvailability($pdo, $form);
    if ($availErr) {
        $errors['global'] = $availErr;
        return $errors;
    }

    // ۴. اعتبارسنجی تک‌تک فیلدها
    foreach ($fields as $field) {
        $key = 'field_' . $field['id'];
        $val = trim($post[$key] ?? '');
        $val = toEnglishDigits($val);

        // فیلدهای الزامی
        if (!empty($field['required']) && $val === '') {
            $errors[$key] = 'تکمیل فیلد «' . htmlspecialchars($field['label']) . '» الزامی است.';
            continue;
        }

        if ($val === '') {
            continue;
        }

        // بررسی نوع فیلد
        switch ($field['type']) {
            case 'tel':
                if (!preg_match('/^09\d{9}$/', $val)) {
                    $errors[$key] = 'شماره موبایل وارد شده باید ۱۱ رقمی و با 09 آغاز شود.';
                }
                break;

            case 'email':
                if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = 'فرمت ایمیل وارد شده معتبر نیست.';
                }
                break;

            case 'student_id':
                if (!preg_match('/^\d{6,14}$/', $val)) {
                    $errors[$key] = 'شماره دانشجویی باید فقط شامل ارقام (۶ تا ۱۴ رقم) باشد.';
                }
                break;

            case 'number':
                if (!is_numeric($val)) {
                    $errors[$key] = 'این فیلد فقط مقدار عددی می‌پذیرد.';
                }
                break;

            case 'select':
            case 'radio':
                $allowed = array_map('trim', explode("\n", (string)$field['options']));
                if (!in_array($val, $allowed, true)) {
                    $errors[$key] = 'گزینه انتخاب شده نامعتبر است.';
                }
                break;
        }

        // بررسی یکتایی فیلد در فرم (is_unique)
        if (!empty($field['is_unique']) && !isset($errors[$key])) {
            $searchJson = '%"' . $field['label'] . '":"' . $val . '"%';
            $st = $pdo->prepare("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND data LIKE ?");
            $st->execute([(int)$form['id'], $searchJson]);
            if ((int)$st->fetchColumn() > 0) {
                $errors[$key] = 'این مقدار قبلاً در سامانه ثبت شده است و امکان ارسال مجدد وجود ندارد.';
            }
        }
    }

    return $errors;
}

/**
 * ذخیره داده‌های فرم در دیتابیس با ساختار امن JSON
 */
function saveFormSubmission($pdo, $formId, $fields, $post) {
    $snapshot = [];
    foreach ($fields as $field) {
        $key = 'field_' . $field['id'];
        $val = trim($post[$key] ?? '');
        $val = toEnglishDigits($val);
        $snapshot[$field['label']] = $val;
    }

    $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $pdo->prepare("INSERT INTO form_submissions (form_id, data, status, created_at) VALUES (?, ?, 'new', NOW())");
    $stmt->execute([(int)$formId, $json]);
    return $pdo->lastInsertId();
}

/**
 * رندر ظاهر فرم به صورت تمیز و استاندارد
 */
function renderFormHtml($form, $fields, $errors = [], $old = [], $action = '') {
    $html = '<form method="POST" action="' . htmlspecialchars($action) . '" class="site-form-layout" novalidate>';
    
    // فیلدهای امنیتی مخفی
    $html .= '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
    $html .= '<div style="display:none;" aria-hidden="true"><input type="text" name="website_hp" tabindex="-1" autocomplete="off"></div>';

    if (!empty($errors['global'])) {
        $html .= '<div class="alert alert-danger" role="alert" style="margin-bottom:20px;">' . htmlspecialchars($errors['global']) . '</div>';
    }

    foreach ($fields as $field) {
        $key = 'field_' . $field['id'];
        $val = htmlspecialchars($old[$key] ?? '');
        $hasErr = isset($errors[$key]);
        $errHtml = $hasErr ? '<div class="form-error-msg">' . htmlspecialchars($errors[$key]) . '</div>' : '';

        $html .= '<div class="form-group ' . ($hasErr ? 'has-error' : '') . '">';
        $html .= '<label class="form-label" for="' . $key . '">';
        $html .= htmlspecialchars($field['label']);
        if (!empty($field['required'])) {
            $html .= ' <span class="required-star" aria-label="الزامی">*</span>';
        }
        $html .= '</label>';

        $placeholder = !empty($field['placeholder']) ? ' placeholder="' . htmlspecialchars($field['placeholder']) . '"' : '';

        switch ($field['type']) {
            case 'textarea':
                $html .= '<textarea id="' . $key . '" name="' . $key . '" class="form-control" rows="4"' . $placeholder . '>' . $val . '</textarea>';
                break;

            case 'select':
                $opts = array_map('trim', explode("\n", (string)$field['options']));
                $html .= '<select id="' . $key . '" name="' . $key . '" class="form-control">';
                $html .= '<option value="">-- انتخاب کنید --</option>';
                foreach ($opts as $opt) {
                    if ($opt === '') continue;
                    $sel = ($val === $opt) ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($opt) . '"' . $sel . '>' . htmlspecialchars($opt) . '</option>';
                }
                $html .= '</select>';
                break;

            case 'radio':
                $opts = array_map('trim', explode("\n", (string)$field['options']));
                $html .= '<div class="radio-group-wrap">';
                foreach ($opts as $idx => $opt) {
                    if ($opt === '') continue;
                    $optId = $key . '_' . $idx;
                    $chk = ($val === $opt) ? ' checked' : '';
                    $html .= '<label class="radio-label" for="' . $optId . '">';
                    $html .= '<input type="radio" id="' . $optId . '" name="' . $key . '" value="' . htmlspecialchars($opt) . '"' . $chk . '> ';
                    $html .= htmlspecialchars($opt);
                    $html .= '</label>';
                }
                $html .= '</div>';
                break;

            case 'checkbox':
                $chk = ($val === '1' || $val === 'بله') ? ' checked' : '';
                $html .= '<div class="checkbox-single-wrap">';
                $html .= '<label class="checkbox-label" for="' . $key . '">';
                $html .= '<input type="checkbox" id="' . $key . '" name="' . $key . '" value="بله"' . $chk . '> ';
                $html .= htmlspecialchars($field['help'] ?: 'موافقم');
                $html .= '</label>';
                $html .= '</div>';
                break;

            case 'date':
                $html .= '<input type="text" id="' . $key . '" name="' . $key . '" class="form-control" value="' . $val . '" placeholder="مثال: 1403/09/20">';
                break;

            default:
                $inputType = ($field['type'] === 'email') ? 'email' : (($field['type'] === 'tel') ? 'tel' : 'text');
                $html .= '<input type="' . $inputType . '" id="' . $key . '" name="' . $key . '" class="form-control" value="' . $val . '"' . $placeholder . '>';
                break;
        }

        if (!empty($field['help']) && $field['type'] !== 'checkbox') {
            $html .= '<small class="form-help-text">' . htmlspecialchars($field['help']) . '</small>';
        }

        $html .= $errHtml;
        $html .= '</div>';
    }

    $html .= '<div class="form-actions-wrap">';
    $html .= '<button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:12px 24px; font-size:15px;">ثبت و ارسال اطلاعات</button>';
    $html .= '</div>';

    $html .= '</form>';
    return $html;
}
?>
