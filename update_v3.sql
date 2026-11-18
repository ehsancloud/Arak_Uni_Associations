-- ============================================================
-- به‌روزرسانی ساختار دیتابیس سامانه انجمن‌های علمی دانشگاه اراک (نسخه ۳)
-- ============================================================

-- ۱. افزودن فیلدهای جدید به جدول انجمن‌ها
ALTER TABLE `associations`
  ADD COLUMN `icon_key` varchar(50) DEFAULT 'general' AFTER `icon`,
  ADD COLUMN `goals` text DEFAULT NULL AFTER `description`,
  ADD COLUMN `email` varchar(255) DEFAULT NULL,
  ADD COLUMN `phone` varchar(50) DEFAULT NULL,
  ADD COLUMN `telegram` varchar(100) DEFAULT NULL,
  ADD COLUMN `instagram` varchar(100) DEFAULT NULL,
  ADD COLUMN `linkedin` varchar(100) DEFAULT NULL,
  ADD COLUMN `website` varchar(255) DEFAULT NULL,
  ADD COLUMN `address` text DEFAULT NULL,
  ADD COLUMN `membership_open` tinyint(1) DEFAULT 1;

-- ۲. جدول فرم‌ها (فرم‌های عضویت، رویدادها، همکاری، سفارشی)
CREATE TABLE IF NOT EXISTS `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL DEFAULT 0,
  `type` enum('membership','event','cooperation','custom') NOT NULL DEFAULT 'custom',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `opens_at` datetime DEFAULT NULL,
  `closes_at` datetime DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4;

-- ۳. فیلدهای فرم
CREATE TABLE IF NOT EXISTS `form_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `form_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'text',
  `options` text DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `is_unique` tinyint(1) NOT NULL DEFAULT 0,
  `placeholder` varchar(255) DEFAULT NULL,
  `help` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  KEY `idx_form_id` (`form_id`)
) DEFAULT CHARSET=utf8mb4;

-- ۴. ثبت‌نام‌ها و ارسال‌های فرم
CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `form_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `status` enum('new','approved','rejected') NOT NULL DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_form_status` (`form_id`, `status`)
) DEFAULT CHARSET=utf8mb4;

-- ۵. رویدادها و همایش‌ها
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `form_id` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_assoc_starts` (`association_id`, `starts_at`)
) DEFAULT CHARSET=utf8mb4;

-- ۶. تصاویر گالری
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_assoc_gallery` (`association_id`, `sort_order`)
) DEFAULT CHARSET=utf8mb4;

-- ۷. اسناد و گزارش عملکرد
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `kind` enum('report','file') NOT NULL DEFAULT 'file',
  `file` varchar(255) NOT NULL,
  `period_label` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_assoc_docs` (`association_id`, `kind`)
) DEFAULT CHARSET=utf8mb4;

-- ۸. نشریات علمی دانشجویی
CREATE TABLE IF NOT EXISTS `publications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `issue_no` varchar(50) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `file` varchar(255) NOT NULL,
  `published_at` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_assoc_pub` (`association_id`, `published_at`)
) DEFAULT CHARSET=utf8mb4;

-- ۹. ساخت فرم جشنواره حرکت و مهاجرت داده‌های ثبت‌نامی‌های قبلی
INSERT INTO `forms` (`id`, `association_id`, `type`, `title`, `description`, `is_open`) 
VALUES (1, 0, 'event', 'ثبت‌نام کادر اجرایی جشنواره حرکت', 'فرم ثبت‌نام دانشجویان در کادر اجرایی جشنواره دانشگاهی حرکت دانشگاه اراک', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `form_fields` (`form_id`, `label`, `type`, `required`, `sort_order`) VALUES
(1, 'نام و نام خانوادگی', 'text', 1, 1),
(1, 'شماره دانشجویی', 'student_id', 1, 2),
(1, 'شماره تماس', 'tel', 1, 3);

-- انتقال داده‌های festival_registrations در صورت وجود
INSERT INTO `form_submissions` (`form_id`, `data`, `status`, `created_at`)
SELECT 1, 
       CONCAT('{"نام و نام خانوادگی":"', REPLACE(REPLACE(full_name, '\\\\', '\\\\\\\\'), '"', '\\"'), '","شماره دانشجویی":"', REPLACE(REPLACE(student_id, '\\\\', '\\\\\\\\'), '"', '\\"'), '","شماره تماس":"', REPLACE(REPLACE(phone, '\\\\', '\\\\\\\\'), '"', '\\"'), '"}'),
       'new',
       COALESCE(register_date, NOW())
FROM `festival_registrations`;
