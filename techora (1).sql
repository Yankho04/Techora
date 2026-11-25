-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2026 at 06:56 AM
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
-- Database: `techora`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `action`, `details`, `created_at`) VALUES
(1, 3, 'Added Product', 'Added product: Cases', '2026-11-15 17:42:58'),
(2, 3, 'Added Product', 'Added product: Silicone Cases', '2026-11-15 18:32:46'),
(3, 3, 'Edited Product', 'Updated product: Charger (ID: 16)', '2026-11-15 18:44:44'),
(4, 1, 'Edited Product', 'Updated product: Silicone Cases (ID: 20)', '2026-11-17 07:34:21'),
(5, 1, 'Added Product', 'Added product: Iphone cases', '2026-11-17 07:36:49'),
(6, 1, 'Edited Product', 'Updated product: Silicone Cases (ID: 20)', '2026-11-17 07:38:33'),
(7, 1, 'Added Product', 'Added product: Samsung Phone Case', '2026-11-23 18:10:19'),
(8, 1, 'Edited Product', 'Updated product: Samsung Phone Cases (ID: 22)', '2026-11-23 18:11:14'),
(9, 1, 'Added Product', 'Added product: Wireless Bluetooth Headsets', '2026-11-23 18:29:40'),
(10, 1, 'Added Product', 'Added product: Multi Charge Cable', '2026-11-23 18:34:01'),
(11, 1, 'Added Product', 'Added product: USB-C', '2026-11-23 18:35:23'),
(12, 1, 'Added Product', 'Added product: Mega safe power bank', '2026-11-24 07:15:41'),
(13, 1, 'Added Product', 'Added product: Faster charger', '2026-11-24 07:19:18'),
(14, 1, 'Edited Product', 'Updated product: Faster charger (ID: 27)', '2026-11-24 07:51:01'),
(15, 1, 'Added Product', 'Added product: 120W Fast Charging 4 Ports', '2026-11-24 08:03:15'),
(16, 1, 'Edited Product', 'Updated product: 120W Fast Charging 4 Ports (ID: 28)', '2026-11-24 08:26:14');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`, `is_read`) VALUES
(1, 'Yankho Banda', 'yankhobanda93@gmail.com', 'Improve some of your services, especially the delivery process.', '2026-11-12 08:04:29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_address` varchar(255) NOT NULL DEFAULT '',
  `phone_number` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `created_at`) VALUES
(9, 1, 'cdb19383c7c637fcfd503908d74bd53cc368d8f7bdccf08884bc0923c57c9474', '2026-11-19 20:17:00', '2026-11-19 18:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `image`, `stock`, `created_at`) VALUES
(8, 'Earphones', 'Quality, long-lasting earphones.', 45000.00, 'Earphones', 'assets/images/product_6ae8d40d5c3647.31475883.jpg', 20, '2026-11-02 18:18:53'),
(9, 'USB Cable', 'Durable charging and data transfer cable', 10000.00, 'USB Cables', 'assets/images/product_6ae8d4ad086644.86071829.jpg', 10, '2026-11-02 18:21:33'),
(10, 'Privacy screen protector', 'High quality screen protectors', 6500.00, 'Screen Protectors', 'assets/images/product_6ae8d5083898d7.11301088.jpg', 8, '2026-11-02 18:23:04'),
(11, 'Power Bank 5000Amh', 'Always running out of battery while traveling or outside? These high-capacity power banks are perfect for emergencies, work, school, and long trips. Fast charging, reliable', 55000.00, 'Power Banks', 'assets/images/product_6ae979266fcf88.37952832.jpg', 10, '2026-11-02 18:27:01'),
(12, 'EarBuds', '🔥 92% OFF right now! HAOYUYAN Wireless Earbuds, Sports Bluetooth Headphones, 80-Hour Playtime Earbuds with LED Power Display', 35000.00, 'Earphones', 'assets/images/product_6ae9885f859a91.95718245.jpg', 30, '2026-11-03 07:08:15'),
(13, 'USB C headphones', 'USB-C Headphones for iPhone 17 16 15, Type C Earphones with Microphone & Remote Control Noise', 15000.00, 'Earphones', 'assets/images/product_6ae988dfdbd144.69092762.jpg', 25, '2026-11-03 07:10:23'),
(14, 'Power Bank 50000Amh', 'Long-lasting power banks: this can charge your phone for a month', 65000.00, 'Power Banks', 'assets/images/product_6ae98937af5557.57568131.jpg', 5, '2026-11-03 07:11:51'),
(15, 'Faster charger', 'Fast-charging charger 42W', 5500.00, 'Chargers', 'assets/images/product_6ae98f1c226851.47514916.jpg', 6, '2026-11-03 07:37:00'),
(16, 'Charger', 'Charger set with 20W quick charging charger head', 10000.00, 'Chargers', 'assets/images/product_6ae98fea4e5062.59781654.jpg', 7, '2026-11-03 07:40:26'),
(17, 'Iphone 13 screen protector', 'The screen protector on your iPhone covers its glass from damage', 15000.00, 'Screen Protectors', 'assets/images/product_6ae99129bcd5c1.14649501.jpg', 5, '2026-11-03 07:45:45'),
(18, 'Siltcon Solar PowerBank', 'A long-lasting power bank to power up your world; it can charge your phone for months if fully charged.', 150000.00, 'Power Banks', 'assets/images/product_6af88041b37ea6.87829139.jpg', 45, '2026-11-14 15:37:37'),
(20, 'Silicone Cases', 'Beautiful cases to protect your phone. Which one do you like', 12000.00, 'Phone Cases', 'assets/images/product_6af9facead9771.21206217.jpg', 25, '2026-11-15 18:32:46'),
(21, 'Iphone cases', 'Amazing iPhone cases, which suit your phone.', 15000.00, 'Phone Cases', 'assets/images/product_6afc04113e8205.39218295.jpg', 40, '2026-11-17 07:36:49'),
(22, 'Samsung Phone Cases', 'Case for Samsung Galaxy A03S Case with Screen Protector Kickstand Holder Built-in Magnetic Car Mount Shockproof Camera Protective Case for Samsung Galaxy A03S Phone Case (Green) : Cell Phones & Accessories.', 20000.00, 'Phone Cases', 'assets/images/product_6b04818bace011.37843809.jpg', 30, '2026-11-23 18:10:19'),
(23, 'Wireless Bluetooth Headsets', 'Wireless headsets to enlighten your life—get one from us.', 35000.00, 'Earphones', 'assets/images/product_6b048614e7fd51.74503727.jpg', 15, '2026-11-23 18:29:40'),
(24, 'Multi Charge Cable', 'Multi Charging Cable 3M Nylon Braided Cord 3 in 1 USB Charger Cable 10 ft Multiple Ports with Lightning Type C Micro USB Connectors for iPhone, Android, Cell Phones, Tablets, Universal Use Charging Only Check more at https://us.productsoffer.in/multi-charging-cable-3m-nylon-braided-cord-3-in-1-usb-charger-cable-10ft-multiple-ports-with-lightning-type-c-micro-usb-connectors-for-iphone-android-cell-phones-tablets-universal-use-charging-only/', 10000.00, 'USB Cables', 'assets/images/product_6b048719d26428.14118166.jpg', 7, '2026-11-23 18:34:01'),
(25, 'USB-C', 'Type-C data transfer cables are available in all brands.', 6500.00, 'USB Cables', 'assets/images/product_6b04876b2de5e7.27775689.jpg', 10, '2026-11-23 18:35:23'),
(26, 'Mega safe power bank', 'Get your power bank at fair prices', 45000.00, 'Power Banks', 'assets/images/product_6b05399da0d446.58508630.jpg', 12, '2026-11-24 07:15:41'),
(27, 'Faster charger', 'Check out 60W Super Fast Charger for Samsung | Foldable Plug + 2M USB-C Cable (PPS/PD), the latest item.', 8500.00, 'Chargers', 'assets/images/product_6b053a76ef2e39.49105270.jpg', 8, '2026-11-24 07:19:18'),
(28, '120W Fast Charging 4 Ports', '120W Fast Charging 4 Ports Mobile Phone Charger QC3.0 USB Type-C Chargers Dual PD USB Wall Adapter.', 7500.00, 'Chargers', 'assets/images/product_6b0544c3ea2915.07138887.jpg', 20, '2026-11-24 08:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `review`, `created_at`) VALUES
(1, 14, 4, 4, 'Best power bank', '2026-11-16 17:48:00'),
(3, 12, 4, 5, 'Good earbuds', '2026-11-16 17:51:54'),
(4, 18, 4, 5, 'best power long lasting power banks', '2026-11-17 07:11:28'),
(5, 16, 4, 4, 'Reliable chargers.', '2026-11-17 07:31:22'),
(6, 10, 4, 5, 'best screen protectors.', '2026-11-17 07:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Yankho Banda', 'yankhobanda93@gmail.com', '$2y$10$Y8vJgMoUZ/vTMxCNG2/1q.RVflpteBg7cXlW9WIJFmPvi0nRMy9Ae', 'admin', '2026-10-26 07:35:08'),
(3, 'Vanessah Ganda', 'vanessahganda1@gmail.com', '$2y$10$Ew2ZJjmxY2rAE9MBuMBZMu0r8AcsiP3dVA9Urq38FCR0jPhRyL1nO', 'admin', '2026-11-14 15:26:03'),
(4, 'Samantha Mwansambo', 'sammie92@gmail.com', '$2y$10$TMqugVwy4TIxqSuofPS9JOplEJbxHhtTVW8B1pmoGV8sfU5YENOV6', 'customer', '2026-11-16 17:46:55'),
(5, 'Christian Nthara', 'christiannthara192@gmail.com', '$2y$10$U7iBNMW9gUgLWygEyb0czO3yJLnioaxDqe29yR7Pl5EWQU/RjGpw6', 'customer', '2026-11-25 05:47:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
