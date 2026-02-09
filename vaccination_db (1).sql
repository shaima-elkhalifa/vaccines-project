-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 01:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vaccination_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `head_circumference` varchar(10) DEFAULT NULL,
  `child_weight` varchar(10) DEFAULT NULL,
  `record_number` int(11) DEFAULT NULL,
  `doctor_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `child_del` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `name`, `birth_date`, `gender`, `head_circumference`, `child_weight`, `record_number`, `doctor_id`, `user_id`, `child_del`) VALUES
(1, 't3st one', '2025-10-30', 'Male', '2', '05', NULL, 6, 21, 0),
(2, 'test two', '2025-09-23', 'Male', '8', '9', 5, 6, 21, 0),
(3, 'ghena', '2025-05-25', 'Female', '', '5', 1, 6, 24, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `user_del` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `user_del`) VALUES
(6, 'Dr Hytham', 'h@doctor.com', '2510c39011c5be704182423e3a695e91', 'doctor', 0),
(12, 'admin', 'admin123@admin.com', '0192023a7bbd73250516f069df18b500', 'admin', 0),
(15, 'nr.huda', 'huda@nurse.com', '0075a4e7a2e71083262da135ecdbdd14', 'nurse', 0),
(16, 'shaimaa', 'shaimaa@nurse.com', '756c7fa7716d14aa0bea0786f8aab463', 'nurse', 0),
(17, 'ola abdulaal', 'ola@parent.com', '236d1336e98985dce3a625d46aebfd02', 'parent', 0),
(18, 'layan ahmed', 'layan@ahmed.com', '722cbfde14deaac3a4b1b25e1f0d6343', 'parent', 0),
(19, 'doctor', 'docotor@doctor.com', 'f9f16d97c90d8c6f2cab37bb6d1f1992', 'doctor', 0),
(20, 'Emad', 'emad@parent.com', '$2y$10$Lp18hQgMZp5ZzL7Dj5jVX.D7pKk8.JKIlnLiWSg8wMZ.lw9AoJvxO', 'parent', 0),
(21, 'test user', 'test@parent.com', '098f6bcd4621d373cade4e832627b4f6', 'parent', 0),
(22, 'test11', 'test11@parent.com', '$2y$10$vHDV44/u0ZeDh1qJZ02eH.5RWzbfOr3nFOxcmH.iYcjIGcmvd/RMa', 'parent', 0),
(23, 'Lubna', 'Lubna@parent.com', '$2y$10$ET/BWy/RWaNkp5lTbZHITeGltdKWtp0UOvUNcq5Km4SICynvmWQne', 'parent', 0),
(24, 'noha awad', 'noha@parent.com', '4c8b40018f893d4384fcfe60302cb46a', 'parent', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fcm_token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user_tokens`
--

INSERT INTO `user_tokens` (`id`, `user_id`, `fcm_token`, `created_at`) VALUES
(20, 21, 'dsdsdsdsd', '2025-11-01 01:12:30'),
(21, 24, 'cZ9MPJBxRF-6oJ29FTS4su:APA91bHzxk3lW7e7eN9ESunG3k37RgdP6tmj5sss9vVa7K62AXLkLVjBntHtoMVWHN3c0TecbPCDOp4rDfdZ13ZlKlZHQGV1BxzYqi_KU6HGxi4aoAvm8vY', '2025-11-15 00:24:45');

-- --------------------------------------------------------

--
-- Table structure for table `vaccinations`
--

CREATE TABLE `vaccinations` (
  `id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `vaccine_date` date DEFAULT NULL,
  `vaccine_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `notified` tinyint(11) NOT NULL DEFAULT 0,
  `is_vaccinated` tinyint(1) DEFAULT 0,
  `vaccined_by` varchar(255) DEFAULT NULL,
  `vaccinated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `vaccinations`
--

INSERT INTO `vaccinations` (`id`, `child_id`, `vaccine_date`, `vaccine_name`, `notes`, `notified`, `is_vaccinated`, `vaccined_by`, `vaccinated_date`) VALUES
(25, 1, '2025-10-30', 'BCG, Hepatitis B', '', 0, 0, NULL, NULL),
(26, 1, '2025-12-29', 'DTP, Polio, Hib', '', 0, 0, NULL, NULL),
(27, 1, '2026-02-27', 'DTP, Polio, Hib', '', 0, 0, NULL, NULL),
(28, 1, '2026-04-28', 'Hepatitis B, Polio', '', 0, 0, NULL, NULL),
(29, 1, '2026-07-27', 'MMR', '', 0, 0, NULL, NULL),
(30, 1, '2026-10-30', 'Chickenpox', '', 0, 0, NULL, NULL),
(31, 2, '2025-09-23', 'BCG, Hepatitis B', '', 0, 0, NULL, NULL),
(32, 2, '2025-11-22', 'DTP, Polio, Hib', '', 0, 0, NULL, NULL),
(33, 2, '2026-01-21', 'DTP, Polio, Hib', '', 0, 0, NULL, NULL),
(34, 2, '2026-03-22', 'Hepatitis B, Polio', '', 0, 0, NULL, NULL),
(35, 2, '2026-06-20', 'MMR', '', 0, 0, NULL, NULL),
(36, 2, '2026-09-23', 'Chickenpox', '', 0, 0, NULL, NULL),
(37, 3, '2025-05-25', 'BCG, Hepatitis B', '', 0, 0, NULL, NULL),
(38, 3, '2025-07-24', 'DTP, Polio, Hib', '', 0, 0, NULL, NULL),
(39, 3, '2025-09-22', 'DTP, Polio, Hib', '', 0, 0, NULL, NULL),
(40, 3, '2025-11-21', 'Hepatitis B, Polio', '', 1, 0, NULL, NULL),
(41, 3, '2026-02-19', 'MMR', '', 1, 0, NULL, NULL),
(42, 3, '2026-05-25', 'Chickenpox', '', 1, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vaccine_schedule`
--

CREATE TABLE `vaccine_schedule` (
  `id` int(11) NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `due_days` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `vaccine_schedule`
--

INSERT INTO `vaccine_schedule` (`id`, `vaccine_name`, `due_days`, `description`) VALUES
(1, 'BCG, Hepatitis B', 0, 'عند الولادة'),
(2, 'DTP, Polio, Hib', 60, 'عمر شهرين'),
(3, 'DTP, Polio, Hib', 120, 'عمر 4 أشهر'),
(4, 'Hepatitis B, Polio', 180, 'عمر 6 أشهر'),
(5, 'MMR', 270, 'عمر 9 أشهر'),
(6, 'Chickenpox', 365, 'عمر سنة'),
(7, 'OPV2 , Combiend ( DTap + HIP + IPV ), MMR2 ', 548, 'عمر سنة ونص '),
(8, 'MMR , Combined ( DTap + IPV ) + OPV3 + Vraicella2 ', 1825, 'عمر 5 سنوات ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_children_record_number` (`record_number`),
  ADD KEY `idx_children_user_id` (`user_id`),
  ADD KEY `idx_children_doctor_id` (`doctor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `fcm_token` (`fcm_token`);

--
-- Indexes for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vaccinations_ibfk_1` (`child_id`);

--
-- Indexes for table `vaccine_schedule`
--
ALTER TABLE `vaccine_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vaccine_name` (`vaccine_name`,`due_days`),
  ADD UNIQUE KEY `due_days` (`due_days`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `vaccine_schedule`
--
ALTER TABLE `vaccine_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `fk_children_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_children_parent` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_children_record_schedule` FOREIGN KEY (`record_number`) REFERENCES `vaccine_schedule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD CONSTRAINT `fk_vaccinations_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
