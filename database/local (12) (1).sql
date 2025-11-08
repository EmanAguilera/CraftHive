-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Jan 06, 2025 at 09:33 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

--
-- This database has been sanitized for public demonstration.
-- All personal and sensitive user data has been replaced with placeholder information.
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `local`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `unique_id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `unique_id`, `fname`, `lname`, `phone`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 246325485, 'Admin', 'User', '09000000000', 'admin@example.com', '$2y$10$6SGZGJex9p/1ggtS2aG7wOwMXDa8ux89/y6yMWSOeGb0EUnhOu3uu', 'Active', '2024-12-21 01:39:25', '2024-12-21 01:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `buyer`
--

CREATE TABLE `buyer` (
  `buyer_id` int(11) NOT NULL,
  `unique_id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buyer`
--

INSERT INTO `buyer` (`buyer_id`, `unique_id`, `fname`, `lname`, `phone`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(3, 161775742, 'Juan', 'Dela Cruz', '09000000001', 'juan.cruz@example.com', '$2y$10$FIUzJwZ4Av3noXixU6O0R.zeYTyXghE8etEizmXNG8gHmeev3lnam', 'Active', '2024-12-21 00:15:18', '2025-01-05 07:55:56'),
(4, 585581597, 'Maria', 'Santos', '09000000002', 'maria.santos@example.com', '$2y$10$IaExdDzIjChMYdhAFw6hN.24gRpm95BOJdvZYUa1QWMSZ6RXS4RjG', 'Active', '2024-12-22 13:05:44', '2024-12-22 13:05:44'),
(5, 900000000, 'Jose', 'Rizal', '09000000003', 'jose.rizal@example.com', '$2y$10$lptxwuvjHq0QoeswEtFdXeTiUnR5UqjTccmpK4l2DAtw.NLurY7Qu', 'Active', '2025-01-02 00:04:42', '2025-01-02 00:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `cart_unique_id` int(11) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `company_rep_id` varchar(255) DEFAULT NULL,
  `delivery_id` int(11) DEFAULT NULL,
  `buyer_name` varchar(50) DEFAULT NULL,
  `delivery_name` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `seller_name` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `shape` varchar(100) DEFAULT NULL,
  `stocks` int(11) DEFAULT NULL,
  `shipping_fee` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `cart_unique_id`, `buyer_id`, `product_id`, `seller_id`, `company_rep_id`, `delivery_id`, `buyer_name`, `delivery_name`, `product_name`, `seller_name`, `price`, `image`, `quantity`, `size`, `color`, `shape`, `stocks`, `shipping_fee`, `created_at`, `updated_at`) VALUES
(65, 228954042, 161775742, 26348243, 82952833, 'AD1K5181-1412', 823521135, 'Juan Dela Cruz', 'Andres Bonifacio', 'Rattan Lamps: Harmony Ambience!', 'Ana Reyes', '450.00', 'product_46.jpg', 1, 'Width: 2.7 to 4 inches. Height: 3.3 to 4.7 inches', 'Light brown', 'Conical', 4, 35, '2025-01-05 12:37:41', '2025-01-05 12:37:41'),
(66, 209761080, 161775742, 864443688, 332573901, '6FA6E873-2936', 406650367, 'Juan Dela Cruz', 'Pedro Penduko', 'Tropical Handwoven Fruit Basket', 'Juan Dela Cruz', '400.00', 'product_1.png', 1, '18 x 24 Inches', 'Light Brown', 'Round', 5, 50, '2025-01-06 18:53:15', '2025-01-06 18:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `unique_id` int(11) NOT NULL,
  `company_rep_id` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `subscription_plan` varchar(50) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT curdate(),
  `expiry_date` date DEFAULT NULL,
  `total_sellers` int(11) DEFAULT 0,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `payment_status` enum('Free','Pending','Paid') DEFAULT 'Free',
  `commission_rate` decimal(3,2) DEFAULT 0.20,
  `gcash_reference_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `unique_id`, `company_rep_id`, `fname`, `lname`, `company_name`, `phone`, `email`, `password`, `subscription_plan`, `price`, `start_date`, `expiry_date`, `total_sellers`, `status`, `payment_status`, `commission_rate`, `gcash_reference_number`, `created_at`, `updated_at`) VALUES
(1, 15149918, '6FA6E873-2936', 'Juan', 'Dela Cruz', 'Likha Creations', '09000000001', 'company.rep1@example.com', '$2y$10$2fz18qZYpf3tlMSExloiI.ylCW3iVJAc/7W3kQBnyuBSuFNm2Pia6', 'Affordable Plan', '500', '2025-01-05', '2025-02-05', 1, 'Active', 'Pending', '0.20', 'REF12345678', '2024-12-21 00:39:10', '2025-01-05 08:40:53'),
(2, 574090383, 'D8BAA7DA-8908', 'Maria', 'Santos', 'Pinoy Crafts Co.', '09000000002', 'company.rep2@example.com', '$2y$10$NvhAdGjrkrMtIJChMIddD.g22gGTQ55L13DPXEuMrpB9FaHKaJ4gS', 'Basic Plan', '500', '2024-12-27', '2025-01-27', 1, 'Active', 'Pending', '0.20', 'REF87654321', '2024-12-21 03:09:41', '2024-12-27 10:15:32'),
(3, 756912164, 'AD1K5181-1412', 'Ana', 'Reyes', 'Manila Weavers', '09000000003', 'company.rep3@example.com', '$2y$10$cB5eujMwVnHrs8b2GOcKWuzHqlhu9JcylIfWwP/WdZ7Tat5VAMKZq', 'Affordable', '500', '2025-02-26', '0000-00-00', 0, 'Active', 'Free', '0.20', NULL, '2024-12-21 20:56:45', '2024-12-27 08:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `deliver_id` int(11) NOT NULL,
  `unique_id` int(11) NOT NULL,
  `company_rep_id` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `total_delivered` int(11) DEFAULT 0,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`deliver_id`, `unique_id`, `company_rep_id`, `fname`, `lname`, `phone`, `email`, `password`, `total_delivered`, `status`, `created_at`, `updated_at`) VALUES
(1, 406650367, '6FA6E873-2936', 'Pedro', 'Penduko', '09000000011', 'delivery1@example.com', '$2y$10$jFTVPBEZS5aBv2lzRoQ/GOwRyO6IvABmIzcTJrKFplJUp6jwKTMJe', 10, 'Active', '2024-12-21 01:31:19', '2025-01-05 10:09:50'),
(2, 901573967, 'D8BAA7DA-8908', 'Andres', 'Bonifacio', '09000000012', 'delivery2@example.com', '$2y$10$1ASsmUh9d7HjrYUWZt3lbul83YPlvr4QinlR7h5Nkd0kVvuoD40MC', NULL, 'Active', '2024-12-21 03:20:39', '2024-12-21 03:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `description`
--

CREATE TABLE `description` (
  `company_id` int(11) NOT NULL,
  `company_unique_id` varchar(255) NOT NULL,
  `company_rep_id` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `payment` varchar(255) NOT NULL,
  `policy` text NOT NULL,
  `gcash_qrcode` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `description`
--

INSERT INTO `description` (`company_id`, `company_unique_id`, `company_rep_id`, `company_name`, `location`, `phone`, `payment`, `policy`, `gcash_qrcode`, `image`, `created_at`, `updated_at`) VALUES
(45, '15149918', '6FA6E873-2936', 'Likha Creations', '123 Sampaguita St, Metro Manila, Philippines', '09000000001', 'Gcash or COD', 'No Refund', 'sample_qr_code.jpg', 'picture_1.jpg', '2024-12-22 03:28:35', '2025-01-04 15:43:44'),
(46, '29841395', 'AD1K5181-1412', 'Manila Weavers', '456 Narra Ave, Quezon City, Philippines', '09000000003', 'Gcash or COD', 'No Refund', 'sample_qr_code.jpg', 'location_3.png', '2025-01-05 02:03:58', '2025-01-05 02:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `msg_id` int(11) NOT NULL,
  `incoming_msg_id` int(255) NOT NULL,
  `outgoing_msg_id` int(255) NOT NULL,
  `msg` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `company_rep_id` varchar(255) DEFAULT NULL,
  `delivery_id` int(11) DEFAULT NULL,
  `delivery_name` varchar(255) DEFAULT NULL,
  `buyer_name` varchar(255) NOT NULL,
  `seller_name` varchar(50) DEFAULT NULL,
  `product_name` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(100) DEFAULT NULL,
  `shape` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `shipping_fee` int(11) DEFAULT NULL,
  `stocks` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `seller` decimal(10,2) DEFAULT NULL,
  `company` decimal(10,2) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `payment` enum('Cash on Delivery','GCash') NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `proof_receipt` varchar(255) DEFAULT NULL,
  `gcash_reference` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Cancelled','Delivered','Approved','Rejected') NOT NULL,
  `approval` enum('Confirm','Rejected','Pending') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `product_id`, `seller_id`, `buyer_id`, `company_rep_id`, `delivery_id`, `delivery_name`, `buyer_name`, `seller_name`, `product_name`, `quantity`, `size`, `shape`, `color`, `shipping_fee`, `stocks`, `total_price`, `seller`, `company`, `shipping_address`, `payment`, `delivery_date`, `proof_receipt`, `gcash_reference`, `status`, `approval`, `created_at`, `updated_at`) VALUES
(21, 588049611, 332573901, 161775742, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Juan Dela Cruz', 'Juan Dela Cruz', 'Sun-Kissed Woven Hat Accessory', 1, 'Diameter: 12-16 inches Brim Width: 4-6 inches', 'Round', 'Natural and Reddish Brown', 35, NULL, '235.00', NULL, NULL, '123 Sampaguita St, Metro Manila, Philippines', '', NULL, 'image-3101.png', 'REF11223344', 'Delivered', 'Confirm', '2025-01-03 18:27:24', '2025-01-05 12:18:18'),
(23, 552709668, 332573901, 161775742, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Juan Dela Cruz', 'Juan Dela Cruz', 'Woven Rattan Tray with Fabric Liner', 1, 'Length: Approximately 12-14 inches Width: Around 8-10 inches Height: Approximately 2 inches', 'Rectangular', 'Natural Rattan with Beige Fabric Liner', 35, NULL, '385.00', NULL, NULL, '123 Sampaguita St, Metro Manila, Philippines', '', NULL, 'image-3101.png', 'REF11223344', 'Pending', 'Pending', '2025-01-03 18:27:24', '2025-01-05 10:09:50'),
(24, 552709668, 332573901, 161775742, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Juan Dela Cruz', 'Juan Dela Cruz', 'Woven Rattan Tray with Fabric Liner', 1, '', '', '', 35, NULL, '385.00', NULL, NULL, '123 Sampaguita St, Metro Manila, Philippines', '', NULL, 'image-3101.png', 'REF55667788', 'Delivered', 'Confirm', '2025-01-05 08:28:11', '2025-01-05 12:17:29'),
(25, 465752516, 332573901, 161775742, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Juan Dela Cruz', 'Juan Dela Cruz', 'Sunflower-Themed Paper Lanterns', 5, '', '', '', 35, NULL, '2535.00', NULL, NULL, '123 Sampaguita St, Metro Manila, Philippines', '', NULL, 'image-3101.png', 'REF55667788', 'Delivered', 'Confirm', '2025-01-05 08:28:11', '2025-01-05 12:18:48'),
(26, 26348243, 82952833, 161775742, 'AD1K5181-1412', 823521135, 'Andres Bonifacio', 'Juan Dela Cruz', 'Ana Reyes', 'Rattan Lamps: Harmony Ambience!', 1, 'Width: 2.7 to 4 inches. Height: 3.3 to 4.7 inches', 'Conical', 'Light brown', 35, NULL, '485.00', NULL, NULL, '123 Sampaguita St, Metro Manila, Philippines', '', NULL, NULL, 'REF99887766', 'Delivered', 'Pending', '2025-01-05 08:29:54', '2025-01-05 10:12:12'),
(27, 595501250, 332573901, 161775742, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Juan Dela Cruz', 'Juan Dela Cruz', 'Vibrant Colorful String Light Balls', 5, '', '', '', 35, NULL, '2035.00', NULL, NULL, '123 Sampaguita St, Metro Manila, Philippines', '', NULL, NULL, 'REF12121212', 'Pending', 'Pending', '2025-01-05 12:40:05', '2025-01-05 12:40:05');

-- --------------------------------------------------------

--
-- Table structure for table `product` is unchanged as it contains no sensitive user data
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_unique_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `company_rep_id` varchar(255) DEFAULT NULL,
  `delivery_id` int(11) DEFAULT NULL,
  `delivery_name` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `seller_name` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `shape` varchar(100) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT NULL,
  `stocks` int(11) DEFAULT NULL,
  `seller_description` tinytext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_unique_id`, `seller_id`, `company_rep_id`, `delivery_id`, `delivery_name`, `product_name`, `seller_name`, `price`, `size`, `color`, `shape`, `category`, `shipping_fee`, `stocks`, `seller_description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(34, 864443688, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Tropical Handwoven Fruit Basket', 'Juan Dela Cruz', '400.00', 'Size: 18 x 24 Inches', 'Color: Light Brown', 'Shape: Round', 'Basket', '50.00', 5, 'A large, round, woven fruit basket with two handles. It’s made of natural materials like rattan.', 'product_1.png', NULL, '2025-01-02 02:35:47', '2025-01-03 06:25:13'),
(35, 588049611, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Sun-Kissed Woven Hat Accessory', 'Juan Dela Cruz', '200.00', 'Size: Diameter: 12-16 inches Brim Width: 4-6 inches', 'Color: Natural and Reddish Brown', 'Shape: Round', 'Hats', '35.00', 4, 'A beautifully woven hat, made from natural materials like palm leaves or straw. It features a unique design with alternating bands of natural and reddish-brown colors, creating a visually appealing pattern.', 'product_2.png', 'Active', '2025-01-03 01:10:45', '2025-01-03 18:27:24'),
(36, 864350842, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Nito Food Cover Keeper - Preserve ', 'Juan Dela Cruz', '400.00', 'Size: Diameter:10-12 inches and Height: 6-8 inches', 'Color: Natural Nito', 'Shape: Dome-Shaped', 'Food Cover', '35.00', 5, 'A dome-shaped, woven basket cover made from natural materials like Nito. It has a handle on top for easy lifting and a circular base that fits snugly over a bowl or plate. The cover is designed to keep food fresh and protected from insects and dust.', 'product_3.png', 'Active', '2025-01-03 01:13:24', '2025-01-04 01:58:09'),
(37, 552709668, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Woven Rattan Tray with Fabric Liner', 'Juan Dela Cruz', '350.00', 'Size: Length: Approximately 12-14 inches Width: Around 8-10 inches Height: Approximately 2 inches', 'Color: Natural Rattan with Beige Fabric Liner', 'Shape: Rectangular', 'Serving ware', '35.00', 3, 'A stylish and functional woven rattan tray with a soft, removable fabric liner. Perfect for serving food, organizing small items, or adding a touch of natural beauty to your home.', 'product_5.png', 'Active', '2025-01-03 01:46:59', '2025-01-05 08:28:11'),
(38, 595501250, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Vibrant Colorful String Light Balls', 'Juan Dela Cruz', '400.00', 'Size: 6-8 inches in diameter', 'Color: Red, Green, Yellow', 'Shape: Spherical', 'Lighting', '35.00', 0, 'Add a touch of whimsy and warmth to your space with these colorful string light balls. Perfect for indoor or outdoor use, these lights create a soft, ambient glow.', 'product_6.png', 'Active', '2025-01-03 01:48:37', '2025-01-05 12:40:05'),
(39, 49968623, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Capiz Shell Lanterns - Elegant Lighting', 'Juan Dela Cruz', '500.00', 'Size: 6-8 inches in diameter', 'Color: White with Gold Accents', 'Shape: Geometric, Polygonal', 'Lighting', '35.00', 5, 'These beautiful capiz shell lanterns add a touch of elegance and traditional Filipino craftsmanship to your home decor. ', 'product_7.png', 'Active', '2025-01-03 01:51:10', '2025-01-03 06:20:12'),
(40, 829028322, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Vintage-Style Wicker Picnic Basket', 'Juan Dela Cruz', '750.00', 'Size: Length:12-14 inches Width: 8-10 inches Height: 6-8 inches', 'Color: Natural Wicker', 'Shape: Rectangular with a Hinged Lid', 'Picnic Essentials', '35.00', 5, 'A charming vintage-style wicker basket. Perfect for outdoor adventures, this basket features a hinged lid and sturdy handles for easy carrying.', 'product_8.png', 'Active', '2025-01-03 01:53:24', '2025-01-03 01:53:24'),
(41, 465752516, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Sunflower-Themed Paper Lanterns', 'Juan Dela Cruz', '500.00', 'Size: 8-10 inches in diameter', 'Color: White with Yellow and Brown Sunflower Design', 'Shape: Spherical', 'Lighting', '35.00', 0, 'Brighten up any space with these cheerful sunflower paper lanterns. Perfect for parties, weddings, or home decor.', 'product_9.png', 'Active', '2025-01-03 01:55:34', '2025-01-05 08:28:11'),
(42, 7002258, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Natural Bamboo Back Scratcher Tool', 'Juan Dela Cruz', '250.00', 'Size: Approximately 12-14 inches long', 'Color: Natural Bamboo', 'Shape: Long and Slender with Multiple Scratching Ends', 'Personal Care', '35.00', 5, 'Relieve back itch and tension with this handy bamboo back scratcher. ', 'product_10.png', 'Active', '2025-01-03 01:57:17', '2025-01-03 06:56:58'),
(43, 632141238, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Compartmented Wooden Platter', 'Juan Dela Cruz', '350.00', 'Size: Length: Approximately 12-14 inches. Width: Around 10-12 inches', 'Color: Natural Wood', 'Shape: Flower-shaped with Five Compartments', 'Serving ware', '35.00', 5, 'This elegant wooden serving platter is perfect for presenting appetizers, dips, or snacks. The five divided compartments keep food organized and visually appealing.', 'product_11.png', 'Active', '2025-01-03 01:59:14', '2025-01-03 06:13:53'),
(44, 480234474, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Exquisite Decorative Chopstick Set', 'Juan Dela Cruz', '400.00', 'Size: Standard Chopstick Length', 'Color: Black Chopsticks with Jute Pouch', 'Shape: Slender and Cylindrical', 'Dining Accessories', '35.00', 5, 'Elevate your dining experience with this stylish set of chopsticks (400 pesos for 4 pieces). ', 'product_12.png', 'Active', '2025-01-03 02:01:30', '2025-01-03 06:58:32'),
(45, 365894078, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Refined Clam-Style Mantel Clock', 'Juan Dela Cruz', '350.00', 'Size: 8-10 inches in diameter', 'Color: White with Gold Accents', 'Shape: Oval with a Decorative Shell Design', 'Clocks', '35.00', 4, 'Add a touch of coastal charm to your home with this beautiful clam wall clock.', 'product_13.png', 'Active', '2025-01-03 02:03:13', '2025-01-03 18:27:24'),
(47, 385747453, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Wooden Serving Tray with Handle', 'Juan Dela Cruz', '400.00', 'Size: Approximately 12-14 inches. Width: Around 8-10 inches', 'Color: Natural Wood', 'Shape: Rectangular with a Handle', 'Serving ware', '35.00', 5, 'This versatile wooden serving tray is perfect for serving appetizers. The unique handle makes it easy to carry and serve.\r\nCategory: Home Decor, Kitchenware, Serving ware', 'product_15.png', 'Active', '2025-01-04 07:31:58', '2025-01-04 07:31:58'),
(48, 347517801, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Wooden Condiment Serving Container', 'Juan Dela Cruz', '450.00', 'Size: Diameter: 6-8 inches, Height: 4-6 inches', 'Color: Natural Wood', 'Shape: Round with a Dome-Shaped Lid and a Cutout for Dispensing', 'Serving ware', '35.00', 5, ' This stylish wooden condiment container is perfect for storing and serving your favorite spices, herbs, or snacks. ', 'product_18.png', 'Active', '2025-01-04 07:37:33', '2025-01-04 07:37:33'),
(49, 220553137, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Mini Seashell Bag Hanging Decor', 'Juan Dela Cruz', '400.00', 'Size: Approximately 5-6 inches tall', 'Color: Red, Blue', 'Shape: Bag-shaped with Seashell and Fish Décor', 'Wall Decor', '35.00', 5, 'Perfect for hanging on a wall or using as a small storage pouch, these decorative pieces feature colorful seashells and a playful fish design.', 'product_19.png', 'Active', '2025-01-04 07:54:07', '2025-01-04 07:54:07'),
(50, 231659643, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Wooden Serving Spoon and Fork Set', 'Juan Dela Cruz', '450.00', 'Size: Standard Serving Spoon and Fork Size', 'Color: Natural Wood with Metal Utensils', 'Shape: Spoon and Fork with Wooden Handles and Fish Design', 'Serving ware', '35.00', 5, ' This stylish serving set includes a wooden spoon and fork with a unique fish design. Perfect for serving salads, pasta, or other dishes.', 'product_20.png', 'Active', '2025-01-04 07:56:42', '2025-01-04 07:56:42'),
(51, 93794801, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Handcrafted Wooden Cups Set', 'Juan Dela Cruz', '180.00', 'Size: 3-4 inches in diameter', 'Color: Natural Wood', 'Shape: Round and Cup-shaped', 'Serving ware', '35.00', 5, 'This set of four small wooden cups is perfect for serving dips, drinks, or nuts.', 'product_40.png', 'Active', '2025-01-04 08:21:04', '2025-01-04 08:21:04'),
(52, 320195862, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Philippine Jeepney Toy Car', 'Juan Dela Cruz', '500.00', 'Size: 5-6 inches long', 'Color: Red, Gold', 'Shape: Jeepney-shaped Toy Car', 'Toys', '35.00', 5, 'This iconic Philippine Jeepney toy car is a perfect souvenir or gift for kids and collectors. It features vibrant colors, detailed designs, and a fun, pull-back mechanism.', 'product_41.png', 'Active', '2025-01-04 08:23:58', '2025-01-04 08:23:58'),
(53, 590197729, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Wooden Mortar and Pestle Set', 'Juan Dela Cruz', '300.00', 'Size: Diameter: Approximately 8-10 inches. Pestle Length: Around 8-10 inches', 'Color: Natural Wood', 'Shape: Bowl-shaped Mortar and Cylindrical Pestle', 'Serving ware', '35.00', 5, 'This traditional wooden mortar and pestle set is perfect for grinding spices, herbs, or making your own homemade sauces. ', 'product_22.png', 'Active', '2025-01-04 09:12:11', '2025-01-04 09:12:11'),
(54, 725952186, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', ': Capiz Shell Coasters Filipino Designs', 'Juan Dela Cruz', '300.00', 'Size: Approximately 4-5 inches in diameter', 'Color: White with Colorful Designs', 'Shape: Oval', 'Souvenirs', '35.00', 5, 'Add a touch of Filipino culture to your home with these beautiful Capiz shell coasters. Each coaster features a unique design showcasing iconic Philippine symbols like the jeepney, food, and the Philippine flag.', 'product_23.png', 'Active', '2025-01-04 09:14:52', '2025-01-04 09:14:52'),
(55, 649934950, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Elegant Shell Chandelier Pendants', 'Juan Dela Cruz', '1500.00', 'Size: Approximately 2-3 inches in diameter', 'Color: White', 'Shape: Round and Flat', 'Lighting', '35.00', 5, 'Create a stunning and elegant chandelier with these beautiful capiz shell pendants. ', 'product_24.png', 'Active', '2025-01-04 11:12:18', '2025-01-04 11:12:18'),
(56, 506687386, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Organic Coconut Shell Spoon', 'Juan Dela Cruz', '300.00', 'Size: Approximately 12-14 inches long', 'Color: Natural Wood and Coconut Shell', 'Shape: Spoon-shaped with a Coconut Shell Bowl and Wooden Handle', 'Serving ware', '35.00', 5, 'This versatile round rattan basket is perfect for storage and serving.', 'product_25.png', 'Active', '2025-01-04 11:16:18', '2025-01-04 11:16:18'),
(57, 174067199, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Charming Kitchen Wall Decor', 'Juan Dela Cruz', '350.00', 'Size: 12-14 inches tall', 'Color: Natural Colors with Red Accents', 'Shape: Rectangular', 'Home Decor', '35.00', 5, 'This charming kitchen wall decor features a variety of natural materials like dried vegetables, burlap, and wood. ', 'product_27.png', 'Active', '2025-01-04 11:20:50', '2025-01-04 11:20:50'),
(58, 138879626, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Handwoven Rattan Table Lamp', 'Juan Dela Cruz', '700.00', 'Size: 12-14 inches tall', 'Color: Natural Rattan', 'Shape: Dome-shaped Shade with a Cylindrical Base', 'Lighting', '35.00', 5, 'This unique and eco-friendly table lamp is made entirely of woven rattan. ', 'product_29.png', 'Active', '2025-01-04 13:05:58', '2025-01-04 13:05:58'),
(59, 111387388, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Eco-Friendly Colorful Woven Bag', 'Juan Dela Cruz', '350.00', 'Size: Length: Approximately 12-14 inches. Height: Around 8-10 inches. Width: Approximately 6-8 inche', 'Color: Pink and White', 'Shape: Rectangular with Two Handles', 'Bag', '35.00', 5, 'This stylish and eco-friendly bag is made from woven natural fibers. The vibrant pink and white pattern add a pop of color to any outfit. ', 'product_30.png', 'Active', '2025-01-04 13:08:32', '2025-01-04 13:08:32'),
(60, 774439342, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Seashell Tissue Box Cover', 'Juan Dela Cruz', '400.00', 'Size: Standard Tissue Box Size', 'Color: Assorted Shell Colors', 'Shape: Rectangular with Seashell Design', 'Tissue Box Covers', '35.00', 5, 'This beautiful seashell tissue box cover adds a touch of coastal charm to your home. The colorful and textured shells create a unique and eye-catching piece.', 'product_31.png', 'Active', '2025-01-04 13:10:53', '2025-01-04 13:10:53'),
(61, 90424263, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Capiz Shell Tissue Box Cover', 'Juan Dela Cruz', '400.00', 'Size: Standard Tissue Box Size', 'Color: White with Gold Accents', 'Shape: Rectangular with a Shell-like Design', 'Tissue Box Covers', '35.00', 5, 'This elegant tissue box cover is made from beautiful capiz shells, adding a touch of coastal charm to your home. ', 'product_33.png', 'Active', '2025-01-04 15:22:10', '2025-01-04 15:22:10'),
(62, 976886705, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Handcrafted Woven Bamboo Placemats', 'Juan Dela Cruz', '300.00', 'Size: Standard Placemat Size', 'Color: Natural Bamboo with Black Accents, Light Brown, Black and White, Multiple Arrangement of Red,', 'Shape: Rectangular', 'Serving ware', '35.00', 5, 'These stylish and eco-friendly placemats are made from woven bamboo. ', 'product_37.png', 'Active', '2025-01-04 15:26:51', '2025-01-04 15:26:51'),
(63, 565462224, 332573901, '6FA6E873-2936', 406650367, 'Pedro Penduko', 'Luxurious Elegant Folding Fan', 'Juan Dela Cruz', '167.00', 'Size: Approximately 8-10 inches long; Unfolded: Varies', 'Color: White Lace with Wooden Handle', 'Shape: Folding Fan with Lace Design', 'Folding Fan', '35.00', 5, 'This elegant folding fan is perfect for cooling down on a hot day or adding a touch of vintage charm to your outfit. ', 'product_32.png', 'Active', '2025-01-04 15:38:07', '2025-01-04 15:38:07'),
(64, 26348243, 82952833, 'AD1K5181-1412', 823521135, 'Andres Bonifacio', 'Rattan Lamps: Harmony Ambience!', 'Ana Reyes', '450.00', 'Size: Width: 2.7 to 4 inches. Height: 3.3 to 4.7 inches', 'Color:  Light brown, tan', 'Shape: Conical, Hourglass, Cylindrical/Drum, Rounded', 'Lighting', '35.00', 4, 'Discover serene illumination with Rattan Lamps: Harmony Ambiance! Illuminate every corner effortlessly; whether suspended above dining tables, nestled beside reading nooks, or accentuating outdoor patios, experience gentle brilliance at dusk. ', 'product_46.jpg', 'Active', '2025-01-05 02:18:02', '2025-01-05 08:29:54');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `order_id`, `user_id`, `seller_id`, `product_id`, `rating`, `review_text`, `created_at`) VALUES
(6, 3, 784014562, 713367773, 30, 5, 'Amazing product, highly recommended!', '2024-12-29 06:48:24'),
(7, 4, 784014562, 332573901, 33, 3, 'It was okay, not bad but not great.', '2024-12-29 06:48:25'),
(9, 6, 784014562, 332573901, 35, 5, 'This was an awesome purchase!', '2024-12-29 06:48:25'),
(19, 14, 161775742, 332573901, 33, 3, 'I think it is okay', '2024-12-29 23:38:48'),
(27, 25, 161775742, 332573901, 41, 4, 'I love the overall services. Thank you!', '2025-01-05 10:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `seller_id` int(11) NOT NULL,
  `unique_id` int(11) NOT NULL,
  `company_rep_id` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `commission_rate` decimal(3,2) DEFAULT 0.80,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `seller`
--

INSERT INTO `seller` (`seller_id`, `unique_id`, `company_rep_id`, `fname`, `lname`, `phone`, `email`, `password`, `status`, `commission_rate`, `created_at`, `updated_at`) VALUES
(3, 332573901, '6FA6E873-2936', 'Juan', 'Dela Cruz', '09000000021', 'seller1@example.com', '$2y$10$4lZJ2VVPlwsFg76r/4fSYe0mfa0XpfIkWWf/lG3vttadnnmm5tTSy', 'Active', '0.80', '2024-12-21 02:07:40', '2025-01-02 01:58:35'),
(4, 159191419, 'D8BAA7DA-8908', 'Maria', 'Santos', '09000000022', 'seller2@example.com', '$2y$10$DKkKjOTgz9xBDWOihe7GzeO7RZX3J.vYDwQZscNxs5flttR4flGhG', 'Active', '0.80', '2025-12-21 03:12:10', '2024-12-31 20:32:01'),
(5, 82952833, 'AD1K5181-1412', 'Ana', 'Reyes', '09000000023', 'seller3@example.com', '$2y$10$2k/bijf69iucjsv9wqwjmubeNO4z/cRoWs0WXplKLe3/zb8dOZlsK', 'Active', '0.80', '2024-12-26 04:37:15', '2024-12-26 06:07:35');


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `unique_id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `unique_id`, `fname`, `lname`, `email`, `password`, `img`, `status`) VALUES
(2, 1241421777, 'Test', 'User', 'test.user@example.com', '84fdd11bbc315d9795a84ffcdecd232b', 'profile_placeholder.jpg', 'Active now');

--
-- Indexes for dumped tables are unchanged
--

--
-- AUTO_INCREMENT for dumped tables are unchanged
--

ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `buyer`
  ADD PRIMARY KEY (`buyer_id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `delivery`
  ADD PRIMARY KEY (`deliver_id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `description`
  ADD PRIMARY KEY (`company_id`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`msg_id`);

ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`);

ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `unique_id` (`product_unique_id`),
  ADD KEY `seller_id` (`seller_id`,`company_rep_id`,`delivery_id`,`delivery_name`,`product_name`);

ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `seller`
  ADD PRIMARY KEY (`seller_id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_rep_id` (`company_rep_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `buyer`
  MODIFY `buyer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `delivery`
  MODIFY `deliver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `description`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

ALTER TABLE `messages`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

ALTER TABLE `seller`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;