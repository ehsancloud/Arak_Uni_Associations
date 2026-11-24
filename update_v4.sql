-- اسکریپت مهاجرت به نسخه ۴.۰: تفکیک دسترسی دبیران انجمن‌ها (RBAC)
-- اجرای این فایل ستون‌های لازم برای نقش‌ها و انتساب انجمن به کاربران را اضافه می‌کند.

ALTER TABLE `admins`
  ADD COLUMN IF NOT EXISTS `role` enum('superadmin','secretary') NOT NULL DEFAULT 'superadmin' AFTER `password`,
  ADD COLUMN IF NOT EXISTS `association_id` int(11) NULL DEFAULT NULL AFTER `role`,
  ADD COLUMN IF NOT EXISTS `full_name` varchar(150) NULL DEFAULT NULL AFTER `association_id`,
  ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `full_name`;

-- اطمینان از اینکه کاربر پیش‌فرض ادمین، نقش superadmin دارد
UPDATE `admins` SET `role` = 'superadmin', `association_id` = NULL, `full_name` = 'مدیر کل سامانه' WHERE `id` = 1;

-- ایندکس روی association_id
ALTER TABLE `admins` ADD INDEX IF NOT EXISTS `idx_admins_assoc` (`association_id`);
