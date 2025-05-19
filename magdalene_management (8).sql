-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 08:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `magdalene_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_schedules`
--

CREATE TABLE `activity_schedules` (
  `activity_id` int(10) NOT NULL,
  `activity_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `activity_date` date DEFAULT NULL,
  `assigned_staff` int(10) DEFAULT NULL,
  `staff_name` varchar(55) NOT NULL,
  `archived` tinyint(4) DEFAULT 0,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_schedules`
--

INSERT INTO `activity_schedules` (`activity_id`, `activity_name`, `description`, `start_time`, `end_time`, `activity_date`, `assigned_staff`, `staff_name`, `archived`, `status`) VALUES
(4, 'teaching', NULL, '18:01:00', '18:01:00', '2025-04-16', 21, '', 0, 'pending'),
(12, 'cooking', NULL, '21:09:00', '21:09:00', '2025-04-16', 2, 'pre banda', 1, 'completed'),
(13, 'washing', NULL, '22:11:00', '22:11:00', '2025-04-16', 21, 'anika banda', 0, 'overdue'),
(14, 'teaching', NULL, '09:16:00', '09:16:00', NULL, 26, 'anita elisy', 0, 'pending'),
(15, 'cleaning', NULL, '21:18:00', '21:18:00', NULL, 21, 'memory banda', 0, 'complete'),
(16, 'cooking', NULL, '08:55:00', '08:55:00', NULL, 21, 'memory banda', 1, 'pending'),
(17, 'cooking', NULL, '08:57:00', '08:57:00', NULL, 21, 'memory banda', 1, 'overdue'),
(18, 'cooking', NULL, '17:16:00', '17:15:00', NULL, 56, 'aman black', 0, 'completed'),
(19, 'cooking', NULL, '17:16:00', '17:15:00', NULL, 56, 'aman black', 0, 'completed'),
(20, 'washing', NULL, '16:35:00', '16:35:00', NULL, 47, 'amina chuku', 0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `child_records`
--

CREATE TABLE `child_records` (
  `child_id` int(10) NOT NULL,
  `fname` varchar(30) NOT NULL,
  `lname` varchar(30) NOT NULL,
  `dateofbirth` date NOT NULL,
  `medical_info` text DEFAULT NULL,
  `education_info` text DEFAULT NULL,
  `staff_email` varchar(255) NOT NULL,
  `relatives_phonenumber` int(11) DEFAULT NULL,
  `child_backgroundinfo` text NOT NULL,
  `relatives_address` varchar(255) DEFAULT NULL,
  `archived` tinyint(4) DEFAULT 0,
  `gender` varchar(30) DEFAULT NULL,
  `is_graduate` tinyint(1) DEFAULT 0,
  `graduate` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_records`
--

INSERT INTO `child_records` (`child_id`, `fname`, `lname`, `dateofbirth`, `medical_info`, `education_info`, `staff_email`, `relatives_phonenumber`, `child_backgroundinfo`, `relatives_address`, `archived`, `gender`, `is_graduate`, `graduate`) VALUES
(24, 'pre', 'banda', '2015-02-11', 'autism', 'standard 5', 'anitah@gmail.com', 999746398, 'opharn', 'rumphi', 0, 'Male', 0, 0),
(25, 'pre', 'banda', '2025-04-24', 'deaf', 'form 3', 'annie@gmail.com', 999746322, 'opharn', 'rumphi', 0, 'Male', 0, 0),
(26, 'pre', 'banda', '2008-04-12', 'autism', 'std 4', 'pre@gmail.com', 999746397, 'ayi', 'rumphi', 0, 'Male', 0, 0),
(27, 'anim', 'phil', '2009-04-04', 'autism', 'grade 2', 'anitah@gmail.com', 999876509, 'opharn', 'rumphi', 0, 'Male', 0, 0),
(28, 'peaceful', 'phiri', '2020-02-12', 'no leg', 'standard 1', 'carlo@gmal.com', 999213456, 'leaves far from school', 'rumphi', 0, 'Male', 0, 0),
(29, 'linda', 'nee', '2021-02-01', 'blind', 'standard 1', 'nita@gmail.com', 9987461, 'opharn', 'p.o box X138, lilongwe 3', 0, 'Female', 1, 0),
(31, 'pinkie', 'phiri', '2012-06-14', 'Physical Impairment', 'standard 6', 'maria@gmail.com', 998746109, 'orphan', 'lilongwe', 0, 'Female', 0, 0),
(32, 'pinkie', 'phiri', '2012-06-14', 'autism', 'standard 6', 'anika@gmail.com', 998746109, 'orphan', 'lilongwe', 0, 'Female', 0, 0),
(33, 'pinkie', 'phiri', '2014-01-29', 'Hearing Impairment', 'standard 6', 'pre@gmail.com', 998746109, 'orphan', 'lilongwe', 0, 'Female', 1, 0),
(34, 'pre', 'bandah', '2013-07-12', 'Autism', 'elementary', 'pre@gmail.com', 999746397, 'orphan', 'hjkl', 0, 'Male', 0, 0),
(35, 'pre', 'banda', '2023-01-30', 'Autism', 'nursery', 'pre@gmail.com', 999746397, 'opharn', 'btz', 0, 'Male', 0, 0),
(36, 'chichi', 'banda', '2025-05-12', 'None', 'asdfgb', 'nala@gmail.com', 0, 'asdf', 'asdf', 0, 'Female', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `submitted_at`, `status`) VALUES
(1, 'Ian Chikalema', 'iantchikalema@gmail.com', 'non', 'okay', '2025-04-05 09:17:55', 'read'),
(2, 'memo', 'cen-01-42-21@unilia.ac.mw', 'lydia', 'banda', '2025-04-16 11:56:11', 'replied'),
(3, 'memory banda', 'anniemangoche@gmail.com', 'memo', 'hello', '2025-04-16 12:02:21', 'replied'),
(4, 'memo', 'cen-01-36-21@unilia.ac.mw', 'memory banda', 'hyyyy', '2025-04-16 12:11:06', 'replied'),
(5, 'Ian Chikalema', 'iantchikalema@gmail.com', 'hello', 'bhobho', '2025-04-16 15:59:30', 'replied'),
(6, 'memo', 'cen-01-36-21@unilia.ac.mw', 'hy', 'hello', '2025-04-17 13:42:28', 'replied'),
(7, 'annie', 'anniemangoche@gmail.com', 'non', 'yes', '2025-04-21 18:37:01', 'replied'),
(8, 'anika', 'cen-01-42-21@unilia.ac.mw', 'Donor', 'i have made donation', '2025-04-22 21:27:50', 'unread'),
(9, 'anika', 'cen-01-42-21@unilia.ac.mw', 'Donor', 'i have made donation', '2025-04-22 21:28:46', 'unread'),
(10, 'merc', 'panjie@unilia.ac.mw', 'hello', 'hello', '2025-04-23 06:22:40', 'replied'),
(11, 'linda', 'lydiabanda265@gmail.com', 'yes', 'yes', '2025-04-25 20:01:20', 'unread'),
(12, 'uchindamimwafuliwa', 'hildafaifi677@gmail.com', 'ineyo', 'child inquere', '2025-05-08 13:59:20', 'unread'),
(13, 'lani phiri', 'cen-01-42-21@unilia.ac.mw', 'Staff', 'am done, lani', '2025-05-08 15:42:30', 'unread'),
(14, 'lani phiri', 'cen-01-42-21@unilia.ac.mw', 'Staff', 'am done, lani', '2025-05-08 15:43:47', 'unread'),
(15, 'lani phiri', 'cen-01-42-21@unilia.ac.mw', 'Staff', 'hello annie', '2025-05-09 03:24:56', 'unread'),
(16, 'lani phiri', 'cen-01-42-21@unilia.ac.mw', 'Staff', 'hello annie', '2025-05-09 03:25:05', 'replied');

-- --------------------------------------------------------

--
-- Stand-in structure for view `dashboard_task_stats`
-- (See below for the actual view)
--
CREATE TABLE `dashboard_task_stats` (
`staff_name` varchar(55)
,`completed_tasks` decimal(22,0)
,`pending_tasks` decimal(22,0)
,`upcoming_tasks` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `donated_materials`
--

CREATE TABLE `donated_materials` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text NOT NULL,
  `donation_date` date NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donated_materials`
--

INSERT INTO `donated_materials` (`id`, `donor_name`, `material_name`, `quantity`, `description`, `donation_date`, `image_path`, `is_archived`, `created_at`) VALUES
(1, 'anita phir', 'books', 5, 'books for the kids', '2025-05-09', 'Uploads/681d99603e910.png', 1, '2025-05-09 05:57:52'),
(2, 'anita phir', 'pencil', 50, 'pencil for the kids', '2025-05-09', 'Uploads/681d9a7e85ced.png', 0, '2025-05-09 06:02:38'),
(3, 'anita phir', 'books', 5, 'adsfghj', '2025-05-09', 'Uploads/6821e31f1b2de.jpg', 0, '2025-05-12 12:01:35'),
(4, 'anita phir', 'books', 5, 'sdfgbhnm', '2025-05-09', 'Uploads/682207904c8f4.jpg', 0, '2025-05-12 14:37:04');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donor_name` varchar(50) NOT NULL,
  `amount` decimal(50,0) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donation_records`
--

CREATE TABLE `donation_records` (
  `donation_id` int(10) NOT NULL,
  `donor_name` varchar(50) NOT NULL,
  `donor_contact` varchar(20) DEFAULT NULL,
  `donation_type` varchar(20) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `date_received` date NOT NULL,
  `donor_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_records`
--

INSERT INTO `donation_records` (`donation_id`, `donor_name`, `donor_contact`, `donation_type`, `amount`, `date_received`, `donor_email`) VALUES
(1, 'annie', '0999746398', 'Cash', 500.00, '2025-02-03', 'anniemangoche@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `amount` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `date`, `time`, `amount`, `status`, `description`, `image_path`, `created_at`, `is_archived`) VALUES
(4, 'luu', '2025-05-31', '08:41:00', 'MWK 500', '', 'books for the kids', 'Uploads/6815c98d9df54.png', '2025-04-03 11:12:24', 1),
(5, 'Books', '2025-04-03', '13:04:00', 'MWK 200,000', '', 'books for the kids', 'uploads/67ee6d64cfcce.jpg', '2025-04-03 11:13:40', 1),
(6, 'braille', '2025-04-03', '13:04:00', 'MWK 250', '', 'braille for the blind kids', 'uploads/67ee6f5b89f27.jpg', '2025-04-03 11:22:03', 1),
(8, 'wheelchair', '2025-04-19', '14:34:00', 'MWK 367,000', '', 'wheel chair ', 'uploads/680398dc6053c.png', '2025-04-19 12:36:44', 1),
(9, 'walkerr', '2025-05-11', '08:04:00', 'MWK 5000', '', 'walker for a lame boy', 'uploads/68087a4adca1f.png', '2025-04-23 05:27:38', 1),
(10, 'walker', '2025-04-24', '07:26:00', 'MWK 200,000', '', 'walker for a lame boy', 'uploads/68087bc374267.png', '2025-04-23 05:33:55', 1),
(11, 'clothes', '2025-04-23', '08:26:00', 'MWK 200,000', '', 'donation for clothes', 'uploads/6808886b462b1.jpg', '2025-04-23 06:27:55', 1),
(12, 'annie', '2025-05-11', '10:48:00', 'MWK 500', 'active', 'annie', 'Uploads/6821b74f63602.jpeg', '2025-05-12 08:54:39', 1),
(13, 'annie', '2025-05-11', '10:48:00', 'MWK 500', 'active', 'annie', 'Uploads/6821b952e6771.jpeg', '2025-05-12 09:03:14', 1),
(14, 'annie', '2025-05-11', '10:48:00', 'MWK 500', 'active', 'annie', 'Uploads/6821ba1bd7fb3.jpeg', '2025-05-12 09:06:35', 1),
(15, 'precious', '2025-05-11', '12:06:00', '4000', 'active', 'xcvbn', 'Uploads/6821d7472f086.jpg', '2025-05-12 11:11:03', 1),
(16, 'pr', '2025-05-11', '13:59:00', '4000', 'active', 'zxdfcgvbnhm', 'Uploads/6821e2c77e178.png', '2025-05-12 12:00:07', 1),
(17, 'pr', '2025-05-11', '16:37:00', '4000', 'active', 'szdxfcvbn', 'Uploads/682207c84eee9.jpg', '2025-05-12 14:38:00', 1),
(18, 'pr', '2025-05-11', '16:59:00', '4000', 'active', 'XZcvbn', 'Uploads/68220ca393e9b.jpg', '2025-05-12 14:58:43', 1),
(19, 'mary', '2025-05-11', '16:59:00', '4000', 'active', 'dsfgh', 'Uploads/68220cd42b2b0.jpg', '2025-05-12 14:59:32', 1);

-- --------------------------------------------------------

--
-- Table structure for table `financial_records`
--

CREATE TABLE `financial_records` (
  `transaction_id` int(10) NOT NULL,
  `transaction_type` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_records`
--

CREATE TABLE `inventory_records` (
  `inventory_id` int(10) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `category` varchar(30) NOT NULL,
  `quantity` int(10) NOT NULL,
  `initial_quantity` int(11) DEFAULT NULL,
  `stock_status` varchar(10) NOT NULL,
  `date_updated` date NOT NULL,
  `archived` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_records`
--

INSERT INTO `inventory_records` (`inventory_id`, `item_name`, `category`, `quantity`, `initial_quantity`, `stock_status`, `date_updated`, `archived`) VALUES
(4, 'maize', '50kg bags of maize', 50, NULL, 'active', '2025-04-20', 0),
(5, 'Rice', 'food, 2 bags', 2, NULL, 'active', '2025-04-20', 0),
(6, 'nkhuni', 'cooking material', 30, NULL, 'active', '2025-04-23', 0),
(7, 'mangoes', 'food', 9, 10, 'active', '2025-04-26', 0),
(8, 'cassava', 'food', 2, 4, 'active', '2025-05-09', 0),
(9, 'cassava', 'food', 3, 4, 'active', '2025-05-12', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_usage_log`
--

CREATE TABLE `inventory_usage_log` (
  `log_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `usage_purpose` text NOT NULL,
  `log_date` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_usage_log`
--

INSERT INTO `inventory_usage_log` (`log_id`, `inventory_id`, `previous_quantity`, `new_quantity`, `usage_purpose`, `log_date`, `user_id`) VALUES
(1, 7, 10, 9, 'it was fruit after meal for the kids', '2025-04-26 02:56:02', NULL),
(2, 8, 4, 2, 'used for breakfast', '2025-05-09 09:40:52', NULL),
(3, 9, 4, 3, 'used for ', '2025-05-12 16:33:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pay`
--

CREATE TABLE `pay` (
  `id` int(11) NOT NULL,
  `charge_id` varchar(255) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL COMMENT 'Donation amount',
  `payment_date` datetime DEFAULT current_timestamp() COMMENT 'Date of payment',
  `payment_method` enum('Mobile Money','Bank Transfer') DEFAULT 'Mobile Money' COMMENT 'Payment method',
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending' COMMENT 'Transaction status',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last updated timestamp',
  `type` enum('Deposit','Withdrawal') NOT NULL DEFAULT 'Deposit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pay`
--

INSERT INTO `pay` (`id`, `charge_id`, `fee`, `payment_date`, `payment_method`, `donor_name`, `donor_email`, `event_name`, `status`, `updated_at`, `type`) VALUES
(47, 'pay_1ea2e8e1_1745358344', 2000.00, '2025-04-22 23:45:50', 'Mobile Money', 'peaceful banda', 'anniemangoche@gmail.com', 'wheelchair', 'success', '2025-04-22 23:48:43', 'Deposit'),
(48, 'pay_e60b6994_1745389125', 50.00, '2025-04-23 08:18:52', 'Mobile Money', 'peaceful banda', 'anniemangoche@gmail.com', 'Books', 'success', '2025-04-23 08:19:09', 'Deposit'),
(49, 'pay_981cdbba_1745389199', 50.00, '2025-04-23 08:20:06', 'Mobile Money', 'peaceful banda', 'anniemangoche@gmail.com', 'Books', 'success', '2025-04-23 08:20:22', 'Deposit'),
(50, 'pay_53303ad5_1745612284', 50.00, '2025-04-25 22:18:19', 'Mobile Money', 'memory banda', 'anniemangoche@gmail.com', 'braille', 'success', '2025-04-25 22:18:37', 'Deposit'),
(51, 'pay_ff3ad978_1745959335', 50.00, '2025-04-29 22:42:21', 'Mobile Money', 'memory banda', 'anniemangoche@gmail.com', 'wheelchair', 'success', '2025-04-29 22:42:33', 'Deposit'),
(52, 'pay_52393ef9_1745959699', 50.00, '2025-04-29 22:48:24', 'Mobile Money', 'memory banda', 'anniemangoche@gmail.com', 'braille', 'success', '2025-04-29 22:48:48', 'Deposit'),
(53, 'pay_7f186d7f_1746709775', 50.00, '2025-05-08 15:09:41', 'Mobile Money', 'annie mangoche', 'anniemangoche@gmail.com', 'clothes', 'success', '2025-05-08 15:09:53', 'Deposit'),
(54, 'pay_9be617fd_1746741512', 50.00, '2025-05-08 23:58:38', 'Mobile Money', 'lani phiri', 'cen-01-42-21@unilia.ac.mw', 'Books', 'success', '2025-05-08 23:58:55', 'Deposit'),
(55, 'pay_c381b4b1_1746762009', 50.00, '2025-05-09 05:40:15', 'Mobile Money', 'peaceful mangoche', 'anniemangoche@gmail.com', 'General', 'success', '2025-05-09 05:40:36', 'Deposit'),
(56, 'pay_5bd7a380_1746765070', 50.00, '2025-05-09 06:31:15', 'Mobile Money', 'peaceful mangoche', 'anniemangoche@gmail.com', 'General', 'success', '2025-05-09 06:31:38', 'Deposit'),
(57, 'pay_7933c4cc_1746765343', 50.00, '2025-05-09 06:35:48', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'General', 'pending', '2025-05-09 06:35:48', 'Deposit'),
(58, 'pay_881b6eed_1746765503', 50.00, '2025-05-09 06:38:29', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'General', 'pending', '2025-05-09 06:38:29', 'Deposit'),
(59, 'pay_8e6a3023_1746765576', 50.00, '2025-05-09 06:39:43', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'General', 'pending', '2025-05-09 06:39:43', 'Deposit'),
(60, 'pay_7f9c84d9_1746765669', 50.00, '2025-05-09 06:41:15', 'Mobile Money', 'memory banda', 'anniemangoche@gmail.com', 'braille', 'pending', '2025-05-09 06:41:15', 'Deposit'),
(61, 'pay_283ec2bd_1746765979', 50.00, '2025-05-09 06:46:24', 'Mobile Money', 'memory banda', 'anniemangoche@gmail.com', 'braille', 'pending', '2025-05-09 06:46:24', 'Deposit'),
(62, 'pay_6d128011_1746766461', 50.00, '2025-05-09 06:54:26', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'wheelchair', 'pending', '2025-05-09 06:54:26', 'Deposit'),
(63, 'pay_bedfe49a_1746767169', 50.00, '2025-05-09 07:06:14', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'General', 'pending', '2025-05-09 07:06:14', 'Deposit'),
(64, 'pay_9d95af53_1746775490', 50.00, '2025-05-09 09:24:56', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'General', 'pending', '2025-05-09 09:24:56', 'Deposit'),
(65, 'pay_16761620_1746775632', 50.00, '2025-05-09 09:27:18', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'wheelchair', 'pending', '2025-05-09 09:27:18', 'Deposit'),
(66, 'pay_4a9c7d9e_1746775707', 50.00, '2025-05-09 09:28:34', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'wheelchair', 'success', '2025-05-09 09:29:13', 'Deposit'),
(67, 'pay_9fe33c4d_1746775800', 50.00, '2025-05-09 09:30:07', 'Mobile Money', 'anita phiri', 'cen-01-42-21@unilia.ac.mw', 'wheelchair', 'pending', '2025-05-09 09:30:07', 'Deposit'),
(68, 'pay_48c1f693_1746777745', 50.00, '2025-05-09 10:02:30', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'General', 'success', '2025-05-09 10:02:40', 'Deposit'),
(69, 'pay_48557fd9_1746777787', 50.00, '2025-05-09 10:03:12', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'General', 'success', '2025-05-09 10:03:29', 'Deposit'),
(70, 'pay_6ad284d9_1746777865', 50.00, '2025-05-09 10:04:33', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'General', 'success', '2025-05-09 10:04:39', 'Deposit'),
(71, 'pay_22c2b11d_1746778066', 50.00, '2025-05-09 10:07:59', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'General', 'success', '2025-05-09 10:08:13', 'Deposit'),
(72, 'pay_9dc181f4_1746785285', 50.00, '2025-05-09 12:08:11', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'walker', 'pending', '2025-05-09 12:08:11', 'Deposit'),
(73, 'pay_90901bc0_1746785332', 50.00, '2025-05-09 12:09:07', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'walker', 'success', '2025-05-09 12:11:23', 'Deposit'),
(74, 'pay_40b0492e_1746785624', 50.00, '2025-05-09 12:13:51', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'braille', 'pending', '2025-05-09 12:13:51', 'Deposit'),
(75, 'pay_3df40e3f_1746785665', 50.00, '2025-05-09 12:14:31', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'General', 'pending', '2025-05-09 12:14:31', 'Deposit'),
(76, 'pay_afb64aaf_1746785671', 50.00, '2025-05-09 12:15:01', 'Mobile Money', 'peaceful phir', 'anniemangoche@gmail.com', 'General', 'pending', '2025-05-09 12:15:01', 'Deposit');

-- --------------------------------------------------------

--
-- Table structure for table `reports_log`
--

CREATE TABLE `reports_log` (
  `report_id` int(10) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `generated_by` varchar(50) DEFAULT NULL,
  `date_generated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_records`
--

CREATE TABLE `staff_records` (
  `staff_id` int(10) NOT NULL,
  `fname` varchar(30) NOT NULL,
  `lname` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `confirm_password` varchar(255) NOT NULL,
  `username` varchar(30) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `archived` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_expiry` datetime DEFAULT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_records`
--

INSERT INTO `staff_records` (`staff_id`, `fname`, `lname`, `email`, `phone`, `role`, `address`, `password`, `confirm_password`, `username`, `profile_picture`, `archived`, `created_at`, `email_verified`, `verification_token`, `verification_expiry`, `reset_token_hash`, `reset_token_expires_at`) VALUES
(2, 'pre', 'banda', 'pre@gmail.com', '0999746398', 'caregiver', 'rumphi', '0', '0', '', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(4, 'pre', 'banda', 'pre@gmail.com', '0999746398', 'admin', 'rumphi', '123', '123', '', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(6, 'peace', 'phiri', 'peace@gmail.com', '0999746398', 'caregiver', 'rumphi', '0', '0', '', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(20, 'anika', 'phil', 'anika@gmail.com', '0999876509', 'volunteer', 'rumphi', '0', '0', '', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(21, 'anika', 'banda', 'annie@gmail.com', '0998760089', 'volunteer', 'rumphi', '$2y$10$nS9bNfPeLTDOrHZ/RbVeFeEu0MVTN/B/42iStjBH9bBcc7pG2JvLm', '$2y$10$nS9bNfPeLTDOrHZ/RbVeFeEu0MVTN/B/42iStjBH9bBcc7pG2JvLm', 'memo', NULL, 0, '2025-04-19 12:12:51', 0, '3714385c91875370ee5e840f5649ccce50476e43cd2964e527a9a09f53f390ef', '2025-04-22 21:22:54', NULL, NULL),
(24, 'mina', 'banda', 'annie@gmail.com', '0998760089', 'volunteer', 'rumphi', '$2y$10$KWeRSR2dmD7AFFNcZ7Mh6.7SWr6dK1COadb5aAZ.m3N0b0lB.ELDa', '$2y$10$KWeRSR2dmD7AFFNcZ7Mh6.7SWr6dK1COadb5aAZ.m3N0b0lB.ELDa', 'mimi', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(25, 'nita', 'maida', 'nita@gmail.com', '0998760089', 'volunteer', 'lilongwe', '$2y$10$Iagdd9hLJuwAUK3DZlStqObTGRPD8NOVy8KKiNsEU2gN2I/w4o0Pm', '$2y$10$Iagdd9hLJuwAUK3DZlStqObTGRPD8NOVy8KKiNsEU2gN2I/w4o0Pm', 'nita', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(26, 'anita', 'elisy', 'anita@gmail.com', '0998760080', 'donor', 'll', '$2y$10$GHaejcE1eWDsYj1yMEEM3.fx8HIHUKHs7bzseTRrmr9c2YCGpG7Qm', '$2y$10$GHaejcE1eWDsYj1yMEEM3.fx8HIHUKHs7bzseTRrmr9c2YCGpG7Qm', 'anita', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(27, 'anita', 'phir', 'anitah@gmail.com', '0998760087', 'volunteer', 'll', '$2y$10$785x5uc3APMGyhTNcAoGi.HT8YE398B2MoZmEcTSFe9NfkZDrp0Zm', '$2y$10$785x5uc3APMGyhTNcAoGi.HT8YE398B2MoZmEcTSFe9NfkZDrp0Zm', 'nii', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(28, 'maria', 'phiri', 'maria@gmail.com', '0998760087', 'volunteer', 'rumphi', '$2y$10$Pa8EzNZtjAZEgEynhnGu6.tl3tvyYzTAd5hLrPdDYRktM2Bf4RJAi', '$2y$10$Pa8EzNZtjAZEgEynhnGu6.tl3tvyYzTAd5hLrPdDYRktM2Bf4RJAi', 'maria', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(29, 'mary', 'masamba', 'mariamangoche@gmail.com', '0991197635', 'admin', 'lilongwe', '$2y$10$yqromC.S2kuI1NykLx.lfukpQri7NpaLbsL4gDeDZ7AxAuepjVB9y', '', 'mary', NULL, 0, '2025-04-19 12:12:51', 0, NULL, NULL, NULL, NULL),
(30, 'carlo', 'mic', 'carlo@gmal.com', '0991189706', 'caregiver', NULL, '', '', '', NULL, 0, '2025-04-20 20:53:28', 0, NULL, NULL, NULL, NULL),
(42, 'francis', 'jose', 'premamangoche@gmail.com', '999746396', 'Donor', NULL, '$2y$10$xkVQPcSPyd6TcQR8F.FFzOz0vTRyKCJ81JT4bFROu.YFyQ6Ss7T3u', '', '', NULL, 0, '2025-04-23 04:51:04', 0, 'c258b5f978a2fb9cf453224bedd0007bf2301b29656582c2135722d3f02b0531', '2025-04-24 06:51:04', NULL, NULL),
(44, 'Annie', 'Mangoche', 'anambewecleaningservice@gmail.com', '0897077260', 'cooker', NULL, '$2y$10$/NhJuRDt0ABu5sBpPfY7Z.bjSAN/S.RKv.svCEqJAKfDphBr..EgS', '', '', '../uploads/profile_pictures/6808b3139df086.11941378.jpg', 0, '2025-04-23 09:29:55', 0, 'af7448b486cdcdbeead1d44b370778143e2ef80a7df626d61f49dbab150df094', '2025-04-24 11:29:55', NULL, NULL),
(45, 'annie', 'mangoche', 'anniemangoche@gmail.com', '0999746390', 'admin', 'rumphi', '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '', '../uploads/profile_pictures/6808b5596df249.19643509.jpg', 0, '2025-04-23 09:39:37', 1, 'dc56d7ec97dfd31b1bf7ec9ed5730d6715a0212ab64d99e15d30361af59b2850', '2025-04-24 11:39:37', NULL, NULL),
(46, 'tao', 'phiri', 'cen-01-36-21@unilia.ac.mw', '999746398', 'Donor', NULL, '$2y$10$mEyvQNqQNmO34zN5R9grs.QK3JkNQrxTybqny/imRoekWiLUkAEwG', '', '', NULL, 0, '2025-04-25 13:13:48', 0, '4e1ba3e48fcbbc3d9fa793287fbafec21527f745ca54eea7195f43b77664ac8b', '2025-04-26 15:13:48', NULL, NULL),
(47, 'amina', 'chuku', 'preciousmangoche@gmail.com', '0989841181', 'caregiver', NULL, '$2y$10$FBhRk0X0mDE85cUCzBMxoO/y52jnZiRmoW8wCeE5oe6h0u2GNUyiO', '', '', NULL, 0, '2025-04-25 20:33:47', 0, NULL, NULL, NULL, NULL),
(48, 'amina', 'chuku', 'anniemangoche@gmail.com', '0989841181', 'caregiver', NULL, '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '', NULL, 0, '2025-04-25 21:14:10', 0, 'dfe31fa61cad019c448dca09601a42fcf4ddf591242d2878b4196c659e8c24b5', '2025-04-26 23:14:10', NULL, NULL),
(49, 'lnyl', 'chuku', 'anniemangoche@gmail.com', '0989841180', 'caregiver', NULL, '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '', NULL, 0, '2025-04-25 21:22:50', 0, 'dcaac72aca16c23f6a0e9ea258797c37e29349436b1809ac1cb56d4494de075e', '2025-04-26 23:22:50', NULL, NULL),
(50, 'lnyl', 'chuku', 'anniemangoche@gmail.com', '0989841180', 'caregiver', NULL, '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '$2y$10$B3wIx7Uz.FnGm1HFm.SvF.ZIa6G5d0cwAeVa3WkDXl0KL8ig5uRpS', '', NULL, 0, '2025-04-25 21:40:00', 0, 'c4172daac4a9917e2ae91b1798f96da508df11f3be677d84772f8bc70ad9c4a9', '2025-04-26 23:40:00', NULL, NULL),
(52, 'lani', 'phiri', 'cen-01-42-21@unilia.ac.mw', '0999746390', 'Donor', 'zomba', '$2y$10$RV8isp5pFEOzMNoJG3Eld.3pQxh/eC4nMLooWqwOrfNGed/l09h1e', '', '', NULL, 0, '2025-04-25 22:21:10', 1, 'cfdca822e58a8eb70fee03ba1342dfc3828065c8b0839e8aacac0ebbf921389e', '2025-04-27 00:21:10', NULL, NULL),
(53, 'tnana', 'phiri', 'mariamasamba@gmail.com', '0897077260', 'Donor', NULL, '$2y$10$m1Szu1yie4mFC/MTEN325ed5O.2qaMrOFX.hWT89qq8fteWpqBXMK', '', '', NULL, 0, '2025-04-25 23:10:01', 0, '2c997596ceb7b01a949a6a1256ed6c2b29d10ba90c563be328351f316f93c456', '2025-04-27 01:10:01', NULL, NULL),
(54, 'mickey', 'banda', 'lydiabanda@gmail.com', '0999746398', 'Volunteer', 'zomba', '$2y$10$pERZZkgBdtkYC/8hAdERhOZrhPcTYaw0b.a6kkOlY5xdJO68GUBES', '', '', NULL, 0, '2025-04-25 23:42:13', 0, 'cad97c48861d3160b6c8add5fe3f57dc0d38f3f396dc4edef8b0df4033de3cd2', '2025-04-27 01:42:13', NULL, NULL),
(55, 'ruth', 'nyirenda', 'israelmsukwa777@gmail.com', '0998763499', 'teacher', 'lilongwe', '$2y$10$F1f2I5da812bRbsHBwUHDeYd.v0iLTd/9OqKAXD.8YR0L.1jh1tBu', '', '', NULL, 0, '2025-04-29 21:09:49', 0, '28321aba8fa3375f88f9be989285c611e94e80fbe01e751248d3abe0908e327a', '2025-04-30 23:09:49', NULL, NULL),
(56, 'aman', 'aman', 'cen-01-25-21@unilia.ac.mw', '0987833323', 'Donor', 'lilongwe', '$2y$10$rxKMuCMnfQItd33STlRy2.UiEajFO/bALZgz634NoksjfEqdWQ3fm', '', '', NULL, 0, '2025-05-08 11:02:16', 1, 'a046c7deff08fc1ade56d5e04adf7ab37717f57655ffedcdb7c064391f2180c9', '2025-05-09 13:02:16', NULL, NULL),
(57, 'ommil', 'himm', 'cen-01-35-21@unilia.ac.mw', '09877654321', 'teacher', NULL, '$2y$10$tT/0maJYSiqbhAIbcgY7quzzJZovQTb6bUCXMM0YWTlowKQlQvIRu', '', '', NULL, 0, '2025-05-09 07:45:20', 0, '9de92aeeb6c89233202073e0c04010b6ecae916071b7780621cf184ceb3948b5', '2025-05-10 09:45:20', NULL, NULL),
(58, 'martha', 'black', 'memorykholoti@gmail.com', '0999876540', 'admin', NULL, '$2y$10$e0T2ONun5gitmk48/40uNuv7rmMnLk.ctXs9a93bmCoco/M4d3s3S', '', '', NULL, 0, '2025-05-09 11:12:49', 0, '6cac890c3a858ef5ee9eff0df6c5d4b5fad8f2da90b0c2dd3ba611753fdbd506', '2025-05-10 13:12:49', NULL, NULL),
(59, 'mie', 'luf', 'nala@gmail.com', '0986543209', 'teacher', NULL, '$2y$10$.dlAz.5JbJ9BOaGfDs1ZAu81UUnv2WgC6X3Bl9SYwXuyT3tlDNkRm', '', '', NULL, 0, '2025-05-09 11:14:31', 0, 'f0449fcc135264188cb32dd31c7666d35f08c17391f31ea4c7148faf869a93e2', '2025-05-10 13:14:31', NULL, NULL),
(60, 'uchindami', 'aaa', 'uchindamimwafuliwa@gmail.com', '1234512345', 'Donor', NULL, '$2y$10$bS8PM1Tu1v/iQIFT9sRktu7BTZBkO58SiuRj/ASK2OXlk5TBpxBJK', '', '', NULL, 0, '2025-05-12 11:18:05', 0, '0e2d064743e19d43ec7eedf1a518184ed29ccedb39c81277f31b50cc46b9cece', '2025-05-13 13:18:05', NULL, NULL),
(61, 'dsfghj', 'dsfghj', 'uchindamimwafulirwa@gmail.com', '123456', 'Donor', NULL, '$2y$10$ZvLY63JE71xRA0CNFQcH7.OxBjuqlx5tTGen6nNZjyqTDhJjrPq1W', '', '', NULL, 0, '2025-05-12 11:56:03', 0, '2088133a643341aa38f5c05f6d6eefe801134e6bedfa3e7683d9d693d139ab1e', '2025-05-13 13:56:03', NULL, NULL),
(62, 'dsfghj', 'dsfghj', 'uchindamimwafulirwa@gmail.com', '123456', 'Donor', NULL, '$2y$10$fJPtXwpFDXQrcCIYEysVge1MhkY5CftICVHt4Gq29PXjkS/9Ub00O', '', '', NULL, 0, '2025-05-12 11:56:10', 0, '88ef8034374502bbde1863987803ad24436f6f746db83fc463fa5907d592258f', '2025-05-13 13:56:10', NULL, NULL),
(63, 'dsfghj', 'dsfghj', 'uchindamimwafulirwa@gmail.com', '123456', 'Donor', NULL, '$2y$10$dSUSZVGs3nkotxJjq/eHYefJZhIitDaRI7E0rg1TD8wBdHvLRVudu', '', '', NULL, 0, '2025-05-12 11:56:16', 0, '0c33c493d3c54c8459081da1cca1e9bfa76158a6b8c8ff6cb0639e4ad9a8a142', '2025-05-13 13:56:16', NULL, NULL),
(64, 'dsfghj', 'dsfghj', 'uchindamimwafulirwa@gmail.com', '123456', 'Donor', NULL, '$2y$10$V11J99aWd83MAL1vNnArQuCRgzK2GeQsbbYig8o.J3Wzr7Gu95Whu', '', '', NULL, 0, '2025-05-12 11:56:21', 0, '685793ed061939034a6641b798ebd5b7ca7d26e4436404fe729dc658458e54eb', '2025-05-13 13:56:21', NULL, NULL),
(65, 'hilda', 'asdfg', 'hildafaifi677@gmail.com', '234567890', 'caregiver', NULL, '$2y$10$yhieJBr.QXDpbXEVYGdl0uoDpCJC.GEG2Vfkqq3jdj1444zIulySm', '', '', NULL, 0, '2025-05-12 11:58:31', 0, '4d15ae4833cd0cb3e0717d0f7bfbf869f820761fe3edf8196bd732970cab721e', '2025-05-13 13:58:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(10) NOT NULL,
  `action` varchar(50) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_reports`
--

CREATE TABLE `task_reports` (
  `report_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `report_text` text NOT NULL,
  `report_date` datetime NOT NULL,
  `staff_name` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_reports`
--

INSERT INTO `task_reports` (`report_id`, `activity_id`, `report_text`, `report_date`, `staff_name`) VALUES
(1, 15, 'still working on it', '2025-04-22 21:25:34', 'memory banda'),
(2, 18, 'done', '2025-05-08 17:22:29', 'aman black');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `charge_id` varchar(255) DEFAULT NULL,
  `amount` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `customer_fname` varchar(100) DEFAULT NULL,
  `customer_lname` varchar(100) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_id`, `charge_id`, `amount`, `status`, `provider`, `phone`, `customer_fname`, `customer_lname`, `customer_email`, `reference`, `created_date`, `completed_date`, `created_at`) VALUES
(1, 'e8aee652-b4c0-433c-98e7-cf7cc3486797', '1c9f8ef3-6a1e-4d52-9d7b-bc443eaa57dc', '50 MK', 'successful', 'Airtel Money', '+265993xxxx23', 'memory', 'banda', 'anniemangoche@gmail.com', '81935688874', '2025-04-21 16:00:25', '2025-04-21 16:00:38', '2025-04-21 16:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_records`
--

CREATE TABLE `user_records` (
  `user_id` int(10) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirm_password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_records`
--

INSERT INTO `user_records` (`user_id`, `fullname`, `username`, `email`, `password`, `confirm_password`) VALUES
(1, '', 'annie', '', '8e35c2cd3bf6641bdb0e2050b76932cbb2e6034a0ddacc1d9bea82a6ba57f7cf', '');

-- --------------------------------------------------------

--
-- Structure for view `dashboard_task_stats`
--
DROP TABLE IF EXISTS `dashboard_task_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_task_stats`  AS SELECT `activity_schedules`.`staff_name` AS `staff_name`, sum(case when `activity_schedules`.`status` = 'completed' then 1 else 0 end) AS `completed_tasks`, sum(case when `activity_schedules`.`status` = 'pending' then 1 else 0 end) AS `pending_tasks`, sum(case when `activity_schedules`.`status` = 'upcoming' then 1 else 0 end) AS `upcoming_tasks` FROM `activity_schedules` GROUP BY `activity_schedules`.`staff_name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_schedules`
--
ALTER TABLE `activity_schedules`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `assigned_staff` (`assigned_staff`),
  ADD KEY `idx_staff_status` (`staff_name`,`status`),
  ADD KEY `idx_activity_date` (`activity_date`);

--
-- Indexes for table `child_records`
--
ALTER TABLE `child_records`
  ADD PRIMARY KEY (`child_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donated_materials`
--
ALTER TABLE `donated_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donation_records`
--
ALTER TABLE `donation_records`
  ADD PRIMARY KEY (`donation_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_records`
--
ALTER TABLE `financial_records`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `inventory_records`
--
ALTER TABLE `inventory_records`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `inventory_usage_log`
--
ALTER TABLE `inventory_usage_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `pay`
--
ALTER TABLE `pay`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`charge_id`);

--
-- Indexes for table `reports_log`
--
ALTER TABLE `reports_log`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `staff_records`
--
ALTER TABLE `staff_records`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `idx_archived` (`archived`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `task_reports`
--
ALTER TABLE `task_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_records`
--
ALTER TABLE `user_records`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_schedules`
--
ALTER TABLE `activity_schedules`
  MODIFY `activity_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `child_records`
--
ALTER TABLE `child_records`
  MODIFY `child_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `donated_materials`
--
ALTER TABLE `donated_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `donation_records`
--
ALTER TABLE `donation_records`
  MODIFY `donation_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `financial_records`
--
ALTER TABLE `financial_records`
  MODIFY `transaction_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_records`
--
ALTER TABLE `inventory_records`
  MODIFY `inventory_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory_usage_log`
--
ALTER TABLE `inventory_usage_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pay`
--
ALTER TABLE `pay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `reports_log`
--
ALTER TABLE `reports_log`
  MODIFY `report_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_records`
--
ALTER TABLE `staff_records`
  MODIFY `staff_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_reports`
--
ALTER TABLE `task_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_records`
--
ALTER TABLE `user_records`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_schedules`
--
ALTER TABLE `activity_schedules`
  ADD CONSTRAINT `fk_assigned_staff` FOREIGN KEY (`assigned_staff`) REFERENCES `staff_records` (`staff_id`),
  ADD CONSTRAINT `fk_staff_assignment` FOREIGN KEY (`assigned_staff`) REFERENCES `staff_records` (`staff_id`);

--
-- Constraints for table `financial_records`
--
ALTER TABLE `financial_records`
  ADD CONSTRAINT `financial_records_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `staff_records` (`staff_id`);

--
-- Constraints for table `inventory_usage_log`
--
ALTER TABLE `inventory_usage_log`
  ADD CONSTRAINT `inventory_usage_log_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_records` (`inventory_id`);

--
-- Constraints for table `reports_log`
--
ALTER TABLE `reports_log`
  ADD CONSTRAINT `reports_log_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `user_records` (`user_id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_records` (`user_id`);

--
-- Constraints for table `task_reports`
--
ALTER TABLE `task_reports`
  ADD CONSTRAINT `task_reports_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activity_schedules` (`activity_id`);

--
-- Constraints for table `user_records`
--
ALTER TABLE `user_records`
  ADD CONSTRAINT `user_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `donation_records` (`donation_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
