-- phpMyAdmin SQL Dump
-- version 4.6.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 05, 2016 at 01:37 AM
-- Server version: 5.6.31
-- PHP Version: 5.6.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stinjee`
--

-- --------------------------------------------------------

--
-- Table structure for table `batch`
--

CREATE TABLE `batch` (
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `promotion` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `ends_at` datetime NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `test` tinyint(1) NOT NULL,
  `purchased_online` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device`
--

CREATE TABLE `device` (
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `location_latitude` double(12,10) NOT NULL,
  `location_longitude` double(15,12) NOT NULL,
  `device_type` int(11) NOT NULL,
  `device_token` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `sended` tinyint(1) NOT NULL,
  `last_visit_date` datetime NOT NULL,
  `device_id` varchar(42) COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managerbillinglog`
--

CREATE TABLE `managerbillinglog` (
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `month` date NOT NULL,
  `tokens` text COLLATE utf8_unicode_ci NOT NULL,
  `price` double(8,2) NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `manager_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `manager_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `manager` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2015_08_17_115117_create_device_table', 1),
('2015_08_18_051802_create_specials_table', 1),
('2015_08_18_051827_create_batch_table', 1),
('2015_08_20_133250_create_managers_table', 1),
('2015_08_25_073136_create_transactions_table', 1),
('2015_08_26_095039_create_tokens_table', 1),
('2015_08_27_065647_create_managerBillingLog_table', 1),
('2015_09_09_081535_create_users_table', 1),
('2015_09_21_063154_change_object_id_length', 1);

-- --------------------------------------------------------

--
-- Table structure for table `specials`
--

CREATE TABLE `specials` (
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `location_latitude` double(13,10) NOT NULL,
  `location_longitude` double(15,12) NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `batch` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `store` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `site` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `let_admin_choose_image` tinyint(1) NOT NULL,
  `valid_for` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `activated_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `image600` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image320` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image100` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `views_count` int(11) NOT NULL,
  `store_logo` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `store_logo_bg` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `batch` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `manager` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ends_at` datetime NOT NULL,
  `purchase_date` datetime NOT NULL,
  `price` double(8,2) NOT NULL,
  `purchase_online` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `object_id` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `cart_number` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `person` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `amount` double(8,2) NOT NULL,
  `batch_id` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `company` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `store` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `province` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `postal_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `fiscal_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `updated` tinyint(1) NOT NULL,
  `tokens` int(11) NOT NULL,
  `buyed_online` tinyint(1) NOT NULL,
  `online` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'support@stinjee.com', 'Administrator', '$2y$10$kEtOQtOE7jj9XCFg8WjyJuKDmaLCg200esE8og/Dr/tHq68wqqije', '', '2016-07-04 15:00:44', '2016-07-04 15:00:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `batch`
--
ALTER TABLE `batch`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `device`
--
ALTER TABLE `device`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `managerbillinglog`
--
ALTER TABLE `managerbillinglog`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `specials`
--
ALTER TABLE `specials`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`object_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
