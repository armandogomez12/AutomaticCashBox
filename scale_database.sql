-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2025 at 04:37 PM
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
-- Database: `scale_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(100) NOT NULL DEFAULT 'pendiente@correo.com'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `is_active`, `created_at`, `email`) VALUES
(5, 'admin', '$2y$10$bKAA/tfQCv5jamqOyb1sM.fnnmmLSdK5xB9dts1FE7j8a9HLsawtW', 0, '2025-10-11 03:28:04', 'pendiente@correo.com');

-- --------------------------------------------------------

--
-- Table structure for table `measurement_logs`
--

CREATE TABLE `measurement_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `scale_id` varchar(50) NOT NULL,
  `measured_weight` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expected_weight` decimal(10,2) DEFAULT NULL,
  `is_valid` tinyint(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `measurement_logs`
--

INSERT INTO `measurement_logs` (`id`, `user_id`, `scale_id`, `measured_weight`, `price`, `expected_weight`, `is_valid`, `timestamp`) VALUES
(17, 2, 'XBOX_SERIES', 0.00, 14000.00, 4500.00, 1, '2025-10-18 14:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `created_at`) VALUES
(2, 'Alex_01', 'jesus.jimenez7858@alumnos.udg.mx', '$2y$10$9uxIacnDu/SYNqHXPr5mdeX20CFORTGRuXhR7hs55Wf/eY3dl4wiK', 'Alejandro Jimenez Castellanos', '2025-10-11 03:00:43'),
(7, 'Ale', 'alejandrojesuscastellanos92@gmail.com', '$2y$10$GInZTci4Tkh5d3p5ANn2m.bw2ianp/7dc/GKyH8tvht0o0MwELeDS', 'Jesús Alejandro', '2025-11-28 02:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_purchases`
--

CREATE TABLE `user_purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scale_id` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `expected_weight` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_purchases`
--

INSERT INTO `user_purchases` (`id`, `user_id`, `scale_id`, `product_name`, `expected_weight`, `price`, `timestamp`) VALUES
(3, 2, 'XBOX_SERIES', 'Xbox Series X', 4500.00, 14000.00, '2025-11-29 07:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `validation_pending`
--

CREATE TABLE `validation_pending` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scale_id` varchar(50) NOT NULL,
  `expected_weight` decimal(10,2) NOT NULL,
  `tolerance` decimal(10,2) NOT NULL,
  `measured_weight` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('PENDING','VALIDATED','FAILED','EXPIRED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `validated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `validation_pending`
--

INSERT INTO `validation_pending` (`id`, `user_id`, `scale_id`, `expected_weight`, `tolerance`, `measured_weight`, `price`, `status`, `created_at`, `validated_at`) VALUES
(11, 4, 'AGUACATE_HASS', 1000.00, 10.00, NULL, 70.00, 'VALIDATED', '2025-11-29 15:36:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `weight_standards`
--

CREATE TABLE `weight_standards` (
  `id` int(11) NOT NULL,
  `scale_id` varchar(50) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `expected_weight` decimal(10,2) NOT NULL,
  `tolerance` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weight_standards`
--

INSERT INTO `weight_standards` (`id`, `scale_id`, `product_name`, `expected_weight`, `tolerance`, `price`, `is_active`, `created_at`) VALUES
(1, 'MANZANA_ROJA', 'Manzana Roja Grande', 1000.00, 10.00, 30.25, 1, '2025-09-27 03:06:53'),
(2, 'PLATANO_CHIAPAS', 'Plátano de Chiapas', 1000.00, 15.00, 28.00, 1, '2025-09-27 03:49:43'),
(3, 'JITOMATE_BOLA', 'Jitomate Bola', 1000.00, 10.00, 40.00, 1, '2025-09-27 03:49:43'),
(4, 'AGUACATE_HASS', 'Aguacate Hass', 1000.00, 10.00, 70.00, 1, '2025-09-27 03:49:43'),
(5, 'CEBOLLA_BLANCA', 'Cebolla Blanca', 1000.00, 10.00, 34.50, 1, '2025-09-27 03:49:43'),
(6, 'FRESA_ROJA', 'Fresas', 1000.00, 10.00, 75.00, 1, '2025-09-27 04:31:08'),
(7, 'PAPAYA', 'Papaya', 1700.00, 10.00, 30.00, 1, '2025-10-18 14:39:03'),
(8, 'XBOX_SERIES', 'Xbox Series X', 4500.00, 10.00, 14000.00, 1, '2025-10-18 14:50:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `measurement_logs`
--
ALTER TABLE `measurement_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_purchases`
--
ALTER TABLE `user_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `validation_pending`
--
ALTER TABLE `validation_pending`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_scale_id` (`scale_id`);

--
-- Indexes for table `weight_standards`
--
ALTER TABLE `weight_standards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scale_id` (`scale_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `measurement_logs`
--
ALTER TABLE `measurement_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_purchases`
--
ALTER TABLE `user_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `validation_pending`
--
ALTER TABLE `validation_pending`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `weight_standards`
--
ALTER TABLE `weight_standards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
