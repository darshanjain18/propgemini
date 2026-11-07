-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2026 at 12:21 AM
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
-- Database: `propgemini`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
(1, 'PropGemini Admin', 'admin@propgemini.com', '$2y$10$nL3rNHMtIEWpD8Ny4mmi3eDorcDosrfj3pwOHkqXDE.WoEXCLAmju', 'admin', 1, '2026-11-02 20:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(150) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, NULL, '2026-11-02 20:58:37'),
(2, 1, 'login', NULL, NULL, NULL, '2026-11-02 20:58:59'),
(3, 1, 'login', NULL, NULL, NULL, '2026-11-02 21:00:50'),
(4, 1, 'login', NULL, NULL, NULL, '2026-11-02 21:13:07'),
(5, 1, 'logout', NULL, NULL, NULL, '2026-11-02 21:14:25'),
(6, 1, 'login', NULL, NULL, NULL, '2026-11-02 21:14:56'),
(7, 1, 'logout', NULL, NULL, NULL, '2026-11-02 21:15:08'),
(8, 1, 'login', NULL, NULL, NULL, '2026-11-02 21:20:30'),
(9, 1, 'enquiry_update', 'enquiry', 1, 'qualified', '2026-11-02 21:25:01'),
(10, 1, 'settings_update', NULL, NULL, NULL, '2026-11-02 21:26:05'),
(11, 1, 'password_change', NULL, NULL, NULL, '2026-11-02 21:26:31'),
(12, 1, 'property_delete', 'property', 7, 'Horizon Address', '2026-11-02 21:36:08'),
(13, 1, 'settings_update', NULL, NULL, NULL, '2026-11-02 21:53:00'),
(14, 1, 'settings_update', NULL, NULL, NULL, '2026-11-02 21:53:00'),
(15, 1, 'run_automations', NULL, NULL, '{\"visit_reminders\":0,\"stale_leads\":1,\"rera_alerts\":0,\"featured_rotated\":3,\"sitemap\":true}', '2026-11-02 22:24:35'),
(16, 1, 'featured_rotation', 'property', NULL, 'Rotated 3', '2026-11-02 22:24:36'),
(17, 1, 'featured_rotation', 'property', NULL, 'Rotated 3', '2026-11-02 22:24:37'),
(18, 1, 'featured_rotation', 'property', NULL, 'Rotated 3', '2026-11-02 22:24:38'),
(19, 1, 'featured_rotation', 'property', NULL, 'Rotated 3', '2026-11-02 22:24:44'),
(20, 1, 'featured_rotation', 'property', NULL, 'Rotated 3', '2026-11-02 22:24:47'),
(21, 1, 'logout', NULL, NULL, NULL, '2026-11-02 22:33:14');

-- --------------------------------------------------------

--
-- Table structure for table `admin_alerts`
--

CREATE TABLE `admin_alerts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alert_type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_alerts`
--

INSERT INTO `admin_alerts` (`id`, `alert_type`, `title`, `body`, `entity_type`, `entity_id`, `severity`, `is_read`, `created_at`) VALUES
(1, 'stale_lead', 'No follow-up for 3 days: Asha Mehta', 'Enquiry #2 still marked new since 2026-10-29 15:16:30', 'enquiry', 2, 'critical', 1, '2026-11-02 22:19:19'),
(2, 'featured_rotation', 'Featured properties rotated', 'Auto-rotation selected 3 published listings.', NULL, NULL, 'info', 1, '2026-11-02 22:19:19'),
(3, 'stale_lead', 'No follow-up for 3 days: Asha Mehta', 'Enquiry #2 still marked new since 2026-10-29 15:16:30', 'enquiry', 2, 'critical', 1, '2026-11-02 22:20:25'),
(4, 'featured_rotation', 'Featured properties rotated', 'Auto-rotation selected 3 published listings.', NULL, NULL, 'info', 1, '2026-11-02 22:20:25');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_seen_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `city_id`, `name`) VALUES
(104, 1, 'Airoli'),
(2, 1, 'Andheri East'),
(1, 1, 'Andheri West'),
(4, 1, 'Bandra East'),
(3, 1, 'Bandra West'),
(107, 1, 'Belapur'),
(90, 1, 'Bhandup'),
(99, 1, 'Bhayandar'),
(5, 1, 'Borivali'),
(86, 1, 'Borivali East'),
(85, 1, 'Borivali West'),
(97, 1, 'Byculla'),
(93, 1, 'Chembur'),
(67, 1, 'Churchgate'),
(65, 1, 'Colaba'),
(66, 1, 'Cuffe Parade'),
(74, 1, 'Dadar'),
(87, 1, 'Dahisar'),
(102, 1, 'Dombivli'),
(88, 1, 'Ghatkopar'),
(6, 1, 'Goregaon'),
(83, 1, 'Jogeshwari'),
(81, 1, 'Juhu'),
(103, 1, 'Kalyan'),
(84, 1, 'Kandivali'),
(92, 1, 'Kanjurmarg'),
(77, 1, 'Khar West'),
(108, 1, 'Kharghar'),
(94, 1, 'Kurla'),
(10, 1, 'Lower Parel'),
(71, 1, 'Mahalaxmi'),
(76, 1, 'Mahim'),
(69, 1, 'Malabar Hill'),
(7, 1, 'Malad'),
(68, 1, 'Marine Lines'),
(75, 1, 'Matunga'),
(98, 1, 'Mira Road'),
(91, 1, 'Mulund'),
(12, 1, 'Navi Mumbai'),
(106, 1, 'Nerul'),
(110, 1, 'Panvel'),
(73, 1, 'Parel'),
(8, 1, 'Powai'),
(72, 1, 'Prabhadevi'),
(79, 1, 'Santacruz East'),
(78, 1, 'Santacruz West'),
(95, 1, 'Sion'),
(64, 1, 'South Mumbai'),
(70, 1, 'Tardeo'),
(11, 1, 'Thane'),
(109, 1, 'Ulwe'),
(100, 1, 'Vasai'),
(105, 1, 'Vashi'),
(82, 1, 'Versova'),
(89, 1, 'Vikhroli'),
(80, 1, 'Vile Parle'),
(101, 1, 'Virar'),
(96, 1, 'Wadala'),
(9, 1, 'Worli'),
(144, 2, 'Akurdi'),
(119, 2, 'Amanora Park Town'),
(21, 2, 'Aundh'),
(14, 2, 'Balewadi'),
(13, 2, 'Baner'),
(127, 2, 'Bavdhan'),
(134, 2, 'Bibwewadi'),
(113, 2, 'Camp'),
(151, 2, 'Chakan'),
(146, 2, 'Chinchwad'),
(112, 2, 'Deccan'),
(122, 2, 'Dhanori'),
(132, 2, 'Dhayari'),
(130, 2, 'Erandwane'),
(19, 2, 'Hadapsar'),
(16, 2, 'Hinjawadi'),
(114, 2, 'Kalyani Nagar'),
(128, 2, 'Karve Nagar'),
(133, 2, 'Katraj'),
(15, 2, 'Kharadi'),
(135, 2, 'Kondhwa'),
(20, 2, 'Koregaon Park'),
(22, 2, 'Kothrud'),
(123, 2, 'Lohegaon'),
(118, 2, 'Magarpatta'),
(126, 2, 'Mahalunge'),
(120, 2, 'Manjri'),
(138, 2, 'Mohammadwadi'),
(150, 2, 'Moshi'),
(117, 2, 'Mundhwa'),
(137, 2, 'NIBM'),
(143, 2, 'Nigdi'),
(124, 2, 'Pashan'),
(139, 2, 'Phursungi'),
(148, 2, 'Pimple Nilakh'),
(147, 2, 'Pimple Saudagar'),
(145, 2, 'Pimpri'),
(141, 2, 'Punawale'),
(149, 2, 'Rahatani'),
(142, 2, 'Ravet'),
(111, 2, 'Shivajinagar'),
(131, 2, 'Sinhagad Road'),
(125, 2, 'Sus'),
(152, 2, 'Talegaon'),
(140, 2, 'Tathawade'),
(136, 2, 'Undri'),
(18, 2, 'Viman Nagar'),
(116, 2, 'Wadgaon Sheri'),
(121, 2, 'Wagholi'),
(17, 2, 'Wakad'),
(129, 2, 'Warje'),
(115, 2, 'Yerawada'),
(27, 3, 'DLF Phase 1'),
(28, 3, 'DLF Phase 2'),
(29, 3, 'DLF Phase 3'),
(153, 3, 'DLF Phase 4'),
(154, 3, 'DLF Phase 5'),
(25, 3, 'Dwarka Expressway'),
(24, 3, 'Golf Course Extension Road'),
(23, 3, 'Golf Course Road'),
(227, 3, 'Manesar'),
(33, 3, 'New Gurgaon'),
(160, 3, 'Nirvana Country'),
(161, 3, 'Palam Vihar'),
(214, 3, 'Sector 102'),
(215, 3, 'Sector 103'),
(216, 3, 'Sector 104'),
(217, 3, 'Sector 106'),
(218, 3, 'Sector 107'),
(219, 3, 'Sector 108'),
(220, 3, 'Sector 109'),
(221, 3, 'Sector 110'),
(222, 3, 'Sector 111'),
(223, 3, 'Sector 112'),
(224, 3, 'Sector 113'),
(225, 3, 'Sector 114'),
(226, 3, 'Sector 115'),
(162, 3, 'Sector 14'),
(163, 3, 'Sector 15'),
(164, 3, 'Sector 21'),
(165, 3, 'Sector 22'),
(166, 3, 'Sector 23'),
(167, 3, 'Sector 28'),
(168, 3, 'Sector 29'),
(169, 3, 'Sector 31'),
(170, 3, 'Sector 37'),
(171, 3, 'Sector 38'),
(172, 3, 'Sector 39'),
(173, 3, 'Sector 43'),
(174, 3, 'Sector 45'),
(175, 3, 'Sector 46'),
(176, 3, 'Sector 47'),
(177, 3, 'Sector 48'),
(30, 3, 'Sector 49'),
(178, 3, 'Sector 50'),
(179, 3, 'Sector 51'),
(180, 3, 'Sector 52'),
(181, 3, 'Sector 53'),
(182, 3, 'Sector 54'),
(183, 3, 'Sector 55'),
(31, 3, 'Sector 56'),
(184, 3, 'Sector 57'),
(185, 3, 'Sector 58'),
(186, 3, 'Sector 59'),
(187, 3, 'Sector 60'),
(188, 3, 'Sector 61'),
(189, 3, 'Sector 62'),
(190, 3, 'Sector 63'),
(191, 3, 'Sector 64'),
(32, 3, 'Sector 65'),
(192, 3, 'Sector 66'),
(193, 3, 'Sector 67'),
(194, 3, 'Sector 68'),
(195, 3, 'Sector 69'),
(196, 3, 'Sector 70'),
(197, 3, 'Sector 71'),
(198, 3, 'Sector 72'),
(199, 3, 'Sector 76'),
(200, 3, 'Sector 77'),
(201, 3, 'Sector 78'),
(202, 3, 'Sector 79'),
(203, 3, 'Sector 80'),
(204, 3, 'Sector 81'),
(205, 3, 'Sector 82'),
(206, 3, 'Sector 83'),
(207, 3, 'Sector 84'),
(208, 3, 'Sector 85'),
(209, 3, 'Sector 86'),
(210, 3, 'Sector 89'),
(211, 3, 'Sector 90'),
(212, 3, 'Sector 91'),
(213, 3, 'Sector 92'),
(228, 3, 'Sohna'),
(26, 3, 'Sohna Road'),
(158, 3, 'South City 1'),
(159, 3, 'South City 2'),
(155, 3, 'Sushant Lok 1'),
(156, 3, 'Sushant Lok 2'),
(157, 3, 'Sushant Lok 3');

-- --------------------------------------------------------

--
-- Table structure for table `brochure_requests`
--

CREATE TABLE `brochure_requests` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `country_code` varchar(10) DEFAULT '+91',
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status` enum('New','Contacted','Completed') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`) VALUES
(3, 'Gurgaon'),
(1, 'Mumbai'),
(2, 'Pune');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `whatsapp_consent` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('new','contacted','qualified','closed','spam') NOT NULL DEFAULT 'new',
  `source` varchar(100) NOT NULL DEFAULT 'website',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `property_id` int(11) DEFAULT NULL,
  `follow_up_date` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `assigned_admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `whatsapp_consent`, `status`, `source`, `created_at`, `updated_at`, `property_id`, `follow_up_date`, `admin_notes`, `assigned_admin_id`) VALUES
(1, 'Asha Mehta', 'asha@example.com', '+91 9876543210', 'Site visit', 'I would like to visit this weekend.', 0, 'qualified', 'visit-form', '2026-10-29 09:45:30', '2026-11-02 21:25:01', NULL, NULL, NULL, 1),
(2, 'Asha Mehta', 'asha@example.com', '+91 9876543210', 'Brochure request', 'Property: The Canopy Residences (Baner, Pune). Please send the brochure.', 0, 'new', 'visit-form', '2026-10-29 09:46:30', '2026-10-29 09:46:30', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `city_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `property_status` varchar(100) DEFAULT NULL,
  `bhk` varchar(50) DEFAULT NULL,
  `min_price` decimal(15,2) DEFAULT NULL,
  `max_price` decimal(15,2) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `overview` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `possession_date` date DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `map_url` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `featured_rotated_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `removal_flagged_at` datetime DEFAULT NULL,
  `unavailable_reason` varchar(255) DEFAULT NULL,
  `publication_status` enum('Draft','Published','Unpublished') DEFAULT 'Draft',
  `construction_status` varchar(100) DEFAULT NULL,
  `construction_progress` tinyint(3) UNSIGNED DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `rera_number` varchar(100) DEFAULT NULL,
  `rera_expiry` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `name`, `slug`, `city_id`, `area_id`, `property_type`, `property_status`, `bhk`, `min_price`, `max_price`, `address`, `overview`, `amenities`, `possession_date`, `latitude`, `longitude`, `map_url`, `is_featured`, `featured_rotated_at`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`, `removal_flagged_at`, `unavailable_reason`, `publication_status`, `construction_status`, `construction_progress`, `seo_title`, `seo_description`, `rera_number`, `rera_expiry`) VALUES
(1, 'The Canopy Residences', 'canopy', 2, 13, 'Apartment', 'Ready', '3 BHK', 16500000.00, 16500000.00, 'Baner, Pune', 'A demonstration residence with a calm interior and room for a family.', 'Clubhouse, Landscaped gardens, Fitness studio, Covered parking', '2025-12-01', 18.5590000, 73.7868000, NULL, 1, '2026-11-03 03:54:47', 1, 1, '2026-11-02 20:44:12', '2026-11-02 22:28:17', NULL, NULL, NULL, 'Published', 'Ready', 100, 'The Canopy Residences | PropGemini', 'A demonstration residence with a calm interior and room for a family.', NULL, NULL),
(2, 'Arbor House', 'arbor', 1, 8, 'Apartment', 'Under Construction', '2 BHK', 22500000.00, 22500000.00, 'Powai, Mumbai', 'A sample city apartment arranged around light and everyday comfort.', 'Swimming pool, Clubhouse, Children play area', '2027-06-01', 19.1176000, 72.9060000, NULL, 0, '2026-11-03 03:54:37', 1, 2, '2026-11-02 20:44:12', '2026-11-02 22:28:17', NULL, NULL, NULL, 'Published', 'Under Construction', 55, 'Arbor House | PropGemini', 'A sample city apartment arranged around light and everyday comfort.', NULL, NULL),
(3, 'Verdant Private Villas', 'villas', 2, 17, 'Villa', 'New Launch', '4 BHK', 31000000.00, 31000000.00, 'Wakad, Pune', 'A sample villa layout with private outdoor space.', 'Private garden, Clubhouse, Security', '2028-03-01', 18.5985000, 73.7634000, NULL, 0, '2026-11-03 03:54:38', 1, 3, '2026-11-02 20:44:12', '2026-11-02 22:28:17', NULL, NULL, NULL, 'Published', 'New Launch', 10, 'Verdant Private Villas | PropGemini', 'A sample villa layout with private outdoor space.', NULL, NULL),
(4, 'Meridian Living', 'meridian', 1, 1, 'Apartment', 'Ready', '3 BHK', 19500000.00, 19500000.00, 'Andheri West, Mumbai', 'A sample urban residence with a straightforward plan.', 'Gym, Rooftop deck, Power backup', '2024-08-01', 19.1364000, 72.8277000, NULL, 0, '2026-11-03 03:54:44', 1, 4, '2026-11-02 20:44:12', '2026-11-02 22:28:17', NULL, NULL, NULL, 'Published', 'Ready', 100, 'Meridian Living | PropGemini', 'A sample urban residence with a straightforward plan.', NULL, NULL),
(5, 'Eastline Apartments', 'eastline', 2, 15, 'Apartment', 'Under Construction', '2 BHK', 9800000.00, 9800000.00, 'Kharadi, Pune', 'A compact sample apartment near Pune’s eastern business corridor.', 'Clubhouse, Jogging track, Visitor parking', '2027-12-01', 18.5515000, 73.9373000, NULL, 1, '2026-11-03 03:54:47', 1, 5, '2026-11-02 20:44:12', '2026-11-02 22:28:17', NULL, NULL, NULL, 'Published', 'Under Construction', 40, 'Eastline Apartments | PropGemini', 'A compact sample apartment near Pune’s eastern business corridor.', NULL, NULL),
(6, 'The Foundry Offices', 'offices', 1, 10, 'Commercial', 'Ready', 'Office', NULL, NULL, 'Lower Parel, Mumbai', 'A sample workspace listing. Availability is still to be confirmed.', 'Reception lobby, High-speed lifts, Parking', NULL, 18.9935000, 72.8290000, NULL, 1, '2026-11-03 03:54:47', 1, 6, '2026-11-02 20:44:12', '2026-11-02 22:28:17', NULL, NULL, NULL, 'Published', 'Ready', 100, 'The Foundry Offices | PropGemini', 'A sample workspace listing. Availability is still to be confirmed.', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `property_amenities`
--

CREATE TABLE `property_amenities` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amenity_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_amenities`
--

INSERT INTO `property_amenities` (`id`, `property_id`, `amenity_name`) VALUES
(1, 1, 'Clubhouse'),
(2, 1, 'Landscaped gardens'),
(3, 1, 'Fitness studio'),
(4, 1, 'Covered parking'),
(5, 2, 'Swimming pool'),
(6, 2, 'Clubhouse'),
(7, 2, 'Children play area'),
(8, 3, 'Private garden'),
(9, 3, 'Clubhouse'),
(10, 3, 'Security'),
(11, 4, 'Gym'),
(12, 4, 'Rooftop deck'),
(13, 4, 'Power backup'),
(14, 5, 'Clubhouse'),
(15, 5, 'Jogging track'),
(16, 5, 'Visitor parking'),
(17, 6, 'Reception lobby'),
(18, 6, 'High-speed lifts'),
(19, 6, 'Parking');

-- --------------------------------------------------------

--
-- Table structure for table `property_brochures`
--

CREATE TABLE `property_brochures` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `brochure_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_edit_history`
--

CREATE TABLE `property_edit_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `change_summary` varchar(255) NOT NULL,
  `before_json` longtext DEFAULT NULL,
  `after_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_floor_plans`
--

CREATE TABLE `property_floor_plans` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `bhk` varchar(50) DEFAULT NULL,
  `area_sqft` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `media_type` enum('image','video_thumbnail') DEFAULT 'image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `sort_order`, `media_type`) VALUES
(1, 1, 'public/images/canopy.jpg', 0, 'image'),
(2, 2, 'public/images/arbor.jpg', 0, 'image'),
(3, 3, 'public/images/villas.jpg', 0, 'image'),
(4, 4, 'public/images/meridian.jpg', 0, 'image'),
(5, 5, 'public/images/eastline.jpg', 0, 'image'),
(6, 6, 'public/images/offices.jpg', 0, 'image');

-- --------------------------------------------------------

--
-- Table structure for table `property_price_history`
--

CREATE TABLE `property_price_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` int(11) NOT NULL,
  `old_min_price` decimal(15,2) DEFAULT NULL,
  `new_min_price` decimal(15,2) DEFAULT NULL,
  `old_max_price` decimal(15,2) DEFAULT NULL,
  `new_max_price` decimal(15,2) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_status_history`
--

CREATE TABLE `property_status_history` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `old_status` varchar(100) DEFAULT NULL,
  `new_status` varchar(100) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_unit_types`
--

CREATE TABLE `property_unit_types` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `configuration` varchar(50) NOT NULL,
  `carpet_area_sqft` decimal(10,2) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `floor_plan_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_unit_types`
--

INSERT INTO `property_unit_types` (`id`, `property_id`, `configuration`, `carpet_area_sqft`, `price`, `floor_plan_path`, `description`, `is_available`, `sort_order`, `created_at`) VALUES
(1, 1, '3 BHK', 1450.00, 16500000.00, NULL, 'A demonstration residence with a calm interior and room for a family.', 1, 0, '2026-11-02 20:44:12'),
(2, 2, '2 BHK', 1080.00, 22500000.00, NULL, 'A sample city apartment arranged around light and everyday comfort.', 1, 0, '2026-11-02 20:44:12'),
(3, 3, '4 BHK', 2600.00, 31000000.00, NULL, 'A sample villa layout with private outdoor space.', 1, 0, '2026-11-02 20:44:12'),
(4, 4, '3 BHK', 1320.00, 19500000.00, NULL, 'A sample urban residence with a straightforward plan.', 1, 0, '2026-11-02 20:44:12'),
(5, 5, '2 BHK', 980.00, 9800000.00, NULL, 'A compact sample apartment near Pune’s eastern business corridor.', 1, 0, '2026-11-02 20:44:12'),
(6, 6, 'Office', 2000.00, NULL, NULL, 'A sample workspace listing. Availability is still to be confirmed.', 1, 0, '2026-11-02 20:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `property_videos`
--

CREATE TABLE `property_videos` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `video_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `duration_seconds` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_videos`
--

INSERT INTO `property_videos` (`id`, `property_id`, `video_path`, `sort_order`, `duration_seconds`) VALUES
(1, 1, 'public/videos/canopy-tour.mp4', 0, NULL),
(2, 2, 'public/videos/arbor-tour.mp4', 0, NULL),
(3, 3, 'public/videos/verdant-villas-tour.mp4', 0, NULL),
(4, 4, 'public/videos/meridian-tour.mp4', 0, NULL),
(5, 5, 'public/videos/eastline-tour.mp4', 0, NULL),
(6, 6, 'public/videos/foundry-offices-tour.mp4', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `property_views`
--

CREATE TABLE `property_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` int(11) NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `viewer_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_views`
--

INSERT INTO `property_views` (`id`, `property_id`, `viewed_at`, `viewer_ip`) VALUES
(1, 6, '2026-11-03 03:56:17', '127.0.0.1'),
(2, 1, '2026-11-03 04:04:10', '127.0.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'whatsapp_number', '919323443610', '2026-11-02 21:53:00'),
(2, 'contact_phone', '+91 9323443610', '2026-11-02 21:53:00'),
(3, 'contact_email', 'info@propgemini.com', '2026-11-02 21:53:00'),
(4, 'contact_address', 'Pune · Mumbai · Gurgaon', '2026-11-02 20:44:12'),
(5, 'about_blurb', 'PropGemini curates premium residences across India’s key cities.', '2026-11-02 20:44:12'),
(6, 'emi_default_loan', '5000000', '2026-11-02 20:44:12'),
(7, 'emi_default_rate', '8.5', '2026-11-02 20:44:12'),
(8, 'emi_default_years', '20', '2026-11-02 20:44:12'),
(17, 'terms_title', 'Terms & Conditions', '2026-11-02 21:41:53'),
(18, 'terms_content', '', '2026-11-02 21:41:53'),
(19, 'privacy_title', 'Privacy Policy', '2026-11-02 21:41:53'),
(20, 'privacy_content', '', '2026-11-02 21:41:53'),
(21, 'legal_terms_title', 'Terms & Conditions', '2026-11-02 21:44:35'),
(22, 'legal_terms_body', 'Last updated: November 2026\r\n\r\n1. About these terms\r\nThese Terms & Conditions govern your use of the PropGemini website and any property enquiry, brochure request, or site-visit request you submit through it. By using this website you agree to these terms.\r\n\r\n2. Property information\r\nListings, prices, configurations, carpet areas, possession timelines, amenities, floor plans, images, videos and project status shown on this website are indicative and may change without notice. Developers, RERA filings, sanctioned plans and on-site conditions prevail. Always verify critical details with a PropGemini advisor and the project developer before making any purchase decision.\r\n\r\n3. No offer or reservation\r\nBrowsing listings or submitting an enquiry does not create a booking, reservation, allotment, agreement to sell, or any contractual right in a property. Any transaction is subject to separate documentation between you, the developer and/or the seller.\r\n\r\n4. Advisory role\r\nPropGemini acts as a property advisory / facilitation platform. We do not guarantee financing approval, investment returns, rental yields, appreciation, construction quality, or project completion timelines.\r\n\r\n5. Enquiries and site visits\r\nContact details and preferences you share are used to respond to your request and arrange viewings. Site-visit slots are subject to availability, project access rules and confirmation by our team.\r\n\r\n6. Acceptable use\r\nYou agree not to misuse the website, scrape content without permission, upload harmful material, or attempt unauthorised access to systems or data.\r\n\r\n7. Intellectual property\r\nWebsite design, copy, branding and curated content belong to PropGemini or its licensors. You may not copy or redistribute them for commercial use without written consent.\r\n\r\n8. Third-party projects\r\nProject names, logos and materials may belong to respective developers. References are for identification of listed inventory only.\r\n\r\n9. Limitation of liability\r\nTo the fullest extent permitted by law, PropGemini is not liable for indirect or consequential loss arising from use of this website or reliance on published information. Confirm all commercial terms independently.\r\n\r\n10. Changes\r\nWe may update these terms from time to time. Continued use of the website after changes means you accept the revised terms.\r\n\r\n11. Contact\r\nFor questions about these terms, email hello@propgemini.com or use the Enquire page.', '2026-11-02 21:44:35'),
(23, 'legal_privacy_title', 'Privacy Policy', '2026-11-02 21:44:35'),
(24, 'legal_privacy_body', 'Last updated: November 2026\r\n\r\n1. Introduction\r\nThis Privacy Policy explains how PropGemini collects, uses and protects personal information when you browse our website, enquire about a property, request a brochure, or book a site visit.\r\n\r\n2. Information we collect\r\nWe may collect: name, phone number, email address, preferred city/area/budget, property of interest, message content, preferred visit date/time, and basic technical data such as browser type and pages viewed.\r\n\r\n3. How we use your information\r\nWe use your information to respond to enquiries, schedule site visits, share relevant property options, improve our advisory service, and meet legal or compliance requirements where applicable.\r\n\r\n4. Sharing\r\nWe may share necessary details with project developers, channel partners or service providers solely to fulfil your request (for example, confirming inventory or arranging a visit). We do not sell your personal information.\r\n\r\n5. WhatsApp and email\r\nIf you contact us or provide a mobile number, we may communicate via phone, SMS, WhatsApp or email regarding your enquiry. You can ask us to stop marketing messages at any time.\r\n\r\n6. Data retention\r\nWe retain enquiry and visit records for as long as needed to provide follow-up and maintain business records, unless a longer period is required by law or you request deletion where applicable.\r\n\r\n7. Security\r\nWe take reasonable technical and organisational steps to protect personal data. No method of transmission over the internet is completely secure.\r\n\r\n8. Your choices\r\nYou may request access, correction or deletion of your personal information by emailing hello@propgemini.com. We may need to verify your identity before acting on a request.\r\n\r\n9. Cookies and analytics\r\nOur site may use cookies or similar technologies to remember preferences and understand traffic. You can control cookies through your browser settings.\r\n\r\n10. Children\r\nThis website is intended for adults exploring residential or commercial property. We do not knowingly collect data from children.\r\n\r\n11. Updates\r\nWe may update this Privacy Policy periodically. The “Last updated” date at the top will change when we do.\r\n\r\n12. Contact\r\nPrivacy questions: hello@propgemini.com', '2026-11-02 21:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `site_traffic`
--

CREATE TABLE `site_traffic` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `viewer_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_traffic`
--

INSERT INTO `site_traffic` (`id`, `path`, `page_title`, `property_id`, `viewer_ip`, `user_agent`, `referrer`, `viewed_at`) VALUES
(1, '/pages/property', 'The Foundry Offices | PropGemini', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', NULL, '2026-11-03 03:56:17'),
(2, '/', 'PropGemini | Exceptional Property. Considered Living.', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/admin/properties', '2026-11-03 03:56:40'),
(3, '/pages/property', 'The Canopy Residences | PropGemini', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/properties', '2026-11-03 04:04:10'),
(4, '/', 'PropGemini | Exceptional Property. Considered Living.', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/property?id=canopy', '2026-11-03 04:04:12'),
(5, '/', 'PropGemini | Exceptional Property. Considered Living.', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/property?id=canopy', '2026-11-03 04:04:13'),
(6, '/', 'PropGemini | Exceptional Property. Considered Living.', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/property?id=canopy', '2026-11-03 04:30:56'),
(7, '/pages/properties', 'Properties | PropGemini', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/', '2026-11-03 04:30:57'),
(8, '/pages/properties', 'Properties | PropGemini', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/properties', '2026-11-03 04:30:58'),
(9, '/pages/about', 'About PropGemini', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/properties', '2026-11-03 04:30:59'),
(10, '/pages/emi-calculator', 'EMI Calculator | PropGemini', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/about', '2026-11-03 04:31:00'),
(11, '/pages/emi-calculator', 'EMI Calculator | PropGemini', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/about', '2026-11-03 04:31:01'),
(12, '/', 'PropGemini | Exceptional Property. Considered Living.', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/about', '2026-11-03 04:31:03'),
(13, '/', 'PropGemini | Exceptional Property. Considered Living.', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/pages/about', '2026-11-03 04:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `site_visit_requests`
--

CREATE TABLE `site_visit_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `country_code` varchar(10) DEFAULT '+91',
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `reschedule_date` date DEFAULT NULL,
  `reschedule_time` time DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled','Rescheduled') NOT NULL DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_clicks`
--

CREATE TABLE `whatsapp_clicks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_label` varchar(100) NOT NULL DEFAULT 'site',
  `property_id` int(11) DEFAULT NULL,
  `viewer_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `clicked_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `alert_type` (`alert_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `city_id` (`city_id`,`name`);

--
-- Indexes for table `brochure_requests`
--
ALTER TABLE `brochure_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enquiries_status` (`status`),
  ADD KEY `idx_enquiries_created_at` (`created_at`),
  ADD KEY `idx_enquiries_email` (`email`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indexes for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_brochures`
--
ALTER TABLE `property_brochures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_edit_history`
--
ALTER TABLE `property_edit_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `property_floor_plans`
--
ALTER TABLE `property_floor_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_price_history`
--
ALTER TABLE `property_price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_status_history`
--
ALTER TABLE `property_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `property_unit_types`
--
ALTER TABLE `property_unit_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_videos`
--
ALTER TABLE `property_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_views`
--
ALTER TABLE `property_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `viewed_at` (`viewed_at`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `site_traffic`
--
ALTER TABLE `site_traffic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `viewed_at` (`viewed_at`),
  ADD KEY `path` (`path`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `site_visit_requests`
--
ALTER TABLE `site_visit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `whatsapp_clicks`
--
ALTER TABLE `whatsapp_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clicked_at` (`clicked_at`),
  ADD KEY `source_label` (`source_label`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `brochure_requests`
--
ALTER TABLE `brochure_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_amenities`
--
ALTER TABLE `property_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `property_brochures`
--
ALTER TABLE `property_brochures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_edit_history`
--
ALTER TABLE `property_edit_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_floor_plans`
--
ALTER TABLE `property_floor_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_price_history`
--
ALTER TABLE `property_price_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_status_history`
--
ALTER TABLE `property_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_unit_types`
--
ALTER TABLE `property_unit_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_videos`
--
ALTER TABLE `property_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_views`
--
ALTER TABLE `property_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `site_traffic`
--
ALTER TABLE `site_traffic`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `site_visit_requests`
--
ALTER TABLE `site_visit_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_clicks`
--
ALTER TABLE `whatsapp_clicks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`);

--
-- Constraints for table `brochure_requests`
--
ALTER TABLE `brochure_requests`
  ADD CONSTRAINT `brochure_requests_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`);

--
-- Constraints for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD CONSTRAINT `property_amenities_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_brochures`
--
ALTER TABLE `property_brochures`
  ADD CONSTRAINT `property_brochures_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_floor_plans`
--
ALTER TABLE `property_floor_plans`
  ADD CONSTRAINT `property_floor_plans_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_status_history`
--
ALTER TABLE `property_status_history`
  ADD CONSTRAINT `property_status_history_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_unit_types`
--
ALTER TABLE `property_unit_types`
  ADD CONSTRAINT `property_unit_types_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_videos`
--
ALTER TABLE `property_videos`
  ADD CONSTRAINT `property_videos_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `site_visit_requests`
--
ALTER TABLE `site_visit_requests`
  ADD CONSTRAINT `site_visit_requests_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
