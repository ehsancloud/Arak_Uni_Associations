-- ابتدا دیتابیس خودتان را در phpMyAdmin انتخاب کنید، سپس این فایل را Import کنید.

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `site_title` varchar(255) DEFAULT 'دانشگاه اراک',
  `font_family` varchar(100) DEFAULT 'Vazirmatn',
  `festival_status` tinyint(1) DEFAULT '1',
  `about_text` text
) DEFAULT CHARSET=utf8mb4;
INSERT INTO `settings` (`site_title`, `font_family`, `festival_status`, `about_text`) VALUES ('سامانه انجمن های علمی دانشگاه اراک', 'Vazirmatn', 1, '');

CREATE TABLE `associations` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `description` text,
  `icon` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `content` text,
  `image` varchar(255) NOT NULL,
  `created_at` date NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `festival_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `full_name` varchar(255) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `register_date` datetime DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticker_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `association_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(500) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1'
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) DEFAULT CHARSET=utf8mb4;
INSERT INTO `admins` (`username`, `password`) VALUES ('admin', 'e10adc3949ba59abbe56e057f20f883e');
