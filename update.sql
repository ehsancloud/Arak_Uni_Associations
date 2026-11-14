-- اگر دیتابیس را قبلاً ساخته‌اید، فقط همین فایل را در phpMyAdmin (روی دیتابیس خودتان) Import/Run کنید.
ALTER TABLE `news` ADD COLUMN `content` text AFTER `title`;
ALTER TABLE `settings` ADD COLUMN `about_text` text;

CREATE TABLE `ticker_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(500) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1'
) DEFAULT CHARSET=utf8mb4;
