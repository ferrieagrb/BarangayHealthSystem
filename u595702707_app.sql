-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 22, 2026 at 12:26 PM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u595702707_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `citizen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Career Fair', 'External Affairs Event', 'Community Program', '2026-05-05 02:49:39', '2026-05-05 02:49:39'),
(2, 'TEST2', 'TEST2', 'Urgent Alert', '2026-05-05 07:22:35', '2026-05-05 07:22:35'),
(3, 'TEST3', 'TEST3', 'Community Program', '2026-05-05 07:22:47', '2026-05-05 07:22:47'),
(4, 'TEST', 'TEST', 'Health Advisory', '2026-05-08 00:46:27', '2026-05-08 00:46:27'),
(5, 'THIS IS AN ANNOUNCEMENT', 'AAAAAAAAAAAAAHHHHHHHHHHHHh', 'Health Advisory', '2026-05-15 01:38:01', '2026-05-15 01:38:01'),
(6, 'Trydaw', 'WOW', 'Urgent Alert', '2026-05-15 10:11:51', '2026-05-15 10:11:51'),
(7, 'MAY 15', 'GUTOM NA SI SIR KEM', 'Urgent Alert', '2026-05-15 12:41:33', '2026-05-15 12:41:33');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizens`
--

CREATE TABLE `citizens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `Citizen_FName` varchar(255) NOT NULL,
  `Citizen_LName` varchar(255) NOT NULL,
  `Citizen_BirthDate` date NOT NULL,
  `Citizen_ContactNo` varchar(255) NOT NULL,
  `Citizen_Purok` varchar(255) DEFAULT NULL,
  `Citizen_Age` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `citizens`
--

INSERT INTO `citizens` (`id`, `Citizen_FName`, `Citizen_LName`, `Citizen_BirthDate`, `Citizen_ContactNo`, `Citizen_Purok`, `Citizen_Age`, `created_at`, `updated_at`) VALUES
(10, 'Ferrie', 'Blanza', '2026-05-01', '09785674321', 'Purok 1', 1, '2026-05-05 03:27:56', '2026-05-05 03:27:56'),
(11, 'Ferrie', 'Blanza', '2026-05-04', '09602132388', 'Purok 2', 5, '2026-05-05 03:28:14', '2026-05-05 03:28:14'),
(12, 'Eunice', 'Queriazon', '2026-05-06', '09785642378', 'Purok 1', 20, '2026-05-06 16:06:48', '2026-05-06 16:06:48'),
(15, 'Ferrie', 'Blanza', '2026-05-15', '09785674321', 'Purok 1', 21, '2026-05-15 00:52:31', '2026-05-15 00:52:31'),
(16, 'Minecraft', 'Dancer', '2026-05-20', '1020200202', 'Purok 2', 15, '2026-05-15 01:35:49', '2026-05-15 01:35:49'),
(17, 'jungkook', 'BTS', '2026-05-15', '09166144943', 'Purok 1', 21, '2026-05-15 03:28:17', '2026-05-15 03:28:17'),
(18, 'Stella Rose', 'Tiangco', '2040-10-28', '639 051 68 23 67 00254', 'Purok 2', 29, '2026-05-15 04:45:30', '2026-05-15 04:45:30'),
(19, 'Nenita', 'Rey', '2002-01-15', '09954630295', 'Purok 1', 69, '2026-05-15 09:13:21', '2026-05-15 09:13:21'),
(21, 'Ferrie', 'Blanza', '2026-09-21', '09785674321', 'Purok 2', 0, '2026-09-21 15:37:47', '2026-09-22 03:15:09'),
(22, 'weh', 'GrpTwo', '2026-09-22', '09171234567', 'Purok 1', 0, '2026-09-22 20:22:48', '2026-09-22 20:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `citizen_activity_logs`
--

CREATE TABLE `citizen_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL DEFAULT 'citizen',
  `citizen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `citizen_activity_logs`
--

INSERT INTO `citizen_activity_logs` (`id`, `user_id`, `action`, `module`, `citizen_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'create', 'citizen', 1, 'Added new citizen', '2026-05-05 02:45:44', '2026-05-05 02:45:44'),
(2, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-05 02:45:48', '2026-05-05 02:45:48'),
(3, 1, 'update', 'citizen', 1, 'Updated citizen details', '2026-05-05 02:46:02', '2026-05-05 02:46:02'),
(4, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-05 02:46:02', '2026-05-05 02:46:02'),
(5, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-05 02:46:33', '2026-05-05 02:46:33'),
(6, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-05 03:15:02', '2026-05-05 03:15:02'),
(7, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-05 03:17:44', '2026-05-05 03:17:44'),
(8, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-05 03:21:54', '2026-05-05 03:21:54'),
(9, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-05 03:22:18', '2026-05-05 03:22:18'),
(10, 1, 'create', 'citizen', 2, 'Added new citizen', '2026-05-05 03:24:20', '2026-05-05 03:24:20'),
(11, 1, 'create', 'citizen', 3, 'Added new citizen', '2026-05-05 03:24:40', '2026-05-05 03:24:40'),
(12, 1, 'create', 'citizen', 4, 'Added new citizen', '2026-05-05 03:24:56', '2026-05-05 03:24:56'),
(13, 1, 'create', 'citizen', 5, 'Added new citizen', '2026-05-05 03:25:13', '2026-05-05 03:25:13'),
(14, 1, 'create', 'citizen', 6, 'Added new citizen', '2026-05-05 03:26:15', '2026-05-05 03:26:15'),
(15, 1, 'create', 'citizen', 7, 'Added new citizen', '2026-05-05 03:26:50', '2026-05-05 03:26:50'),
(16, 1, 'create', 'citizen', 8, 'Added new citizen', '2026-05-05 03:27:10', '2026-05-05 03:27:10'),
(17, 1, 'create', 'citizen', 9, 'Added new citizen', '2026-05-05 03:27:26', '2026-05-05 03:27:26'),
(18, 1, 'create', 'citizen', 10, 'Added new citizen', '2026-05-05 03:27:56', '2026-05-05 03:27:56'),
(19, 1, 'create', 'citizen', 11, 'Added new citizen', '2026-05-05 03:28:14', '2026-05-05 03:28:14'),
(20, 1, 'view', 'citizen', 4, 'Viewed citizen health record', '2026-05-05 04:30:51', '2026-05-05 04:30:51'),
(21, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-05 04:31:37', '2026-05-05 04:31:37'),
(22, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-05 04:31:46', '2026-05-05 04:31:46'),
(23, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-05 06:34:59', '2026-05-05 06:34:59'),
(24, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-05 06:35:10', '2026-05-05 06:35:10'),
(25, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 16:02:00', '2026-05-06 16:02:00'),
(26, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 16:02:55', '2026-05-06 16:02:55'),
(27, 1, 'view', 'citizen', 3, 'Viewed citizen health record', '2026-05-06 16:03:38', '2026-05-06 16:03:38'),
(28, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 16:03:43', '2026-05-06 16:03:43'),
(29, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-06 16:03:47', '2026-05-06 16:03:47'),
(30, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 16:03:54', '2026-05-06 16:03:54'),
(31, 1, 'create', 'citizen', 12, 'Added new citizen', '2026-05-06 16:06:48', '2026-05-06 16:06:48'),
(32, 1, 'view', 'citizen', 12, 'Viewed citizen health record', '2026-05-06 16:08:52', '2026-05-06 16:08:52'),
(33, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 16:53:02', '2026-05-06 16:53:02'),
(34, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-06 16:53:05', '2026-05-06 16:53:05'),
(35, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-06 16:53:06', '2026-05-06 16:53:06'),
(36, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-06 16:56:40', '2026-05-06 16:56:40'),
(37, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 16:56:50', '2026-05-06 16:56:50'),
(38, 1, 'view', 'citizen', 4, 'Viewed citizen health record', '2026-05-06 17:07:42', '2026-05-06 17:07:42'),
(39, 1, 'view', 'citizen', 4, 'Viewed citizen health record', '2026-05-06 17:08:14', '2026-05-06 17:08:14'),
(40, 1, 'view', 'citizen', 4, 'Viewed citizen health record', '2026-05-06 17:08:26', '2026-05-06 17:08:26'),
(41, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-06 17:08:42', '2026-05-06 17:08:42'),
(42, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-06 17:08:48', '2026-05-06 17:08:48'),
(43, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-07 14:39:42', '2026-05-07 14:39:42'),
(44, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-07 21:12:33', '2026-05-07 21:12:33'),
(45, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-08 00:46:34', '2026-05-08 00:46:34'),
(46, 1, 'view', 'citizen', 2, 'Viewed citizen health record', '2026-05-08 00:46:37', '2026-05-08 00:46:37'),
(47, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-08 00:46:41', '2026-05-08 00:46:41'),
(48, 1, 'view', 'citizen', 5, 'Viewed citizen health record', '2026-05-08 00:46:46', '2026-05-08 00:46:46'),
(49, 1, 'create', 'citizen', 13, 'Added new citizen', '2026-05-08 00:47:20', '2026-05-08 00:47:20'),
(50, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-08 00:48:17', '2026-05-08 00:48:17'),
(51, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-08 16:20:59', '2026-05-08 16:20:59'),
(52, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 15:10:13', '2026-05-14 15:10:13'),
(53, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 15:10:22', '2026-05-14 15:10:22'),
(54, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 21:31:06', '2026-05-14 21:31:06'),
(55, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 21:44:02', '2026-05-14 21:44:02'),
(56, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 21:46:23', '2026-05-14 21:46:23'),
(57, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 21:59:49', '2026-05-14 21:59:49'),
(58, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 22:00:13', '2026-05-14 22:00:13'),
(59, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 22:01:45', '2026-05-14 22:01:45'),
(60, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 22:11:32', '2026-05-14 22:11:32'),
(61, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 22:17:09', '2026-05-14 22:17:09'),
(62, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 22:17:15', '2026-05-14 22:17:15'),
(63, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 22:26:37', '2026-05-14 22:26:37'),
(64, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 22:27:06', '2026-05-14 22:27:06'),
(65, 1, 'view', 'citizen', 13, 'Viewed citizen details page', '2026-05-14 22:27:41', '2026-05-14 22:27:41'),
(66, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-14 22:42:42', '2026-05-14 22:42:42'),
(67, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-14 22:43:38', '2026-05-14 22:43:38'),
(68, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 00:15:25', '2026-05-15 00:15:25'),
(69, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 00:19:02', '2026-05-15 00:19:02'),
(70, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 00:32:39', '2026-05-15 00:32:39'),
(71, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 00:32:58', '2026-05-15 00:32:58'),
(72, 1, 'create', 'citizen', 14, 'Added new citizen', '2026-05-15 00:33:49', '2026-05-15 00:33:49'),
(73, 1, 'view', 'citizen', 14, 'Viewed citizen details page', '2026-05-15 00:34:12', '2026-05-15 00:34:12'),
(74, 1, 'create', 'citizen', 15, 'Added new citizen', '2026-05-15 00:52:31', '2026-05-15 00:52:31'),
(75, 1, 'create', 'citizen', 16, 'Added new citizen', '2026-05-15 01:35:49', '2026-05-15 01:35:49'),
(76, 1, 'view', 'citizen', 2, 'Viewed citizen details page', '2026-05-15 01:36:07', '2026-05-15 01:36:07'),
(77, 1, 'view', 'citizen', 2, 'Viewed citizen details page', '2026-05-15 01:36:07', '2026-05-15 01:36:07'),
(78, 1, 'view', 'citizen', 12, 'Viewed citizen health record', '2026-05-15 01:36:32', '2026-05-15 01:36:32'),
(79, 1, 'view', 'citizen', 12, 'Viewed citizen health record', '2026-05-15 01:36:33', '2026-05-15 01:36:33'),
(80, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 01:49:16', '2026-05-15 01:49:16'),
(81, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 01:49:16', '2026-05-15 01:49:16'),
(82, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 02:09:34', '2026-05-15 02:09:34'),
(83, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 02:09:36', '2026-05-15 02:09:36'),
(84, 1, 'view', 'citizen', 7, 'Viewed citizen details page', '2026-05-15 02:09:43', '2026-05-15 02:09:43'),
(85, 1, 'view', 'citizen', 7, 'Viewed citizen details page', '2026-05-15 02:09:46', '2026-05-15 02:09:46'),
(86, 1, 'create', 'citizen', 17, 'Added new citizen', '2026-05-15 03:28:17', '2026-05-15 03:28:17'),
(87, 1, 'view', 'citizen', 2, 'Viewed citizen details page', '2026-05-15 04:42:44', '2026-05-15 04:42:44'),
(88, 1, 'create', 'citizen', 18, 'Added new citizen', '2026-05-15 04:45:30', '2026-05-15 04:45:30'),
(89, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 04:46:01', '2026-05-15 04:46:01'),
(90, 1, 'view', 'citizen', 18, 'Viewed citizen details page', '2026-05-15 04:59:37', '2026-05-15 04:59:37'),
(91, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 05:16:35', '2026-05-15 05:16:35'),
(92, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 05:58:18', '2026-05-15 05:58:18'),
(93, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:00:55', '2026-05-15 06:00:55'),
(94, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:02:10', '2026-05-15 06:02:10'),
(95, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:02:15', '2026-05-15 06:02:15'),
(96, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:03:26', '2026-05-15 06:03:26'),
(97, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:03:36', '2026-05-15 06:03:36'),
(98, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:04:57', '2026-05-15 06:04:57'),
(99, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:06:24', '2026-05-15 06:06:24'),
(100, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:07:40', '2026-05-15 06:07:40'),
(101, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:07:42', '2026-05-15 06:07:42'),
(102, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:07:54', '2026-05-15 06:07:54'),
(103, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:08:36', '2026-05-15 06:08:36'),
(104, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:08:40', '2026-05-15 06:08:40'),
(105, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 06:36:01', '2026-05-15 06:36:01'),
(106, 1, 'delete', 'citizen', 2, 'Deleted citizen', '2026-05-15 09:12:24', '2026-05-15 09:12:24'),
(107, 1, 'delete', 'citizen', 3, 'Deleted citizen', '2026-05-15 09:12:27', '2026-05-15 09:12:27'),
(108, 1, 'delete', 'citizen', 4, 'Deleted citizen', '2026-05-15 09:12:31', '2026-05-15 09:12:31'),
(109, 1, 'delete', 'citizen', 5, 'Deleted citizen', '2026-05-15 09:12:35', '2026-05-15 09:12:35'),
(110, 1, 'delete', 'citizen', 6, 'Deleted citizen', '2026-05-15 09:12:38', '2026-05-15 09:12:38'),
(111, 1, 'delete', 'citizen', 7, 'Deleted citizen', '2026-05-15 09:12:40', '2026-05-15 09:12:40'),
(112, 1, 'delete', 'citizen', 8, 'Deleted citizen', '2026-05-15 09:12:42', '2026-05-15 09:12:42'),
(113, 1, 'delete', 'citizen', 9, 'Deleted citizen', '2026-05-15 09:12:44', '2026-05-15 09:12:44'),
(114, 1, 'create', 'citizen', 19, 'Added new citizen', '2026-05-15 09:13:21', '2026-05-15 09:13:21'),
(115, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 10:00:24', '2026-05-15 10:00:24'),
(116, 1, 'create', 'citizen', 20, 'Added new citizen', '2026-05-15 10:11:11', '2026-05-15 10:11:11'),
(117, 1, 'view', 'citizen', 20, 'Viewed citizen details page', '2026-05-15 10:11:22', '2026-05-15 10:11:22'),
(118, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 11:22:54', '2026-05-15 11:22:54'),
(119, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 12:33:56', '2026-05-15 12:33:56'),
(120, 1, 'update', 'citizen', 1, 'Updated citizen details', '2026-05-15 12:34:29', '2026-05-15 12:34:29'),
(121, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-15 12:34:29', '2026-05-15 12:34:29'),
(122, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 12:35:23', '2026-05-15 12:35:23'),
(123, 1, 'view', 'citizen', 1, 'Viewed citizen health record', '2026-05-15 12:35:43', '2026-05-15 12:35:43'),
(124, 1, 'view', 'citizen', 1, 'Viewed citizen details page', '2026-05-26 10:59:50', '2026-05-26 10:59:50'),
(125, 1, 'delete', 'citizen', 14, 'Deleted citizen', '2026-05-29 10:43:00', '2026-05-29 10:43:00'),
(126, 1, 'delete', 'citizen', 1, 'Deleted citizen', '2026-05-29 10:43:06', '2026-05-29 10:43:06'),
(127, 1, 'delete', 'citizen', 13, 'Deleted citizen', '2026-05-29 10:44:10', '2026-05-29 10:44:10'),
(128, 1, 'delete', 'citizen', 20, 'Deleted citizen', '2026-05-29 10:44:21', '2026-05-29 10:44:21'),
(129, 1, 'view', 'citizen', 10, 'Viewed citizen details page', '2026-06-13 15:38:50', '2026-06-13 15:38:50'),
(130, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-14 13:48:56', '2026-09-14 13:48:56'),
(131, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-21 15:34:23', '2026-09-21 15:34:23'),
(132, 1, 'create', 'citizen', 21, 'Added new citizen', '2026-09-21 15:37:47', '2026-09-21 15:37:47'),
(133, 1, 'view', 'citizen', 10, 'Viewed citizen details page', '2026-09-22 02:22:57', '2026-09-22 02:22:57'),
(134, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-22 02:29:46', '2026-09-22 02:29:46'),
(135, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-22 02:54:03', '2026-09-22 02:54:03'),
(136, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-22 02:54:13', '2026-09-22 02:54:13'),
(137, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-22 03:04:42', '2026-09-22 03:04:42'),
(138, 1, 'view', 'citizen', 12, 'Viewed citizen health record', '2026-09-22 03:13:03', '2026-09-22 03:13:03'),
(139, 1, 'view', 'citizen', 12, 'Viewed citizen health record', '2026-09-22 03:13:14', '2026-09-22 03:13:14'),
(140, 1, 'view', 'citizen', 10, 'Viewed citizen health record', '2026-09-22 03:13:37', '2026-09-22 03:13:37'),
(141, 1, 'view', 'citizen', 15, 'Viewed citizen health record', '2026-09-22 03:13:44', '2026-09-22 03:13:44'),
(142, 1, 'view', 'citizen', 15, 'Viewed citizen health record', '2026-09-22 03:13:57', '2026-09-22 03:13:57'),
(143, 1, 'view', 'citizen', 16, 'Viewed citizen health record', '2026-09-22 03:14:11', '2026-09-22 03:14:11'),
(144, 1, 'view', 'citizen', 16, 'Viewed citizen health record', '2026-09-22 03:14:21', '2026-09-22 03:14:21'),
(145, 1, 'view', 'citizen', 18, 'Viewed citizen health record', '2026-09-22 03:14:26', '2026-09-22 03:14:26'),
(146, 1, 'view', 'citizen', 18, 'Viewed citizen health record', '2026-09-22 03:14:36', '2026-09-22 03:14:36'),
(147, 1, 'view', 'citizen', 21, 'Viewed citizen details page', '2026-09-22 03:15:04', '2026-09-22 03:15:04'),
(148, 1, 'update', 'citizen', 21, 'Updated citizen details', '2026-09-22 03:15:09', '2026-09-22 03:15:09'),
(149, 1, 'view', 'citizen', 21, 'Viewed citizen details page', '2026-09-22 03:15:10', '2026-09-22 03:15:10'),
(150, 1, 'view', 'citizen', 21, 'Viewed citizen health record', '2026-09-22 03:15:18', '2026-09-22 03:15:18'),
(151, 1, 'view', 'citizen', 21, 'Viewed citizen health record', '2026-09-22 03:15:26', '2026-09-22 03:15:26'),
(152, 1, 'view', 'citizen', 17, 'Viewed citizen health record', '2026-09-22 03:15:31', '2026-09-22 03:15:31'),
(153, 1, 'view', 'citizen', 17, 'Viewed citizen health record', '2026-09-22 03:15:40', '2026-09-22 03:15:40'),
(154, 1, 'create', 'citizen', 22, 'Added new citizen', '2026-09-22 20:22:48', '2026-09-22 20:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `start` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `time` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `announcement_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `start`, `created_at`, `updated_at`, `time`, `description`, `announcement_id`) VALUES
(1, 'Career Day', '2026-05-06', '2026-05-05 02:49:05', '2026-05-05 02:49:05', NULL, NULL, NULL),
(2, 'ASD', '2026-05-13', '2026-05-14 10:38:15', '2026-05-14 10:38:15', NULL, NULL, NULL),
(4, 'LAST DAY OF SCHOOL', '2026-05-15', '2026-05-14 16:21:58', '2026-05-14 16:21:58', NULL, NULL, NULL),
(5, 'SE PRESENTATION', '2026-05-15', '2026-05-14 16:22:24', '2026-05-14 16:22:24', NULL, NULL, NULL),
(6, 'HIV AWARENESS', '2026-05-27', '2026-05-15 00:14:37', '2026-05-15 00:14:37', NULL, NULL, NULL),
(7, 'HIV AWARENESS', '2026-05-27', '2026-05-15 00:14:37', '2026-05-15 00:14:37', NULL, NULL, NULL),
(8, 'HIB AWARENESSS', '2026-05-27', '2026-05-15 01:36:49', '2026-05-15 01:36:49', NULL, NULL, NULL),
(9, 'YAMAHA AUDIO', '2026-05-19', '2026-05-15 12:37:45', '2026-05-15 12:37:45', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `citizen_id` bigint(20) UNSIGNED NOT NULL,
  `diagnosis` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`id`, `citizen_id`, `diagnosis`, `created_at`, `updated_at`, `record_date`, `comments`) VALUES
(6, 10, 'Trangkaso', '2026-09-22 02:54:13', '2026-09-22 02:54:13', NULL, NULL),
(7, 12, 'Not sure', '2026-09-22 03:13:14', '2026-09-22 03:13:14', NULL, NULL),
(8, 15, 'Trangkaso', '2026-09-22 03:13:56', '2026-09-22 03:13:56', NULL, NULL),
(9, 16, 'trangkaso', '2026-09-22 03:14:21', '2026-09-22 03:14:21', NULL, NULL),
(10, 18, 'Trangkaso', '2026-09-22 03:14:35', '2026-09-22 03:14:35', NULL, NULL),
(11, 21, 'Trangkaso', '2026-09-22 03:15:25', '2026-09-22 03:15:25', NULL, NULL),
(12, 17, 'Die hard fan', '2026-09-22 03:15:39', '2026-09-22 03:15:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `health_record_activity_logs`
--

CREATE TABLE `health_record_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `citizen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `health_record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_record_activity_logs`
--

INSERT INTO `health_record_activity_logs` (`id`, `user_id`, `action`, `citizen_id`, `health_record_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 02:46:09', '2026-05-05 02:46:09'),
(2, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 02:46:40', '2026-05-05 02:46:40'),
(3, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 02:52:11', '2026-05-05 02:52:11'),
(4, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 02:52:29', '2026-05-05 02:52:29'),
(5, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:06:24', '2026-05-05 03:06:24'),
(6, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:14:57', '2026-05-05 03:14:57'),
(7, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-05 03:15:02', '2026-05-05 03:15:02'),
(8, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-05 03:17:44', '2026-05-05 03:17:44'),
(9, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:17:46', '2026-05-05 03:17:46'),
(10, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:17:50', '2026-05-05 03:17:50'),
(11, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:21:49', '2026-05-05 03:21:49'),
(12, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-05 03:21:54', '2026-05-05 03:21:54'),
(13, 1, 'create', 1, 1, 'Added health record: Rhinitis', '2026-05-05 03:22:18', '2026-05-05 03:22:18'),
(14, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-05 03:22:18', '2026-05-05 03:22:18'),
(15, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:22:20', '2026-05-05 03:22:20'),
(16, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:29:07', '2026-05-05 03:29:07'),
(17, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:32:18', '2026-05-05 03:32:18'),
(18, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:33:26', '2026-05-05 03:33:26'),
(19, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:33:39', '2026-05-05 03:33:39'),
(20, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 03:33:44', '2026-05-05 03:33:44'),
(21, 1, 'view', 4, NULL, 'Viewed health records of citizen ID 4', '2026-05-05 04:30:51', '2026-05-05 04:30:51'),
(22, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:30:56', '2026-05-05 04:30:56'),
(23, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-05 04:31:37', '2026-05-05 04:31:37'),
(24, 1, 'create', 1, 2, 'Added health record: Sakit Ulo', '2026-05-05 04:31:45', '2026-05-05 04:31:45'),
(25, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-05 04:31:46', '2026-05-05 04:31:46'),
(26, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:31:47', '2026-05-05 04:31:47'),
(27, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:19', '2026-05-05 04:33:19'),
(28, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:22', '2026-05-05 04:33:22'),
(29, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:23', '2026-05-05 04:33:23'),
(30, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:26', '2026-05-05 04:33:26'),
(31, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:29', '2026-05-05 04:33:29'),
(32, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:31', '2026-05-05 04:33:31'),
(33, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:33', '2026-05-05 04:33:33'),
(34, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:37', '2026-05-05 04:33:37'),
(35, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 04:33:42', '2026-05-05 04:33:42'),
(36, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:12:57', '2026-05-05 06:12:57'),
(37, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:31:10', '2026-05-05 06:31:10'),
(38, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:31:13', '2026-05-05 06:31:13'),
(39, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:34:39', '2026-05-05 06:34:39'),
(40, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:34:45', '2026-05-05 06:34:45'),
(41, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:34:47', '2026-05-05 06:34:47'),
(42, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-05 06:34:59', '2026-05-05 06:34:59'),
(43, 1, 'create', 2, 3, 'Added health record: Sakit Ulo', '2026-05-05 06:35:10', '2026-05-05 06:35:10'),
(44, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-05 06:35:10', '2026-05-05 06:35:10'),
(45, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:35:12', '2026-05-05 06:35:12'),
(46, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:46:01', '2026-05-05 06:46:01'),
(47, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 06:46:29', '2026-05-05 06:46:29'),
(48, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-05 08:13:44', '2026-05-05 08:13:44'),
(49, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 05:33:58', '2026-05-06 05:33:58'),
(50, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 08:51:24', '2026-05-06 08:51:24'),
(51, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:01:56', '2026-05-06 16:01:56'),
(52, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 16:02:00', '2026-05-06 16:02:00'),
(53, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:02:51', '2026-05-06 16:02:51'),
(54, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 16:02:55', '2026-05-06 16:02:55'),
(55, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:03:03', '2026-05-06 16:03:03'),
(56, 1, 'view', 3, NULL, 'Viewed health records of citizen ID 3', '2026-05-06 16:03:38', '2026-05-06 16:03:38'),
(57, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:03:41', '2026-05-06 16:03:41'),
(58, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 16:03:43', '2026-05-06 16:03:43'),
(59, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-06 16:03:47', '2026-05-06 16:03:47'),
(60, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:03:49', '2026-05-06 16:03:49'),
(61, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 16:03:54', '2026-05-06 16:03:54'),
(62, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:04:08', '2026-05-06 16:04:08'),
(63, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:08:45', '2026-05-06 16:08:45'),
(64, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:08:49', '2026-05-06 16:08:49'),
(65, 1, 'view', 12, NULL, 'Viewed health records of citizen ID 12', '2026-05-06 16:08:52', '2026-05-06 16:08:52'),
(66, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:09:13', '2026-05-06 16:09:13'),
(67, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:23:15', '2026-05-06 16:23:15'),
(68, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:53:00', '2026-05-06 16:53:00'),
(69, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 16:53:02', '2026-05-06 16:53:02'),
(70, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-06 16:53:05', '2026-05-06 16:53:05'),
(71, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-06 16:53:06', '2026-05-06 16:53:06'),
(72, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-06 16:56:40', '2026-05-06 16:56:40'),
(73, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:56:46', '2026-05-06 16:56:46'),
(74, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 16:56:50', '2026-05-06 16:56:50'),
(75, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 16:58:02', '2026-05-06 16:58:02'),
(76, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 17:07:36', '2026-05-06 17:07:36'),
(77, 1, 'view', 4, NULL, 'Viewed health records of citizen ID 4', '2026-05-06 17:07:42', '2026-05-06 17:07:42'),
(78, 1, 'create', 4, 4, 'Added health record: Cough', '2026-05-06 17:08:13', '2026-05-06 17:08:13'),
(79, 1, 'view', 4, NULL, 'Viewed health records of citizen ID 4', '2026-05-06 17:08:14', '2026-05-06 17:08:14'),
(80, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 17:08:17', '2026-05-06 17:08:17'),
(81, 1, 'view', 4, NULL, 'Viewed health records of citizen ID 4', '2026-05-06 17:08:26', '2026-05-06 17:08:26'),
(82, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-06 17:08:29', '2026-05-06 17:08:29'),
(83, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-06 17:08:42', '2026-05-06 17:08:42'),
(84, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-06 17:08:48', '2026-05-06 17:08:48'),
(85, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-07 14:53:26', '2026-05-07 14:53:26'),
(86, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-07 14:53:27', '2026-05-07 14:53:27'),
(87, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-07 21:12:30', '2026-05-07 21:12:30'),
(88, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-07 21:12:33', '2026-05-07 21:12:33'),
(89, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-07 21:12:41', '2026-05-07 21:12:41'),
(90, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-08 00:46:31', '2026-05-08 00:46:31'),
(91, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-08 00:46:34', '2026-05-08 00:46:34'),
(92, 1, 'view', 2, NULL, 'Viewed health records of citizen ID 2', '2026-05-08 00:46:37', '2026-05-08 00:46:37'),
(93, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-08 00:46:41', '2026-05-08 00:46:41'),
(94, 1, 'view', 5, NULL, 'Viewed health records of citizen ID 5', '2026-05-08 00:46:46', '2026-05-08 00:46:46'),
(95, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-08 00:48:12', '2026-05-08 00:48:12'),
(96, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-08 00:48:17', '2026-05-08 00:48:17'),
(97, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-08 16:20:56', '2026-05-08 16:20:56'),
(98, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-08 16:20:59', '2026-05-08 16:20:59'),
(99, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-11 19:58:05', '2026-05-11 19:58:05'),
(100, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 15:10:19', '2026-05-14 15:10:19'),
(101, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 15:10:22', '2026-05-14 15:10:22'),
(102, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 15:10:27', '2026-05-14 15:10:27'),
(103, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 15:53:47', '2026-05-14 15:53:47'),
(104, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 16:16:54', '2026-05-14 16:16:54'),
(105, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 16:21:31', '2026-05-14 16:21:31'),
(106, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 21:30:57', '2026-05-14 21:30:57'),
(107, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 21:31:06', '2026-05-14 21:31:06'),
(108, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 21:31:09', '2026-05-14 21:31:09'),
(109, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 21:46:07', '2026-05-14 21:46:07'),
(110, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 21:46:23', '2026-05-14 21:46:23'),
(111, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 21:46:41', '2026-05-14 21:46:41'),
(112, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 21:46:52', '2026-05-14 21:46:52'),
(113, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 21:52:27', '2026-05-14 21:52:27'),
(114, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:01:35', '2026-05-14 22:01:35'),
(115, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 22:01:45', '2026-05-14 22:01:45'),
(116, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:01:56', '2026-05-14 22:01:56'),
(117, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:11:14', '2026-05-14 22:11:14'),
(118, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:11:29', '2026-05-14 22:11:29'),
(119, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 22:11:32', '2026-05-14 22:11:32'),
(120, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:17:14', '2026-05-14 22:17:14'),
(121, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 22:17:15', '2026-05-14 22:17:15'),
(122, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:33:34', '2026-05-14 22:33:34'),
(123, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:41:29', '2026-05-14 22:41:29'),
(124, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:42:03', '2026-05-14 22:42:03'),
(125, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:42:11', '2026-05-14 22:42:11'),
(126, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:43:34', '2026-05-14 22:43:34'),
(127, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-14 22:43:38', '2026-05-14 22:43:38'),
(128, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:43:52', '2026-05-14 22:43:52'),
(129, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-14 22:43:54', '2026-05-14 22:43:54'),
(130, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:15:20', '2026-05-15 00:15:20'),
(131, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 00:15:25', '2026-05-15 00:15:25'),
(132, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:15:53', '2026-05-15 00:15:53'),
(133, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:18:21', '2026-05-15 00:18:21'),
(134, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 00:19:02', '2026-05-15 00:19:02'),
(135, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:19:34', '2026-05-15 00:19:34'),
(136, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:20:28', '2026-05-15 00:20:28'),
(137, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:21:27', '2026-05-15 00:21:27'),
(138, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:25:09', '2026-05-15 00:25:09'),
(139, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:30:01', '2026-05-15 00:30:01'),
(140, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:32:49', '2026-05-15 00:32:49'),
(141, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 00:34:22', '2026-05-15 00:34:22'),
(142, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:35:59', '2026-05-15 01:35:59'),
(143, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:00', '2026-05-15 01:36:00'),
(144, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:11', '2026-05-15 01:36:11'),
(145, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:11', '2026-05-15 01:36:11'),
(146, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:12', '2026-05-15 01:36:12'),
(147, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:13', '2026-05-15 01:36:13'),
(148, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:14', '2026-05-15 01:36:14'),
(149, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:15', '2026-05-15 01:36:15'),
(150, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:19', '2026-05-15 01:36:19'),
(151, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:36:20', '2026-05-15 01:36:20'),
(152, 1, 'view', 12, NULL, 'Viewed health records of citizen ID 12', '2026-05-15 01:36:32', '2026-05-15 01:36:32'),
(153, 1, 'view', 12, NULL, 'Viewed health records of citizen ID 12', '2026-05-15 01:36:33', '2026-05-15 01:36:33'),
(154, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:45:40', '2026-05-15 01:45:40'),
(155, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:45:41', '2026-05-15 01:45:41'),
(156, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:45:53', '2026-05-15 01:45:53'),
(157, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:45:53', '2026-05-15 01:45:53'),
(158, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:01', '2026-05-15 01:47:01'),
(159, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:02', '2026-05-15 01:47:02'),
(160, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:03', '2026-05-15 01:47:03'),
(161, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:04', '2026-05-15 01:47:04'),
(162, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:41', '2026-05-15 01:47:41'),
(163, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:41', '2026-05-15 01:47:41'),
(164, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:51', '2026-05-15 01:47:51'),
(165, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:52', '2026-05-15 01:47:52'),
(166, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:54', '2026-05-15 01:47:54'),
(167, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:47:55', '2026-05-15 01:47:55'),
(168, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:48:00', '2026-05-15 01:48:00'),
(169, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:48:00', '2026-05-15 01:48:00'),
(170, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:48:28', '2026-05-15 01:48:28'),
(171, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:48:28', '2026-05-15 01:48:28'),
(172, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:58:07', '2026-05-15 01:58:07'),
(173, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:58:08', '2026-05-15 01:58:08'),
(174, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:59:55', '2026-05-15 01:59:55'),
(175, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:59:56', '2026-05-15 01:59:56'),
(176, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:59:57', '2026-05-15 01:59:57'),
(177, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 01:59:58', '2026-05-15 01:59:58'),
(178, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:05:16', '2026-05-15 02:05:16'),
(179, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:05:18', '2026-05-15 02:05:18'),
(180, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:05:37', '2026-05-15 02:05:37'),
(181, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:05:39', '2026-05-15 02:05:39'),
(182, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:08:21', '2026-05-15 02:08:21'),
(183, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:10:09', '2026-05-15 02:10:09'),
(184, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:10:09', '2026-05-15 02:10:09'),
(185, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:10:28', '2026-05-15 02:10:28'),
(186, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:10:29', '2026-05-15 02:10:29'),
(187, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:14:06', '2026-05-15 02:14:06'),
(188, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:14:06', '2026-05-15 02:14:06'),
(189, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:14:42', '2026-05-15 02:14:42'),
(190, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:14:43', '2026-05-15 02:14:43'),
(191, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:14:45', '2026-05-15 02:14:45'),
(192, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:14:46', '2026-05-15 02:14:46'),
(193, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:02', '2026-05-15 02:15:02'),
(194, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:03', '2026-05-15 02:15:03'),
(195, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:03', '2026-05-15 02:15:03'),
(196, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:03', '2026-05-15 02:15:03'),
(197, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:26', '2026-05-15 02:15:26'),
(198, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:27', '2026-05-15 02:15:27'),
(199, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:47', '2026-05-15 02:15:47'),
(200, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:15:47', '2026-05-15 02:15:47'),
(201, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:28:53', '2026-05-15 02:28:53'),
(202, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:29:04', '2026-05-15 02:29:04'),
(203, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:30:53', '2026-05-15 02:30:53'),
(204, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 02:30:54', '2026-05-15 02:30:54'),
(205, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 04:06:10', '2026-05-15 04:06:10'),
(206, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 04:45:54', '2026-05-15 04:45:54'),
(207, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 04:45:58', '2026-05-15 04:45:58'),
(208, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 04:46:01', '2026-05-15 04:46:01'),
(209, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 04:48:37', '2026-05-15 04:48:37'),
(210, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 04:59:46', '2026-05-15 04:59:46'),
(211, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 05:02:31', '2026-05-15 05:02:31'),
(212, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 05:16:35', '2026-05-15 05:16:35'),
(213, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 05:17:34', '2026-05-15 05:17:34'),
(214, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 05:19:51', '2026-05-15 05:19:51'),
(215, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 05:27:54', '2026-05-15 05:27:54'),
(216, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 05:27:57', '2026-05-15 05:27:57'),
(217, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 05:58:17', '2026-05-15 05:58:17'),
(218, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 05:58:18', '2026-05-15 05:58:18'),
(219, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:00:55', '2026-05-15 06:00:55'),
(220, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:02:10', '2026-05-15 06:02:10'),
(221, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:02:12', '2026-05-15 06:02:12'),
(222, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:02:15', '2026-05-15 06:02:15'),
(223, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:03:26', '2026-05-15 06:03:26'),
(224, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:03:29', '2026-05-15 06:03:29'),
(225, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:03:36', '2026-05-15 06:03:36'),
(226, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:04:57', '2026-05-15 06:04:57'),
(227, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:05:00', '2026-05-15 06:05:00'),
(228, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:06:23', '2026-05-15 06:06:23'),
(229, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:06:24', '2026-05-15 06:06:24'),
(230, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:07:40', '2026-05-15 06:07:40'),
(231, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:07:42', '2026-05-15 06:07:42'),
(232, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:07:52', '2026-05-15 06:07:52'),
(233, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:07:54', '2026-05-15 06:07:54'),
(234, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:08:36', '2026-05-15 06:08:36'),
(235, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:08:38', '2026-05-15 06:08:38'),
(236, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:08:40', '2026-05-15 06:08:40'),
(237, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:35:46', '2026-05-15 06:35:46'),
(238, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 06:35:49', '2026-05-15 06:35:49'),
(239, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 06:36:01', '2026-05-15 06:36:01'),
(240, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 07:19:59', '2026-05-15 07:19:59'),
(241, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 09:13:26', '2026-05-15 09:13:26'),
(242, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 09:13:29', '2026-05-15 09:13:29'),
(243, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 09:13:35', '2026-05-15 09:13:35'),
(244, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 09:13:38', '2026-05-15 09:13:38'),
(245, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 09:20:22', '2026-05-15 09:20:22'),
(246, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:00:08', '2026-05-15 10:00:08'),
(247, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 10:00:24', '2026-05-15 10:00:24'),
(248, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:00:40', '2026-05-15 10:00:40'),
(249, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:00:54', '2026-05-15 10:00:54'),
(250, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:00:55', '2026-05-15 10:00:55'),
(251, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:00:58', '2026-05-15 10:00:58'),
(252, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:10:14', '2026-05-15 10:10:14'),
(253, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:12:37', '2026-05-15 10:12:37'),
(254, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:12:58', '2026-05-15 10:12:58'),
(255, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:13:00', '2026-05-15 10:13:00'),
(256, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:13:08', '2026-05-15 10:13:08'),
(257, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:13:32', '2026-05-15 10:13:32'),
(258, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:13:36', '2026-05-15 10:13:36'),
(259, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:28:24', '2026-05-15 10:28:24'),
(260, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:30:46', '2026-05-15 10:30:46'),
(261, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:47:49', '2026-05-15 10:47:49'),
(262, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 10:55:01', '2026-05-15 10:55:01'),
(263, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 11:21:27', '2026-05-15 11:21:27'),
(264, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 11:22:51', '2026-05-15 11:22:51'),
(265, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 11:22:54', '2026-05-15 11:22:54'),
(266, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 11:23:12', '2026-05-15 11:23:12'),
(267, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 12:34:56', '2026-05-15 12:34:56'),
(268, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 12:35:16', '2026-05-15 12:35:16'),
(269, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 12:35:23', '2026-05-15 12:35:23'),
(270, 1, 'view', 1, NULL, 'Viewed health records of citizen ID 1', '2026-05-15 12:35:43', '2026-05-15 12:35:43'),
(271, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-15 12:35:48', '2026-05-15 12:35:48'),
(272, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-26 10:59:22', '2026-05-26 10:59:22'),
(273, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-05-26 10:59:24', '2026-05-26 10:59:24'),
(274, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-06-02 19:50:09', '2026-06-02 19:50:09'),
(275, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-07-07 21:38:12', '2026-07-07 21:38:12'),
(276, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-07-07 21:52:33', '2026-07-07 21:52:33'),
(277, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-14 13:48:53', '2026-09-14 13:48:53'),
(278, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-14 13:48:56', '2026-09-14 13:48:56'),
(279, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-14 13:49:11', '2026-09-14 13:49:11'),
(280, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-17 16:42:11', '2026-09-17 16:42:11'),
(281, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-21 15:06:41', '2026-09-21 15:06:41'),
(282, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-21 15:34:19', '2026-09-21 15:34:19'),
(283, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-21 15:34:23', '2026-09-21 15:34:23'),
(284, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-21 22:33:55', '2026-09-21 22:33:55'),
(285, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-22 02:29:40', '2026-09-22 02:29:40'),
(286, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-22 02:29:46', '2026-09-22 02:29:46'),
(287, 1, 'view', NULL, NULL, 'Viewed health record dashboard', '2026-09-22 02:29:50', '2026-09-22 02:29:50'),
(288, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-22 02:54:03', '2026-09-22 02:54:03'),
(289, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-22 02:54:13', '2026-09-22 02:54:13'),
(290, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-22 03:04:42', '2026-09-22 03:04:42'),
(291, 1, 'view', 12, NULL, 'Viewed health records of citizen ID 12', '2026-09-22 03:13:03', '2026-09-22 03:13:03'),
(292, 1, 'view', 12, NULL, 'Viewed health records of citizen ID 12', '2026-09-22 03:13:14', '2026-09-22 03:13:14'),
(293, 1, 'view', 10, NULL, 'Viewed health records of citizen ID 10', '2026-09-22 03:13:37', '2026-09-22 03:13:37'),
(294, 1, 'view', 15, NULL, 'Viewed health records of citizen ID 15', '2026-09-22 03:13:44', '2026-09-22 03:13:44'),
(295, 1, 'view', 15, NULL, 'Viewed health records of citizen ID 15', '2026-09-22 03:13:57', '2026-09-22 03:13:57'),
(296, 1, 'view', 16, NULL, 'Viewed health records of citizen ID 16', '2026-09-22 03:14:11', '2026-09-22 03:14:11'),
(297, 1, 'view', 16, NULL, 'Viewed health records of citizen ID 16', '2026-09-22 03:14:21', '2026-09-22 03:14:21'),
(298, 1, 'view', 18, NULL, 'Viewed health records of citizen ID 18', '2026-09-22 03:14:26', '2026-09-22 03:14:26'),
(299, 1, 'view', 18, NULL, 'Viewed health records of citizen ID 18', '2026-09-22 03:14:36', '2026-09-22 03:14:36'),
(300, 1, 'view', 21, NULL, 'Viewed health records of citizen ID 21', '2026-09-22 03:15:18', '2026-09-22 03:15:18'),
(301, 1, 'view', 21, NULL, 'Viewed health records of citizen ID 21', '2026-09-22 03:15:26', '2026-09-22 03:15:26'),
(302, 1, 'view', 17, NULL, 'Viewed health records of citizen ID 17', '2026-09-22 03:15:31', '2026-09-22 03:15:31'),
(303, 1, 'view', 17, NULL, 'Viewed health records of citizen ID 17', '2026-09-22 03:15:40', '2026-09-22 03:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2014_10_12_100000_create_password_resets_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2026_04_27_024108_create_citizens_table', 1),
(7, '2026_04_27_061947_add_purok_to_citizens_table', 1),
(8, '2026_04_27_182204_create_supplies_table', 1),
(9, '2026_04_28_021743_create_health_records_table', 1),
(10, '2026_04_30_060040_add_fields_to_health_records_table', 1),
(11, '2026_04_30_071903_create_announcements_table', 1),
(12, '2026_05_01_071106_create_logs_table', 1),
(13, '2026_05_01_073016_create_supply_logs_table', 1),
(14, '2026_05_01_084806_create_activity_logs_table', 1),
(15, '2026_05_01_085644_create_citizen_activity_logs_table', 1),
(16, '2026_05_01_090543_create_health_record_activity_logs_table', 1),
(17, '2026_05_01_093047_add_user_id_to_citizen_activity_logs', 1),
(18, '2026_05_01_093108_add_user_id_to_health_record_activity_logs', 1),
(19, '2026_05_01_111429_create_events_table', 1),
(20, '2026_05_01_114133_add_details_to_events_table', 1),
(21, '2026_05_01_205858_add_role_to_users_table', 1),
(22, '2026_05_05_142150_create_vehicle_logs_table', 2),
(23, '2026_05_14_203925_create_referrals_table', 3),
(24, '2026_05_15_055109_create_vaccinations_table', 4),
(25, '2026_05_15_055124_create_vaccinations_table', 5),
(26, '2026_09_16_020533_add_details_to_supplies_table', 6),
(27, '2026_09_22_035232_create_audit_logs_table', 7),
(28, '2026_09_22_185353_create_page_views_table', 8),
(29, '2026_09_22_193214_add_browser_to_page_views_table', 9),
(30, '2026_09_22_195755_create_admin_activity_logs_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date_of_referral` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `requests_for` text NOT NULL,
  `vital_signs` text DEFAULT NULL,
  `treatment_given` text DEFAULT NULL,
  `medication_given` text DEFAULT NULL,
  `self_medication` text DEFAULT NULL,
  `maintenance_schedule` text DEFAULT NULL,
  `referred_by` varchar(255) NOT NULL,
  `status` enum('approved','released','returned') NOT NULL DEFAULT 'approved',
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `date_of_referral`, `name`, `age`, `gender`, `address`, `requests_for`, `vital_signs`, `treatment_given`, `medication_given`, `self_medication`, `maintenance_schedule`, `referred_by`, `status`, `file_path`, `created_at`, `updated_at`) VALUES
(1, '2026-05-14', 'Ferrie Blanza', 20, 'Male', '215, Pooc 1, Silang', 'CHECK UP', 'BP ASDADA', 'KATINKO', 'BIOGESIC', 'BIOGESIC', 'N/A', 'Yours Truly', 'approved', NULL, '2026-05-14 21:09:52', '2026-05-14 21:09:52'),
(2, '2026-05-14', 'Ferrie Blanza', 20, 'Male', '215, Pooc 1, Silang', 'CHECK UP', 'BP ASDADA', 'KATINKO', 'BIOGESIC', 'BIOGESIC', 'N/A', 'Yours Truly', 'approved', NULL, '2026-05-14 21:11:27', '2026-05-14 21:11:27'),
(3, '2026-05-14', 'Ferrie Blanza', 20, 'Male', '215, Pooc 1, Silang', 'CHECK UP', 'BP ASDADA', 'KATINKO', 'BIOGESIC', 'BIOGESIC', 'N/A', 'Yours Truly', 'returned', 'storage/referrals/referral_3_1778764365.pdf', '2026-05-14 21:12:45', '2026-05-15 01:37:28'),
(4, '2026-05-14', 'Vernz Arnold P. Malabad', 21, 'Male', 'silang cavite', 's', 's', 's', 's', 's', 's', 's', 'released', 'storage/referrals/referral_4_1778764975.pdf', '2026-05-14 21:22:55', '2026-05-14 21:23:33'),
(5, '2026-05-14', 'sam', 60, 'Female', 'cavite', 'aa', 'a', 'a', 'a', 'a', 'a', 'tom', 'released', NULL, '2026-05-14 22:13:04', '2026-05-15 01:37:15'),
(6, '1952-03-07', 'Stella Tiangco', 79, 'Female', 'Symphony St. Gracia Village', 'Colonoscopy', 'Unstable', 'Blood Pressure, ECG check in 1974', 'Buscopan', 'Matcha Barley', 'every Millenium', 'MR. Esteban Santos', 'released', NULL, '2026-05-15 05:05:56', '2026-05-15 07:24:30'),
(7, '2026-05-15', 'Ferrie Blanza', 20, 'Male', '215 Pooc 1', 'CONFINEMENT', '233232', 'N/A', 'VITAMINS', 'BIOGESIC', 'N/A', 'Vernz', 'approved', NULL, '2026-05-15 12:40:27', '2026-05-15 12:40:27');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1sTMnoauAQhQ9Xxgcgz3JBUhE4MSACmFKXjk0BWm', NULL, '66.249.69.65', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.8010.52 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUW8wZzUzYW9qWEpUSGtTU05SU0t0cWx1RlplUjBpNlhCTnVmVndGSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vd3d3LmJoZWFtcy5jb20iO3M6NToicm91dGUiO3M6NzoibGFuZGluZyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1790069840),
('PUzmsS8xqZZvQOLVqkkbyTL7SNBD4AM64M9CoYJf', NULL, '77.112.251.125', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjgzNVV1WGlYWDJ6aENSSE1Cb2ZBN3VLaG9taG1ucEY3a0d5UTAwbiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTg6Imh0dHBzOi8vYmhlYW1zLmNvbSI7czo1OiJyb3V0ZSI7czo3OiJsYW5kaW5nIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1790071440),
('RQ9OzEP5xHbjEoHURCPRfYhaf2QWFwPfrIOe8Pi5', NULL, '136.239.180.199', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid2NZYjUzU0c3ZWttWGdYSWtoOFU4UjB1VmE5MGFSQjVtSFBodTVxRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjQ6Imh0dHBzOi8vYmhlYW1zLmNvbS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1790079773);

-- --------------------------------------------------------

--
-- Table structure for table `supplies`
--

CREATE TABLE `supplies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `item_number` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `expiration_date` date DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplies`
--

INSERT INTO `supplies` (`id`, `name`, `item_number`, `serial_number`, `category`, `unit`, `quantity`, `min_stock`, `expiration_date`, `supplier`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Biogesic', NULL, NULL, 'Medicine', NULL, 55, 5, NULL, NULL, NULL, 'Available', '2026-05-05 02:59:54', '2026-05-15 11:20:13'),
(2, 'Neozep', NULL, NULL, 'Medicine', NULL, 10, 5, NULL, NULL, NULL, 'Available', '2026-05-06 08:51:58', '2026-09-16 00:55:31'),
(4, 'Benadryl', NULL, NULL, 'Medicine', NULL, 1, 5, NULL, NULL, NULL, 'Available', '2026-05-15 04:53:40', '2026-05-15 11:21:21'),
(5, 'Tuseran Forte', NULL, NULL, 'Medicine', NULL, 1, 5, NULL, NULL, NULL, 'Available', '2026-05-15 04:54:35', '2026-05-15 04:54:35'),
(6, 'Cetirizine', NULL, NULL, 'Medicine', NULL, 0, 5, NULL, NULL, NULL, 'Available', '2026-05-15 12:36:44', '2026-05-15 12:37:15'),
(7, 'Monte Lucas', NULL, NULL, 'Medicine', NULL, 0, 5, NULL, NULL, NULL, 'Out of Stock', '2026-09-16 03:29:53', '2026-09-16 03:29:53'),
(8, 'Monte Lucas', NULL, NULL, 'Medicine', NULL, 0, 5, NULL, NULL, NULL, 'Out of Stock', '2026-09-16 03:33:00', '2026-09-16 03:33:00'),
(9, 'Monte Lucas', '1', '1', 'Medicine', '1', 1, 5, '2030-08-01', NULL, NULL, 'Available', '2026-09-16 03:34:16', '2026-09-16 03:34:16'),
(11, 'Cherifer', NULL, NULL, 'Medicine', NULL, 0, 5, NULL, NULL, NULL, 'Out of Stock', '2026-09-16 03:36:06', '2026-09-16 03:36:06'),
(12, 'Solmux', NULL, NULL, 'Medicine', NULL, 0, 5, NULL, NULL, NULL, 'Out of Stock', '2026-09-16 03:38:55', '2026-09-16 03:38:55'),
(13, 'Monte Lucas', '5', '5', 'Medicine', '5', 44, 5, '2026-09-19', NULL, NULL, 'Available', '2026-09-16 03:48:00', '2026-09-16 03:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `supply_logs`
--

CREATE TABLE `supply_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `supply_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `citizen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supply_logs`
--

INSERT INTO `supply_logs` (`id`, `action`, `supply_id`, `quantity`, `user_id`, `citizen_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'release', 1, 46, 1, NULL, 'Test', '2026-05-05 03:05:39', '2026-05-05 03:05:39'),
(2, 'deposit', 1, 50, 1, NULL, NULL, '2026-05-05 06:29:59', '2026-05-05 06:29:59'),
(3, 'deposit', 1, 7, 1, NULL, NULL, '2026-05-14 15:53:02', '2026-05-14 15:53:02'),
(9, 'release', 1, 5, 1, NULL, 'Tygg', '2026-05-15 00:20:23', '2026-05-15 00:20:23'),
(10, 'deposit', 1, 9999, 1, NULL, NULL, '2026-05-15 04:47:52', '2026-05-15 04:47:52'),
(11, 'deposit', 1, 250000, 1, NULL, NULL, '2026-05-15 04:48:00', '2026-05-15 04:48:00'),
(12, 'deposit', 2, 1000000, 1, NULL, NULL, '2026-05-15 04:48:14', '2026-05-15 04:48:14'),
(18, 'release', 1, 2000, 1, NULL, '0', '2026-05-15 11:20:02', '2026-05-15 11:20:02'),
(19, 'release', 1, 258000, 1, NULL, NULL, '2026-05-15 11:20:13', '2026-05-15 11:20:13'),
(20, 'release', 2, 1000000, 1, NULL, NULL, '2026-05-15 11:20:23', '2026-05-15 11:20:23'),
(23, 'release', 2, 25, 1, 10, NULL, '2026-05-15 11:21:03', '2026-05-15 11:21:03'),
(24, 'release', 4, 8999, 1, 11, NULL, '2026-05-15 11:21:21', '2026-05-15 11:21:21'),
(25, 'release', 6, 6, 1, 11, NULL, '2026-05-15 12:37:09', '2026-05-15 12:37:09'),
(26, 'release', 6, 4, 1, 11, NULL, '2026-05-15 12:37:15', '2026-05-15 12:37:15'),
(27, 'release', 6, 4, 1, 11, NULL, '2026-05-15 12:37:17', '2026-05-15 12:37:17'),
(28, 'deposit', 2, 1, 1, NULL, NULL, '2026-09-16 00:55:25', '2026-09-16 00:55:25'),
(29, 'deposit', 2, 9, 1, NULL, NULL, '2026-09-16 00:55:31', '2026-09-16 00:55:31'),
(30, 'deposit', 9, 1, 1, NULL, 'New batch deposited', '2026-09-16 03:34:16', '2026-09-16 03:34:16'),
(32, 'deposit', 13, 45, 1, NULL, 'New batch deposited', '2026-09-16 03:48:00', '2026-09-16 03:48:00'),
(33, 'withdraw', 13, 1, 1, NULL, 'Specific batch stock withdrawal', '2026-09-16 03:57:17', '2026-09-16 03:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'bhw'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'bhw user', 'bhw@example.com', NULL, '$2y$12$1aBLgsGkb.ReRx7hsq11Pe6LfuUY/N6y/DhiOQB2hKub9n6sugRWS', NULL, '2026-05-04 10:04:51', '2026-05-04 10:04:51', 'bhw'),
(2, 'admin user', 'admin@example.com', NULL, '$2y$12$ZAoTtc.eQHjYzUMLf/Eat.aYRaOdZ8hegTkqXqNJ8ZgrGx7VzBV2u', NULL, '2026-05-04 10:04:51', '2026-05-04 10:04:51', 'admin'),
(4, 'Ferrie', 'blanzaferrie@gmail.com', NULL, '$2y$12$N7igPntx0ESzoTx1MyJLy.dz0QeJZ7ay/ibEwNNxEgjigz/gt1LjK', NULL, '2026-05-05 06:11:36', '2026-05-05 06:11:36', 'bhw'),
(6, 'Super Administrator', 'superadmin@example.com', NULL, '$2y$12$V5PhKF5r10fsoFnJ0o6iOOtKMRvRleOEKc5aflcAdwqNc/5v3thwu', NULL, '2026-09-11 02:00:09', '2026-09-11 02:00:09', 'superadmin'),
(7, 'Eunice', 'eunice@gmail.com', NULL, '$2y$12$TkmW5bDdJAJKmq2ZZAGJceDiOmQSyQpHmQMKwURojNtJiVQBD57m.', NULL, '2026-09-11 03:31:11', '2026-09-11 03:31:11', 'bhw'),
(8, 'Agustine', 'agustine@gmail.com', NULL, '$2y$12$RUtPPYlJVE97Gs09neplH.x6DwM4nNNl6UdBgR/k1r0P6JvWBZUg2', NULL, '2026-09-11 03:33:36', '2026-09-11 03:33:36', 'bhw'),
(9, 'Test Member', 'test@victory.org', NULL, '$2y$12$7eEUdjz6jHphXnMR2E42euIyzntTtrNOSQHqoJObDW8wSLWs1eci6', NULL, '2026-09-11 03:37:21', '2026-09-11 03:37:21', 'bhw'),
(10, 'Citizen 1', 'citizen@gmail.com', NULL, '$2y$12$I3Xqi.wyOTrjnrxlHdzIO.0nUbr7w4s9okydqxiu/pXmnZ027phau', NULL, '2026-09-11 03:58:03', '2026-09-11 03:58:03', 'citizen');

-- --------------------------------------------------------

--
-- Table structure for table `vaccinations`
--

CREATE TABLE `vaccinations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `citizen_id` bigint(20) UNSIGNED NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `date_administered` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_logs`
--

CREATE TABLE `vehicle_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_name` varchar(255) NOT NULL,
  `borrower` varchar(255) NOT NULL,
  `borrowed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_logs`
--

INSERT INTO `vehicle_logs` (`id`, `vehicle_name`, `borrower`, `borrowed_at`, `created_at`, `updated_at`) VALUES
(1, 'Patrol', 'Ferrie Blanza', '2026-05-05 06:28:00', '2026-05-05 06:28:49', '2026-05-05 06:28:49'),
(2, 'Bukyo', 'Ferrie Blanza', '2026-05-05 07:47:00', '2026-05-05 07:47:15', '2026-05-05 07:47:15'),
(3, 'Volkswagen', 'Samson', '2021-05-15 16:57:00', '2026-05-15 04:58:02', '2026-05-15 04:58:02'),
(4, 'Patrol', 'Josh', '2026-05-14 03:38:00', '2026-05-15 12:39:02', '2026-05-15 12:39:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `citizens`
--
ALTER TABLE `citizens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `citizen_activity_logs`
--
ALTER TABLE `citizen_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `health_records_citizen_id_foreign` (`citizen_id`);

--
-- Indexes for table `health_record_activity_logs`
--
ALTER TABLE `health_record_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `supplies`
--
ALTER TABLE `supplies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supply_logs`
--
ALTER TABLE `supply_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supply_logs_supply_id_foreign` (`supply_id`),
  ADD KEY `supply_logs_user_id_foreign` (`user_id`),
  ADD KEY `supply_logs_citizen_id_foreign` (`citizen_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vaccinations_citizen_id_foreign` (`citizen_id`);

--
-- Indexes for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `citizens`
--
ALTER TABLE `citizens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `citizen_activity_logs`
--
ALTER TABLE `citizen_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `health_record_activity_logs`
--
ALTER TABLE `health_record_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supplies`
--
ALTER TABLE `supplies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `supply_logs`
--
ALTER TABLE `supply_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_logs`
--
ALTER TABLE `vehicle_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_citizen_id_foreign` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supply_logs`
--
ALTER TABLE `supply_logs`
  ADD CONSTRAINT `supply_logs_citizen_id_foreign` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supply_logs_supply_id_foreign` FOREIGN KEY (`supply_id`) REFERENCES `supplies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supply_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD CONSTRAINT `vaccinations_citizen_id_foreign` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
