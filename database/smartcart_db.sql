-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: smartcart_db
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `smartcart_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `smartcart_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `smartcart_db`;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Điện tử','dien-tu','Các sản phẩm thuộc danh mục Điện tử.','2026-10-13 03:50:45','2026-10-13 03:50:45'),(2,'Thời trang','thoi-trang','Các sản phẩm thuộc danh mục Thời trang.','2026-10-13 03:50:45','2026-10-13 03:50:45'),(3,'Gia dụng','gia-dung','Các sản phẩm thuộc danh mục Gia dụng.','2026-10-13 03:50:45','2026-10-13 03:50:45'),(4,'Sách & Văn phòng','sach-van-phong','Các sản phẩm thuộc danh mục Sách & Văn phòng.','2026-10-13 03:50:45','2026-10-13 03:50:45'),(5,'Thể thao','the-thao','Các sản phẩm thuộc danh mục Thể thao.','2026-10-13 03:50:45','2026-10-13 03:50:45'),(6,'Làm đẹp & Sức khỏe','lam-dep-suc-khoe','Mỹ phẩm chính hãng, chăm sóc da và sản phẩm chăm sóc sức khỏe sắc đẹp.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(7,'Mẹ & Bé','me-be','Sản phẩm dinh dưỡng, đồ dùng an toàn và tiện ích hàng đầu cho mẹ và bé.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(8,'Bách hóa & Thực phẩm','bach-hoa-thuc-pham','Nông sản tươi sạch, thực phẩm khô dinh dưỡng và đặc sản chọn lọc.','2026-10-19 03:35:33','2026-10-19 03:35:33');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_13_000001_add_role_and_info_to_users_table',1),(5,'2026_10_13_000002_create_categories_table',1),(6,'2026_10_13_000003_create_products_table',1),(7,'2026_10_13_000004_create_orders_table',1),(8,'2026_10_13_000005_create_order_items_table',1),(9,'2026_10_17_222305_add_checkout_token_to_orders_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_items_order_id_product_id_unique` (`order_id`,`product_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `checkout_token` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_checkout_token_unique` (`checkout_token`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_status_index` (`status`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_name_index` (`name`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Tai nghe không dây',490000.00,20,'products/tai-nghe-khong-day.jpg','Tai nghe không dây — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(2,1,'Loa Bluetooth mini',650000.00,20,'products/loa-bluetooth-mini.jpg','Loa Bluetooth mini — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(3,1,'Chuột không dây',250000.00,20,'products/chuot-khong-day.jpg','Chuột không dây — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(4,1,'Bàn phím cơ',890000.00,20,'products/ban-phim-co.jpg','Bàn phím cơ — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(5,1,'Sạc dự phòng',390000.00,20,'products/sac-du-phong.jpg','Sạc dự phòng — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(6,1,'Đèn bàn LED',320000.00,20,'products/den-ban-led.jpg','Đèn bàn LED — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(7,2,'Áo thun cotton',159000.00,20,'products/ao-thun-cotton.jpg','Áo thun cotton — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(8,2,'Áo sơ mi linen',329000.00,20,'products/ao-so-mi-linen.jpg','Áo sơ mi linen — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(9,2,'Quần jeans',450000.00,20,'products/quan-jeans.jpg','Quần jeans — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(10,2,'Mũ lưỡi trai',129000.00,20,'products/mu-luoi-trai.jpg','Mũ lưỡi trai — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(11,2,'Túi tote',99000.00,20,'products/tui-tote.jpg','Túi tote — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(12,2,'Giày thể thao',690000.00,20,'products/giay-the-thao.jpg','Giày thể thao — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(13,3,'Bình giữ nhiệt',249000.00,20,'products/binh-giu-nhiet.jpg','Bình giữ nhiệt — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(14,3,'Hộp cơm văn phòng',179000.00,20,'products/hop-com-van-phong.jpg','Hộp cơm văn phòng — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(15,3,'Cốc sứ',89000.00,20,'products/coc-su.jpg','Cốc sứ — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(16,3,'Kệ để bàn',219000.00,20,'products/ke-de-ban.jpg','Kệ để bàn — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(17,3,'Khăn tắm cotton',149000.00,20,'products/khan-tam-cotton.jpg','Khăn tắm cotton — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(18,3,'Đồng hồ treo tường',299000.00,20,'products/dong-ho-treo-tuong.jpg','Đồng hồ treo tường — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(19,4,'Sổ tay bìa cứng',79000.00,20,'products/so-tay-bia-cung.jpg','Sổ tay bìa cứng — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(20,4,'Bút bi bộ 5 cây',39000.00,20,'products/but-bi-bo-5-cay.jpg','Bút bi bộ 5 cây — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(21,4,'Sách học Laravel',189000.00,20,'products/sach-hoc-laravel.jpg','Sách học Laravel — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(22,4,'Sách kỹ năng làm việc',129000.00,20,'products/sach-ky-nang-lam-viec.jpg','Sách kỹ năng làm việc — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(23,4,'Bìa hồ sơ',29000.00,20,'products/bia-ho-so.jpg','Bìa hồ sơ — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(24,4,'Giá đỡ sách',99000.00,20,'products/gia-do-sach.jpg','Giá đỡ sách — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(25,5,'Thảm yoga',249000.00,20,'products/tham-yoga.jpg','Thảm yoga — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(26,5,'Dây nhảy',79000.00,20,'products/day-nhay.jpg','Dây nhảy — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(27,5,'Bình nước thể thao',129000.00,20,'products/binh-nuoc-the-thao.jpg','Bình nước thể thao — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(28,5,'Tạ tay 2 kg',159000.00,20,'products/ta-tay-2-kg.jpg','Tạ tay 2 kg — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(29,5,'Bóng đá',299000.00,20,'products/bong-da.jpg','Bóng đá — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(30,5,'Túi đựng đồ tập',219000.00,20,'products/tui-dung-do-tap.jpg','Túi đựng đồ tập — sản phẩm mẫu phục vụ chạy thử SmartCart.','2026-10-13 03:50:45','2026-10-19 03:35:33'),(31,6,'Son dưỡng môi ẩm mượt',120000.00,25,'products/son-duong-moi-am-muot.jpg','Son dưỡng môi chiết xuất bơ hạt mỡ dưỡng ẩm sâu và chống nứt nẻ.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(32,6,'Kem chống nắng phổ rộng',280000.00,30,'products/kem-chong-nang-pho-rong.jpg','Kem chống nắng SPF50+ PA++++ kiềm dầu, nâng tông tự nhiên.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(33,6,'Sữa rửa mặt tạo bọt dịu nhẹ',165000.00,20,'products/sua-rua-mat-tao-bot-diu-nhe.jpg','Sữa rửa mặt pH 5.5 làm sạch sâu lỗ chân lông mà không gây khô da.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(34,6,'Serum Vitamin C sáng da',350000.00,15,'products/serum-vitamin-c-sang-da.jpg','Tinh chất Vitamin C 15% mờ thâm nám và chống oxy hóa hiệu quả.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(35,6,'Nước tẩy trang dịu nhẹ',145000.00,25,'products/nuoc-tay-trang-diu-nhe.jpg','Nước tẩy trang công nghệ Micellar sạch bụi mịn và lớp trang điểm.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(36,6,'Mặt nạ đất sét thải độc',190000.00,20,'products/mat-na-dat-set-thai-doc.jpg','Mặt nạ đất sét khoáng chất hút sạch dầu thừa và bã nhờn sâu.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(37,6,'Bàn chải điện sóng âm',450000.00,18,'products/ban-chai-dien-song-am.jpg','Bàn chải đánh răng điện công nghệ sóng âm rung 40.000 lần/phút.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(38,6,'Máy xịt khoáng nano',210000.00,20,'products/may-xit-khoang-nano.jpg','Máy phun sương nano cấp ẩm tức thì mọi lúc mọi nơi.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(39,6,'Dầu gội bưởi ngăn rụng tóc',185000.00,22,'products/dau-goi-buoi-ngan-rung-toc.jpg','Dầu gội tinh dầu vỏ bưởi tự nhiên kích thích mọc tóc dày mượt.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(40,6,'Tinh dầu tràm trà giảm mụn',135000.00,30,'products/tinh-dau-tram-tra-giam-mun.jpg','Tinh dầu tràm trà nguyên chất kháng khuẩn và xẹp mụn nhanh chóng.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(41,7,'Bình sữa chống sặc PPSU',215000.00,25,'products/binh-sua-chong-sac-ppsu.jpg','Bình sữa PPSU cao cấp núm ti siêu mềm van thông khí chống sặc.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(42,7,'Máy tiệt trùng hâm sữa',580000.00,12,'products/may-tiet-trung-ham-sua.jpg','Máy đa năng sấy khô và tiệt trùng hơi nước bảo vệ hệ tiêu hóa của bé.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(43,7,'Khăn ướt không mùi cho bé',45000.00,50,'products/khan-uot-khong-mui-cho-be.jpg','Gói 80 tờ khăn ướt sợi cotton dệt tinh khiết an toàn cho da sơ sinh.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(44,7,'Ghế ăn dặm đa năng',490000.00,15,'products/ghe-an-dam-da-nang.jpg','Ghế ăn dặm điều chỉnh độ cao linh hoạt có khay ăn tháo rời vệ sinh.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(45,7,'Xe đẩy em bé gấp gọn',890000.00,10,'products/xe-day-em-be-gap-gon.jpg','Xe đẩy du lịch gấp gọn một chạm siêu nhẹ kèm mái che chống tia UV.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(46,7,'Địu em bé trợ lực thoáng khí',360000.00,16,'products/diu-em-be-tro-luc-thoang-khi.jpg','Địu ngồi 4 tư thế đệm lưng thoáng khí chống gù và mỏi vai cho mẹ.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(47,7,'Bộ đồ chơi xếp hình gỗ',175000.00,25,'products/bo-do-choi-xep-hinh-go.jpg','Bộ khối gỗ tự nhiên sơn màu nước rèn luyện tư duy sáng tạo cho trẻ.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(48,7,'Sữa tắm gội thảo dược cho bé',155000.00,30,'products/sua-tam-goi-thao-duoc-cho-be.jpg','Sữa tắm gội 2 trong 1 chiết xuất cúc la mã không cay mắt.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(49,7,'Chăn ủ lông tuyết cho bé',130000.00,20,'products/chan-u-long-tuyet-cho-be.jpg','Chăn ủ có mũ lông tuyết mềm mịn giữ ấm tối ưu cho trẻ sơ sinh.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(50,7,'Gặm nướu silicon kháng khuẩn',85000.00,40,'products/gam-nuou-silicon-khang-khuan.jpg','Gặm nướu silicon thực phẩm giảm ngứa nướu trong thời kỳ mọc răng.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(51,8,'Cà phê rang xay nguyên chất',145000.00,30,'products/ca-phe-rang-xay-nguyen-chat.jpg','Gói 500g cà phê Robusta & Arabica Đắk Lắk hương vị đậm đà truyền thống.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(52,8,'Trà ô long thượng hạng',125000.00,25,'products/tra-o-long-thuong-hang.jpg','Hộp 200g trà ô long búp non Bảo Lộc hương hoa thanh khiết.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(53,8,'Mật ong hoa rừng tự nhiên',220000.00,20,'products/mat-ong-hoa-rung-tu-nhien.jpg','Chai 500ml mật ong hoa rừng nguyên chất đậm đặc không pha tạp.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(54,8,'Hạt điều rang muối vỏ lụa',165000.00,25,'products/hat-dieu-rang-muoi-vo-lua.jpg','Hộp 500g hạt điều Bình Phước giòn rụm béo ngậy thơm ngon.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(55,8,'Granola yến mạch siêu hạt',180000.00,20,'products/granola-yen-mach-sieu-hat.jpg','Hũ 500g ngũ cốc yến mạch nướng mật ong giàu dinh dưỡng cho bữa sáng.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(56,8,'Hạt macca sấy nứt vỏ',195000.00,18,'products/hat-macca-say-nut-vo.jpg','Hũ 500g hạt macca Tây Nguyên sấy chín tự nhiên kèm đồ khui tiện dụng.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(57,8,'Gạo ST25 hữu cơ túi 5kg',230000.00,15,'products/gao-st25-huu-co-tui-5kg.jpg','Gạo ngon nhất thế giới dẻo thơm hạt dài đạt chuẩn hữu cơ quốc tế.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(58,8,'Bột ca cao nguyên chất',110000.00,30,'products/bot-ca-cao-nguyen-chat.jpg','Gói 250g bột ca cao Bến Tre không đường thơm béo pha chế đa năng.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(59,8,'Dầu ô liu Extra Virgin',210000.00,20,'products/dau-o-liu-extra-virgin.jpg','Chai 500ml dầu ô liu nguyên chất ép lạnh cao cấp cho salad và nấu ăn.','2026-10-19 03:35:33','2026-10-19 03:35:33'),(60,8,'Trái cây sấy giòn thập cẩm',95000.00,35,'products/trai-cay-say-gion-thap-cam.jpg','Gói 500g mít, chuối, khoai môn sấy giòn không dầu thơm tự nhiên.','2026-10-19 03:35:33','2026-10-19 03:35:33');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Quản trị viên','admin@gmail.com',NULL,'$2y$12$BCnXVR7t9aZpy1u7A1rzG.W135v6Xg368DRLPOE.dYwv7GaVkw9pS',NULL,'2026-10-13 03:50:44','2026-10-13 03:50:44','admin','0901234567','Thành phố Hồ Chí Minh'),(2,'Khách hàng Demo','user@gmail.com',NULL,'$2y$12$XEvHq8jnMsCp9y93rv2Te.qrhiJsZbsmahBBuAeC2acp5.UA0Necm',NULL,'2026-10-13 03:50:45','2026-10-13 03:50:45','customer','0901234567','Thành phố Hồ Chí Minh');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19 20:03:03
