-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 08:46 PM
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
(13, 'washing', NULL, '22:11:00', '22:11:00', '2025-04-16', 24, 'memo banda', 0, 'pending'),
(14, 'teaching', NULL, '09:16:00', '09:16:00', NULL, 26, 'anita elisy', 0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `child_records`
--

CREATE TABLE `child_records` (
  `child_id` int(10) NOT NULL,
  `fname` varchar(30) NOT NULL,
  `lname` varchar(30) NOT NULL,
  `age` int(3) NOT NULL,
  `medical_info` text DEFAULT NULL,
  `education_info` text DEFAULT NULL,
  `staff_name` varchar(255) NOT NULL,
  `relatives_phonenumber` int(11) NOT NULL,
  `child_backgroundinfo` text NOT NULL,
  `relatives_address` varchar(255) NOT NULL,
  `archived` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_records`
--

INSERT INTO `child_records` (`child_id`, `fname`, `lname`, `age`, `medical_info`, `education_info`, `staff_name`, `relatives_phonenumber`, `child_backgroundinfo`, `relatives_address`, `archived`) VALUES
(24, 'pre', 'banda', 12, 'autism', 'standard 5', '', 999746398, 'opharn', 'rumphi', 0),
(25, 'pre', 'banda', 12, 'deaf', 'form 3', 'miss mangoche', 999746322, 'opharn', 'rumphi', 0),
(26, 'pre', 'banda', 9, 'autism', 'std 4', 'nara', 999746397, 'ayi', 'rumphi', 0),
(27, 'anim', 'phil', 12, 'autism', 'grade 2', 'nita', 999876509, 'opharn', 'rumphi', 0),
(28, 'peaceful', 'phiri', 13, 'no leg', 'standard 4', 'mimi', 999213456, 'leaves far from school', 'rumphi', 0),
(29, 'linda', 'nee', 10, 'blind', 'standard 3', 'memo', 9987461, 'opharn', 'p.o box X138, lilongwe 3', 0);

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
(7, 'annie', 'anniemangoche@gmail.com', 'non', 'yes', '2025-04-21 18:37:01', 'replied');

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
  `amount` decimal(50,0) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `date`, `time`, `amount`, `status`, `description`, `image_path`, `created_at`) VALUES
(2, 'luu', '2025-04-03', '13:04:00', 0, '', 'books for the kids', 'uploads/67ee6bcfd45a5.jpg', '2025-04-03 11:06:55'),
(3, 'luu', '2025-04-03', '13:04:00', 0, '', 'books for the kids', 'uploads/67ee6c16a77d4.jpg', '2025-04-03 11:08:06'),
(4, 'luu', '2025-04-03', '13:04:00', 0, '', 'books for the kids', 'uploads/67ee6d1834aab.jpg', '2025-04-03 11:12:24'),
(5, 'luu', '2025-04-03', '13:04:00', 0, '', 'books for the kids', 'uploads/67ee6d64cfcce.jpg', '2025-04-03 11:13:40'),
(6, 'luu', '2025-04-03', '13:04:00', 0, '', 'books for the kids', 'uploads/67ee6f5b89f27.jpg', '2025-04-03 11:22:03'),
(8, 'wheelchair', '2025-04-19', '14:34:00', 0, '', 'wheel chair ', 'uploads/680398dc6053c.png', '2025-04-19 12:36:44');

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
  `stock_status` varchar(10) NOT NULL,
  `date_updated` date NOT NULL,
  `archived` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_records`
--

INSERT INTO `inventory_records` (`inventory_id`, `item_name`, `category`, `quantity`, `stock_status`, `date_updated`, `archived`) VALUES
(4, 'maize', '50kg bags of maize', 50, 'active', '2025-04-20', 0),
(5, 'Rice', 'food, 2 bags', 2, 'active', '2025-04-20', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pay`
--

CREATE TABLE `pay` (
  `id` int(11) NOT NULL,
  `charge_id` varchar(255) DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `updated_at` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pay`
--

INSERT INTO `pay` (`id`, `charge_id`, `fee`, `payment_date`, `payment_method`, `donor_name`, `donor_email`, `event_name`, `status`, `updated_at`) VALUES
(12, 'dcf21142', 50.00, '2025-04-15 14:23:03', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'success', NULL),
(13, 'c4dd5054', 50.00, '2025-04-15 16:23:43', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(14, 'b3fad123', 50.00, '2025-04-15 17:31:01', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(15, 'acc191c4', 50.00, '2025-04-15 17:52:33', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(16, 'f2fbc245', 50.00, '2025-04-15 18:06:37', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(17, 'b23b8528', 50.00, '2025-04-15 18:24:30', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(18, '8f9317d4', 50.00, '2025-04-15 18:37:25', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(19, '45bb08e3', 50.00, '2025-04-15 18:42:01', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(20, '017cdf2e', 50.00, '2025-04-15 18:46:59', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'success', NULL),
(21, '4d669cf8', 50.00, '2025-04-16 15:25:48', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'success', NULL),
(22, '2b4caf2b', 50.00, '2025-04-16 17:12:59', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(23, '262bdb70', 50.00, '2025-04-16 17:18:25', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(24, '1d81c3f4', 50.00, '2025-04-16 17:57:22', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(25, 'abdb579b', 50.00, '2025-04-16 17:58:26', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(26, '37e8a6d8', 50.00, '2025-04-18 10:29:37', '20be6c20-adeb-4b5b-a7ba-0769820df4fb', 'memory banda', 'anniemangoche@gmail.com', NULL, 'pending', NULL),
(27, 'b2d77733', 50.00, '2025-04-18 10:47:41', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(28, 'dd6540f6', 50.00, '2025-04-18 11:48:32', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(29, 'c4069991', 50.00, '2025-04-18 11:48:52', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(30, 'eecadd3c', 50.00, '2025-04-18 12:22:50', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(31, '4640a303', 50.00, NULL, '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(32, 'a859b141', 50.00, NULL, '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', NULL),
(33, '157b4e21', 50.00, '2025-04-18 13:40:14', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', '2025-04-18 13:40:14'),
(34, '53eb3384', 50.00, '2025-04-18 13:59:52', '27494cb5-ba9e-437f-a114-4e7a7686bcca', 'memory banda', 'anniemangoche@gmail.com', 'luu', 'pending', '2025-04-18 13:59:52');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_records`
--

INSERT INTO `staff_records` (`staff_id`, `fname`, `lname`, `email`, `phone`, `role`, `address`, `password`, `confirm_password`, `username`, `profile_picture`, `archived`, `created_at`) VALUES
(2, 'pre', 'banda', 'pre@gmail.com', '0999746398', 'caregiver', 'rumphi', '0', '0', '', NULL, 0, '2025-04-19 12:12:51'),
(4, 'pre', 'banda', 'pre@gmail.com', '0999746398', 'admin', 'rumphi', '123', '123', '', NULL, 0, '2025-04-19 12:12:51'),
(6, 'peace', 'phiri', 'peace@gmail.com', '0999746398', 'caregiver', 'rumphi', '0', '0', '', NULL, 0, '2025-04-19 12:12:51'),
(20, 'anika', 'phil', 'anika@gmail.com', '0999876509', 'volunteer', 'rumphi', '0', '0', '', NULL, 0, '2025-04-19 12:12:51'),
(21, 'memory', 'banda', 'anniemangoche@gmail.com', '0998760089', 'volunteer', 'rumphi', '$2y$10$nS9bNfPeLTDOrHZ/RbVeFeEu0MVTN/B/42iStjBH9bBcc7pG2JvLm', '$2y$10$nS9bNfPeLTDOrHZ/RbVeFeEu0MVTN/B/42iStjBH9bBcc7pG2JvLm', 'memo', NULL, 0, '2025-04-19 12:12:51'),
(22, 'Ian', 'Chikalema', 'iantchikalema@gmail.com', '0992282545', 'donor', 'rumphi', '$2y$10$rvcxYLid741FXKhyZ4EEqO3OOUVqmKn8/4n/2Ouyz/Knwus2Jzmb2', '$2y$10$e4t7vyQRCWqua8kmQjlMIuikzI7zfTE8o6RV4.aHNannJ9F6rgl/W', 'Goat', NULL, 0, '2025-04-19 12:12:51'),
(24, 'memo', 'banda', 'annie@gmail.com', '0998760089', 'volunteer', 'rumphi', '$2y$10$KWeRSR2dmD7AFFNcZ7Mh6.7SWr6dK1COadb5aAZ.m3N0b0lB.ELDa', '$2y$10$KWeRSR2dmD7AFFNcZ7Mh6.7SWr6dK1COadb5aAZ.m3N0b0lB.ELDa', 'mimi', NULL, 0, '2025-04-19 12:12:51'),
(25, 'nita', 'maida', 'nita@gmail.com', '0998760089', 'volunteer', 'lilongwe', '$2y$10$Iagdd9hLJuwAUK3DZlStqObTGRPD8NOVy8KKiNsEU2gN2I/w4o0Pm', '$2y$10$Iagdd9hLJuwAUK3DZlStqObTGRPD8NOVy8KKiNsEU2gN2I/w4o0Pm', 'nita', NULL, 0, '2025-04-19 12:12:51'),
(26, 'anita', 'elisy', 'anita@gmail.com', '0998760080', 'donor', 'll', '$2y$10$GHaejcE1eWDsYj1yMEEM3.fx8HIHUKHs7bzseTRrmr9c2YCGpG7Qm', '$2y$10$GHaejcE1eWDsYj1yMEEM3.fx8HIHUKHs7bzseTRrmr9c2YCGpG7Qm', 'anita', NULL, 0, '2025-04-19 12:12:51'),
(27, 'anita', 'phir', 'anitah@gmail.com', '0998760087', 'volunteer', 'll', '$2y$10$785x5uc3APMGyhTNcAoGi.HT8YE398B2MoZmEcTSFe9NfkZDrp0Zm', '$2y$10$785x5uc3APMGyhTNcAoGi.HT8YE398B2MoZmEcTSFe9NfkZDrp0Zm', 'nii', NULL, 0, '2025-04-19 12:12:51'),
(28, 'maria', 'phiri', 'maria@gmail.com', '0998760087', 'volunteer', 'rumphi', '$2y$10$Pa8EzNZtjAZEgEynhnGu6.tl3tvyYzTAd5hLrPdDYRktM2Bf4RJAi', '$2y$10$Pa8EzNZtjAZEgEynhnGu6.tl3tvyYzTAd5hLrPdDYRktM2Bf4RJAi', 'maria', NULL, 0, '2025-04-19 12:12:51'),
(29, 'mary', 'masamba', 'mariamangoche@gmail.com', '0991197635', 'admin', 'lilongwe', '$2y$10$yqromC.S2kuI1NykLx.lfukpQri7NpaLbsL4gDeDZ7AxAuepjVB9y', '', 'mary', NULL, 0, '2025-04-19 12:12:51'),
(30, 'carlo', 'mic', 'carlo@gmal.com', '0991189706', 'caregiver', NULL, '', '', '', NULL, 0, '2025-04-20 20:53:28');

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
  MODIFY `activity_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `child_records`
--
ALTER TABLE `child_records`
  MODIFY `child_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `donation_records`
--
ALTER TABLE `donation_records`
  MODIFY `donation_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `financial_records`
--
ALTER TABLE `financial_records`
  MODIFY `transaction_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_records`
--
ALTER TABLE `inventory_records`
  MODIFY `inventory_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pay`
--
ALTER TABLE `pay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `reports_log`
--
ALTER TABLE `reports_log`
  MODIFY `report_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_records`
--
ALTER TABLE `staff_records`
  MODIFY `staff_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(10) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `user_records`
--
ALTER TABLE `user_records`
  ADD CONSTRAINT `user_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `donation_records` (`donation_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
