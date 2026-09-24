-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.33 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for venderkids
CREATE DATABASE IF NOT EXISTS `venderkids` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `venderkids`;

-- Dumping structure for table venderkids.activity_log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_log_log_name_index` (`log_name`),
  KEY `subject` (`subject_id`,`subject_type`),
  KEY `causer` (`causer_id`,`causer_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.activity_log: ~0 rows (approximately)
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;

-- Dumping structure for table venderkids.admin_others
CREATE TABLE IF NOT EXISTS `admin_others` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.admin_others: ~3 rows (approximately)
/*!40000 ALTER TABLE `admin_others` DISABLE KEYS */;
INSERT INTO `admin_others` (`id`, `setting_name`, `setting_value`, `created_at`, `updated_at`) VALUES
	(1, 'teacher', '<p>Test Done Good</p>', '2022-06-12 17:49:57', '2022-10-06 23:44:36'),
	(2, 'student', '<p>&nbsp;By accessing venderkids, you agreed to use cookies in agreement with the venderkids\'s Privacy Policy.</p><p><br></p><p><br></p><p>Most interactive websites use cookies to let us retrieve the user’s details for each visit. Cookies are used by our website to enable the functionality of certain areas to make it easier for people visiting our website. Some of our affiliate/advertising partners may also use cookies. updateed</p>', '2022-06-12 17:49:57', '2022-09-21 00:35:27'),
	(3, 'school', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum</span><br></p>', '2022-06-12 17:49:57', '2022-09-21 00:33:28');
/*!40000 ALTER TABLE `admin_others` ENABLE KEYS */;

-- Dumping structure for table venderkids.age_groups
CREATE TABLE IF NOT EXISTS `age_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `age` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator_id` int(11) NOT NULL,
  `creator` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.age_groups: ~7 rows (approximately)
/*!40000 ALTER TABLE `age_groups` DISABLE KEYS */;
INSERT INTO `age_groups` (`id`, `age`, `creator_id`, `creator`, `created_at`, `updated_at`) VALUES
	(2, '7-9 years', 1, 'Super Admin', '2022-07-08 05:05:12', '2022-07-08 05:05:12'),
	(3, '10-12 years', 1, 'Super Admin', '2022-07-08 05:05:32', '2022-07-08 05:05:32'),
	(4, '13-15 years', 1, 'Super Admin', '2022-07-08 05:05:43', '2022-07-08 05:05:43'),
	(5, '16-18 years', 1, 'Super Admin', '2022-07-08 05:05:57', '2022-07-08 05:05:57'),
	(6, 'Level 1', 1, 'Super Admin', '2022-09-23 03:10:35', '2022-09-23 03:10:35'),
	(7, 'Level 2', 1, 'Super Admin', '2022-09-23 03:10:51', '2022-09-23 03:10:51'),
	(8, 'Level 3', 1, 'Super Admin', '2022-09-23 03:11:05', '2022-09-23 03:11:05');
/*!40000 ALTER TABLE `age_groups` ENABLE KEYS */;

-- Dumping structure for table venderkids.allocation_event
CREATE TABLE IF NOT EXISTS `allocation_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.allocation_event: ~15 rows (approximately)
/*!40000 ALTER TABLE `allocation_event` DISABLE KEYS */;
INSERT INTO `allocation_event` (`id`, `school_id`, `event_name`, `event_date`, `event_color`, `created_at`, `updated_at`) VALUES
	(2, 1, 'Movie Watching', '2022-07-27', NULL, '2022-07-03 05:30:00', '2022-07-03 05:30:00'),
	(6, 1, 'Makers Session', '2022-07-13', 'rgb(255,215,0)', '2022-07-06 05:30:00', '2022-07-06 05:30:00'),
	(8, 1, 'Case Study', '2022-07-11', 'rgb(0, 86, 179)', '2022-07-06 05:30:00', '2022-07-06 05:30:00'),
	(9, 9, 'Guest Speaker', '2022-07-14', 'rgb(0,128,0)', '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(10, 9, 'Makers Session', '2022-07-20', 'rgb(255,215,0)', '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(11, 1, 'Kid Talk', '2022-07-25', 'rgb(0,128,128)', '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(12, 26, 'Makers Session', '2022-08-30', 'rgb(255,215,0)', '2022-08-24 16:13:12', '2022-08-24 16:13:12'),
	(13, 26, 'Kid Talk', '2022-08-26', 'rgb(0,128,128)', '2022-08-24 16:13:35', '2022-08-24 16:13:35'),
	(14, 26, 'Movie Watching', '2022-08-25', 'rgb(255,0,0)', '2022-08-24 16:13:51', '2022-08-24 16:13:51'),
	(15, 28, 'Makers Session', '2022-08-18', 'rgb(255,215,0)', '2022-08-26 19:08:07', '2022-08-26 19:08:07'),
	(16, 28, 'paint', '2022-10-06', 'rgb(0, 86, 179)', '2022-09-19 21:14:08', '2022-09-19 21:14:08'),
	(20, 28, 'Test', '2022-09-20', 'rgb(167, 29, 42)', '2022-09-21 01:38:28', '2022-09-21 01:38:28'),
	(21, 28, 'Movie Watching', '2022-09-29', 'rgb(255,0,0)', '2022-09-21 01:38:40', '2022-09-21 01:38:40'),
	(22, 29, 'Guest Speaker', '2022-09-12', 'rgb(0,128,0)', '2022-09-22 00:41:04', '2022-09-22 00:41:04'),
	(23, 29, 'Makers Session', '2022-10-03', 'rgb(255,215,0)', '2022-09-22 00:41:35', '2022-09-22 00:41:35');
/*!40000 ALTER TABLE `allocation_event` ENABLE KEYS */;

-- Dumping structure for table venderkids.assignments
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `school_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.assignments: ~30 rows (approximately)
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` (`id`, `title`, `school_id`, `grade_id`, `comment`, `created_at`, `updated_at`) VALUES
	(1, 'class ten (chapter 3)', 1, 1, 'Please solved this problem as soon as possible', '2022-06-26 15:33:43', '2022-06-26 15:33:43'),
	(2, 'class ten (chapter 4)', 1, 1, 'comment', '2022-06-27 14:53:47', '2022-06-27 14:53:47'),
	(3, 'class ten (chapter 5)', 2, 2, 'kgjht', '2022-06-27 14:54:23', '2022-06-27 14:54:23'),
	(4, 'class ten (chapter 6)', 3, 3, 'fhnh', '2022-06-27 14:54:51', '2022-06-27 14:54:51'),
	(5, 'class ten (chapter 7)', 4, 4, 'uiyugj', '2022-06-27 14:55:08', '2022-06-27 14:55:08'),
	(6, 'class ten (chapter 8)', 1, 1, 'new ass', '2022-06-27 16:14:49', '2022-06-27 16:14:49'),
	(7, 'class ten (chapter 9)', 1, 3, '<p>This is first assignment.</p>', '2022-06-30 19:23:37', '2022-06-30 19:23:37'),
	(8, 'class ten (chapter 10)', 1, 1, '<p>This is our second assignment.</p><p>This is our second assignment.</p><hr><p>This is our second assignment.</p><hr><p>This is our second assignment.</p><hr><p>This is our second assignment.</p><hr><p>This is our second assignment.<br></p>', '2022-06-30 19:39:59', '2022-06-30 19:39:59'),
	(9, 'class ten (chapter 10)', 1, 1, '<p>hello description</p>', '2022-07-02 10:31:19', '2022-07-02 10:31:19'),
	(10, 'class ten (chapter 11)', 1, 1, '<p>ghgh</p>', '2022-07-02 10:33:26', '2022-07-02 10:33:26'),
	(11, 'class ten (chapter 12)', 7, 2, '<p>good good</p>', '2022-07-02 11:34:15', '2022-07-02 11:34:15'),
	(12, 'class ten (chapter 13)', 12, 3, '<p>jtujkt&nbsp; u ytu</p>', '2022-07-02 11:35:04', '2022-07-02 11:35:04'),
	(13, 'hello test6', 1, 1, '<p>yedrte</p>', '2022-07-08 00:04:47', '2022-07-08 00:04:47'),
	(14, 'hello test3212', 1, 1, 'fdgdftgd', '2022-07-08 00:06:10', '2022-07-08 00:06:10'),
	(15, 'New assignment for test', 1, 1, '<p>yhghghg</p>', '2022-07-08 04:50:57', '2022-07-08 04:50:57'),
	(16, 'hello bd', 1, 1, '<p>gerdsfvsd</p>', '2022-07-08 04:51:52', '2022-07-08 04:51:52'),
	(17, 'LBS Internation Title', 9, 7, '<p>sdfasdfasdf</p>', '2022-07-08 04:55:57', '2022-07-08 04:55:57'),
	(18, 'July 8th Assignment', 9, 7, '<p>Let\'s see which students can see this assignment. All the best</p>', '2022-07-08 19:53:42', '2022-07-08 19:53:42'),
	(19, '2nd assignment for July 8th', 9, 7, 'Akkad bakkad bamme bo', '2022-07-08 19:55:38', '2022-07-08 19:55:38'),
	(20, 'This is a Testing assignement', 1, 6, 'I want to learn how this assignment works ?', '2022-07-19 16:29:36', '2022-07-19 16:29:36'),
	(21, 'This is a Testing assignement', 9, 7, '<p>sahckwdjejvoejvdmxm&nbsp; wduwehn,c , wfkweufkiwj</p>', '2022-07-19 16:36:45', '2022-07-19 16:36:45'),
	(22, 'Entrepreneurship Infographics Activity', 28, 7, 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form', '2022-08-26 17:24:44', '2022-08-26 17:24:44'),
	(23, 'Idea generation and pitching.', 27, 7, '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</span><br></p>', '2022-08-26 22:49:29', '2022-08-26 22:49:29'),
	(24, 'The Young Entrepreneur Pitch Challenge', 27, 7, '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '2022-08-26 22:57:11', '2022-08-26 22:57:11'),
	(25, 'Entrepreneurship Infographics Activity', 27, 7, 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable.', '2022-08-26 23:05:59', '2022-08-26 23:05:59'),
	(26, 'paint', 28, 1, 'painting', '2022-09-19 23:05:39', '2022-09-19 23:05:39'),
	(27, 'paint', 28, 1, 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout', '2022-09-20 23:07:30', '2022-09-20 23:07:30'),
	(28, 'test', 28, 2, 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout', '2022-09-20 23:18:58', '2022-09-20 23:18:58'),
	(29, 'test', 28, 2, 'many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).', '2022-09-20 23:55:38', '2022-09-20 23:55:38'),
	(30, 'Test', 30, 2, 'What does this image say?', '2022-09-23 03:22:20', '2022-09-23 03:22:20');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;

-- Dumping structure for table venderkids.assignmentsfiles
CREATE TABLE IF NOT EXISTS `assignmentsfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.assignmentsfiles: ~21 rows (approximately)
/*!40000 ALTER TABLE `assignmentsfiles` DISABLE KEYS */;
INSERT INTO `assignmentsfiles` (`id`, `assignment_id`, `attachment`, `created_at`, `updated_at`) VALUES
	(1, 1, 'image/assignment/assignment_oZLUvAgI5q.png', '2022-06-26 15:33:43', '2022-06-26 15:33:43'),
	(2, 1, 'image/assignment/assignment_lrOXum5g5G.png', '2022-06-26 15:33:43', '2022-06-26 15:33:43'),
	(3, 1, 'image/assignment/assignment_fXGN5PxxyO.png', '2022-06-26 15:33:43', '2022-06-26 15:33:43'),
	(4, 1, 'image/assignment/assignment_zSmZJAZ8xI.png', '2022-06-26 15:33:43', '2022-06-26 15:33:43'),
	(5, 13, 'image/assignment/assignment_Zsz2Ki1FZ0.docx', '2022-07-08 00:04:47', '2022-07-08 00:04:47'),
	(6, 14, 'image/assignment/assignment_J1LCBnaeeo.docx', '2022-07-08 00:06:10', '2022-07-08 00:06:10'),
	(7, 15, 'image/assignment/assignment_UmYPWLKMFV.docx', '2022-07-08 04:50:57', '2022-07-08 04:50:57'),
	(8, 16, 'image/assignment/assignment_LziDob3xpm.docx', '2022-07-08 04:51:52', '2022-07-08 04:51:52'),
	(9, 17, 'image/assignment/assignment_ulUcWUiWZr.jpg', '2022-07-08 04:55:57', '2022-07-08 04:55:57'),
	(10, 18, 'image/assignment/assignment_U5mdfQvKJL.doc', '2022-07-08 19:53:42', '2022-07-08 19:53:42'),
	(11, 20, 'image/assignment/assignment_JueyyHBDJF.png', '2022-07-19 16:29:36', '2022-07-19 16:29:36'),
	(12, 21, 'image/assignment/assignment_dWpMNQhLyQ.png', '2022-07-19 16:36:45', '2022-07-19 16:36:45'),
	(13, 22, 'image/assignment/assignment_l7bJ0WDVy3.pdf', '2022-08-26 17:24:44', '2022-08-26 17:24:44'),
	(14, 23, 'image/assignment/assignment_2d8HL5cdqz.doc', '2022-08-26 22:49:29', '2022-08-26 22:49:29'),
	(15, 24, 'image/assignment/assignment_9nZZW2yyLX.doc', '2022-08-26 22:57:11', '2022-08-26 22:57:11'),
	(16, 25, 'image/assignment/assignment_CwI4vQXiFS.doc', '2022-08-26 23:05:59', '2022-08-26 23:05:59'),
	(17, 26, 'image/assignment/assignment_jFAprjwzNx.jpg', '2022-09-19 23:05:39', '2022-09-19 23:05:39'),
	(18, 27, 'image/assignment/assignment_6Nt9r4kcWX.jpg', '2022-09-20 23:07:30', '2022-09-20 23:07:30'),
	(19, 28, 'image/assignment/assignment_CmYnT7mIp1.jpg', '2022-09-20 23:18:58', '2022-09-20 23:18:58'),
	(20, 29, 'image/assignment/assignment_MZKAeHm2oO.jpg', '2022-09-20 23:55:38', '2022-09-20 23:55:38'),
	(21, 30, 'image/assignment/assignment_MufqwALtwL.jpg', '2022-09-23 03:22:20', '2022-09-23 03:22:20');
/*!40000 ALTER TABLE `assignmentsfiles` ENABLE KEYS */;

-- Dumping structure for table venderkids.assignment_comment
CREATE TABLE IF NOT EXISTS `assignment_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.assignment_comment: ~17 rows (approximately)
/*!40000 ALTER TABLE `assignment_comment` DISABLE KEYS */;
INSERT INTO `assignment_comment` (`id`, `assignment_id`, `reciever_id`, `sender_id`, `message`, `created_at`, `updated_at`) VALUES
	(43, 2, 5, 1, 'aa', '2022-07-03 10:21:47', '2022-07-03 10:21:47'),
	(44, 2, 12, 1, 'aa', '2022-07-03 10:29:57', '2022-07-03 10:29:57'),
	(45, 2, 1, 12, 'bb', '2022-07-03 10:29:57', '2022-07-03 10:29:57'),
	(46, 2, 12, 1, 'cc', '2022-07-03 10:29:57', '2022-07-03 10:29:57'),
	(47, 2, 1, 12, 'dd', '2022-07-03 10:29:57', '2022-07-03 10:29:57'),
	(48, 2, 1, 12, 'zzzzzzzzzzzz', '2022-07-03 15:20:34', '2022-07-03 15:20:34'),
	(49, 2, 1, 12, 'zzzzz', '2022-07-03 15:25:37', '2022-07-03 15:25:37'),
	(50, 2, 1, 12, 'hello voldimir rafi da', '2022-07-03 15:27:17', '2022-07-03 15:27:17'),
	(51, 2, 1, 12, 'dfdfc', '2022-07-03 15:27:44', '2022-07-03 15:27:44'),
	(52, 2, 12, 1, 'vv', '2022-07-03 19:01:53', '2022-07-03 19:01:53'),
	(53, 2, 12, 1, 'nn', '2022-07-03 19:02:15', '2022-07-03 19:02:15'),
	(54, 2, 12, 1, 'mm', '2022-07-03 19:02:51', '2022-07-03 19:02:51'),
	(55, 2, 12, 1, 'xcvxvcb', '2022-07-03 19:04:35', '2022-07-03 19:04:35'),
	(56, 2, 12, 1, 'hh', '2022-07-03 19:05:15', '2022-07-03 19:05:15'),
	(57, 2, 12, 1, 't', '2022-07-03 19:07:07', '2022-07-03 19:07:07'),
	(58, 23, 29, 13, 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.', '2022-08-26 22:51:16', '2022-08-26 22:51:16'),
	(59, 24, 29, 13, 'The Young Entrepreneur Pitch Challenge Toolkit is a free, five session, engaging curriculum that is fun for everyone! Kids connect their passion with the solution to a problem, develop a pitch for their solution, and perform the pitch either live or on video.', '2022-08-26 23:02:01', '2022-08-26 23:02:01');
/*!40000 ALTER TABLE `assignment_comment` ENABLE KEYS */;

-- Dumping structure for table venderkids.assignment_details
CREATE TABLE IF NOT EXISTS `assignment_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `read_status` tinyint(2) DEFAULT NULL,
  `comment_status` tinyint(2) DEFAULT NULL COMMENT 'complete = 1, uncomplete = 2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.assignment_details: ~8 rows (approximately)
/*!40000 ALTER TABLE `assignment_details` DISABLE KEYS */;
INSERT INTO `assignment_details` (`id`, `student_id`, `assignment_id`, `read_status`, `comment_status`, `created_at`, `updated_at`) VALUES
	(12, 102, 10, 1, 1, '2022-07-08 03:26:03', '2022-07-08 03:26:05'),
	(13, 102, 6, 1, 1, '2022-07-08 03:26:38', '2022-07-08 03:26:38'),
	(14, 117, 17, 1, 1, '2022-07-08 05:21:05', '2022-07-08 05:23:09'),
	(15, 117, 19, 1, 1, '2022-07-08 19:57:50', '2022-07-19 11:53:14'),
	(16, 117, 18, 1, 1, '2022-07-19 11:53:15', '2022-07-19 11:53:16'),
	(17, 93, 18, 1, 1, '2022-07-19 11:53:15', '2022-07-19 11:53:16'),
	(18, 5, 1, 1, 1, '2022-07-19 11:53:15', '2022-07-21 19:38:09'),
	(19, 157, 26, 1, 1, '2022-09-20 23:13:41', '2022-09-22 01:29:20');
/*!40000 ALTER TABLE `assignment_details` ENABLE KEYS */;

-- Dumping structure for table venderkids.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `order` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.categories: ~0 rows (approximately)
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

-- Dumping structure for table venderkids.class_schedule
CREATE TABLE IF NOT EXISTS `class_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `day` varchar(255) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `start_time` varchar(255) DEFAULT NULL,
  `end_time` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.class_schedule: ~22 rows (approximately)
/*!40000 ALTER TABLE `class_schedule` DISABLE KEYS */;
INSERT INTO `class_schedule` (`id`, `school_id`, `day`, `grade`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
	(28, 5, '6', NULL, '10:00', '12:00', '2022-06-30 05:30:00', '2022-06-30 05:30:00'),
	(29, 5, '1', NULL, '11:00', '12:00', '2022-06-30 05:30:00', '2022-06-30 05:30:00'),
	(30, 5, '2', NULL, '15:00', '16:00', '2022-06-30 05:30:00', '2022-06-30 05:30:00'),
	(31, 5, '3', NULL, '16:00', '17:00', '2022-06-30 05:30:00', '2022-06-30 05:30:00'),
	(98, 7, '6', NULL, '08:00', '09:00', '2022-07-06 05:30:00', '2022-07-06 05:30:00'),
	(110, 23, '1', NULL, '10:00', '11:00', '2022-07-12 14:14:45', '2022-07-12 14:14:45'),
	(111, 23, '1', NULL, '11:00', '12:00', '2022-07-12 14:14:45', '2022-07-12 14:14:45'),
	(133, 1, '5', 1, '10:00', '11:00', '2022-07-16 16:07:54', '2022-07-16 16:07:54'),
	(134, 1, '5', 1, '11:00', '12:00', '2022-07-16 16:07:54', '2022-07-16 16:07:54'),
	(135, 1, '2', 2, '11:00', '12:00', '2022-07-16 16:07:54', '2022-07-16 16:07:54'),
	(136, 1, '1', 3, '08:05', '10:05', '2022-07-16 16:07:54', '2022-07-16 16:07:54'),
	(137, 1, '4', 4, '09:00', '11:00', '2022-07-16 16:07:54', '2022-07-16 16:07:54'),
	(153, 9, '1', 7, '10:00', '11:00', '2022-07-26 13:49:16', '2022-07-26 13:49:16'),
	(154, 9, '1', 8, '11:00', '12:00', '2022-07-26 13:49:16', '2022-07-26 13:49:16'),
	(155, 9, '2', 6, '11:00', '12:00', '2022-07-26 13:49:16', '2022-07-26 13:49:16'),
	(162, 26, '1', 1, '09:00', '10:00', '2022-08-24 16:31:32', '2022-08-24 16:31:32'),
	(163, 26, '1', 2, '11:00', '12:00', '2022-08-24 16:31:32', '2022-08-24 16:31:32'),
	(164, 26, '2', 3, '12:00', '13:00', '2022-08-24 16:31:32', '2022-08-24 16:31:32'),
	(179, 29, '1', 1, '10:00', '12:00', '2022-09-22 00:46:33', '2022-09-22 00:46:33'),
	(188, 27, '1', 7, '10:00', '11:00', '2022-09-24 23:07:45', '2022-09-24 23:07:45'),
	(189, 27, '1', 4, '11:00', '12:00', '2022-09-24 23:07:45', '2022-09-24 23:07:45'),
	(190, 32, '6', 1, '03:45', '16:45', '2022-10-07 00:11:44', '2022-10-07 00:11:44');
/*!40000 ALTER TABLE `class_schedule` ENABLE KEYS */;

-- Dumping structure for table venderkids.comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `commentable_id` bigint(20) unsigned DEFAULT NULL,
  `commentable_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `moderated_by` int(10) unsigned DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.comments: ~0 rows (approximately)
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;

-- Dumping structure for table venderkids.contents
CREATE TABLE IF NOT EXISTS `contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `level_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `video_status` int(11) DEFAULT NULL,
  `video` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worksheet` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `learning_object` text COLLATE utf8mb4_unicode_ci,
  `outcome_session` text COLLATE utf8mb4_unicode_ci,
  `question_access_knowledge` text COLLATE utf8mb4_unicode_ci,
  `introduce_topic_student` text COLLATE utf8mb4_unicode_ci,
  `related_activity_one` text COLLATE utf8mb4_unicode_ci,
  `related_activity_two` text COLLATE utf8mb4_unicode_ci,
  `vocabulary` text COLLATE utf8mb4_unicode_ci,
  `tips_of_parents` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.contents: ~5 rows (approximately)
/*!40000 ALTER TABLE `contents` DISABLE KEYS */;
INSERT INTO `contents` (`id`, `title`, `description`, `level_id`, `stream_id`, `trainer_id`, `video_status`, `video`, `worksheet`, `learning_object`, `outcome_session`, `question_access_knowledge`, `introduce_topic_student`, `related_activity_one`, `related_activity_two`, `vocabulary`, `tips_of_parents`, `created_at`, `updated_at`) VALUES
	(5, 'Test', NULL, 2, 2, NULL, NULL, 'istockphoto-1257442559-640_adpp_is.mp4', 'confusion add content.docx', '<p><font color="#000000" style="background-color: rgb(255, 255, 0);">This is one</font></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is two</span><br></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is three</span><br></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is four</span><br></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is five</span><br></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is six</span><br></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is seven</span><br></p>', '<p><span style="color: rgb(0, 0, 0); background-color: rgb(255, 255, 0);">This is eight</span><br></p>', '2022-07-18 18:23:03', '2022-07-18 18:23:03'),
	(7, 'Content uploaded for testing', NULL, 3, 2, NULL, NULL, 'sample-5s.mp4', 'Budget Worksheet.doc', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">&nbsp;If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text.&nbsp;</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form,</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet.&nbsp;</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">&nbsp;It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form,</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">&nbsp;The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC</span><br></p>', '2022-08-26 17:16:12', '2022-08-26 17:16:12'),
	(8, 'Cognitive training', NULL, 3, 4, NULL, NULL, 'sample-20s.mp4', 'Budget Worksheet.doc', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '2022-08-26 18:42:24', '2022-08-26 18:42:24'),
	(9, 'text', NULL, 7, 3, NULL, NULL, 'sample-20s.mp4', 'sample-20s.mp4', '<p>text</p>', '<p>text<br></p>', '<p>text<br></p>', '<p>text<br></p>', '<p>text<br></p>', '<p>text<br></p>', '<p>text<br></p>', '<p>text<br></p>', '2022-09-23 19:29:54', '2022-09-23 19:29:54'),
	(10, 'Carol Good for health', NULL, 3, 3, NULL, NULL, 'Pexels Videos 1580507.mp4', '5_6154728434846140122 (1).docx', 'added', '<span style="font-weight: 700;">Outcome of session</span>', 'Quesldlfsd', 'introler', 'related', 'activity', 'vocab', 'parent', '2022-10-05 23:14:16', '2022-10-07 00:31:23');
/*!40000 ALTER TABLE `contents` ENABLE KEYS */;

-- Dumping structure for table venderkids.email_info
CREATE TABLE IF NOT EXISTS `email_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_description` text COLLATE utf8mb4_unicode_ci,
  `group` int(11) DEFAULT NULL COMMENT 'Admin=1,School=2,Trainer=3,Student=4\r\n',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.email_info: ~183 rows (approximately)
/*!40000 ALTER TABLE `email_info` DISABLE KEYS */;
INSERT INTO `email_info` (`id`, `name`, `mail_address`, `mail_description`, `group`, `created_at`, `updated_at`) VALUES
	(1, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Trainer Name: Shukriti Ranjan Das<br>Your Username: shukriti@sahajjo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-06-24 02:36:02', '2022-06-24 02:36:02'),
	(2, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 3, '2022-06-24 02:45:42', '2022-06-24 02:45:42'),
	(3, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: nazmul@sahajjo.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-06-24 02:46:07', '2022-06-24 02:46:07'),
	(4, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 3, '2022-06-24 02:46:11', '2022-06-24 02:46:11'),
	(5, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Trainer Name: Shukriti Ranjan Das<br>Your Username: shukriti@sahajjo.com<br>Your Password: 123456<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/login\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-06-24 02:48:50', '2022-06-24 02:48:50'),
	(6, 'rafi', 'ajaxrafi@sahajjo.com', 'Trainer Name: rafi<br>Your Username: ajaxrafi@sahajjo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'http://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-06-24 02:53:21', '2022-06-24 02:53:21'),
	(7, 'National ideal', 'nazmul@sahajjo.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-06-24 02:57:41', '2022-06-24 02:57:41'),
	(8, 'National ideal', 'nazmul@sahajjo.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 2, '2022-06-24 02:57:57', '2022-06-24 02:57:57'),
	(9, 'Mamun Hossain Student', 'pyqtdsxbuwjucmutex@bvhrk.com', 'Student Name: Mamun Hossain Student<br>Student Username: pyqtdsxbuwjucmutex@bvhrk.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 03:03:33', '2022-06-24 03:03:33'),
	(10, 'Rahim Mia Student', 'wxtmniqdwhzjbcpndx@kvhrs.com', 'Student Name: Rahim Mia Student<br>Student Username: wxtmniqdwhzjbcpndx@kvhrs.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 03:03:33', '2022-06-24 03:03:33'),
	(11, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-24 03:07:11', '2022-06-24 03:07:11'),
	(12, 'Mamun Hossain Student', 'pyqtdsxbuwjucmutex@bvhrk.com', 'Student Name: Mamun Hossain Student<br>Student Username: pyqtdsxbuwjucmutex@bvhrk.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 03:41:33', '2022-06-24 03:41:33'),
	(13, 'Rahim Mia Student', 'wxtmniqdwhzjbcpndx@kvhrs.com', 'Student Name: Rahim Mia Student<br>Student Username: wxtmniqdwhzjbcpndx@kvhrs.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 03:41:34', '2022-06-24 03:41:34'),
	(14, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-24 03:41:34', '2022-06-24 03:41:34'),
	(15, 'Rafi Student', 'kugpvasdhzkmobawtn@bvhrk.com', 'Student Name: Rafi Student<br>Student Username: kugpvasdhzkmobawtn@bvhrk.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 04:05:09', '2022-06-24 04:05:09'),
	(16, 'Shukriti Student', 'pjxxumsuulkwhcijzb@nvhrw.com', 'Student Name: Shukriti Student<br>Student Username: pjxxumsuulkwhcijzb@nvhrw.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 04:05:10', '2022-06-24 04:05:10'),
	(17, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-24 04:05:10', '2022-06-24 04:05:10'),
	(18, 'National ideal', 'nazmul@sahajjo.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-06-24 04:17:34', '2022-06-24 04:17:34'),
	(19, 'National ideal', 'nazmul@sahajjo.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 2, '2022-06-24 04:17:41', '2022-06-24 04:17:41'),
	(20, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 3, '2022-06-24 04:17:50', '2022-06-24 04:17:50'),
	(21, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 3, '2022-06-24 04:17:55', '2022-06-24 04:17:55'),
	(22, 'Rafi Student', 'student@sahajjo.com', 'Student Name: Rafi Student<br>Student Username: student@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://schoolmanagement.com/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-23 19:29:15', '2022-06-23 19:29:15'),
	(23, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-23 19:29:15', '2022-06-23 19:29:15'),
	(24, 'Rafi Student', 'student2@sahajjo.com', 'Student Name: Rafi Student<br>Student Username: student2@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://schoolmanagement.com/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-23 19:44:54', '2022-06-23 19:44:54'),
	(25, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-23 19:44:54', '2022-06-23 19:44:54'),
	(26, 'Rafi Student', 'lhkxbuttkmovcgbgjz@kvhrw.com', 'Student Name: Rafi Student<br>Student Username: lhkxbuttkmovcgbgjz@kvhrw.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 05:56:00', '2022-06-24 05:56:00'),
	(27, 'Shukriti Student', 'bnuzbzknxvtdegwsnd@kvhrr.com', 'Student Name: Shukriti Student<br>Student Username: bnuzbzknxvtdegwsnd@kvhrr.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 05:56:01', '2022-06-24 05:56:01'),
	(28, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-24 05:56:01', '2022-06-24 05:56:01'),
	(29, 'Rafi Student', 'afiqur@sahajjo.com', 'Student Name: Rafi Student<br>Student Username: afiqur@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-24 06:02:59', '2022-06-24 06:02:59'),
	(30, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-24 06:02:59', '2022-06-24 06:02:59'),
	(31, 'hjhjhjhjhj', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: xeheg71348@runqx.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-06-24 21:59:02', '2022-06-24 21:59:02'),
	(32, 'hjhjhjhjhj', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi hjhjhjhjhj</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-24 22:54:31', '2022-06-24 22:54:31'),
	(33, 'hjhjhjhjhj', 'xeheg71348@runqx.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-06-24 22:56:05', '2022-06-24 22:56:05'),
	(34, 'hjhjhjhjhj', 'xeheg71348@runqx.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 2, '2022-06-24 22:56:16', '2022-06-24 22:56:16'),
	(35, 'Trainer 05', 'xaniv50587@runqx.com', 'Trainer Name: Trainer 05<br>Your Username: xaniv50587@runqx.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-06-24 22:57:21', '2022-06-24 22:57:21'),
	(36, 'Trainer 05', 'xaniv50587@runqx.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 3, '2022-06-24 23:00:00', '2022-06-24 23:00:00'),
	(37, 'Trainer 05', 'xaniv50587@runqx.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 3, '2022-06-24 23:00:23', '2022-06-24 23:00:23'),
	(38, 'zcsdvfasc', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: cowono2551@serosin.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-06-25 01:51:37', '2022-06-25 01:51:37'),
	(39, 'mkhugjbu', 'coxeye9925@exoacre.com', 'Trainer Name: mkhugjbu<br>Your Username: coxeye9925@exoacre.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-06-25 01:53:06', '2022-06-25 01:53:06'),
	(40, 'zcsdvfasc', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi zcsdvfasc</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-25 01:59:45', '2022-06-25 01:59:45'),
	(41, 'Rafi Student', 'womjixsonffntshwqv@kvhrs.com', 'Student Name: Rafi Student<br>Student Username: womjixsonffntshwqv@kvhrs.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-25 21:33:52', '2022-06-25 21:33:52'),
	(42, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-25 21:33:52', '2022-06-25 21:33:52'),
	(43, 'Rafi Student', 'gxoejykfcxxbapkrjl@bvhrs.com', 'Student Name: Rafi Student<br>Student Username: gxoejykfcxxbapkrjl@bvhrs.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-25 21:44:14', '2022-06-25 21:44:14'),
	(44, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-25 21:44:14', '2022-06-25 21:44:14'),
	(45, 'asdfasdf', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: asdfsdf@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-06-26 05:33:22', '2022-06-26 05:33:22'),
	(46, 'asdfasdf', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi asdfasdf</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-26 05:36:14', '2022-06-26 05:36:14'),
	(47, 'asdfasdf', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi asdfasdf</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-26 05:37:33', '2022-06-26 05:37:33'),
	(48, 'Rafi Student', 'afiqur5@sahajjo.com', 'Student Name: Rafi Student<br>Student Username: afiqur5@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-06-26 05:55:12', '2022-06-26 05:55:12'),
	(49, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-06-26 05:55:12', '2022-06-26 05:55:12'),
	(50, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-06 06:20:00', '2022-07-06 06:20:00'),
	(51, 'bonosriAkbor', 'akbor@gmail.com', 'Trainer Name: bonosriAkbor<br>Your Username: akbor@gmail.com<br>Your Password: 123456<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/login\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-06 20:34:48', '2022-07-06 20:34:48'),
	(52, 'Test Trainer', 'afiqur+6@sahajjo.com', 'Trainer Name: Test Trainer<br>Your Username: afiqur+6@sahajjo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-06 20:59:31', '2022-07-06 20:59:31'),
	(53, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-06 21:59:14', '2022-07-06 21:59:14'),
	(54, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-06 21:59:42', '2022-07-06 21:59:42'),
	(55, 'Trainer 5', 'afiqur+11@sahajjo.com', 'Trainer Name: Trainer 5<br>Your Username: afiqur+11@sahajjo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-06 22:21:54', '2022-07-06 22:21:54'),
	(56, 'Test school', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: afiqur+12@sahajjo.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-06 22:26:34', '2022-07-06 22:26:34'),
	(57, 'Shukriti Ranjan Das', 'shukriti@sahajjo.com', 'Trainer Name: Shukriti Ranjan Das<br>Your Username: shukriti@sahajjo.com<br>Your Password: 123456<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/login\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-06 22:57:56', '2022-07-06 22:57:56'),
	(58, 'National ideal', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideal</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 00:48:19', '2022-07-07 00:48:19'),
	(59, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 00:52:20', '2022-07-07 00:52:20'),
	(60, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: roweg90454@hekarro.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-07 03:45:40', '2022-07-07 03:45:40'),
	(61, 'Anshika singh', 'wadij43188@lankew.com', 'Student Name: Anshika singh<br>Student Username: wadij43188@lankew.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-07 03:49:39', '2022-07-07 03:49:39'),
	(62, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 03:54:46', '2022-07-07 03:54:46'),
	(63, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:13:16', '2022-07-07 04:13:16'),
	(64, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:13:39', '2022-07-07 04:13:39'),
	(65, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:14:37', '2022-07-07 04:14:37'),
	(66, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:16:19', '2022-07-07 04:16:19'),
	(67, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:16:56', '2022-07-07 04:16:56'),
	(68, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:18:20', '2022-07-07 04:18:20'),
	(69, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:19:38', '2022-07-07 04:19:38'),
	(70, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:31:12', '2022-07-07 04:31:12'),
	(71, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:46:51', '2022-07-07 04:46:51'),
	(72, 'Rafi Student', 'afiqur88888@sahajjo.com', 'Student Name: Rafi Student<br>Student Username: afiqur88888@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-07 04:47:48', '2022-07-07 04:47:48'),
	(73, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:47:48', '2022-07-07 04:47:48'),
	(74, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:49:54', '2022-07-07 04:49:54'),
	(75, 'National academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 04:52:07', '2022-07-07 04:52:07'),
	(76, 'Central school', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: markevil07@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-07 20:23:24', '2022-07-07 20:23:24'),
	(77, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: officialbentic@yahoo.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-07 20:43:45', '2022-07-07 20:43:45'),
	(78, 'Grace murphy', 'gracemurphy321@yahoo.com', 'Trainer Name: Grace murphy<br>Your Username: gracemurphy321@yahoo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-07 20:48:02', '2022-07-07 20:48:02'),
	(79, 'Alfie soloman', 'alfiesolomon391@yahoo.com', 'Student Name: Alfie soloman<br>Student Username: alfiesolomon391@yahoo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-07 20:58:41', '2022-07-07 20:58:41'),
	(80, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 20:58:42', '2022-07-07 20:58:42'),
	(81, 'National school and college', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: nazmulsahajjo@yahoo.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-07 21:21:36', '2022-07-07 21:21:36'),
	(82, 'National school and colleges', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: nazmul+1@sahajjo.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-07 21:22:49', '2022-07-07 21:22:49'),
	(83, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-07 21:32:59', '2022-07-07 21:32:59'),
	(84, 'National ideals', 'nazmul@sahajjo.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-07-08 18:15:32', '2022-07-08 18:15:32'),
	(85, 'David mosley', 'mosleyofficial1@gmail.com', 'Student Name: David mosley<br>Student Username: mosleyofficial1@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-08 19:24:45', '2022-07-08 19:24:45'),
	(86, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-08 19:24:46', '2022-07-08 19:24:46'),
	(87, 'David mosley', 'poposi6934@meidir.com', 'Student Name: David mosley<br>Student Username: poposi6934@meidir.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://aptest.therssoftware.com/kidsinterpreneurship/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-08 19:31:17', '2022-07-08 19:31:17'),
	(88, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-08 19:31:18', '2022-07-08 19:31:18'),
	(89, 'venderkids', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: swati@venderkids.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-08 20:00:48', '2022-07-08 20:00:48'),
	(90, 'New National school and college', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: amtsrivastava007@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-08 21:10:06', '2022-07-08 21:10:06'),
	(91, 'National school and college new', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: amtsrivastava007@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-08 21:14:46', '2022-07-08 21:14:46'),
	(92, 'New National school and college', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: amtsrivastava007@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-08 21:21:17', '2022-07-08 21:21:17'),
	(93, 'New National ideals bangladesh', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: amtsrivastava007@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-08 21:34:53', '2022-07-08 21:34:53'),
	(94, 'Another', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: robelsust+52@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-09 13:44:47', '2022-07-09 13:44:47'),
	(95, 'G D Goenka', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: jacobsmithtemp@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-11 15:48:01', '2022-07-11 15:48:01'),
	(96, 'G D Goenka', 'jacobsmithtemp@gmail.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-07-11 15:53:42', '2022-07-11 15:53:42'),
	(97, 'G D Goenka', 'jacobsmithtemp@gmail.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 2, '2022-07-11 15:54:00', '2022-07-11 15:54:00'),
	(98, 'G D Goenka', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi G D Goenka</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-11 18:35:00', '2022-07-11 18:35:00'),
	(99, 'William Bentic', 'officialbentic@gmail.com', 'Student Name: William Bentic<br>Student Username: officialbentic@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-11 18:41:27', '2022-07-11 18:41:27'),
	(100, 'G D Goenka', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi G D Goenka</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-11 18:41:30', '2022-07-11 18:41:30'),
	(101, 'Test School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: robelsust+52@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-12 05:22:03', '2022-07-12 05:22:03'),
	(102, 'Test School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Test School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-12 05:26:25', '2022-07-12 05:26:25'),
	(103, 'Test School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Test School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-12 05:26:46', '2022-07-12 05:26:46'),
	(104, 'Rafi Student', 'robelsust+55@gmail.com', 'Student Name: Rafi Student<br>Student Username: robelsust+55@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-12 05:27:22', '2022-07-12 05:27:22'),
	(105, 'Test School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Test School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-12 05:27:26', '2022-07-12 05:27:26'),
	(106, 'Thomas ', 'sheltommy673@gmail.com', 'Student Name: Thomas <br>Student Username: sheltommy673@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-12 14:11:13', '2022-07-12 14:11:13'),
	(107, 'G D Goenka', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi G D Goenka</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-12 14:11:17', '2022-07-12 14:11:17'),
	(108, 'G D Goenka', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi G D Goenka</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-12 14:14:50', '2022-07-12 14:14:50'),
	(109, 'Grace murphy', 'gracemurphy321@yahoo.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 3, '2022-07-12 14:27:52', '2022-07-12 14:27:52'),
	(110, 'Grace murphy', 'gracemurphy321@yahoo.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 3, '2022-07-12 14:28:17', '2022-07-12 14:28:17'),
	(111, 'Nihar Trainer test', 'robelsust+59@gmail.com', 'Trainer Name: Nihar Trainer test<br>Your Username: robelsust+59@gmail.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-13 02:40:43', '2022-07-13 02:40:43'),
	(112, 'Nihar Trainer test', 'robelsust+59@gmail.com', 'Trainer Name: Nihar Trainer test<br>Your Username: robelsust+59@gmail.com<br>Your Password: 123456<br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/login\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-13 02:41:58', '2022-07-13 02:41:58'),
	(113, 'Rs it', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: nazmul+1@sahajjo.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-07-14 13:52:50', '2022-07-14 13:52:50'),
	(114, 'Shukriti SWE', 'shukriti+1@sahajjo.com', 'Trainer Name: Shukriti SWE<br>Your Username: shukriti+1@sahajjo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-07-14 13:57:10', '2022-07-14 13:57:10'),
	(115, 'Anthony Gomes', 'shukriti+2@sahajjo.com', 'Student Name: Anthony Gomes<br>Student Username: shukriti+2@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-14 14:10:22', '2022-07-14 14:10:22'),
	(116, 'Rampura', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Rampura</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-14 14:10:25', '2022-07-14 14:10:25'),
	(117, 'Limia jones', 'shukriti+4@sahajjo.com', 'Student Name: Limia jones<br>Student Username: shukriti+4@sahajjo.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'https://q-study.com/kids/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-07-14 14:36:45', '2022-07-14 14:36:45'),
	(118, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-14 14:36:49', '2022-07-14 14:36:49'),
	(119, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-14 17:47:58', '2022-07-14 17:47:58'),
	(120, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-14 17:54:39', '2022-07-14 17:54:39'),
	(121, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-14 17:54:51', '2022-07-14 17:54:51'),
	(122, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-14 19:58:14', '2022-07-14 19:58:14'),
	(123, 'National ideals', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi National ideals</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-16 16:07:56', '2022-07-16 16:07:56'),
	(124, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-25 16:16:21', '2022-07-25 16:16:21'),
	(125, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-25 16:21:23', '2022-07-25 16:21:23'),
	(126, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-25 19:14:56', '2022-07-25 19:14:56'),
	(127, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-25 19:27:53', '2022-07-25 19:27:53'),
	(128, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-26 13:47:30', '2022-07-26 13:47:30'),
	(129, 'LBS International', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi LBS International</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-07-26 13:49:20', '2022-07-26 13:49:20'),
	(130, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: sodoya4418@vpsrec.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-08-24 15:45:13', '2022-08-24 15:45:13'),
	(131, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-24 15:50:41', '2022-08-24 15:50:41'),
	(132, 'Mr. Charlie', 'rifatil220@rxcay.com', 'Trainer Name: Mr. Charlie<br>Your Username: rifatil220@rxcay.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-08-24 15:59:01', '2022-08-24 15:59:01'),
	(133, 'Mr. Shelby', 'favij21792@xitudy.com', 'Trainer Name: Mr. Shelby<br>Your Username: favij21792@xitudy.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-08-24 16:04:37', '2022-08-24 16:04:37'),
	(134, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-24 16:17:18', '2022-08-24 16:17:18'),
	(135, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-24 16:21:07', '2022-08-24 16:21:07'),
	(136, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-24 16:31:33', '2022-08-24 16:31:33'),
	(137, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: ciyeyaf424@xitudy.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-08-25 20:13:51', '2022-08-25 20:13:51'),
	(138, 'Michael Grey', 'tosefa8282@rxcay.com', 'Student Name: Michael Grey<br>Student Username: tosefa8282@rxcay.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-08-25 22:31:44', '2022-08-25 22:31:44'),
	(139, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-25 22:31:46', '2022-08-25 22:31:46'),
	(140, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-25 22:43:45', '2022-08-25 22:43:45'),
	(141, 'Mr. Charlie', 'fopaye3288@lurenwu.com', 'Trainer Name: Mr. Charlie<br>Your Username: fopaye3288@lurenwu.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-08-25 23:45:28', '2022-08-25 23:45:28'),
	(142, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-25 23:53:35', '2022-08-25 23:53:35'),
	(143, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-25 23:53:57', '2022-08-25 23:53:57'),
	(144, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-26 00:03:16', '2022-08-26 00:03:16'),
	(145, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-26 00:03:49', '2022-08-26 00:03:49'),
	(146, 'Rampus Academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: kelon96729@otodir.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-08-26 00:09:32', '2022-08-26 00:09:32'),
	(147, 'Rampus Academy', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Rampus Academy</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-26 00:17:58', '2022-08-26 00:17:58'),
	(148, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-26 19:09:02', '2022-08-26 19:09:02'),
	(149, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-26 19:11:02', '2022-08-26 19:11:02'),
	(150, 'William Bentic', 'officialbentic@gmail.com', 'Student Name: William Bentic<br>Student Username: officialbentic@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-08-26 19:49:19', '2022-08-26 19:49:19'),
	(151, 'David Mosley', 'mosleyofficial1@gmail.com', 'Student Name: David Mosley<br>Student Username: mosleyofficial1@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-08-26 19:49:20', '2022-08-26 19:49:20'),
	(152, 'Karl Marx', 'markskarl1705@gmail.com', 'Student Name: Karl Marx<br>Student Username: markskarl1705@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-08-26 19:56:02', '2022-08-26 19:56:02'),
	(153, 'Evil Mark', 'markevil07@gmail.com', 'Student Name: Evil Mark<br>Student Username: markevil07@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-08-26 19:56:02', '2022-08-26 19:56:02'),
	(154, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-08-26 19:56:03', '2022-08-26 19:56:03'),
	(155, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-15 18:25:23', '2022-09-15 18:25:23'),
	(156, 'New Standard Public School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: palak98787@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-09-16 00:51:10', '2022-09-16 00:51:10'),
	(157, 'New Standard Public School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi New Standard Public School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-16 00:57:50', '2022-09-16 00:57:50'),
	(158, 'Otis', 'ootis548@gmail.com', 'Trainer Name: Otis<br>Your Username: ootis548@gmail.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-09-16 02:01:17', '2022-09-16 02:01:17'),
	(159, 'Rakheash', 'a67081161@gmail.com', 'Student Name: Rakheash<br>Student Username: a67081161@gmail.com<br>Student Password: student <br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/school/dashboard\'>click here</a> <br>Thanks <br> venderkids', 4, '2022-09-16 18:12:09', '2022-09-16 18:12:09'),
	(160, 'New Standard Public School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi New Standard Public School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-16 18:12:12', '2022-09-16 18:12:12'),
	(161, 'Otis', 'ootis548@gmail.com', 'Trainer Name: Otis<br>Your Username: ootis548@gmail.com<br>Your Password: 123456<br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/login\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-09-19 21:03:49', '2022-09-19 21:03:49'),
	(162, 'New Standard Public School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi New Standard Public School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-21 17:13:51', '2022-09-21 17:13:51'),
	(163, 'New Standard Public School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi New Standard Public School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-21 17:15:41', '2022-09-21 17:15:41'),
	(164, 'Little Learning House', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: ellae9018@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-09-21 17:45:25', '2022-09-21 17:45:25'),
	(165, 'Little Learning House', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Little Learning House</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-22 00:35:45', '2022-09-22 00:35:45'),
	(166, 'Little Learning House', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Little Learning House</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-22 00:46:35', '2022-09-22 00:46:35'),
	(167, 'New Standard Public School', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi New Standard Public School</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-22 16:27:17', '2022-09-22 16:27:17'),
	(168, 'venderkids', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: swati@venderkids.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-09-22 22:17:55', '2022-09-22 22:17:55'),
	(169, 'Grace montessory', 'ciyeyaf424@xitudy.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-09-23 01:57:11', '2022-09-23 01:57:11'),
	(170, 'Swati Gauba Kochar', 'swati@hoppingo.com', 'Trainer Name: Swati Gauba Kochar<br>Your Username: swati@hoppingo.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'https://venderkids.neuronsit.sg/public/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-09-23 02:42:10', '2022-09-23 02:42:10'),
	(171, 'Grace montessory', 'ciyeyaf424@xitudy.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 2, '2022-09-23 23:40:30', '2022-09-23 23:40:30'),
	(172, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-24 18:51:03', '2022-09-24 18:51:03'),
	(173, 'Otis', 'ootis548@gmail.com', 'Trainer Name: Otis<br>Your Username: ootis548@gmail.com<br>Your Password: 123456<br>Please login your dashboard by clicking this link <a href=\'http://venderkids.neuronsit.sg/public/login\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-09-24 21:55:38', '2022-09-24 21:55:38'),
	(174, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-24 21:59:43', '2022-09-24 21:59:43'),
	(175, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-24 23:06:28', '2022-09-24 23:06:28'),
	(176, 'Grace montessory', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Grace montessory</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-09-24 23:07:48', '2022-09-24 23:07:48'),
	(177, 'Little Learning House', 'ellae9018@gmail.com', 'Please contact venderkids administrator <br>Thanks <br> venderkids', 2, '2022-09-27 19:11:08', '2022-09-27 19:11:08'),
	(178, 'Little Learning House', 'ellae9018@gmail.com', 'Welcome To venderkids <br>Thanks <br> venderkids', 2, '2022-09-27 19:11:20', '2022-09-27 19:11:20'),
	(179, 'Naomi Flowers', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: bhaviktrambadiya@gmail.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-10-04 22:59:03', '2022-10-04 22:59:03'),
	(180, 'Gemma Sellers', 'hohodurehy@mailinator.com', 'Trainer Name: Gemma Sellers<br>Your Username: hohodurehy@mailinator.com<br>Your Password: trainer<br>Please login your dashboard by clicking this link <a href=\'http://kidsback.test/admin/dashboard\'>click here</a> <br>Thanks <br> venderkids', 3, '2022-10-05 22:28:42', '2022-10-05 22:28:42'),
	(181, 'Simon Reyes', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: tyqifalat@mailinator.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-10-07 00:00:06', '2022-10-07 00:00:06'),
	(182, 'Amena Conway', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: xywan@mailinator.com<br>\r\nPassword: school\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 2, '2022-10-07 00:03:13', '2022-10-07 00:03:13'),
	(183, 'Amena Conway', NULL, '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi Amena Conway</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by Super admin. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\nvenderkids \r\n</p>                                          ', 2, '2022-10-07 00:11:49', '2022-10-07 00:11:49');
/*!40000 ALTER TABLE `email_info` ENABLE KEYS */;

-- Dumping structure for table venderkids.email_notifications
CREATE TABLE IF NOT EXISTS `email_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sub` text,
  `mail_body` text,
  `mail_tags` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.email_notifications: ~12 rows (approximately)
/*!40000 ALTER TABLE `email_notifications` DISABLE KEYS */;
INSERT INTO `email_notifications` (`id`, `mail_sub`, `mail_body`, `mail_tags`) VALUES
	(1, 'New School create ', '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear School Administrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: {email}<br>\r\nPassword: {pass}\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 'action_by,app_name,app_logo,receiver_name,invitation_url'),
	(2, 'Password reset link provided by {app_name}', '                               \r\n          <p>Hi {receiver_name}</p>\r\n\r\n          <p>Your request for reset password has been approved from {app_name}. Press the button below to reset the password.</p>\r\n          <p><a href="{link}" class="btn btn-primary btn-sm">Reset Password</a></p>\r\n          <p>We are highly expecting you as soon as possible. Hope you\'ll join us.</p>\r\n          <p>Thanks for being with us.</p>\r\n\r\n          <p>Thanks &amp; Regards,</p>\r\n\r\n          <p>{app_name}</p>                                   ', 'app_name,app_logo,receiver_name,reset_password_url'),
	(3, 'You have been invited to join {app_name} by {action_by}', '                                    <p><img src="http://property_final2.com/uploads/empty_image.jpg" alt="app_logo" height="50px" style="border:1px solid black;"><br></p>                                       <p class="text_add"><br></p>      \r\n          <p>Hello {receiver_name}</p>\r\n\r\n          <p>Your Login credentials are given,\r\n          Email : {email}\r\n          Password : {password}\r\n          To set up your account, please use these credentials and go to the following link.</p>\r\n\r\n          <p><button class="btn btn-primary btn-sm">Go to your account</button></p>\r\n          <p>You can change your password from your account password settings.</p>\r\n          <p>Hope you will find useful!</p>\r\n          <p>Thanks for being with us.</p>\r\n          <p>Regards,</p>  \r\n          <p>Thanks &amp; Regards,</p>\r\n\r\n          <p>{app_name}</p>                                    ', 'action_by,app_name,app_logo,receiver_name,invitation_url,email,password'),
	(4, 'Invoice {invoice_number} for due {date}', '                                            <p class="text_add"><img src="#" alt="app_logo" height="50px" style="border:1px solid black;">{invoice_number}<img src="#" alt="logo" height="60px" style="border:1px solid black;"></p>      \r\n          <p>Hello {receiver_name}</p>\r\n\r\n          <p>I hope you’re well!\r\n          Please see attached invoice {invoice_number}.\r\n          Don’t hesitate to contact us if you have any questions.</p>\r\n\r\n          <p>Thanks for being with us.</p>\r\n\r\n          <p>Regards,</p>  \r\n\r\n          <p>{app_name}</p>                           ', 'app_name,app_logo,receiver_name,invoice_number,date'),
	(5, 'Payment reminder for invoice {invoice_number}', '                                      <p class="text_add">{app_name}{date}{receiver_name}<img src="#" alt="app_logo" height="50px" style="border:1px solid black;">{date}<img src="#" alt="logo" height="60px" style="border:1px solid black;"></p>      \r\n          <p>Hello {receiver_name}</p>\r\n\r\n          <p>We hope that you’re enjoying our service\r\n          We did want to quickly mention that we haven’t received payment from you yet.</p>\r\n          <p>If you have any questions don’t hesitate to reply to this email.</p>\r\n          <p>Thanks for being with us.</p>\r\n\r\n          <p>Regards,</p>  \r\n\r\n          <p>{app_name}</p>                        ', 'app_name,app_logo,receiver_name,invoice_number,date'),
	(6, 'Registration Confirmed', '<p><img src="http://property_final.com/uploads/avater.png" alt="app_logo" height="50px" style="border:1px solid black;"></p><p>Hi {receiver_name}</p>\r\n\r\n          <p>Welcome to our {app_name}.</p>\r\n\r\n          <p>Thanks &amp; Regards,</p>\r\n\r\n          <p>{app_name}</p><p></p> ', 'name,action_by,app_name,app_logo,receiver_name,resource_url'),
	(7, 'A new roles has been created in {app_name}', '                    <p>{name}{name}<img src="#" alt="logo" height="60px" style="border:1px solid black;"></p>      \r\n            <p>Hi {receiver_name}</p>\r\n\r\n            <p>It\'s a piece of good news that a new roles named {name} has been created in our application by {action_by}. Please have a look at that.</p>\r\n\r\n            <p><button class="btn btn-primary btn-sm">View Roles</button></p>\r\n            <p>Thanks for being with us.</p>\r\n\r\n            <p>Regards,</p>  \r\n\r\n            <p>{app_name}</p>                   ', 'name,action_by,app_name,app_logo,receiver_name,resource_url'),
	(8, 'A roles has been updated in {app_name}', '                    <p>{name}{name}<img src="#" alt="logo" height="60px" style="border:1px solid black;"></p>      \r\n            <p>Hi {receiver_name}</p>\r\n\r\n            <p>It\'s a piece of good news that a new roles named {name} has been created in our application by {action_by}. Please have a look at that.</p>\r\n\r\n            <p><button class="btn btn-primary btn-sm">View Roles</button></p>\r\n            <p>Thanks for being with us.</p>         ', 'name,action_by,app_name,app_logo,receiver_name,resource_url'),
	(9, 'A roles has been deleted in {app_name}', '          <p class="text_add">{name}<img src="#" alt="logo" height="60px" style="border:1px solid black;"></p>      \r\n            <p>Hi {receiver_name}</p>\r\n\r\n            <p>We are going to inform you that a roles named has been deleted from our application by {action_by}.</p>\r\n\r\n            <p>Thanks for being with us.</p>\r\n\r\n            <p>Regards,</p>  \r\n\r\n            <p>{app_name}</p>         ', 'name,action_by,app_name,app_logo,receiver_name'),
	(10, 'New School Edited ', '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi {receiver_name}</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited school to our application by {action_by}. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\n{app_name} \r\n</p>                                          ', 'action_by,app_name,app_logo,receiver_name,invitation_url'),
	(11, 'New Trainer Edited ', '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Hi {receiver_name}</p>\r\n<p>Hope this mail finds you well and healthy. We are informing you that you\'ve been edited Trainerto our application by {action_by}. It\'ll be a great opportunity to work with you.<br>\r\nThanks &amp; Regards,\r\n{app_name} \r\n</p>                                          ', 'action_by,app_name,app_logo,receiver_name,invitation_url'),
	(12, 'New Trainer create ', '                                                                      <p><img src="http://schoolmanagement.com/image/school.png" alt="app_logo" height="50px" style=""></p><p>Dear Trainer\r\nAdministrator,</p>\r\n<p>Your profile has been created with venderkids. This helps you give access to entrepreneurship education to your students.</p>\r\n <p>To get started, please <a href="{{route(\'backend.dashboard\')}}">click here</a> and complete registration process using below login info</p>\r\n<p>\r\nEmail: {email}<br>\r\nPassword: {pass}\r\n</p>\r\n<p>\r\nThanks & Regards,<br>\r\nTeam venderkids\r\n</p>\r\n                                          ', 'action_by,app_name,app_logo,receiver_name,invitation_url');
/*!40000 ALTER TABLE `email_notifications` ENABLE KEYS */;

-- Dumping structure for table venderkids.events
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) DEFAULT NULL,
  `event_image` varchar(255) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_last_date` date DEFAULT NULL,
  `event_address` varchar(255) DEFAULT NULL,
  `landing_page` varchar(255) DEFAULT NULL,
  `event_fee` varchar(255) DEFAULT NULL,
  `currency` varchar(191) NOT NULL,
  `event_poster` text,
  `event_description` text,
  `created_at` varchar(255) DEFAULT NULL,
  `updated_at` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.events: ~2 rows (approximately)
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` (`id`, `event_name`, `event_image`, `event_date`, `event_last_date`, `event_address`, `landing_page`, `event_fee`, `currency`, `event_poster`, `event_description`, `created_at`, `updated_at`) VALUES
	(3, 'Entrepreneurial meet', 'image/event/eventimage_0rheiAn09F.jpg', '2022-09-30', '2022-09-15', 'School Campus New Delhi', 'https://www.google.com/', '550', 'inr', 'image/event/poster/poster_8bR3ETvVLH.jpg', '<p><strong style="margin: 0px; padding: 0px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Lorem Ipsum</strong><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span><br></p>', '2022-08-26 16:11:09', '2022-10-06 23:39:57'),
	(5, 'Painting', 'image/event/eventimage_9rtf2wNLfl.jpg', '2022-09-17', '2022-09-21', 'Test', 'https://in.pinterest.com/', '100', 'sgd', 'image/event/poster/poster_T4fEiBKv1j.jpg', '<p><span style="color: rgb(102, 102, 102); font-family: Raleway, sans-serif; background-color: rgb(232, 232, 232);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry’s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span></p><p><span style="color: rgb(102, 102, 102); font-family: Raleway, sans-serif; background-color: rgb(232, 232, 232);"><br></span><br></p>', '2022-09-19 14:25:38', '2022-10-06 23:40:17');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;

-- Dumping structure for table venderkids.event_registration
CREATE TABLE IF NOT EXISTS `event_registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `person` int(11) NOT NULL,
  `date` varchar(100) NOT NULL,
  `position` tinyint(2) NOT NULL,
  `booking_agree` tinyint(2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.event_registration: ~4 rows (approximately)
/*!40000 ALTER TABLE `event_registration` DISABLE KEYS */;
INSERT INTO `event_registration` (`id`, `event_id`, `student_id`, `name`, `email`, `person`, `date`, `position`, `booking_agree`, `created_at`, `updated_at`) VALUES
	(1, 1, 90, 'aaa', 'dd@gmail.com', 1, '2022-06-28', 2, 1, '2022-06-28 14:44:31', '2022-06-28 14:44:31'),
	(5, 3, 90, 'aa', 'fb_user@gmail.com', 2, '2022-06-28', 2, 1, '2022-06-28 16:44:42', '2022-06-28 16:44:42'),
	(6, 1, 93, 'Rafi Test Event', 'rafitestevent@gmail.com', 4, '2022-06-28', 1, 1, '2022-06-28 19:08:16', '2022-06-28 19:08:16'),
	(7, 1, 117, 'Alfie soloman', 'alfiesolomon391@yahoo.com', 1, '2022-07-07', 3, 1, '2022-07-07 21:59:12', '2022-07-07 21:59:12');
/*!40000 ALTER TABLE `event_registration` ENABLE KEYS */;

-- Dumping structure for table venderkids.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.failed_jobs: ~0 rows (approximately)
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;

-- Dumping structure for table venderkids.grades
CREATE TABLE IF NOT EXISTS `grades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `grade` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.grades: ~8 rows (approximately)
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` (`id`, `grade`, `created_at`, `updated_at`) VALUES
	(1, 'Level 1', NULL, NULL),
	(2, 'Level 2', NULL, NULL),
	(3, 'Level 3', NULL, NULL),
	(4, 'Level 4', NULL, NULL),
	(5, 'Level 5', NULL, NULL),
	(6, 'Level 6', NULL, NULL),
	(7, 'Level 7', NULL, NULL),
	(8, 'Level 8', NULL, NULL);
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;

-- Dumping structure for table venderkids.media
CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.media: ~0 rows (approximately)
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
/*!40000 ALTER TABLE `media` ENABLE KEYS */;

-- Dumping structure for table venderkids.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.migrations: ~25 rows (approximately)
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(2, '2014_10_12_100000_create_password_resets_table', 1),
	(3, '2018_03_11_062135_create_posts_table', 1),
	(4, '2018_03_12_062135_create_categories_table', 1),
	(5, '2019_08_19_000000_create_failed_jobs_table', 1),
	(6, '2020_02_19_152418_create_permission_tables', 1),
	(7, '2020_02_19_173115_create_activity_log_table', 1),
	(8, '2020_02_19_173641_create_settings_table', 1),
	(9, '2020_02_19_173700_create_userprofiles_table', 1),
	(10, '2020_02_19_173711_create_notifications_table', 1),
	(11, '2020_02_22_115918_create_user_providers_table', 1),
	(12, '2020_05_01_163442_create_tags_table', 1),
	(13, '2020_05_01_163833_create_polymorphic_taggables_table', 1),
	(14, '2020_05_04_151517_create_comments_table', 1),
	(15, '2020_10_27_155557_create_media_table', 1),
	(17, '2022_06_05_132819_create_student_grades_table', 2),
	(19, '2022_06_05_162030_create_grades_table', 4),
	(22, '2022_06_05_132628_create_schools_table', 6),
	(27, '2014_10_12_000000_create_users_table', 8),
	(31, '2022_06_05_152719_create_trainers_table', 9),
	(33, '2022_06_08_193404_create_streams_table', 10),
	(34, '2022_06_08_194038_create_contents_table', 10),
	(35, '2022_06_08_193252_create_age_groups_table', 11),
	(36, '2022_06_11_195059_create_admin_others_table', 12),
	(38, '2022_10_05_234450_change_column_agegroup_id_to_level_id_to_contents', 13),
	(39, '2022_10_06_231744_add_column_currency_to_events', 14);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;

-- Dumping structure for table venderkids.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.model_has_permissions: ~228 rows (approximately)
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 0),
	(40, 'App\\Models\\User', 0),
	(1, 'App\\Models\\User', 13),
	(40, 'App\\Models\\User', 13),
	(1, 'App\\Models\\User', 14),
	(40, 'App\\Models\\User', 14),
	(1, 'App\\Models\\User', 15),
	(40, 'App\\Models\\User', 15),
	(1, 'App\\Models\\User', 16),
	(40, 'App\\Models\\User', 16),
	(1, 'App\\Models\\User', 17),
	(40, 'App\\Models\\User', 17),
	(1, 'App\\Models\\User', 18),
	(40, 'App\\Models\\User', 18),
	(1, 'App\\Models\\User', 19),
	(40, 'App\\Models\\User', 19),
	(1, 'App\\Models\\User', 20),
	(40, 'App\\Models\\User', 20),
	(1, 'App\\Models\\User', 21),
	(40, 'App\\Models\\User', 21),
	(1, 'App\\Models\\User', 22),
	(40, 'App\\Models\\User', 22),
	(1, 'App\\Models\\User', 23),
	(40, 'App\\Models\\User', 23),
	(1, 'App\\Models\\User', 24),
	(40, 'App\\Models\\User', 24),
	(1, 'App\\Models\\User', 25),
	(40, 'App\\Models\\User', 25),
	(1, 'App\\Models\\User', 26),
	(40, 'App\\Models\\User', 26),
	(1, 'App\\Models\\User', 27),
	(40, 'App\\Models\\User', 27),
	(1, 'App\\Models\\User', 28),
	(40, 'App\\Models\\User', 28),
	(1, 'App\\Models\\User', 29),
	(40, 'App\\Models\\User', 29),
	(1, 'App\\Models\\User', 30),
	(40, 'App\\Models\\User', 30),
	(1, 'App\\Models\\User', 31),
	(40, 'App\\Models\\User', 31),
	(1, 'App\\Models\\User', 32),
	(40, 'App\\Models\\User', 32),
	(1, 'App\\Models\\User', 33),
	(40, 'App\\Models\\User', 33),
	(1, 'App\\Models\\User', 38),
	(40, 'App\\Models\\User', 38),
	(1, 'App\\Models\\User', 62),
	(40, 'App\\Models\\User', 62),
	(1, 'App\\Models\\User', 66),
	(40, 'App\\Models\\User', 66),
	(1, 'App\\Models\\User', 67),
	(40, 'App\\Models\\User', 67),
	(1, 'App\\Models\\User', 68),
	(40, 'App\\Models\\User', 68),
	(1, 'App\\Models\\User', 69),
	(40, 'App\\Models\\User', 69),
	(1, 'App\\Models\\User', 70),
	(40, 'App\\Models\\User', 70),
	(1, 'App\\Models\\User', 71),
	(40, 'App\\Models\\User', 71),
	(1, 'App\\Models\\User', 72),
	(40, 'App\\Models\\User', 72),
	(1, 'App\\Models\\User', 73),
	(40, 'App\\Models\\User', 73),
	(1, 'App\\Models\\User', 74),
	(40, 'App\\Models\\User', 74),
	(1, 'App\\Models\\User', 75),
	(40, 'App\\Models\\User', 75),
	(1, 'App\\Models\\User', 76),
	(40, 'App\\Models\\User', 76),
	(1, 'App\\Models\\User', 77),
	(40, 'App\\Models\\User', 77),
	(1, 'App\\Models\\User', 78),
	(40, 'App\\Models\\User', 78),
	(1, 'App\\Models\\User', 79),
	(42, 'App\\Models\\User', 79),
	(1, 'App\\Models\\User', 80),
	(40, 'App\\Models\\User', 80),
	(1, 'App\\Models\\User', 87),
	(42, 'App\\Models\\User', 87),
	(1, 'App\\Models\\User', 88),
	(42, 'App\\Models\\User', 88),
	(1, 'App\\Models\\User', 90),
	(42, 'App\\Models\\User', 90),
	(1, 'App\\Models\\User', 91),
	(42, 'App\\Models\\User', 91),
	(1, 'App\\Models\\User', 92),
	(42, 'App\\Models\\User', 92),
	(1, 'App\\Models\\User', 93),
	(42, 'App\\Models\\User', 93),
	(1, 'App\\Models\\User', 94),
	(40, 'App\\Models\\User', 94),
	(1, 'App\\Models\\User', 95),
	(40, 'App\\Models\\User', 95),
	(1, 'App\\Models\\User', 96),
	(40, 'App\\Models\\User', 96),
	(1, 'App\\Models\\User', 97),
	(40, 'App\\Models\\User', 97),
	(1, 'App\\Models\\User', 98),
	(42, 'App\\Models\\User', 98),
	(1, 'App\\Models\\User', 99),
	(42, 'App\\Models\\User', 99),
	(1, 'App\\Models\\User', 100),
	(40, 'App\\Models\\User', 100),
	(1, 'App\\Models\\User', 102),
	(42, 'App\\Models\\User', 102),
	(1, 'App\\Models\\User', 103),
	(40, 'App\\Models\\User', 103),
	(1, 'App\\Models\\User', 104),
	(42, 'App\\Models\\User', 104),
	(1, 'App\\Models\\User', 105),
	(42, 'App\\Models\\User', 105),
	(1, 'App\\Models\\User', 106),
	(40, 'App\\Models\\User', 106),
	(1, 'App\\Models\\User', 107),
	(40, 'App\\Models\\User', 107),
	(1, 'App\\Models\\User', 108),
	(40, 'App\\Models\\User', 108),
	(1, 'App\\Models\\User', 109),
	(40, 'App\\Models\\User', 109),
	(1, 'App\\Models\\User', 110),
	(40, 'App\\Models\\User', 110),
	(1, 'App\\Models\\User', 111),
	(40, 'App\\Models\\User', 111),
	(1, 'App\\Models\\User', 112),
	(42, 'App\\Models\\User', 112),
	(1, 'App\\Models\\User', 113),
	(42, 'App\\Models\\User', 113),
	(1, 'App\\Models\\User', 114),
	(40, 'App\\Models\\User', 114),
	(1, 'App\\Models\\User', 115),
	(40, 'App\\Models\\User', 115),
	(1, 'App\\Models\\User', 116),
	(40, 'App\\Models\\User', 116),
	(1, 'App\\Models\\User', 117),
	(42, 'App\\Models\\User', 117),
	(1, 'App\\Models\\User', 118),
	(40, 'App\\Models\\User', 118),
	(1, 'App\\Models\\User', 119),
	(40, 'App\\Models\\User', 119),
	(1, 'App\\Models\\User', 120),
	(42, 'App\\Models\\User', 120),
	(1, 'App\\Models\\User', 121),
	(42, 'App\\Models\\User', 121),
	(1, 'App\\Models\\User', 122),
	(40, 'App\\Models\\User', 122),
	(1, 'App\\Models\\User', 123),
	(40, 'App\\Models\\User', 123),
	(1, 'App\\Models\\User', 124),
	(40, 'App\\Models\\User', 124),
	(1, 'App\\Models\\User', 125),
	(40, 'App\\Models\\User', 125),
	(1, 'App\\Models\\User', 126),
	(40, 'App\\Models\\User', 126),
	(1, 'App\\Models\\User', 127),
	(40, 'App\\Models\\User', 127),
	(1, 'App\\Models\\User', 128),
	(40, 'App\\Models\\User', 128),
	(1, 'App\\Models\\User', 129),
	(40, 'App\\Models\\User', 129),
	(1, 'App\\Models\\User', 130),
	(40, 'App\\Models\\User', 130),
	(1, 'App\\Models\\User', 131),
	(40, 'App\\Models\\User', 131),
	(1, 'App\\Models\\User', 132),
	(40, 'App\\Models\\User', 132),
	(1, 'App\\Models\\User', 133),
	(40, 'App\\Models\\User', 133),
	(1, 'App\\Models\\User', 134),
	(40, 'App\\Models\\User', 134),
	(1, 'App\\Models\\User', 135),
	(42, 'App\\Models\\User', 135),
	(1, 'App\\Models\\User', 136),
	(40, 'App\\Models\\User', 136),
	(1, 'App\\Models\\User', 137),
	(42, 'App\\Models\\User', 137),
	(1, 'App\\Models\\User', 138),
	(42, 'App\\Models\\User', 138),
	(1, 'App\\Models\\User', 139),
	(40, 'App\\Models\\User', 139),
	(1, 'App\\Models\\User', 140),
	(40, 'App\\Models\\User', 140),
	(1, 'App\\Models\\User', 141),
	(40, 'App\\Models\\User', 141),
	(1, 'App\\Models\\User', 142),
	(42, 'App\\Models\\User', 142),
	(1, 'App\\Models\\User', 143),
	(42, 'App\\Models\\User', 143),
	(1, 'App\\Models\\User', 144),
	(40, 'App\\Models\\User', 144),
	(1, 'App\\Models\\User', 145),
	(40, 'App\\Models\\User', 145),
	(1, 'App\\Models\\User', 146),
	(40, 'App\\Models\\User', 146),
	(1, 'App\\Models\\User', 147),
	(40, 'App\\Models\\User', 147),
	(1, 'App\\Models\\User', 148),
	(42, 'App\\Models\\User', 148),
	(1, 'App\\Models\\User', 149),
	(40, 'App\\Models\\User', 149),
	(1, 'App\\Models\\User', 150),
	(40, 'App\\Models\\User', 150),
	(1, 'App\\Models\\User', 151),
	(42, 'App\\Models\\User', 151),
	(1, 'App\\Models\\User', 152),
	(42, 'App\\Models\\User', 152),
	(1, 'App\\Models\\User', 153),
	(42, 'App\\Models\\User', 153),
	(1, 'App\\Models\\User', 154),
	(42, 'App\\Models\\User', 154),
	(1, 'App\\Models\\User', 155),
	(40, 'App\\Models\\User', 155),
	(1, 'App\\Models\\User', 156),
	(40, 'App\\Models\\User', 156),
	(1, 'App\\Models\\User', 157),
	(42, 'App\\Models\\User', 157),
	(1, 'App\\Models\\User', 158),
	(40, 'App\\Models\\User', 158),
	(1, 'App\\Models\\User', 159),
	(40, 'App\\Models\\User', 159),
	(1, 'App\\Models\\User', 160),
	(40, 'App\\Models\\User', 160),
	(1, 'App\\Models\\User', 162),
	(40, 'App\\Models\\User', 162),
	(1, 'App\\Models\\User', 163),
	(40, 'App\\Models\\User', 163),
	(1, 'App\\Models\\User', 164),
	(40, 'App\\Models\\User', 164);
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;

-- Dumping structure for table venderkids.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.model_has_roles: ~123 rows (approximately)
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(6, 'App\\Models\\User', 0),
	(1, 'App\\Models\\User', 1),
	(2, 'App\\Models\\User', 2),
	(3, 'App\\Models\\User', 3),
	(4, 'App\\Models\\User', 4),
	(5, 'App\\Models\\User', 5),
	(6, 'App\\Models\\User', 6),
	(6, 'App\\Models\\User', 7),
	(6, 'App\\Models\\User', 8),
	(6, 'App\\Models\\User', 9),
	(6, 'App\\Models\\User', 13),
	(6, 'App\\Models\\User', 14),
	(6, 'App\\Models\\User', 15),
	(6, 'App\\Models\\User', 16),
	(6, 'App\\Models\\User', 17),
	(6, 'App\\Models\\User', 18),
	(6, 'App\\Models\\User', 19),
	(6, 'App\\Models\\User', 20),
	(7, 'App\\Models\\User', 21),
	(7, 'App\\Models\\User', 22),
	(7, 'App\\Models\\User', 23),
	(7, 'App\\Models\\User', 24),
	(6, 'App\\Models\\User', 25),
	(7, 'App\\Models\\User', 26),
	(7, 'App\\Models\\User', 27),
	(7, 'App\\Models\\User', 28),
	(7, 'App\\Models\\User', 29),
	(7, 'App\\Models\\User', 30),
	(7, 'App\\Models\\User', 31),
	(6, 'App\\Models\\User', 32),
	(6, 'App\\Models\\User', 33),
	(6, 'App\\Models\\User', 38),
	(6, 'App\\Models\\User', 62),
	(7, 'App\\Models\\User', 66),
	(6, 'App\\Models\\User', 67),
	(7, 'App\\Models\\User', 68),
	(7, 'App\\Models\\User', 69),
	(7, 'App\\Models\\User', 70),
	(7, 'App\\Models\\User', 71),
	(6, 'App\\Models\\User', 72),
	(6, 'App\\Models\\User', 73),
	(6, 'App\\Models\\User', 74),
	(6, 'App\\Models\\User', 75),
	(6, 'App\\Models\\User', 76),
	(7, 'App\\Models\\User', 77),
	(6, 'App\\Models\\User', 78),
	(8, 'App\\Models\\User', 79),
	(6, 'App\\Models\\User', 80),
	(8, 'App\\Models\\User', 87),
	(8, 'App\\Models\\User', 88),
	(8, 'App\\Models\\User', 90),
	(8, 'App\\Models\\User', 91),
	(8, 'App\\Models\\User', 92),
	(8, 'App\\Models\\User', 93),
	(7, 'App\\Models\\User', 94),
	(6, 'App\\Models\\User', 95),
	(7, 'App\\Models\\User', 96),
	(6, 'App\\Models\\User', 97),
	(8, 'App\\Models\\User', 98),
	(8, 'App\\Models\\User', 99),
	(7, 'App\\Models\\User', 100),
	(8, 'App\\Models\\User', 102),
	(7, 'App\\Models\\User', 103),
	(8, 'App\\Models\\User', 104),
	(8, 'App\\Models\\User', 105),
	(6, 'App\\Models\\User', 106),
	(6, 'App\\Models\\User', 107),
	(6, 'App\\Models\\User', 108),
	(6, 'App\\Models\\User', 109),
	(7, 'App\\Models\\User', 110),
	(7, 'App\\Models\\User', 111),
	(8, 'App\\Models\\User', 112),
	(8, 'App\\Models\\User', 113),
	(7, 'App\\Models\\User', 114),
	(7, 'App\\Models\\User', 115),
	(6, 'App\\Models\\User', 116),
	(8, 'App\\Models\\User', 117),
	(7, 'App\\Models\\User', 118),
	(7, 'App\\Models\\User', 119),
	(8, 'App\\Models\\User', 120),
	(8, 'App\\Models\\User', 121),
	(7, 'App\\Models\\User', 122),
	(7, 'App\\Models\\User', 123),
	(7, 'App\\Models\\User', 124),
	(7, 'App\\Models\\User', 125),
	(7, 'App\\Models\\User', 126),
	(7, 'App\\Models\\User', 127),
	(7, 'App\\Models\\User', 128),
	(7, 'App\\Models\\User', 129),
	(7, 'App\\Models\\User', 130),
	(7, 'App\\Models\\User', 131),
	(7, 'App\\Models\\User', 132),
	(7, 'App\\Models\\User', 133),
	(7, 'App\\Models\\User', 134),
	(8, 'App\\Models\\User', 135),
	(7, 'App\\Models\\User', 136),
	(8, 'App\\Models\\User', 137),
	(8, 'App\\Models\\User', 138),
	(6, 'App\\Models\\User', 139),
	(7, 'App\\Models\\User', 140),
	(6, 'App\\Models\\User', 141),
	(8, 'App\\Models\\User', 142),
	(8, 'App\\Models\\User', 143),
	(7, 'App\\Models\\User', 144),
	(6, 'App\\Models\\User', 145),
	(6, 'App\\Models\\User', 146),
	(7, 'App\\Models\\User', 147),
	(8, 'App\\Models\\User', 148),
	(6, 'App\\Models\\User', 149),
	(7, 'App\\Models\\User', 150),
	(8, 'App\\Models\\User', 151),
	(8, 'App\\Models\\User', 152),
	(8, 'App\\Models\\User', 153),
	(8, 'App\\Models\\User', 154),
	(7, 'App\\Models\\User', 155),
	(6, 'App\\Models\\User', 156),
	(8, 'App\\Models\\User', 157),
	(7, 'App\\Models\\User', 158),
	(7, 'App\\Models\\User', 159),
	(6, 'App\\Models\\User', 160),
	(6, 'App\\Models\\User', 162),
	(7, 'App\\Models\\User', 163),
	(7, 'App\\Models\\User', 164);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;

-- Dumping structure for table venderkids.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.notifications: ~0 rows (approximately)
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;

-- Dumping structure for table venderkids.notification_info
CREATE TABLE IF NOT EXISTS `notification_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `receiver_status` int(11) DEFAULT NULL COMMENT '1=>''School'',2=>''Trainer'',\r\n3=>''Student''',
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `creator_id` int(11) DEFAULT NULL COMMENT 'Admin=1,School=2,Trainer=3,\r\nStudent=4',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.notification_info: ~66 rows (approximately)
/*!40000 ALTER TABLE `notification_info` DISABLE KEYS */;
INSERT INTO `notification_info` (`id`, `school_id`, `grade_id`, `receiver_id`, `receiver_status`, `title`, `description`, `creator_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 5, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 2, '2022-07-02 19:07:22', '2022-07-02 19:07:22'),
	(2, 1, 1, 7, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 2, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(3, 1, 1, 8, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 2, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(4, 1, 1, 9, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 2, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(5, 1, 1, 10, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 2, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(6, 1, 1, 11, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 3, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(7, 1, 1, 13, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 3, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(8, 1, 1, 14, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 3, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(9, 1, 1, 15, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 3, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(10, 1, 1, 16, 3, 'Trainer test', 'Hello student how are you? Please submitted all project within 11th July. Your trainer Jon Doe', 3, '2022-07-02 19:07:23', '2022-07-02 19:07:23'),
	(15, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Trainer 05.Class Schedule at09:00 to 11:00', 1, '2022-07-03 14:47:37', '2022-07-03 14:47:37'),
	(16, 0, 0, 3, 2, 'New Trainer Allocate', 'You have assisgn this National ideal.Your Class Schedule at09:00 to 11:00', 1, '2022-07-03 14:47:37', '2022-07-03 14:47:37'),
	(17, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Shukriti Ranjan Das.Class Schedule at08:05 to 10:05', 1, '2022-07-07 04:56:52', '2022-07-07 04:56:52'),
	(18, 0, 0, 1, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at08:05 to 10:05', 1, '2022-07-07 04:56:52', '2022-07-07 04:56:52'),
	(19, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Shukriti Ranjan Das.Class Schedule at08:05 to 10:05', 1, '2022-07-07 04:59:38', '2022-07-07 04:59:38'),
	(20, 0, 0, 1, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at08:05 to 10:05', 1, '2022-07-07 04:59:38', '2022-07-07 04:59:38'),
	(21, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Shukriti Ranjan Das.Class Schedule at08:05 to 10:05', 1, '2022-07-07 05:02:13', '2022-07-07 05:02:13'),
	(22, 0, 0, 1, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at08:05 to 10:05', 1, '2022-07-07 05:02:13', '2022-07-07 05:02:13'),
	(23, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Shukriti Ranjan Das.Class Schedule at08:05 to 10:05', 1, '2022-07-07 05:23:34', '2022-07-07 05:23:34'),
	(24, 0, 0, 1, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at08:05 to 10:05', 1, '2022-07-07 05:23:34', '2022-07-07 05:23:34'),
	(25, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-07-07 21:26:33', '2022-07-07 21:26:33'),
	(27, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-07-07 21:33:34', '2022-07-07 21:33:34'),
	(29, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign bonosriAkbor.Class Schedule at08:05 to 10:05', 1, '2022-07-07 22:26:53', '2022-07-07 22:26:53'),
	(30, 0, 0, 5, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at08:05 to 10:05', 1, '2022-07-07 22:26:53', '2022-07-07 22:26:53'),
	(31, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign mkhugjbu.Class Schedule at09:00 to 11:00', 1, '2022-07-08 02:49:04', '2022-07-08 02:49:04'),
	(32, 0, 0, 5, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at09:00 to 11:00', 1, '2022-07-08 02:49:04', '2022-07-08 02:49:04'),
	(33, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at11:00 to 12:00', 1, '2022-07-08 04:18:17', '2022-07-08 04:18:17'),
	(35, 0, 0, 23, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-07-12 14:15:39', '2022-07-12 14:15:39'),
	(37, 0, 0, 23, 1, 'New Trainer Allocate', 'New trainer assign bonosriAkbor.Class Schedule at11:00 to 12:00', 1, '2022-07-12 14:15:47', '2022-07-12 14:15:47'),
	(38, 0, 0, 5, 2, 'New Trainer Allocate', 'You have assisgn this G D Goenka.Your Class Schedule at11:00 to 12:00', 1, '2022-07-12 14:15:47', '2022-07-12 14:15:47'),
	(39, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Shukriti Ranjan Das.Class Schedule at08:05 to 10:05', 1, '2022-07-14 18:09:46', '2022-07-14 18:09:46'),
	(41, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign Shukriti Ranjan Das.Class Schedule at11:00 to 12:00', 1, '2022-07-14 18:10:31', '2022-07-14 18:10:31'),
	(43, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign bonosriAkbor.Class Schedule at09:00 to 11:00', 1, '2022-07-14 18:10:57', '2022-07-14 18:10:57'),
	(44, 0, 0, 5, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at09:00 to 11:00', 1, '2022-07-14 18:10:57', '2022-07-14 18:10:57'),
	(45, 0, 0, 1, 1, 'New Trainer Allocate', 'New trainer assign bonosriAkbor.Class Schedule at10:00 to 11:00', 1, '2022-07-14 18:12:32', '2022-07-14 18:12:32'),
	(46, 0, 0, 5, 2, 'New Trainer Allocate', 'You have assisgn this National ideals.Your Class Schedule at10:00 to 11:00', 1, '2022-07-14 18:12:32', '2022-07-14 18:12:32'),
	(49, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-07-19 16:34:59', '2022-07-19 16:34:59'),
	(51, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at11:00 to 12:00', 1, '2022-07-19 16:35:07', '2022-07-19 16:35:07'),
	(53, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-07-25 16:17:38', '2022-07-25 16:17:38'),
	(55, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at11:00 to 12:00', 1, '2022-07-25 16:17:45', '2022-07-25 16:17:45'),
	(57, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at11:00 to 12:00', 1, '2022-07-25 19:10:25', '2022-07-25 19:10:25'),
	(58, 0, 0, 8, 2, 'New Trainer Allocate', 'You have assisgn this LBS International.Your Class Schedule at11:00 to 12:00', 1, '2022-07-25 19:10:25', '2022-07-25 19:10:25'),
	(59, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-07-25 19:15:28', '2022-07-25 19:15:28'),
	(60, 0, 0, 8, 2, 'New Trainer Allocate', 'You have assisgn this LBS International.Your Class Schedule at10:00 to 11:00', 1, '2022-07-25 19:15:28', '2022-07-25 19:15:28'),
	(61, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at11:00 to 12:00', 1, '2022-07-25 19:18:36', '2022-07-25 19:18:36'),
	(62, 0, 0, 8, 2, 'New Trainer Allocate', 'You have assisgn this LBS International.Your Class Schedule at11:00 to 12:00', 1, '2022-07-25 19:18:36', '2022-07-25 19:18:36'),
	(63, 0, 0, 9, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at11:00 to 12:00', 1, '2022-07-26 13:49:43', '2022-07-26 13:49:43'),
	(64, 0, 0, 8, 2, 'New Trainer Allocate', 'You have assisgn this LBS International.Your Class Schedule at11:00 to 12:00', 1, '2022-07-26 13:49:43', '2022-07-26 13:49:43'),
	(65, 0, 0, 26, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at09:00 to 10:00', 1, '2022-08-24 16:08:01', '2022-08-24 16:08:01'),
	(66, 0, 0, 8, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at09:00 to 10:00', 1, '2022-08-24 16:08:01', '2022-08-24 16:08:01'),
	(67, 0, 0, 26, 1, 'New Trainer Allocate', 'New trainer assign Mr. Shelby.Class Schedule at12:00 to 13:00', 1, '2022-08-24 16:24:37', '2022-08-24 16:24:37'),
	(68, 0, 0, 12, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at12:00 to 13:00', 1, '2022-08-24 16:24:37', '2022-08-24 16:24:37'),
	(69, 0, 0, 26, 1, 'New Trainer Allocate', 'New trainer assign Mr. Shelby.Class Schedule at11:00 to 12:00', 1, '2022-08-24 16:31:58', '2022-08-24 16:31:58'),
	(70, 0, 0, 12, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at11:00 to 12:00', 1, '2022-08-24 16:31:58', '2022-08-24 16:31:58'),
	(71, 0, 0, 27, 1, 'New Trainer Allocate', 'New trainer assign Grace murphy.Class Schedule at10:00 to 11:00', 1, '2022-08-25 23:55:58', '2022-08-25 23:55:58'),
	(72, 0, 0, 8, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at10:00 to 11:00', 1, '2022-08-25 23:55:58', '2022-08-25 23:55:58'),
	(73, 0, 0, 27, 1, 'New Trainer Allocate', 'New trainer assign Mr. Charlie.Class Schedule at11:00 to 12:00', 1, '2022-08-26 19:11:25', '2022-08-26 19:11:25'),
	(74, 0, 0, 13, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at11:00 to 12:00', 1, '2022-08-26 19:11:25', '2022-08-26 19:11:25'),
	(75, 0, 0, 29, 1, 'New Trainer Allocate', 'New trainer assign Otis.Class Schedule at10:00 to 12:00', 1, '2022-09-22 00:36:44', '2022-09-22 00:36:44'),
	(76, 0, 0, 14, 2, 'New Trainer Allocate', 'You have assisgn this Little Learning House.Your Class Schedule at10:00 to 12:00', 1, '2022-09-22 00:36:44', '2022-09-22 00:36:44'),
	(77, 0, 0, 27, 1, 'New Trainer Allocate', 'New trainer assign Otis.Class Schedule at03:00 to 05:00', 1, '2022-09-24 22:00:30', '2022-09-24 22:00:30'),
	(78, 0, 0, 14, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at03:00 to 05:00', 1, '2022-09-24 22:00:30', '2022-09-24 22:00:30'),
	(79, 0, 0, 29, 1, 'New Trainer Allocate', 'New trainer assign Mr. Charlie.Class Schedule at10:00 to 12:00', 1, '2022-09-24 22:11:41', '2022-09-24 22:11:41'),
	(80, 0, 0, 13, 2, 'New Trainer Allocate', 'You have assisgn this Little Learning House.Your Class Schedule at10:00 to 12:00', 1, '2022-09-24 22:11:41', '2022-09-24 22:11:41'),
	(81, 0, 0, 27, 1, 'New Trainer Allocate', 'New trainer assign Mr. Charlie.Class Schedule at11:00 to 12:00', 1, '2022-09-24 23:08:26', '2022-09-24 23:08:26'),
	(82, 0, 0, 13, 2, 'New Trainer Allocate', 'You have assisgn this Grace montessory.Your Class Schedule at11:00 to 12:00', 1, '2022-09-24 23:08:26', '2022-09-24 23:08:26');
/*!40000 ALTER TABLE `notification_info` ENABLE KEYS */;

-- Dumping structure for table venderkids.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.password_resets: ~0 rows (approximately)
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;

-- Dumping structure for table venderkids.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.permissions: ~42 rows (approximately)
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'view_backend', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(2, 'edit_settings', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(3, 'view_logs', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(4, 'view_users', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(5, 'add_users', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(6, 'edit_users', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(7, 'delete_users', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(8, 'restore_users', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(9, 'block_users', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(10, 'view_roles', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(11, 'add_roles', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(12, 'edit_roles', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(13, 'delete_roles', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(14, 'restore_roles', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(15, 'view_backups', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(16, 'add_backups', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(17, 'create_backups', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(18, 'download_backups', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(19, 'delete_backups', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(20, 'view_posts', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(21, 'add_posts', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(22, 'edit_posts', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(23, 'delete_posts', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(24, 'restore_posts', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(25, 'view_categories', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(26, 'add_categories', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(27, 'edit_categories', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(28, 'delete_categories', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(29, 'restore_categories', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(30, 'view_tags', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(31, 'add_tags', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(32, 'edit_tags', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(33, 'delete_tags', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(34, 'restore_tags', 'web', '2022-06-04 13:01:04', '2022-06-04 13:01:04'),
	(35, 'view_comments', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(36, 'add_comments', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(37, 'edit_comments', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(38, 'delete_comments', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(39, 'restore_comments', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(40, 'trainer_edit', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(41, 'school_edit', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05'),
	(42, 'student_edit', 'web', '2022-06-04 13:01:05', '2022-06-04 13:01:05');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;

-- Dumping structure for table venderkids.posts
CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro` text COLLATE utf8mb4_unicode_ci,
  `content` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `category_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` int(11) DEFAULT NULL,
  `featured_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_og_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_og_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `order` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `moderated_by` int(10) unsigned DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_alias` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.posts: ~0 rows (approximately)
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;

-- Dumping structure for table venderkids.projectfiles
CREATE TABLE IF NOT EXISTS `projectfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.projectfiles: ~11 rows (approximately)
/*!40000 ALTER TABLE `projectfiles` DISABLE KEYS */;
INSERT INTO `projectfiles` (`id`, `project_id`, `student_id`, `attachment`, `created_at`, `updated_at`) VALUES
	(17, 2, 12, 'image/project/project_ARniZH9QIC.jpg', '2022-06-28 18:06:34', '2022-06-28 18:06:34'),
	(18, 2, 12, 'image/project/project_mjEzscwsXo.jpg', '2022-06-28 18:17:05', '2022-06-28 18:17:05'),
	(19, 4, 12, 'image/project/project_Xb1eGVEe1v.jpg', '2022-06-29 12:07:55', '2022-06-29 12:07:55'),
	(20, 4, 12, 'image/project/project_srdngYeCc8.jpg', '2022-06-29 12:07:55', '2022-06-29 12:07:55'),
	(21, 5, 19, 'image/project/project_ZY7bQK2Gc5.png', '2022-07-07 22:27:52', '2022-07-07 22:27:52'),
	(23, 6, 7, 'image/project/project_gmz473xUUI.docx', '2022-07-08 05:03:22', '2022-07-08 05:03:22'),
	(24, 7, 7, 'image/project/project_jVUj6dZN0x.docx', '2022-07-08 05:04:06', '2022-07-08 05:04:06'),
	(25, 7, 7, 'image/project/project_LrSpXo1OdE.docx', '2022-07-08 05:04:06', '2022-07-08 05:04:06'),
	(26, 8, 15, 'image/project/project_eRZEP7qsZT.docx', '2022-07-08 05:10:33', '2022-07-08 05:10:33'),
	(27, 9, 29, 'image/project/project_RA6pywciiX.doc', '2022-08-26 23:52:29', '2022-08-26 23:52:29'),
	(28, 10, 32, 'image/project/project_dEJxkDbnRl.jpg', '2022-09-22 01:30:25', '2022-09-22 01:30:25');
/*!40000 ALTER TABLE `projectfiles` ENABLE KEYS */;

-- Dumping structure for table venderkids.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `project_status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.projects: ~8 rows (approximately)
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` (`id`, `student_id`, `title`, `description`, `project_status`, `created_at`, `updated_at`) VALUES
	(2, 12, 'Make a robot for new janaration', 'Make a robot for new janaration', 0, '2022-06-27 16:56:34', '2022-06-29 18:05:49'),
	(4, 12, 'Test project', '<p>This is a test project</p>', 0, '2022-06-29 12:07:55', '2022-06-29 18:06:01'),
	(5, 19, 'Project X', '<p>This is testing project X.&nbsp;</p>', 1, '2022-07-07 22:27:52', '2022-07-07 22:29:58'),
	(6, 7, 'Only my project', 'Here is no sujogs', 0, '2022-07-08 03:57:51', '2022-07-08 05:03:22'),
	(7, 7, 'Rs software', '<p>Here is many sujog</p>', 0, '2022-07-08 05:04:06', '2022-07-08 05:04:06'),
	(8, 15, 'Biology for mine', '<p>here tis my project</p>', 0, '2022-07-08 05:10:33', '2022-07-08 05:10:33'),
	(9, 29, 'Kids budget book', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</span><br></p>', 0, '2022-08-26 23:52:29', '2022-08-26 23:52:29'),
	(10, 32, 'test', '<p>XYZ</p>', 0, '2022-09-22 01:30:25', '2022-09-22 01:30:25');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;

-- Dumping structure for table venderkids.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.roles: ~8 rows (approximately)
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'super admin', 'web', '2022-06-14 13:14:18', '2022-06-14 13:14:18'),
	(2, 'administrator', 'web', '2022-06-14 13:14:18', '2022-06-14 13:14:18'),
	(3, 'manager', 'web', '2022-06-14 13:14:18', '2022-06-14 13:14:18'),
	(4, 'executive', 'web', '2022-06-14 13:14:18', '2022-06-14 13:14:18'),
	(5, 'user', 'web', '2022-06-14 13:14:18', '2022-06-14 13:14:18'),
	(6, 'trainer', 'web', '2022-06-14 13:19:07', '2022-06-14 13:19:07'),
	(7, 'school', 'web', '2022-06-14 13:19:28', '2022-06-14 13:19:28'),
	(8, 'student', 'web', '2022-06-24 03:21:00', '2022-06-24 03:21:00');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;

-- Dumping structure for table venderkids.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.role_has_permissions: ~49 rows (approximately)
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 2),
	(2, 2),
	(3, 2),
	(4, 2),
	(5, 2),
	(6, 2),
	(7, 2),
	(8, 2),
	(9, 2),
	(10, 2),
	(11, 2),
	(12, 2),
	(13, 2),
	(14, 2),
	(15, 2),
	(16, 2),
	(17, 2),
	(18, 2),
	(19, 2),
	(20, 2),
	(21, 2),
	(22, 2),
	(23, 2),
	(24, 2),
	(25, 2),
	(26, 2),
	(27, 2),
	(28, 2),
	(29, 2),
	(30, 2),
	(31, 2),
	(32, 2),
	(33, 2),
	(34, 2),
	(35, 2),
	(36, 2),
	(37, 2),
	(38, 2),
	(39, 2),
	(40, 2),
	(41, 2),
	(1, 3),
	(1, 4),
	(1, 6),
	(40, 6),
	(1, 7),
	(41, 7),
	(1, 8),
	(42, 8);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;

-- Dumping structure for table venderkids.schools
CREATE TABLE IF NOT EXISTS `schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `school_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `principle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `official_email_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_student` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_plan` int(11) DEFAULT NULL,
  `school_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_establish` int(11) DEFAULT NULL,
  `incharge_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incharge_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee_per_student` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `venderkids_representative` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_start_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrepreneurship_lab` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weekly_class_for_grade` text COLLATE utf8mb4_unicode_ci,
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.schools: ~5 rows (approximately)
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` (`id`, `user_id`, `school_name`, `city`, `principle_name`, `official_email_id`, `contact_number`, `number_of_student`, `country`, `membership_plan`, `school_address`, `year_establish`, `incharge_name`, `incharge_email`, `fee_per_student`, `venderkids_representative`, `course_start_date`, `entrepreneurship_lab`, `school_logo`, `school_cover_image`, `weekly_class_for_grade`, `status`, `created_at`, `updated_at`) VALUES
	(27, 147, 'Grace montessory', 'Faridabad', 'Mr. Ranvijay', 'ciyeyaf424@xitudy.com', '7574757895', '200', 'India', NULL, 'Faridabad', 1990, 'Grace', 'anything@gmail.com', '350', 'Swati', '2022-08-25', 'yes', 'image/school/school_oweRmM5sEM.webp', 'image/school/cover_image/cover_yIw6Dr4XD0.jpg', NULL, 1, '2022-08-25 20:13:50', '2022-09-24 23:07:45'),
	(29, 158, 'Little Learning House', 'Noida', 'Advik Singh', 'ellae9018@gmail.com', '916547477590', '500', 'Korea, Democratic People"S Republic of', 1, 'TT road', 1998, 'William bentic', 'officialbentic@gmail.com', '800', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2022-09-21 17:45:23', '2022-09-22 00:46:33'),
	(30, 159, 'venderkids', 'Singapore', 'Swati Gauba Kochar', 'swati@venderkids.com', '94278482', '100', 'Singapore', 1, NULL, NULL, NULL, NULL, '160', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2022-09-22 22:17:53', '2022-09-22 22:17:53'),
	(31, 163, 'Simon Reyes', 'Ray Randall', 'Sydnee Bailey', 'tyqifalat@mailinator.com', '+1 (194) 377-4112', '464', 'Guinea', 1, NULL, NULL, NULL, NULL, '81', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2022-10-07 00:00:02', '2022-10-07 00:00:02'),
	(32, 164, 'Amena Conway', 'Inez Conley', 'Teegan Patterson', 'xywan@mailinator.com', '+1 (946) 556-9405', '122', 'Togo', 1, 'Dolorum qui temporib', 2004, 'Dora', 'mugyteluv@mailinator.com', '36', 'Leigh Harrell', '2022-05-05', NULL, 'image/school/school_4X19oHO3m3.jpg', 'image/school/cover_image/cover_4ujqFLZwER.jpg', NULL, 1, '2022-10-07 00:03:08', '2022-10-07 00:11:44');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;

-- Dumping structure for table venderkids.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `val` text COLLATE utf8mb4_unicode_ci,
  `type` char(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.settings: ~0 rows (approximately)
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

-- Dumping structure for table venderkids.streams
CREATE TABLE IF NOT EXISTS `streams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator_id` int(11) NOT NULL,
  `creator` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.streams: ~7 rows (approximately)
/*!40000 ALTER TABLE `streams` DISABLE KEYS */;
INSERT INTO `streams` (`id`, `title`, `creator_id`, `creator`, `created_at`, `updated_at`) VALUES
	(2, 'Entrepreneurial Mindset', 1, 'Super Admin', '2022-07-08 05:06:37', '2022-07-08 05:06:37'),
	(3, 'Entrepreneurial Skills – Non-Cognitive', 1, 'Super Admin', '2022-07-08 05:06:53', '2022-07-08 05:06:53'),
	(4, 'Entrepreneurial Skills – Cognitive', 1, 'Super Admin', '2022-07-08 05:07:09', '2022-07-08 05:07:09'),
	(5, 'Entrepreneurial Knowledge', 1, 'Super Admin', '2022-07-08 05:07:27', '2022-07-08 05:07:27'),
	(6, 'Entrepreneurship in Action', 1, 'Super Admin', '2022-07-08 05:07:40', '2022-07-08 05:07:40'),
	(7, 'Short Term Programs', 1, 'Super Admin', '2022-07-08 05:07:51', '2022-07-08 05:07:51'),
	(8, '3', 1, 'Super Admin', '2022-09-23 17:10:49', '2022-09-23 17:10:49');
/*!40000 ALTER TABLE `streams` ENABLE KEYS */;

-- Dumping structure for table venderkids.students
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `project` int(11) DEFAULT NULL,
  `assignment` int(11) DEFAULT '0',
  `section` varchar(50) DEFAULT NULL,
  `classes_held` int(11) DEFAULT NULL,
  `classes_attended` int(11) DEFAULT NULL,
  `attendance` varchar(100) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `overal_grade` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `address` text,
  `blood_group` varchar(100) DEFAULT NULL,
  `activity_incharge` varchar(255) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.students: ~7 rows (approximately)
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` (`id`, `user_id`, `school_id`, `grade_id`, `project`, `assignment`, `section`, `classes_held`, `classes_attended`, `attendance`, `name`, `overal_grade`, `father_name`, `mother_name`, `address`, `blood_group`, `activity_incharge`, `image`, `created_at`, `updated_at`) VALUES
	(17, 112, 7, 7, 1, 1, NULL, 20, 8, '40%', 'Anshika singh', 'A', 'Rupesh', 'Raani', 'Lucknow', 'O', 'Kuch bhi', 'no_image', '2022-07-07 03:49:38', '2022-07-07 03:49:38'),
	(18, 113, 7, 1, 1, 1, NULL, 10, 8, '70%', 'Rafi Student', 'A', 'Jamal Mia', 'Khadija Khatun', 'Rampura, Dhaka', 'C', 'alex brad', 'no_image', '2022-07-07 04:47:47', '2022-07-07 04:47:47'),
	(28, 151, 27, 1, 0, 0, NULL, 0, 0, '0%', 'William Bentic', 'A', 'Michael Bentic', 'Paula ', 'New Delhi', 'O', 'alex brad', NULL, '2022-08-26 19:49:18', '2022-08-26 19:49:18'),
	(29, 152, 27, 2, 0, 0, NULL, 0, 0, '0', 'David Mosley', 'B', 'Ryan Mosley', 'Emily', 'New Delhi', 'A', 'Rolex', 'image/student/thumbnail/1664002524.jpg', '2022-08-26 19:49:19', '2022-09-24 18:25:24'),
	(30, 153, 27, 6, 0, 0, NULL, 0, 0, '0%', 'Karl Marx', 'A', 'Raul Marx', 'Reeta Marx', 'New Delhi', 'O', 'alex brad', NULL, '2022-08-26 19:56:01', '2022-08-26 19:56:01'),
	(31, 154, 27, 7, 0, 0, NULL, 0, 0, '0', 'Evil Mark', 'B', 'Revil', 'Sevil', 'New Delhi', 'A', 'alex brad', 'image/student/thumbnail/1664003011.jpg', '2022-08-26 19:56:02', '2022-09-24 18:33:31'),
	(32, 157, 28, 1, 1, 1, NULL, 10, 8, '80%', 'Rakheash', 'A', 'Sila', 'Adam', 'Raksha pur', 'C', 'alex brad', 'image/student/thumbnail/1663313153.jpg', '2022-09-16 18:12:06', '2022-09-16 18:55:53');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;

-- Dumping structure for table venderkids.student_attendance
CREATE TABLE IF NOT EXISTS `student_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `class_no` int(11) NOT NULL,
  `attend_status` int(11) NOT NULL COMMENT '1=Present, 2=Absent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.student_attendance: ~35 rows (approximately)
/*!40000 ALTER TABLE `student_attendance` DISABLE KEYS */;
INSERT INTO `student_attendance` (`id`, `student_id`, `trainer_id`, `class_no`, `attend_status`, `created_at`, `updated_at`) VALUES
	(9, 12, 1, 1, 1, '2022-07-05 17:41:41', '2022-07-05 19:58:37'),
	(13, 12, 1, 2, 1, '2022-07-05 19:15:33', '2022-07-05 19:15:33'),
	(14, 12, 1, 3, 1, '2022-07-05 19:34:23', '2022-07-05 19:34:23'),
	(15, 12, 1, 4, 1, '2022-07-05 19:34:24', '2022-07-05 19:34:24'),
	(16, 12, 1, 5, 1, '2022-07-05 19:36:19', '2022-07-05 19:36:19'),
	(17, 5, 1, 6, 2, '2022-07-05 19:36:22', '2022-07-05 19:36:22'),
	(18, 5, 1, 7, 2, '2022-07-06 06:21:51', '2022-07-06 06:21:51'),
	(19, 5, 1, 8, 1, '2022-07-06 06:21:53', '2022-07-06 06:21:53'),
	(20, 5, 1, 7, 1, '2022-07-06 06:22:04', '2022-07-06 06:22:04'),
	(21, 5, 8, 14, 1, '2022-07-19 18:49:17', '2022-07-19 18:49:35'),
	(22, 27, 13, 27, 1, '2022-08-26 19:13:27', '2022-08-26 19:13:27'),
	(23, 27, 13, 28, 1, '2022-08-26 19:13:28', '2022-08-26 19:13:28'),
	(24, 27, 13, 29, 1, '2022-08-26 19:13:29', '2022-08-26 19:13:29'),
	(25, 27, 13, 30, 2, '2022-08-26 19:13:32', '2022-08-26 19:13:38'),
	(26, 27, 13, 31, 1, '2022-08-26 19:13:32', '2022-08-26 19:13:32'),
	(27, 27, 13, 32, 1, '2022-08-26 19:13:33', '2022-08-26 19:13:33'),
	(28, 27, 13, 33, 2, '2022-08-26 19:13:34', '2022-08-26 19:13:34'),
	(29, 27, 13, 34, 2, '2022-08-26 19:13:35', '2022-08-26 19:13:35'),
	(30, 27, 13, 35, 1, '2022-08-26 19:13:41', '2022-08-26 19:13:41'),
	(31, 27, 13, 36, 2, '2022-08-26 19:13:42', '2022-08-26 19:13:42'),
	(32, 28, 13, 1, 1, '2022-09-23 23:52:52', '2022-09-23 23:52:52'),
	(33, 28, 13, 14, 2, '2022-09-23 23:52:59', '2022-09-23 23:53:10'),
	(34, 31, 14, 40, 2, '2022-09-24 22:07:14', '2022-09-24 22:07:34'),
	(35, 31, 14, 41, 2, '2022-09-24 22:07:16', '2022-09-24 22:07:34'),
	(36, 31, 14, 1, 1, '2022-09-24 22:07:25', '2022-09-24 22:07:25'),
	(37, 31, 14, 2, 1, '2022-09-24 22:07:26', '2022-09-24 22:07:26'),
	(38, 31, 14, 3, 1, '2022-09-24 22:07:28', '2022-09-24 22:07:28'),
	(39, 31, 14, 4, 1, '2022-09-24 22:07:29', '2022-09-24 22:07:29'),
	(40, 31, 14, 5, 2, '2022-09-24 22:07:30', '2022-09-24 22:07:30'),
	(41, 31, 14, 6, 1, '2022-09-24 22:07:31', '2022-09-24 22:07:31'),
	(42, 31, 14, 7, 1, '2022-09-24 22:07:38', '2022-09-24 22:07:38'),
	(43, 31, 14, 8, 2, '2022-09-24 22:07:50', '2022-09-24 22:07:50'),
	(44, 31, 14, 9, 1, '2022-09-24 22:50:09', '2022-09-24 22:50:09'),
	(45, 31, 14, 10, 2, '2022-09-24 22:50:11', '2022-09-24 22:50:11'),
	(46, 31, 14, 11, 1, '2022-09-24 22:50:13', '2022-09-24 22:50:13');
/*!40000 ALTER TABLE `student_attendance` ENABLE KEYS */;

-- Dumping structure for table venderkids.student_feedback
CREATE TABLE IF NOT EXISTS `student_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `year` varchar(30) DEFAULT NULL,
  `level` varchar(30) DEFAULT NULL,
  `feedback` varchar(255) DEFAULT NULL,
  `grade` varchar(255) DEFAULT NULL,
  `assessment` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.student_feedback: ~5 rows (approximately)
/*!40000 ALTER TABLE `student_feedback` DISABLE KEYS */;
INSERT INTO `student_feedback` (`id`, `student_id`, `year`, `level`, `feedback`, `grade`, `assessment`, `created_at`, `updated_at`) VALUES
	(18, 12, '2021', 'L1', 'Successfully done level one assesment.', 'A-', 1, '2021-03-01 05:30:00', '2021-03-01 05:30:00'),
	(19, 12, '2021', 'L2', 'Successfully done level two assesment.', 'A+', 1, '2021-06-01 05:30:00', '2021-06-01 05:30:00'),
	(20, 12, '2021', 'L3', 'Successfully done level three assesment.', 'B', 1, '2021-09-01 05:30:00', '2021-09-01 05:30:00'),
	(21, 12, '2021', 'L4', 'Successfully done level four assesment.', 'C', 1, '2022-02-01 05:30:00', '2022-02-01 05:30:00'),
	(22, 29, '2022', 'L3', 'There are many variations of passages of Lorem Ipsum available, but the majority', 'A', 1, '2022-08-26 23:58:07', '2022-08-26 23:58:07');
/*!40000 ALTER TABLE `student_feedback` ENABLE KEYS */;

-- Dumping structure for table venderkids.taggables
CREATE TABLE IF NOT EXISTS `taggables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` bigint(20) unsigned NOT NULL,
  `taggable_id` bigint(20) unsigned NOT NULL,
  `taggable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.taggables: ~0 rows (approximately)
/*!40000 ALTER TABLE `taggables` DISABLE KEYS */;
/*!40000 ALTER TABLE `taggables` ENABLE KEYS */;

-- Dumping structure for table venderkids.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `meta_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keyword` text COLLATE utf8mb4_unicode_ci,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.tags: ~0 rows (approximately)
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;

-- Dumping structure for table venderkids.todo
CREATE TABLE IF NOT EXISTS `todo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `todo_name` varchar(100) DEFAULT NULL,
  `todo_done` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.todo: ~6 rows (approximately)
/*!40000 ALTER TABLE `todo` DISABLE KEYS */;
INSERT INTO `todo` (`id`, `todo_name`, `todo_done`, `created_at`, `updated_at`) VALUES
	(1, 'Library', 1, '2022-06-23 05:30:00', '2022-09-22 16:39:43'),
	(2, 'Sports Club', 0, '2022-06-23 05:30:00', '2022-09-21 01:35:03'),
	(4, 'Task testing 001', 0, '2022-07-07 05:30:00', '2022-09-16 18:03:04'),
	(5, 'Attend Zoom Session', 0, '2022-07-19 10:46:22', '2022-08-26 22:40:21'),
	(6, 'test', 1, '2022-09-21 01:35:26', '2022-09-24 22:14:08'),
	(7, 'task1', 0, '2022-09-24 22:14:30', '2022-09-24 22:14:30');
/*!40000 ALTER TABLE `todo` ENABLE KEYS */;

-- Dumping structure for table venderkids.trainers
CREATE TABLE IF NOT EXISTS `trainers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `trainer_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `official_email_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `incharge_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trainer_fee` double(10,2) NOT NULL,
  `contact_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_date` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `no_of_hour_per_week` int(11) NOT NULL DEFAULT '0',
  `assessment_done` int(11) DEFAULT NULL,
  `demo_video` int(11) DEFAULT NULL,
  `training_hour` int(11) DEFAULT NULL,
  `past_achievements` text COLLATE utf8mb4_unicode_ci,
  `expertise` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.trainers: ~4 rows (approximately)
/*!40000 ALTER TABLE `trainers` DISABLE KEYS */;
INSERT INTO `trainers` (`id`, `user_id`, `trainer_name`, `official_email_id`, `incharge_email`, `trainer_fee`, `contact_no`, `address`, `city`, `join_date`, `date_of_birth`, `image`, `mode`, `type`, `status`, `no_of_hour_per_week`, `assessment_done`, `demo_video`, `training_hour`, `past_achievements`, `expertise`, `created_at`, `updated_at`) VALUES
	(13, 149, 'Mr. Charlie', 'fopaye3288@lurenwu.com', NULL, 400.00, '8585852365', 'Dharamshala', 'Dharamshala', '2022-08-25', '1993-12-02', NULL, 1, 1, 1, 16, NULL, NULL, NULL, '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary,</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '2022-08-25 23:45:27', '2022-08-26 18:40:03'),
	(14, 156, 'Otis', 'ootis548@gmail.com', 'ootis548@gmail.com', 500.00, '7867867866', 'Kanpur', 'Kanpur', '2022-09-18', '1990-01-10', NULL, 1, 1, 1, 15, 1, NULL, NULL, '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '<p><span style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;">Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</span><br></p>', '2022-09-16 02:01:15', '2022-09-24 22:23:33'),
	(15, 160, 'Swati Gauba Kochar', 'swati@hoppingo.com', NULL, 40.00, '94278482', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, '2022-09-23 02:42:08', '2022-09-23 02:42:08'),
	(16, 162, 'Gemma Sellers', 'hohodurehy@mailinator.com', NULL, 1.00, '+1 (896) 195-2926', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, '2022-10-05 22:28:38', '2022-10-05 22:28:38');
/*!40000 ALTER TABLE `trainers` ENABLE KEYS */;

-- Dumping structure for table venderkids.trainer_allocation
CREATE TABLE IF NOT EXISTS `trainer_allocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `class_schedule` varchar(255) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `class_date` date DEFAULT NULL,
  `class_duration` int(11) DEFAULT NULL,
  `class_start` varchar(50) DEFAULT NULL,
  `class_end` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.trainer_allocation: ~16 rows (approximately)
/*!40000 ALTER TABLE `trainer_allocation` DISABLE KEYS */;
INSERT INTO `trainer_allocation` (`id`, `school_id`, `trainer_id`, `class_schedule`, `day`, `grade`, `class_date`, `class_duration`, `class_start`, `class_end`, `created_at`, `updated_at`) VALUES
	(29, 1, 1, 'class time:grade3(08:05-10:05)', 1, 'grade3', '2022-07-04', 2, '08:05', '10:05', '2022-05-01 18:09:46', '2022-07-14 18:09:46'),
	(30, 1, 1, 'class time:grade2(11:00-12:00)', 2, 'grade2', '2022-07-12', 1, '11:00', '12:00', '2022-06-01 18:10:31', '2022-07-14 18:10:31'),
	(31, 1, 5, 'class time:grade4(09:00-11:00)', 4, 'grade4', '2022-07-14', 2, '09:00', '11:00', '2022-07-14 18:10:57', '2022-07-14 18:10:57'),
	(32, 1, 5, 'class time:grade1(10:00-11:00)', 5, 'grade1', '2022-07-15', 1, '10:00', '11:00', '2022-07-14 18:12:32', '2022-07-14 18:12:32'),
	(33, 1, 8, 'class time:grade1(11:00-12:00)', 5, 'grade1', '2022-07-08', 1, '11:00', '12:00', '2022-07-14 19:59:29', '2022-07-14 19:59:29'),
	(36, 9, 8, 'class time:grade7(10:00-11:00)', 1, 'grade7', '2022-08-01', 1, '10:00', '11:00', '2022-07-25 16:17:38', '2022-07-25 16:17:38'),
	(37, 9, 8, 'class time:grade8(11:00-12:00)', 1, 'grade8', '2022-08-01', 1, '11:00', '12:00', '2022-07-25 16:17:45', '2022-07-25 16:17:45'),
	(41, 9, 8, 'class time:grade6(11:00-12:00)', 2, 'grade6', '2022-08-09', 1, '11:00', '12:00', '2022-07-26 13:49:43', '2022-07-26 13:49:43'),
	(42, 26, 8, 'class time:grade1(09:00-10:00)', 1, 'grade1', '2022-08-29', 1, '09:00', '10:00', '2022-08-24 16:08:01', '2022-08-24 16:08:01'),
	(43, 26, 12, 'class time:grade3(12:00-13:00)', 2, 'grade3', '2022-08-30', 1, '12:00', '13:00', '2022-08-24 16:24:37', '2022-08-24 16:24:37'),
	(44, 26, 12, 'class time:grade2(11:00-12:00)', 1, 'grade2', '2022-09-05', 1, '11:00', '12:00', '2022-08-24 16:31:58', '2022-08-24 16:31:58'),
	(45, 27, 8, 'class time:grade7(10:00-11:00)', 1, 'grade7', '2022-08-08', 1, '10:00', '11:00', '2022-08-25 23:55:58', '2022-08-25 23:55:58'),
	(46, 27, 13, 'class time:grade7(11:00-12:00)', 3, 'grade7', '2022-08-17', 1, '11:00', '12:00', '2022-08-26 19:11:25', '2022-08-26 19:11:25'),
	(48, 27, 14, 'class time:grade8(03:00-05:00)', 5, 'grade8', '2022-09-23', 2, '03:00', '05:00', '2022-09-24 22:00:30', '2022-09-24 22:00:30'),
	(49, 29, 13, 'class time:grade1(10:00-12:00)', 1, 'grade1', '2022-09-05', 2, '10:00', '12:00', '2022-09-24 22:11:41', '2022-09-24 22:11:41'),
	(50, 27, 13, 'class time:grade4(11:00-12:00)', 1, 'grade4', '2022-09-05', 1, '11:00', '12:00', '2022-09-24 23:08:26', '2022-09-24 23:08:26');
/*!40000 ALTER TABLE `trainer_allocation` ENABLE KEYS */;

-- Dumping structure for table venderkids.trainer_education_background
CREATE TABLE IF NOT EXISTS `trainer_education_background` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainer_id` int(11) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `school_location` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `pass_year` varchar(50) DEFAULT NULL,
  `gread` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.trainer_education_background: ~5 rows (approximately)
/*!40000 ALTER TABLE `trainer_education_background` DISABLE KEYS */;
INSERT INTO `trainer_education_background` (`id`, `trainer_id`, `school_name`, `school_location`, `degree`, `pass_year`, `gread`, `created_at`, `updated_at`) VALUES
	(2, 8, NULL, NULL, NULL, NULL, NULL, '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(7, 1, 'Dhaka cantoment', 'adamje', 'jsc', '2012', 'A+', '2022-07-19 22:10:26', '2022-07-19 22:10:26'),
	(8, 12, NULL, NULL, NULL, NULL, NULL, '2022-08-24 16:23:14', '2022-08-24 16:23:14'),
	(10, 13, 'SSM Pakkibagh', 'Lucknow', 'Intermediate', '2012', 'A', '2022-08-26 18:40:03', '2022-08-26 18:40:03'),
	(14, 14, 'LPS', 'Rana Nagar', 'BCA', '2011', 'B', '2022-09-24 22:23:33', '2022-09-24 22:23:33');
/*!40000 ALTER TABLE `trainer_education_background` ENABLE KEYS */;

-- Dumping structure for table venderkids.trainer_past_achievements
CREATE TABLE IF NOT EXISTS `trainer_past_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainer_id` int(11) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `city_municipality` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trainer_id` (`trainer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table venderkids.trainer_past_achievements: ~8 rows (approximately)
/*!40000 ALTER TABLE `trainer_past_achievements` DISABLE KEYS */;
INSERT INTO `trainer_past_achievements` (`id`, `trainer_id`, `job_title`, `employer`, `city_municipality`, `country`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
	(82, 8, NULL, NULL, NULL, NULL, NULL, NULL, '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(83, 8, NULL, NULL, NULL, NULL, NULL, NULL, '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(84, 8, NULL, NULL, NULL, NULL, NULL, NULL, '2022-07-07 05:30:00', '2022-07-07 05:30:00'),
	(94, 1, 'Programmer language', 'Ahmudulallah', 'Dhaka', 'Bangladeshs', '2022-07-19', '2022-07-22', '2022-07-19 22:10:26', '2022-07-19 22:10:26'),
	(95, 1, 'here', 'dfsd', 'sfsd', 'fsfsdf', '2022-07-19', '2022-07-30', '2022-07-19 22:10:26', '2022-07-19 22:10:26'),
	(96, 12, NULL, NULL, NULL, NULL, NULL, NULL, '2022-08-24 16:23:14', '2022-08-24 16:23:14'),
	(98, 13, 'Trainer', 'Global entrepreneur firm', 'Delhi', 'India', '2016-01-26', '2017-12-12', '2022-08-26 18:40:03', '2022-08-26 18:40:03'),
	(102, 14, 'Trainer', 'Global entrepreneur firm', 'Kanpur', 'India', '2013-10-24', '2019-12-01', '2022-09-24 22:23:33', '2022-09-24 22:23:33');
/*!40000 ALTER TABLE `trainer_past_achievements` ENABLE KEYS */;

-- Dumping structure for table venderkids.userprofiles
CREATE TABLE IF NOT EXISTS `userprofiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_facebook` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_twitter` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_instagram` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_linkedin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_metadata` text COLLATE utf8mb4_unicode_ci,
  `last_ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_count` int(11) NOT NULL DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.userprofiles: ~89 rows (approximately)
/*!40000 ALTER TABLE `userprofiles` DISABLE KEYS */;
INSERT INTO `userprofiles` (`id`, `user_id`, `name`, `first_name`, `last_name`, `username`, `email`, `mobile`, `gender`, `url_website`, `url_facebook`, `url_twitter`, `url_instagram`, `url_linkedin`, `date_of_birth`, `address`, `bio`, `avatar`, `user_metadata`, `last_ip`, `login_count`, `last_login`, `email_verified_at`, `status`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Super Admin', 'Super', 'Admin', '100001', 'super@admin.com', '(463) 722-1356', 'Male', NULL, NULL, NULL, NULL, NULL, '2012-04-16', NULL, NULL, 'img/default-avatar.jpg', NULL, '127.0.0.1', 186, '2022-10-07 20:14:05', NULL, 1, NULL, 1, NULL, '2022-06-14 13:14:17', '2022-10-07 20:14:05', NULL),
	(2, 2, 'Admin Istrator', 'Admin', 'Istrator', '100002', 'admin@admin.com', '470-751-2659', 'Male', NULL, NULL, NULL, NULL, NULL, '1972-11-19', NULL, NULL, 'img/default-avatar.jpg', NULL, '127.0.0.1', 1, '2022-10-04 22:02:17', NULL, 1, NULL, 2, NULL, '2022-06-14 13:14:18', '2022-10-04 22:02:17', NULL),
	(3, 3, 'Manager', 'Manager', 'User User', '100003', 'manager@manager.com', '(347) 631-1528', 'Male', NULL, NULL, NULL, NULL, NULL, '1975-03-01', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2022-06-14 13:14:18', '2022-06-14 13:14:18', NULL),
	(4, 4, 'Executive User', 'Executive', 'User', '100004', 'executive@executive.com', '534-638-4543', 'Female', NULL, NULL, NULL, NULL, NULL, '1982-01-17', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2022-06-14 13:14:18', '2022-06-14 13:14:18', NULL),
	(5, 5, 'General User', 'General', 'User', '100005', 'user@user.com', '567.884.7515', 'Other', NULL, NULL, NULL, NULL, NULL, '1986-08-02', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2022-06-14 13:14:18', '2022-06-14 13:14:18', NULL),
	(6, 77, 'National ideal', NULL, NULL, '100077', 'nazmul@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
	(7, 78, 'Shukriti Ranjan Das', NULL, NULL, '100078', 'shukriti@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '223.178.238.85', 82, '2022-08-23 17:13:42', NULL, 1, 1, 78, NULL, '0000-00-00 00:00:00', '2022-08-23 17:13:42', NULL),
	(8, 79, 'National ideals', NULL, NULL, '100079', 'nazmul@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '223.178.238.85', 73, '2022-08-23 17:13:14', NULL, 1, 1, 79, NULL, '0000-00-00 00:00:00', '2022-08-23 17:13:14', NULL),
	(9, 80, 'rafi', NULL, NULL, '100080', 'ajaxrafi@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
	(10, 81, 'Mamun Hossain Student', NULL, NULL, NULL, 'pyqtdsxbuwjucmutex@bvhrk.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '203.76.221.62', 1, '0000-00-00 00:00:00', NULL, 1, 1, 81, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
	(11, 82, 'Rahim Mia Student', NULL, NULL, NULL, 'wxtmniqdwhzjbcpndx@kvhrs.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
	(47, 83, 'Mamun Hossain Student', NULL, NULL, NULL, 'pyqtdsxbuwjucmutex@bvhrk.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-06-24 03:41:33', '2022-06-24 03:41:33', NULL),
	(48, 84, 'Rahim Mia Student', NULL, NULL, NULL, 'wxtmniqdwhzjbcpndx@kvhrs.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-06-24 03:41:34', '2022-06-24 03:41:34', NULL),
	(49, 85, 'Rafi Student', NULL, NULL, NULL, 'kugpvasdhzkmobawtn@bvhrk.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '203.76.221.62', 1, '2022-06-24 04:09:11', NULL, 1, 1, 85, NULL, '2022-06-24 04:05:09', '2022-06-24 04:09:11', NULL),
	(50, 86, 'Shukriti Student', NULL, NULL, NULL, 'pjxxumsuulkwhcijzb@nvhrw.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '203.76.221.62', 3, '2022-07-14 22:24:01', NULL, 1, 1, 86, NULL, '2022-06-24 04:05:10', '2022-07-14 22:24:01', NULL),
	(51, 87, 'Rafi Student', NULL, NULL, '100087', 'student@test.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '116.204.154.114', 3, '2022-07-08 05:11:49', NULL, 1, 1, 87, NULL, '2022-06-23 19:23:14', '2022-07-08 05:11:49', NULL),
	(52, 88, 'Rafi Student', NULL, NULL, '100088', 'student@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '127.0.0.1', 2, '2022-06-23 19:35:19', NULL, 1, 1, 88, NULL, '2022-06-23 19:29:14', '2022-06-23 19:35:19', NULL),
	(53, 90, 'Rafi Student', NULL, NULL, '100090', 'student2@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 3, '2022-06-28 05:45:07', NULL, 1, 79, 90, NULL, '2022-06-23 19:44:54', '2022-06-28 05:45:07', NULL),
	(54, 91, 'Rafi Student', NULL, NULL, '100091', 'lhkxbuttkmovcgbgjz@kvhrw.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-06-24 05:56:00', '2022-06-24 05:56:00', NULL),
	(55, 92, 'Shukriti Student', NULL, NULL, '100092', 'bnuzbzknxvtdegwsnd@kvhrr.com', '1789562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1992-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-06-24 05:57:09', NULL, 1, 1, 92, NULL, '2022-06-24 05:56:00', '2022-06-24 05:57:09', NULL),
	(56, 93, 'Rafi Student', NULL, NULL, '100093', 'afiqur@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '223.178.238.85', 40, '2022-08-23 17:14:08', NULL, 1, 1, 93, NULL, '2022-06-24 06:02:58', '2022-08-23 17:14:08', NULL),
	(57, 94, 'hjhjhjhjhj', NULL, NULL, '100094', 'xeheg71348@runqx.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '127.0.0.1', 2, '2022-07-03 18:20:47', NULL, 1, 1, 94, NULL, '2022-06-24 21:59:01', '2022-07-03 18:20:47', NULL),
	(58, 95, 'Trainer 05', NULL, NULL, '100095', 'xaniv50587@runqx.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '127.0.0.1', 2, '2022-07-03 18:22:00', NULL, 1, 1, 95, NULL, '2022-06-24 22:57:20', '2022-07-03 18:22:01', NULL),
	(59, 96, 'zcsdvfasc', NULL, NULL, '100096', 'cowono2551@serosin.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '183.83.42.47', 1, '2022-06-25 01:52:21', NULL, 1, 1, 1, NULL, '2022-06-25 01:51:36', '2022-06-25 01:56:44', NULL),
	(60, 97, 'mkhugjbu', NULL, NULL, '100097', 'coxeye9925@exoacre.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '116.204.154.117', 3, '2022-07-21 15:35:05', NULL, 1, 1, 97, NULL, '2022-06-25 01:53:06', '2022-07-21 15:35:05', NULL),
	(61, 98, 'Rafi Student', NULL, NULL, '100098', 'womjixsonffntshwqv@kvhrs.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-06-25 21:35:03', NULL, 1, 1, 98, NULL, '2022-06-25 21:33:51', '2022-06-25 21:35:03', NULL),
	(62, 99, 'Rafi Student', NULL, NULL, '100099', 'gxoejykfcxxbapkrjl@bvhrs.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-06-25 21:45:29', NULL, 1, 79, 99, NULL, '2022-06-25 21:44:13', '2022-06-25 21:45:29', NULL),
	(63, 100, 'asdfasdf', NULL, NULL, '100100', 'asdfsdf@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-06-26 05:33:22', '2022-06-26 05:33:22', NULL),
	(64, 102, 'Rafi Student', NULL, NULL, '100102', 'afiqur5@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '116.204.154.114', 4, '2022-07-08 05:09:54', NULL, 1, 1, 102, NULL, '2022-06-26 05:55:11', '2022-07-08 05:09:54', NULL),
	(65, 103, 'Batara School', NULL, NULL, '100103', 'batara@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-06-30 10:57:20', '2022-06-30 10:57:20', NULL),
	(66, 104, 'Rafi Student', NULL, NULL, '100104', 'afiqur4564@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-06-30 16:14:42', '2022-06-30 16:14:42', NULL),
	(67, 105, 'Grace Murphy', NULL, NULL, '100105', 'graceofficial1714', '9.17585E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-02 10:16:13', '2022-07-02 10:16:13', NULL),
	(68, 106, 'bonosriAkbor', NULL, NULL, '100106', 'akbor@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-04 15:25:18', '2022-07-06 20:34:47', NULL),
	(69, 109, 'Trainer 5', NULL, NULL, '100109', 'afiqur+11@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 2, '2022-07-08 04:09:39', NULL, 1, 1, 109, NULL, '2022-07-06 22:21:53', '2022-07-08 04:09:39', NULL),
	(70, 110, 'Test school', NULL, NULL, '100110', 'afiqur+12@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-06 22:26:34', '2022-07-06 22:26:34', NULL),
	(71, 111, 'National academy', NULL, NULL, '100111', 'roweg90454@hekarro.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 3, '2022-07-07 04:01:48', NULL, 1, 1, 111, NULL, '2022-07-07 03:45:39', '2022-07-07 04:49:01', NULL),
	(72, 112, 'Anshika singh', NULL, NULL, '100112', 'wadij43188@lankew.com', '9.19855E+11', 'Female', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '106.214.242.169', 1, '2022-07-07 03:51:26', NULL, 1, 111, 112, NULL, '2022-07-07 03:49:38', '2022-07-07 03:51:26', NULL),
	(73, 113, 'Rafi Student', NULL, NULL, '100113', 'afiqur88888@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-07 04:47:47', '2022-07-07 04:47:47', NULL),
	(74, 114, 'Central school', NULL, NULL, '100114', 'markevil07@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-07 20:23:23', '2022-07-07 20:23:23', NULL),
	(75, 115, 'LBS International', NULL, NULL, '100115', 'officialbentic@yahoo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '223.178.238.85', 15, '2022-08-23 16:39:32', NULL, 1, 1, 115, NULL, '2022-07-07 20:43:45', '2022-08-23 16:39:32', NULL),
	(76, 116, 'Grace murphy', NULL, NULL, '100116', 'gracemurphy321@yahoo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '122.163.175.100', 18, '2022-07-28 18:43:07', NULL, 1, 1, 116, NULL, '2022-07-07 20:48:01', '2022-07-28 18:43:07', NULL),
	(77, 117, 'Alfie soloman', NULL, NULL, '100117', 'alfiesolomon391@yahoo.com', '9.19875E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '122.163.175.100', 15, '2022-07-28 18:42:12', NULL, 1, 115, 117, NULL, '2022-07-07 20:58:41', '2022-07-28 18:42:12', NULL),
	(78, 118, 'National school and college', NULL, NULL, '100118', 'nazmulsahajjo@yahoo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-07-07 21:46:10', NULL, 1, 1, 118, NULL, '2022-07-07 21:21:35', '2022-07-07 21:46:10', NULL),
	(79, 119, 'National school and colleges', NULL, NULL, '100119', 'nazmul+1@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-07 21:22:48', '2022-07-07 21:22:48', NULL),
	(80, 120, 'David mosley', NULL, NULL, '100120', 'mosleyofficial1@gmail.com', '9.20E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '2010-12-07', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 115, 115, NULL, '2022-07-08 19:24:44', '2022-07-08 19:24:44', NULL),
	(81, 121, 'David mosley', NULL, NULL, '100121', 'poposi6934@meidir.com', '9.20E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '2010-12-07', NULL, NULL, 'img/default-avatar.jpg', NULL, '27.57.118.21', 1, '2022-07-08 19:31:41', NULL, 1, 115, 121, NULL, '2022-07-08 19:31:17', '2022-07-08 19:31:41', NULL),
	(82, 122, 'venderkids', NULL, NULL, '100122', 'swati@venderkids.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 20:00:47', '2022-07-08 20:00:47', NULL),
	(83, 123, 'New National school and college', NULL, NULL, '100123', 'amtsrivastava007@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:10:05', '2022-07-08 21:10:05', NULL),
	(84, 124, 'National school and college new', NULL, NULL, '100124', 'amtsrivastava007@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:14:45', '2022-07-08 21:14:45', NULL),
	(85, 125, 'New National school and college', NULL, NULL, '100125', 'amtsrivastava007@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:21:16', '2022-07-08 21:21:16', NULL),
	(86, 126, 'New National ideals bangladesh', NULL, NULL, '100126', 'amtsrivastava007@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:34:52', '2022-07-08 21:34:52', NULL),
	(87, 127, 'National school and college fgdfgdfg', NULL, NULL, '100127', 'hossainnazmul191@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:50:23', '2022-07-08 21:50:23', NULL),
	(88, 128, 'National school and college fgdfgdfg', NULL, NULL, '100128', 'hossainnazmul191+1@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:51:56', '2022-07-08 21:51:56', NULL),
	(89, 129, 'National school and college fgdfgdfg', NULL, NULL, '100129', 'hossainnazmul191+2@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 21:59:54', '2022-07-08 21:59:54', NULL),
	(90, 130, 'National school and college fgdfgdfg', NULL, NULL, '100130', 'hossainnazmul191+6@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 22:06:29', '2022-07-08 22:06:29', NULL),
	(91, 131, 'National school and college fgdfgdfg', NULL, NULL, '100131', 'hossainnazmul191+123@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-08 18:34:32', '2022-07-08 18:34:32', NULL),
	(92, 132, 'My super test', NULL, NULL, '100132', 'robelsust+51@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-09 13:23:06', '2022-07-09 13:23:06', NULL),
	(93, 133, 'Another', NULL, NULL, '100133', 'robelsust+52@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-09 13:44:44', '2022-07-09 13:44:44', NULL),
	(94, 134, 'G D Goenka', NULL, NULL, '100134', 'jacobsmithtemp@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '106.202.130.97', 3, '2022-07-12 14:08:47', NULL, 1, 1, 134, NULL, '2022-07-11 15:47:58', '2022-07-12 14:08:47', NULL),
	(95, 135, 'William Bentic', NULL, NULL, '100135', 'officialbentic@gmail.com', '91899954781', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-07-11 18:41:25', '2022-07-11 18:41:25', NULL),
	(96, 136, 'Test School', NULL, NULL, '100136', 'robelsust+52@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '103.112.54.213', 1, '2022-07-12 05:22:48', NULL, 1, 1, 136, NULL, '2022-07-12 05:22:00', '2022-07-12 05:22:48', NULL),
	(97, 137, 'Rafi Student', NULL, NULL, '100137', 'robelsust+55@gmail.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '103.112.54.213', 1, '2022-07-12 05:28:03', NULL, 1, 136, 137, NULL, '2022-07-12 05:27:19', '2022-07-12 05:28:03', NULL),
	(98, 138, 'Thomas', NULL, NULL, '100138', 'sheltommy673@gmail.com', '9.19899E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '106.202.130.97', 1, '2022-07-12 14:12:10', NULL, 1, 134, 138, NULL, '2022-07-12 14:11:10', '2022-07-12 14:12:10', NULL),
	(99, 139, 'Nihar Trainer test', NULL, NULL, '100139', 'robelsust+59@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '103.112.54.213', 2, '2022-07-13 02:44:35', NULL, 1, 1, 139, NULL, '2022-07-13 02:40:40', '2022-07-13 02:44:35', NULL),
	(100, 140, 'Rampura', NULL, NULL, '100140', 'nazmul+1@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '116.204.154.117', 2, '2022-07-21 15:24:01', NULL, 1, 1, 140, NULL, '2022-07-14 13:52:47', '2022-07-21 15:24:01', NULL),
	(101, 141, 'Shukriti SWE', NULL, NULL, '100141', 'shukriti+1@sahajjo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-07-14 13:59:08', NULL, 1, 1, 141, NULL, '2022-07-14 13:57:07', '2022-07-14 13:59:08', NULL),
	(102, 142, 'Anthony Gomes', NULL, NULL, '100142', 'shukriti+2@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-07-14 14:22:04', NULL, 1, 1, 142, NULL, '2022-07-14 14:10:20', '2022-07-14 14:22:04', NULL),
	(103, 143, 'Limia jones', NULL, NULL, '100143', 'shukriti+4@sahajjo.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '203.76.221.62', 1, '2022-07-14 14:38:31', NULL, 1, 79, 143, NULL, '2022-07-14 14:36:42', '2022-07-14 14:38:31', NULL),
	(104, 144, 'Grace montessory', NULL, NULL, '100144', 'sodoya4418@vpsrec.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '110.226.97.142', 1, '2022-08-24 15:45:38', NULL, 1, 1, 144, NULL, '2022-08-24 15:45:12', '2022-08-24 15:45:38', NULL),
	(105, 145, 'Mr. Charlie', NULL, NULL, '100145', 'rifatil220@rxcay.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '110.226.97.142', 1, '2022-08-24 16:02:55', NULL, 1, 1, 145, NULL, '2022-08-24 15:59:00', '2022-08-24 16:02:55', NULL),
	(106, 146, 'Mr. Shelby', NULL, NULL, '100146', 'favij21792@xitudy.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '110.226.97.142', 2, '2022-08-24 16:44:46', NULL, 1, 1, 146, NULL, '2022-08-24 16:04:36', '2022-08-24 16:44:46', NULL),
	(107, 147, 'Grace montessory', NULL, NULL, '100147', 'ciyeyaf424@xitudy.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '183.83.43.103', 10, '2022-09-24 22:43:43', NULL, 1, 1, 147, NULL, '2022-08-25 20:13:49', '2022-09-24 22:43:43', NULL),
	(108, 148, 'Michael Grey', NULL, NULL, '100148', 'tosefa8282@rxcay.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '110.226.104.139', 2, '2022-08-26 16:39:26', NULL, 1, 147, 148, NULL, '2022-08-25 22:31:43', '2022-08-26 16:39:26', NULL),
	(109, 149, 'Mr. Charlie', NULL, NULL, '100149', 'fopaye3288@lurenwu.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '171.79.100.178', 5, '2022-09-24 21:58:29', NULL, 1, 1, 149, NULL, '2022-08-25 23:45:27', '2022-09-24 21:58:29', NULL),
	(110, 150, 'Rampus Academy', NULL, NULL, '100150', 'kelon96729@otodir.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '122.163.238.194', 1, '2022-08-26 00:11:36', NULL, 1, 1, 1, NULL, '2022-08-26 00:09:31', '2022-08-26 00:17:57', NULL),
	(111, 151, 'William Bentic', NULL, NULL, '100151', 'officialbentic@gmail.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '106.200.250.72', 1, '2022-09-16 01:08:49', NULL, 1, 1, 151, NULL, '2022-08-26 19:49:18', '2022-09-16 01:08:49', NULL),
	(112, 152, 'David Mosley', NULL, NULL, '100152', 'mosleyofficial1@gmail.com', '9.18521E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '2010-12-02', NULL, NULL, 'img/default-avatar.jpg', NULL, '171.79.100.178', 3, '2022-09-24 21:48:34', NULL, 1, 1, 152, NULL, '2022-08-26 19:49:19', '2022-09-24 21:48:34', NULL),
	(113, 153, 'Karl Marx', NULL, NULL, '100153', 'markskarl1705@gmail.com', '1790562315', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-08-26 19:56:01', '2022-08-26 19:56:01', NULL),
	(114, 154, 'Evil Mark', NULL, NULL, '100154', 'markevil07@gmail.com', '9.19856E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '2009-12-08', NULL, NULL, 'img/default-avatar.jpg', NULL, '183.83.43.103', 3, '2022-09-24 22:45:42', NULL, 1, 1, 154, NULL, '2022-08-26 19:56:02', '2022-09-24 22:45:42', NULL),
	(115, 155, 'New Standard Public School', NULL, NULL, '100155', 'palak98787@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '58.182.63.233', 10, '2022-09-22 16:25:48', NULL, 1, 1, 155, NULL, '2022-09-16 00:51:08', '2022-09-22 16:25:48', NULL),
	(116, 156, 'Otis', NULL, NULL, '100156', 'ootis548@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '183.83.43.103', 13, '2022-09-24 22:47:29', NULL, 1, 1, 156, NULL, '2022-09-16 02:01:15', '2022-09-24 22:47:29', NULL),
	(117, 157, 'Rakheash', NULL, NULL, '100157', 'a67081161@gmail.com', '9.20E+11', 'Male', NULL, NULL, NULL, NULL, NULL, '1995-12-12', NULL, NULL, 'img/default-avatar.jpg', NULL, '58.182.63.233', 14, '2022-09-29 15:53:32', NULL, 1, 155, 157, NULL, '2022-09-16 18:12:06', '2022-09-29 15:53:32', NULL),
	(118, 158, 'Little Learning House', NULL, NULL, '100158', 'ellae9018@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '183.83.43.103', 5, '2022-09-24 22:42:19', NULL, 1, 1, 158, NULL, '2022-09-21 17:45:22', '2022-09-24 22:42:19', NULL),
	(119, 159, 'venderkids', NULL, NULL, '100159', 'swati@venderkids.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '127.0.0.1', 1, '2022-10-04 22:21:01', NULL, 1, 1, 159, NULL, '2022-09-22 22:17:52', '2022-10-04 22:21:01', NULL),
	(120, 160, 'Swati Gauba Kochar', NULL, NULL, '100160', 'swati@hoppingo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, '223.236.160.149', 2, '2022-09-23 23:50:04', NULL, 1, 1, 160, NULL, '2022-09-23 02:42:07', '2022-09-23 23:50:04', NULL),
	(121, 162, 'Naomi Flowers', NULL, NULL, '100162', 'bhaviktrambadiya@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-10-04 22:58:59', '2022-10-04 22:58:59', NULL),
	(122, 162, 'Gemma Sellers', NULL, NULL, '100162', 'hohodurehy@mailinator.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-10-05 22:28:37', '2022-10-05 22:28:37', NULL),
	(123, 163, 'Simon Reyes', NULL, NULL, '100163', 'tyqifalat@mailinator.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-10-07 00:00:02', '2022-10-07 00:00:02', NULL),
	(124, 164, 'Amena Conway', NULL, NULL, '100164', 'xywan@mailinator.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'img/default-avatar.jpg', NULL, NULL, 0, NULL, NULL, 1, 1, 1, NULL, '2022-10-07 00:03:08', '2022-10-07 00:03:08', NULL);
/*!40000 ALTER TABLE `userprofiles` ENABLE KEYS */;

-- Dumping structure for table venderkids.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'img/default-avatar.jpg',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `group` int(11) NOT NULL DEFAULT '1' COMMENT 'Super Admin=1,School=2,Trainer=3,Student=4, Admin=5',
  `suspend` tinyint(2) DEFAULT '2' COMMENT 'suspend = 1, unsuspend = 2',
  `termandcondition` int(11) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.users: ~25 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `first_name`, `last_name`, `username`, `email`, `mobile`, `gender`, `date_of_birth`, `email_verified_at`, `password`, `avatar`, `status`, `group`, `suspend`, `termandcondition`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Super Admin', 'Super', 'Admin', '100001', 'education@therssoftware.com', '(463) 722-1356', 'Male', '2012-04-16', '2022-06-14 13:14:17', '$2y$10$LnJEQISigSGAUS7dI1cNUOc.iwXaA4ogPtR1Kt/GklFXY42sWhLpK', 'asset/dist/img/user2-160x160.jpg', 1, 1, 2, 0, NULL, '2022-06-14 13:14:17', '2022-06-14 13:14:17', NULL),
	(2, 'Admin Istrator', 'Admin', 'Istrator', '100002', 'admin@admin.com', '470-751-2659', 'Male', '1972-11-19', '2022-06-14 13:14:17', '$2y$10$Be8nLIzHKSf./Ad/yAV4c.n6IsXULdvJxSMSrgMvbR8MfyIS2h6me', 'img/default-avatar.jpg', 1, 5, 2, 0, NULL, '2022-06-14 13:14:17', '2022-06-14 13:14:17', NULL),
	(3, 'Manager', 'Manager', 'User User', '100003', 'manager@manager.com', '(347) 631-1528', 'Male', '1975-03-01', '2022-06-14 13:14:17', '$2y$10$miKLt4sGBf3BHktj45dI5enPhTDcSaadh/NsMgWXgBpJpIw6MixqS', 'img/default-avatar.jpg', 1, 5, 2, 0, NULL, '2022-06-14 13:14:17', '2022-06-14 13:14:17', NULL),
	(4, 'Executive User', 'Executive', 'User', '100004', 'executive@executive.com', '534-638-4543', 'Female', '1982-01-17', '2022-06-14 13:14:17', '$2y$10$IGHBIWtdD.ANt1Yb0oa2Te4x7UgXzcAfGccKAFJpqwxaXIzYi7dV.', 'img/default-avatar.jpg', 1, 5, 2, 0, NULL, '2022-06-14 13:14:17', '2022-06-14 13:14:17', NULL),
	(5, 'General User', 'General', 'User', '100005', 'user@user.com', '567.884.7515', 'Other', '1986-08-02', '2022-06-14 13:14:17', '$2y$10$1TOswbYd/vqBw3o6h2139O.lLd47a5a/d08d9VmPw8VU0J8vXq9Ga', 'img/default-avatar.jpg', 1, 5, 2, 0, NULL, '2022-06-14 13:14:17', '2022-06-14 13:14:17', NULL),
	(89, 'Rafi Student', NULL, NULL, NULL, 'student1@sahajjo.com', '1790562315', 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$Pi7e31PsgerYsDB5tAc1tuG2fvU//SCKk0UyLJwvmZ1u8fiCZIdzW', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-06-23 19:43:05', '2022-09-24 20:13:50', NULL),
	(104, 'Rafi Student', NULL, NULL, '100104', 'afiqur4564@sahajjo.com', '1790562315', 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$Td1/FDvYXDukTCgCcIybKOTR1rMSsQ4Vunn3fTISGdQv3UwxustvG', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-06-30 16:14:41', '2022-09-24 20:13:50', NULL),
	(112, 'Anshika singh', NULL, NULL, '100112', 'wadij43188@lankew.com', '9.19855E+11', 'Female', '1995-12-12 00:00:00', NULL, '$2y$10$ZZPQ8di2m7T9kEI5IctPBO9kj0OHMryS0NAaz2F4Y.tgSARW3mnV2', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-07-07 03:49:38', '2022-09-24 20:13:50', NULL),
	(113, 'Rafi Student', NULL, NULL, '100113', 'afiqur88888@sahajjo.com', '1790562315', 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$boqqdMbopCiXOV/OXP0GauO/R.FmFqDvnYldbofMxt9.judYJvpgO', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-07-07 04:47:47', '2022-09-24 20:13:50', NULL),
	(132, 'My super test', NULL, NULL, '100132', 'robelsust+51@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$jXgcpamC2rda2LBT37hmd.i8QI6ZvIBregpKKKntPIq8iLVg0r6Bm', 'img/default-avatar.jpg', 1, 2, NULL, 0, NULL, '2022-07-09 13:23:06', '2022-09-23 15:54:37', NULL),
	(147, 'Grace montessory', NULL, NULL, '100147', 'ciyeyaf424@xitudy.com', NULL, NULL, NULL, NULL, '$2y$10$t1POIUEQs6Lfb5UylbuvOuAXYB7SsYcRjInXkjK90yJXTm5Q/I2qO', 'img/default-avatar.jpg', 1, 2, 2, 0, NULL, '2022-08-25 20:13:49', '2022-09-23 23:40:28', NULL),
	(149, 'Mr. Charlie', NULL, NULL, '100149', 'fopaye3288@lurenwu.com', NULL, NULL, NULL, NULL, '$2y$10$UQLrtZivOthRbFjtceXKZuqu16cRtFMHgcvqRp60QbaI4kW7S./I.', 'img/default-avatar.jpg', 1, 3, 2, 0, NULL, '2022-08-25 23:45:27', '2022-10-06 23:44:36', NULL),
	(151, 'William Bentic', NULL, NULL, '100151', 'officialbentic@gmail.com', '1790562315', 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$OjWfCVj2mXGswoJihqG72eiqOUxHpZHam3ZAc51rMxVNdehQapSHq', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-08-26 19:49:18', '2022-09-24 20:13:50', NULL),
	(152, 'David Mosley', NULL, NULL, '100152', 'mosleyofficial1@gmail.com', '9.18521E+11', 'Male', '2010-12-02 00:00:00', NULL, '$2y$10$WrpdErVvGlgdEHzirwXXEu0HYQctLX2is/URuJ/1P3GYxnDktjYA6', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-08-26 19:49:19', '2022-09-24 20:13:50', NULL),
	(153, 'Karl Marx', NULL, NULL, '100153', 'markskarl1705@gmail.com', '1790562315', 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$.iCOBR2w0a1B5m75zCQX1.NaaFOQYOdQG95.uU1E4i7uy11EejSKe', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-08-26 19:56:01', '2022-09-24 20:13:50', NULL),
	(154, 'Evil Mark', NULL, NULL, '100154', 'markevil07@gmail.com', '9.19856E+11', 'Male', '2009-12-08 00:00:00', NULL, '$2y$10$fTYJZmrWrCM9OSHao2W/Ve9.mi0cjKpCPproNFXwcHMT.6639MNYK', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-08-26 19:56:02', '2022-09-24 20:13:50', NULL),
	(156, 'Otis', NULL, NULL, '100156', 'ootis548@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$rq3TxnML/17eGs0nvmXeJOeIHvuQ2TJXtvLVh5.exUn85e/VL0KgG', 'img/default-avatar.jpg', 1, 3, 2, 0, NULL, '2022-09-16 02:01:15', '2022-10-06 23:44:36', NULL),
	(157, 'Rakheash', NULL, NULL, '100157', 'a67081161@gmail.com', '9.20E+11', 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$4CKW/iR7Lhf4IcOlexUdpudeAMEIodoL9ohOQs2EodzeuOwi3OIRy', 'img/default-avatar.jpg', 1, 4, 2, 0, NULL, '2022-09-16 18:12:06', '2022-09-24 20:13:50', NULL),
	(158, 'Little Learning House', NULL, NULL, '100158', 'ellae9018@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$tbU0.vxwtjw9H4YlgONZA.g8FPQFwCOsN3CIS4WQdflEDiOZmLuEu', 'img/default-avatar.jpg', 1, 2, 2, 0, NULL, '2022-09-21 17:45:22', '2022-09-27 19:11:19', NULL),
	(159, 'venderkids', NULL, NULL, '100159', 'swati@venderkids.com', NULL, NULL, NULL, NULL, '$2y$10$NoupA0.n.IpSpkfFQxhuS.wzt3AS.FmgOo83JzZq5DMdoqhdyLqUe', 'img/default-avatar.jpg', 1, 2, 2, 0, NULL, '2022-09-22 22:17:52', '2022-09-23 15:54:37', NULL),
	(160, 'Swati Gauba Kochar', NULL, NULL, '100160', 'swati@hoppingo.com', NULL, NULL, NULL, NULL, '$2y$10$9yG6xGPlhQEK4c6FrlHFyO3NwzwP3R6SUDS0DBlTmQRqbcJqvRwm.', 'img/default-avatar.jpg', 1, 3, 2, 0, NULL, '2022-09-23 02:42:07', '2022-10-06 23:44:36', NULL),
	(161, 'Bhavik test', 'bhavik', 'test', '100161', 'bhaviktest@gmail.com', NULL, 'Male', '1995-12-12 00:00:00', NULL, '$2y$10$LnJEQISigSGAUS7dI1cNUOc.iwXaA4ogPtR1Kt/GklFXY42sWhLpK', 'img/default-avatar.jpg', 1, 1, 2, 0, 'pxCHdUPwrbGoGlWJUYnVRD2o2wbtEKbwye6tidqxslgwCKUdy85M0t4ThijT', '2022-06-14 13:14:17', '2022-10-02 20:03:30', NULL),
	(162, 'Gemma Sellers', NULL, NULL, '100162', 'hohodurehy@mailinator.com', NULL, NULL, NULL, NULL, '$2y$10$0XsLSB25Qo2bpEIHMq6US.wv28g6LtouwwCkipwutLZf8wYAmHgZW', 'img/default-avatar.jpg', 1, 3, 2, 0, NULL, '2022-10-05 22:28:37', '2022-10-06 23:44:36', NULL),
	(163, 'Simon Reyes', NULL, NULL, '100163', 'tyqifalat@mailinator.com', NULL, NULL, NULL, NULL, '$2y$10$EHoeXwHQU5BFVEbkECeMse4Wh7rOH.gM9xy08BdyjI6/qGvGLp1/G', 'img/default-avatar.jpg', 1, 2, 2, 0, NULL, '2022-10-07 00:00:02', '2022-10-07 00:00:02', NULL),
	(164, 'Amena Conway', NULL, NULL, '100164', 'xywan@mailinator.com', NULL, NULL, NULL, NULL, '$2y$10$8lm/CT.bZ7on28EpCKGoGu6TpvJ2d1xY76R5JzcIoDJSA1zHboluC', 'img/default-avatar.jpg', 1, 2, 2, 0, NULL, '2022-10-07 00:03:08', '2022-10-07 00:03:08', NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Dumping structure for table venderkids.user_providers
CREATE TABLE IF NOT EXISTS `user_providers` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_providers_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table venderkids.user_providers: ~0 rows (approximately)
/*!40000 ALTER TABLE `user_providers` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_providers` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
