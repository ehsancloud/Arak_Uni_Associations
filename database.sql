-- ساخت کامل دیتابیس سامانه انجمن‌های علمی دانشگاه اراک (نسخه کامل و یکپارچه v3)
-- ابتدا دیتابیس خود را در phpMyAdmin بسازید، سپس این فایل را Import نمایید.

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `site_title` varchar(255) DEFAULT 'دانشگاه اراک',
  `festival_status` tinyint(1) DEFAULT 1,
  `about_text` text
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`, `site_title`, `festival_status`, `about_text`)
VALUES (1, 'سامانه انجمن‌های علمی دانشگاه اراک', 1, '')
ON DUPLICATE KEY UPDATE `site_title` = VALUES(`site_title`);

CREATE TABLE IF NOT EXISTS `associations` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `description` text,
  `goals` text,
  `icon` varchar(255) DEFAULT NULL,
  `icon_key` varchar(50) DEFAULT 'general',
  `cover_image` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `telegram` varchar(100) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `linkedin` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text,
  `membership_open` tinyint(1) DEFAULT 1
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `content` text,
  `image` varchar(255) NOT NULL,
  `created_at` date NOT NULL,
  KEY `idx_news_assoc` (`association_id`, `created_at`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  KEY `idx_members_assoc` (`association_id`)
) DEFAULT CHARSET=utf8mb4;

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
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_forms_assoc` (`association_id`, `type`)
) DEFAULT CHARSET=utf8mb4;

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
  KEY `idx_fields_form` (`form_id`, `sort_order`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `form_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `status` enum('new','approved','rejected') NOT NULL DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_sub_form_status` (`form_id`, `status`)
) DEFAULT CHARSET=utf8mb4;

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
  KEY `idx_events_starts` (`starts_at`, `is_published`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_gallery_assoc` (`association_id`, `sort_order`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `kind` enum('report','file') NOT NULL DEFAULT 'file',
  `file` varchar(255) NOT NULL,
  `period_label` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_docs_assoc` (`association_id`, `kind`)
) DEFAULT CHARSET=utf8mb4;

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
  KEY `idx_pub_assoc` (`association_id`, `published_at`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ticker_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL DEFAULT 0,
  `text` varchar(500) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `festival_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `full_name` varchar(255) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `register_date` datetime DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`id`, `username`, `password`) 
VALUES (1, 'admin', 'e10adc3949ba59abbe56e057f20f883e')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);
