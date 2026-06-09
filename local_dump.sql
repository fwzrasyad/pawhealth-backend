-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pawhealth_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ai_scans`
--

DROP TABLE IF EXISTS `ai_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_scans` (
  `scan_id` char(36) NOT NULL,
  `pet_id` char(36) NOT NULL,
  `scan_date` datetime NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `ai_result_label` varchar(255) NOT NULL,
  `confidence_score` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`scan_id`),
  KEY `ai_scans_pet_id_foreign` (`pet_id`),
  CONSTRAINT `ai_scans_pet_id_foreign` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_scans`
--

LOCK TABLES `ai_scans` WRITE;
/*!40000 ALTER TABLE `ai_scans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `appointment_id` char(36) NOT NULL,
  `clinic_id` char(36) DEFAULT NULL,
  `pet_id` char(36) NOT NULL,
  `pet_name` varchar(255) NOT NULL,
  `vet_id` char(36) NOT NULL,
  `vet_name` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `appointment_date` datetime NOT NULL,
  `time_slot` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `amount` decimal(8,2) DEFAULT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`appointment_id`),
  KEY `appointments_pet_id_foreign` (`pet_id`),
  KEY `appointments_vet_id_foreign` (`vet_id`),
  KEY `appointments_clinic_id_foreign` (`clinic_id`),
  CONSTRAINT `appointments_clinic_id_foreign` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`clinic_id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_pet_id_foreign` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_vet_id_foreign` FOREIGN KEY (`vet_id`) REFERENCES `veterinarians` (`vet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES ('003e2aa3-753f-4b4b-be96-48356443460c','8f1b1e5c-2621-4373-97a5-b676386d33c2','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','lone','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','General Consultation','2026-05-07 18:48:18','2026-05-07 09:00:00','cancelled',NULL,NULL,'unpaid','2026-05-06 04:48:58','2026-05-29 02:02:44'),('09634098-0c18-4fa8-8556-bacfa0e31d8a','3bc3c37f-6490-48c1-a192-82a96db6c3c7','03495435-f106-4765-a145-6f377c5b2c8e','Luna','9fdd9354-71ec-42fb-8ffb-53bc77177068','Mrs. Mattie Hilpert','Annual checkup','2026-03-14 00:00:00','2026-03-14 16:00:56','completed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('2365cf49-b59c-44e5-9be4-b6ec75ca4be9','382d82a0-5f3a-4a4b-aeb8-56c313011b33','f6512b54-a3fa-4b95-9ad9-e3b383f1c0e3','Kolby','1dd58110-082d-4481-ac2a-78962d731f53','Janiya Bogisich','Skin irritation','2026-05-20 00:00:00','2026-05-20 15:00:56','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('3d03cd02-5446-45f6-9eb7-8c814f5bab30','8f1b1e5c-2621-4373-97a5-b676386d33c2','4039c919-dc1c-4cd5-a4aa-a62ffe7cb634','Bella','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','Vaccination','2026-05-07 00:00:00','2026-05-07 10:00:00','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('40fac428-0c7d-4645-ba64-62c9262babef','8f1b1e5c-2621-4373-97a5-b676386d33c2','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','lone','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','General Consultation','2026-05-06 16:04:09','2026-05-06 09:00:00','cancelled',NULL,NULL,'unpaid','2026-05-06 02:04:14','2026-05-29 02:02:37'),('416650c6-b4fc-4ba7-90a0-b073e1cafbd2','8f1b1e5c-2621-4373-97a5-b676386d33c2','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','lone','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','General Consultation','2026-05-07 10:51:20','2026-05-07 11:00:00','cancelled',NULL,NULL,'unpaid','2026-05-06 20:51:34','2026-05-29 02:02:41'),('4b2d0c08-87ae-4347-af3a-46aca6e860c4','1d012b8d-abe5-4598-be23-f92f7d107e1a','d642deae-b81f-4a6f-a2fc-11674a5e0368','Aidan','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Skin irritation','2026-04-13 00:00:00','2026-04-13 11:00:33','cancelled',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('4c7974f5-c32c-4a59-ae9f-bf6b3712d7bd','1d012b8d-abe5-4598-be23-f92f7d107e1a','c28dfa59-4a6e-4824-92fc-af041a019d3c','Lafayette','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Annual checkup','2026-05-13 00:00:00','2026-05-13 12:00:34','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('5918fec3-4fd0-4d7c-ad18-bf5b3ea733c2','8f1b1e5c-2621-4373-97a5-b676386d33c2','7d6a6a73-2c4f-427c-9de0-10ebeeba4daa','Hellen','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','Lethargy','2026-04-30 00:00:00','2026-04-30 13:00:22','completed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('62b633b8-eba0-4e71-a36e-356e2531262e','1d012b8d-abe5-4598-be23-f92f7d107e1a','5b5c45aa-d386-4977-bfec-755670a6df8f','Joan','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Dental scaling','2026-05-30 00:00:00','2026-05-30 16:00:19','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('671b161f-12aa-481c-9cdd-438cd37c29be','f1d3de08-ebf9-40a5-986e-fc6a9c589871','408f0b01-1b53-4757-b074-0a9a386c656f','wer','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','General Consultation','2026-05-29 15:40:04','2026-05-29 16:00:00','completed',NULL,NULL,'unpaid','2026-05-29 01:40:12','2026-05-29 01:49:37'),('6f0eac9c-7978-4c3b-b6f8-ee59efd72dee','f1d3de08-ebf9-40a5-986e-fc6a9c589871','408f0b01-1b53-4757-b074-0a9a386c656f','wer','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','General Consultation','2026-05-30 16:03:40','2026-05-30 09:00:00','cancelled',NULL,NULL,'unpaid','2026-05-29 02:03:46','2026-05-29 02:04:38'),('748d0b65-784a-453e-87c0-496d198810d5','a6366208-0abe-44a2-b072-62998686e3e6','408f0b01-1b53-4757-b074-0a9a386c656f','wer','UXGdLupyQncm47ntVwY3QC8D1sx2','Rashid Amin','General Consultation','2026-05-08 18:26:55','2026-05-08 14:00:00','cancelled',NULL,NULL,'unpaid','2026-05-07 04:26:58','2026-05-29 02:03:25'),('828b8c28-8f9d-4d5b-8f9b-b3888169f023','1d012b8d-abe5-4598-be23-f92f7d107e1a','acc33eec-4bff-49fc-80e6-bdcd385a422d','Jammie','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Lethargy','2026-05-11 00:00:00','2026-05-11 09:00:22','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('8b6e3da4-2878-433b-9560-6e5591f13266','a6366208-0abe-44a2-b072-62998686e3e6','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','lone','UXGdLupyQncm47ntVwY3QC8D1sx2','Rashid Amin','General Consultation','2026-05-08 15:56:32','2026-05-08 10:00:00','completed',NULL,NULL,'unpaid','2026-05-07 01:56:38','2026-05-07 04:32:40'),('8bcd20ce-f895-4949-8631-bfe00d6ec4f9','f1d3de08-ebf9-40a5-986e-fc6a9c589871','408f0b01-1b53-4757-b074-0a9a386c656f','wer','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','General Consultation','2026-05-29 15:50:38','2026-05-29 16:00:00','cancelled',NULL,NULL,'unpaid','2026-05-29 01:50:44','2026-05-29 01:58:58'),('8d792cb5-9c97-46b4-b963-d3382f2d8307','8f1b1e5c-2621-4373-97a5-b676386d33c2','4039c919-dc1c-4cd5-a4aa-a62ffe7cb634','Bella','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','Lethargy','2026-05-01 08:47:12','2026-05-01 09:00:12','completed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('95750b2c-851d-40c8-897e-c35761a15004','c976f587-b7c9-4d2d-9c7e-32602b051616','5b5c45aa-d386-4977-bfec-755670a6df8f','Joan','c6822366-e2a0-4066-bad3-e750d510615c','Lia Lakin','Annual checkup','2026-06-05 00:00:00','2026-06-05 12:00:28','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('9821b118-ed1d-4c48-9829-08a538937f3b','8f1b1e5c-2621-4373-97a5-b676386d33c2','03495435-f106-4765-a145-6f377c5b2c8e','Luna','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','Skin irritation','2026-05-09 08:47:12','2026-05-09 14:30:12','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('a2824acd-3288-4405-977f-5806e68462f8','65498bd5-602b-4697-a930-429a4b56d531','cd509e02-764d-45c0-b513-a78d147f2463','Yasmeen','c42bf65e-029a-4996-b846-7f7fb6d7c228','Blanca Leuschke','Vaccination','2026-04-21 00:00:00','2026-04-21 10:00:13','cancelled',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('aea9be3a-ca2d-47b9-afde-8c7cdd1603d0','1d012b8d-abe5-4598-be23-f92f7d107e1a','907a1d61-20f5-44ff-b094-5a331433d255','Emely','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Annual checkup','2026-03-15 00:00:00','2026-03-15 11:00:06','completed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('bdf5d089-db0c-4899-a7c4-6d7ff8eb28eb','65498bd5-602b-4697-a930-429a4b56d531','03495435-f106-4765-a145-6f377c5b2c8e','Luna','803d9eb3-d45a-4869-8785-dad93ab4d807','Kelsi Kassulke','Skin irritation','2026-05-25 00:00:00','2026-05-25 12:00:19','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('c3afba15-cc2b-445b-8cd2-09916d0a3bde','f1d3de08-ebf9-40a5-986e-fc6a9c589871','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','lone','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','General Consultation','2026-05-29 15:59:04','2026-05-29 16:00:00','cancelled',NULL,NULL,'unpaid','2026-05-29 01:59:09','2026-05-29 02:03:22'),('cba79c14-102c-442d-ae4b-079393250f88','3bc3c37f-6490-48c1-a192-82a96db6c3c7','ad828ccd-eb40-4f5e-864f-2ed0ee58b234','Caesar','bc93ed14-4f3e-4bf3-839b-21410897faef','Delphine Mosciski','Lethargy','2026-03-26 00:00:00','2026-03-26 12:00:19','cancelled',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('cc0f3fdd-a382-4dbe-b499-6ed485a71c7f','65498bd5-602b-4697-a930-429a4b56d531','4e3c8364-98fb-4e48-957c-2495bdd039f2','Layne','803d9eb3-d45a-4869-8785-dad93ab4d807','Kelsi Kassulke','Annual checkup','2026-06-26 00:00:00','2026-06-26 10:00:53','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('e040744b-45a3-411f-ab50-411e1c67f679','382d82a0-5f3a-4a4b-aeb8-56c313011b33','7d6a6a73-2c4f-427c-9de0-10ebeeba4daa','Hellen','fba1e0e2-800b-4952-9330-ce2eff203e20','Dr. Heather Turcotte','Annual checkup','2026-05-20 00:00:00','2026-05-20 13:00:17','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('e2cafa25-8b3f-46d4-8d34-b018d8e1afbe','f1d3de08-ebf9-40a5-986e-fc6a9c589871','408f0b01-1b53-4757-b074-0a9a386c656f','wer','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','General Consultation','2026-05-31 16:29:23','2026-05-31 09:00:00','confirmed',20.00,'pi_3TcjD0177ycNBf3W07QqztWw','paid','2026-05-30 02:31:19','2026-05-30 04:49:36'),('e60793f9-fc7b-44c0-a8fd-883871d73049','1d012b8d-abe5-4598-be23-f92f7d107e1a','5b5c45aa-d386-4977-bfec-755670a6df8f','Joan','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Skin irritation','2026-07-04 00:00:00','2026-07-04 14:00:56','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('eaee9c56-433a-436c-b38c-97c0854b484c','f1d3de08-ebf9-40a5-986e-fc6a9c589871','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','lone','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','General Consultation','2026-05-30 16:04:45','2026-05-30 09:00:00','confirmed',NULL,NULL,'unpaid','2026-05-29 02:04:51','2026-05-29 02:05:10'),('eed11400-7e7c-4dc9-873a-da1ba75e12a7','3bc3c37f-6490-48c1-a192-82a96db6c3c7','acc33eec-4bff-49fc-80e6-bdcd385a422d','Jammie','bc93ed14-4f3e-4bf3-839b-21410897faef','Delphine Mosciski','Dental scaling','2026-03-15 00:00:00','2026-03-15 15:00:55','completed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('f7da0a91-0a31-487e-9292-87f9eb639b95','1d012b8d-abe5-4598-be23-f92f7d107e1a','8db37b87-0be2-465d-9502-6a8f4c97e778','Dayton','2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','Annual checkup','2026-06-15 00:00:00','2026-06-15 11:00:04','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('f862a75e-99cc-4503-9745-c27ffeaade40','65498bd5-602b-4697-a930-429a4b56d531','f6512b54-a3fa-4b95-9ad9-e3b383f1c0e3','Kolby','309af4b2-6f3f-4870-95e1-fc19310723cc','Princess Hammes','Annual checkup','2026-05-23 00:00:00','2026-05-23 12:00:02','pending',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('fa49f8cb-15b8-4f4e-8a1d-cb66b2f8c843','8f1b1e5c-2621-4373-97a5-b676386d33c2','4039c919-dc1c-4cd5-a4aa-a62ffe7cb634','Bella','908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','Lethargy','2026-06-12 00:00:00','2026-06-12 16:00:50','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12'),('fb58c00a-1f84-49d8-ad31-0bf42735caf2','65498bd5-602b-4697-a930-429a4b56d531','ad828ccd-eb40-4f5e-864f-2ed0ee58b234','Caesar','c42bf65e-029a-4996-b846-7f7fb6d7c228','Blanca Leuschke','Lethargy','2026-06-02 00:00:00','2026-06-02 09:00:53','confirmed',NULL,NULL,'unpaid','2026-05-06 01:47:12','2026-05-06 01:47:12');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `clinics`
--

DROP TABLE IF EXISTS `clinics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinics` (
  `clinic_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `phone` varchar(50) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `license_file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`clinic_id`),
  KEY `clinics_city_state_index` (`city`,`state`),
  KEY `clinics_latitude_longitude_index` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clinics`
--

LOCK TABLES `clinics` WRITE;
/*!40000 ALTER TABLE `clinics` DISABLE KEYS */;
INSERT INTO `clinics` VALUES ('1d012b8d-abe5-4598-be23-f92f7d107e1a','Reynolds and Sons Veterinary Clinic','17650 Estefania Knolls Suite 198','Georgetown','Penang',5.4575690,100.3487310,'+1-786-498-5556','Dolores magnam perferendis quia mollitia architecto in. Voluptatibus deserunt necessitatibus omnis esse. Veniam consequuntur velit voluptatem consequuntur.',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12','pending',NULL),('382d82a0-5f3a-4a4b-aeb8-56c313011b33','Stehr, Gibson and Johnson Veterinary Clinic','56321 Cole Burg','Georgetown','Penang',5.2631660,100.4534110,'1-534-466-9982','Molestiae autem rem natus vero. Minus voluptas autem repellendus nam molestiae nobis aut ratione. Ad dolores non hic magnam unde officia aut. Dolor sed quis ullam minus dolores vel molestias.',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12','pending',NULL),('3bc3c37f-6490-48c1-a192-82a96db6c3c7','Kunze Group Veterinary Clinic','76848 Quentin Divide Suite 318','Georgetown','Penang',5.3600430,100.2758530,'+1-630-743-8468','Vero expedita et magni id enim. Quis odit dolorum a amet sunt fuga. Et qui repudiandae alias et in commodi eos.',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12','pending',NULL),('65498bd5-602b-4697-a930-429a4b56d531','Wuckert, Mayer and Kuvalis Veterinary Clinic','56339 Cierra Inlet Apt. 511','Georgetown','Penang',5.4145250,100.2797740,'828.470.4482','Consequatur voluptatem impedit quam repellat. Officiis enim modi alias enim. Commodi ab necessitatibus quo repellat reiciendis rerum vitae. Aut adipisci dolores praesentium aut.',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12','pending',NULL),('8f1b1e5c-2621-4373-97a5-b676386d33c2','Penang Central Vet','72730 Bailey Skyway Suite 375','Georgetown','Penang',5.4164000,100.3327000,'+18302144265','Repellat commodi fugit non aspernatur corrupti nihil. Veniam distinctio eius magni. At doloremque expedita minima eius dignissimos. Et facilis dignissimos quos laudantium.',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12','pending',NULL),('a6366208-0abe-44a2-b072-62998686e3e6','Chewy Pets','123 Penang Road','Gelugor','Penang',NULL,NULL,'',NULL,NULL,'2026-05-07 01:50:02','2026-05-07 01:51:45','approved',NULL),('c976f587-b7c9-4d2d-9c7e-32602b051616','Hickle Ltd Veterinary Clinic','52504 Ruecker Cliff Apt. 868','Georgetown','Penang',5.2714520,100.3151560,'(218) 840-0210','Ipsa libero exercitationem dolores dicta et sint. Qui id dicta repellendus pariatur. Et repellendus cum inventore sed doloribus rerum culpa.',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12','pending',NULL),('f1d3de08-ebf9-40a5-986e-fc6a9c589871','Penang Vet Center','123 Penang Street','Georgetown','Penang',NULL,NULL,'01182649226',NULL,NULL,'2026-05-13 06:52:58','2026-05-18 22:12:01','approved','licenses/sG1TIgSivmns8aoqTrTKqyMep7VhY0NYlj9A1Zeu.jpg');
/*!40000 ALTER TABLE `clinics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
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
-- Table structure for table `health_journals`
--

DROP TABLE IF EXISTS `health_journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_journals` (
  `id` char(36) NOT NULL,
  `pet_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `symptom_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`symptom_tags`)),
  `notes` text DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_routine_logs_pet_id_foreign` (`pet_id`),
  CONSTRAINT `daily_routine_logs_pet_id_foreign` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `health_journals`
--

LOCK TABLES `health_journals` WRITE;
/*!40000 ALTER TABLE `health_journals` DISABLE KEYS */;
/*!40000 ALTER TABLE `health_journals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
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
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_records` (
  `record_id` char(36) NOT NULL,
  `pet_id` char(36) NOT NULL,
  `vet_id` char(36) NOT NULL,
  `appointment_id` char(36) DEFAULT NULL,
  `diagnosis` text NOT NULL,
  `doctor_notes` text DEFAULT NULL,
  `medications_prescribed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medications_prescribed`)),
  `follow_up_instructions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `medical_records_pet_id_foreign` (`pet_id`),
  KEY `medical_records_vet_id_foreign` (`vet_id`),
  KEY `medical_records_appointment_id_foreign` (`appointment_id`),
  CONSTRAINT `medical_records_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  CONSTRAINT `medical_records_pet_id_foreign` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_vet_id_foreign` FOREIGN KEY (`vet_id`) REFERENCES `veterinarians` (`vet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_records`
--

LOCK TABLES `medical_records` WRITE;
/*!40000 ALTER TABLE `medical_records` DISABLE KEYS */;
INSERT INTO `medical_records` VALUES ('5c211f16-b5a0-484d-8ed4-41f6c2373254','408f0b01-1b53-4757-b074-0a9a386c656f','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','671b161f-12aa-481c-9cdd-438cd37c29be','no problem','amazing','[]',NULL,'2026-05-29 01:49:49','2026-05-29 01:49:49'),('5c338330-3bbb-4a09-98f3-37d6a42d4322','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','UXGdLupyQncm47ntVwY3QC8D1sx2','8b6e3da4-2878-433b-9560-6e5591f13266','Scabies','Patch of infection on head','[\"Salep 250g\"]','Check up in 1 week','2026-05-07 06:05:38','2026-05-07 06:05:38'),('6e3962ca-78d6-4a52-a532-1f1f430ef1bf','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','UXGdLupyQncm47ntVwY3QC8D1sx2','8b6e3da4-2878-433b-9560-6e5591f13266','patient has scabies','YES',NULL,'return in one week','2026-05-07 04:34:05','2026-05-07 04:34:05'),('9ffc9f2d-6fb3-47a8-91f1-0d64103751ce','40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','UXGdLupyQncm47ntVwY3QC8D1sx2','8b6e3da4-2878-433b-9560-6e5591f13266','patient has scabies','YES','[]','return in one week','2026-05-07 05:30:49','2026-05-07 05:30:49');
/*!40000 ALTER TABLE `medical_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_25_000001_create_pets_table',1),(5,'2026_03_25_000002_create_veterinarians_table',1),(6,'2026_03_25_000003_create_vet_available_slots_table',1),(7,'2026_03_25_000004_create_appointments_table',1),(8,'2026_03_25_000005_create_medical_records_table',1),(9,'2026_03_25_000006_create_daily_routine_logs_table',1),(10,'2026_03_25_000007_create_ai_scans_table',1),(11,'2026_03_31_162915_add_weekly_schedule_to_veterinarians_table',1),(12,'2026_04_01_143411_add_status_to_veterinarians_table',1),(13,'2026_04_06_000001_add_profile_image_url_to_pets_table',1),(14,'2026_05_06_061900_update_appointments_table_nullable_vet_and_status',1),(15,'2026_05_06_070000_create_clinics_table',1),(16,'2026_05_06_070001_add_clinic_id_to_users_table',1),(17,'2026_05_06_070002_update_appointments_for_multi_clinic',1),(18,'2026_05_06_070003_add_manager_role_to_users_table',1),(19,'2026_05_07_075127_add_status_to_clinics_table',2),(20,'2026_05_07_092215_rename_daily_routine_logs_to_health_journals',3),(21,'2026_05_07_092253_update_medical_records_table',3),(22,'2026_05_07_092500_rename_daily_routine_logs_to_health_journals',3),(23,'2026_05_07_092501_update_medical_records_table_for_appointments',3),(24,'2026_05_13_130000_add_license_file_path_to_clinics_table',4),(25,'2026_05_19_051007_add_super_admin_role_to_users_table',5),(26,'2026_05_25_112903_create_notifications_table',6),(27,'2026_05_25_112915_add_fcm_token_to_users_table',6),(28,'2026_05_29_090130_change_notifiable_id_to_string_in_notifications_table',7),(29,'2026_05_29_143201_add_profile_image_url_to_users_table',8),(30,'2026_05_30_064204_add_consultation_fee_to_veterinarians_table',9),(31,'2026_05_30_064245_add_payment_fields_to_appointments_table',9),(32,'2026_05_31_064601_add_image_url_to_clinics_table',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('407c4bef-52a2-4d35-9888-59ce32b44469','App\\Notifications\\NewAppointmentAssignedNotification','App\\Models\\User','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','{\"appointment_id\":\"6f0eac9c-7978-4c3b-b6f8-ee59efd72dee\",\"title\":\"New Appointment Assigned\",\"body\":\"You have been assigned a new appointment.\"}',NULL,'2026-05-29 02:04:05','2026-05-29 02:04:05'),('59b88e84-6bcc-4be9-b91d-399a10fa8a9e','App\\Notifications\\AppointmentStatusNotification','App\\Models\\User','8VrsCA2dGbWh3IBVJuXM7QZV1OH2','{\"appointment_id\":\"6f0eac9c-7978-4c3b-b6f8-ee59efd72dee\",\"title\":\"Appointment Status Updated\",\"body\":\"Your appointment status is now confirmed.\"}',NULL,'2026-05-29 02:04:03','2026-05-29 02:04:03'),('a11e5901-3590-4136-9f9d-e60f85148c18','App\\Notifications\\AppointmentStatusNotification','App\\Models\\User','8VrsCA2dGbWh3IBVJuXM7QZV1OH2','{\"appointment_id\":\"e2cafa25-8b3f-46d4-8d34-b018d8e1afbe\",\"title\":\"Appointment Status Updated\",\"body\":\"Your appointment status is now confirmed.\"}',NULL,'2026-05-30 04:49:37','2026-05-30 04:49:37'),('d75235b2-27d3-4c4e-97d2-fdd944cee9d8','App\\Notifications\\NewAppointmentAssignedNotification','App\\Models\\User','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','{\"appointment_id\":\"e2cafa25-8b3f-46d4-8d34-b018d8e1afbe\",\"title\":\"New Appointment Assigned\",\"body\":\"You have been assigned a new appointment.\"}',NULL,'2026-05-30 04:49:39','2026-05-30 04:49:39'),('ea14125c-f62f-4ef3-bb1a-edb045e65beb','App\\Notifications\\NewAppointmentAssignedNotification','App\\Models\\User','4mD8jSx5nfX6VuG1FpvXTUBVM6h2','{\"appointment_id\":\"eaee9c56-433a-436c-b38c-97c0854b484c\",\"title\":\"New Appointment Assigned\",\"body\":\"You have been assigned a new appointment.\"}',NULL,'2026-05-29 02:05:10','2026-05-29 02:05:10'),('f66987e9-7237-4aa1-9e16-3b68e747cd04','App\\Notifications\\AppointmentStatusNotification','App\\Models\\User','8VrsCA2dGbWh3IBVJuXM7QZV1OH2','{\"appointment_id\":\"eaee9c56-433a-436c-b38c-97c0854b484c\",\"title\":\"Appointment Status Updated\",\"body\":\"Your appointment status is now confirmed.\"}',NULL,'2026-05-29 02:05:10','2026-05-29 02:05:10');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
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
-- Table structure for table `pets`
--

DROP TABLE IF EXISTS `pets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pets` (
  `pet_id` char(36) NOT NULL,
  `owner_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `species` varchar(100) NOT NULL,
  `breed` varchar(100) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `gender` varchar(20) NOT NULL,
  `weight` double NOT NULL,
  `profile_image_url` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pet_id`),
  KEY `pets_owner_id_foreign` (`owner_id`),
  CONSTRAINT `pets_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pets`
--

LOCK TABLES `pets` WRITE;
/*!40000 ALTER TABLE `pets` DISABLE KEYS */;
INSERT INTO `pets` VALUES ('03495435-f106-4765-a145-6f377c5b2c8e','c3754e8d-0c38-443a-a150-41ca157a0c4f','Luna','Cat','Persian',12,'Male',8.88,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('0e7c7195-b316-4f93-b246-d17863bf651d','37fd6f80-8b02-49d6-870d-e36317a6b38a','Sydnie','Dog','French Bulldog',13,'Female',20.38,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('11388dcb-3f5d-4701-82e4-87d3c0540c52','92027b09-528e-48df-adde-ed1980219f8b','Frieda','Dog','Mixed Breed',15,'Female',3.15,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('1206e2e1-c4d9-4b3e-aa60-764be0ca137b','8VrsCA2dGbWh3IBVJuXM7QZV1OH2','Doodle','Dog','Golden Retriever',5,'Male',2,NULL,'2026-05-30 08:03:04','2026-05-30 08:03:04'),('1ee60e52-1568-4644-9212-cd5e8696081e','e7d132b9-ad7a-4d23-a09e-53cfee525291','Ulices','Cat','Ragdoll',7,'Female',24.23,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('39f0fa9d-c4e2-4c1d-a228-bd0a9611d409','57f111af-6daa-4510-bc69-2534deedf3ca','Barrett','Dog','Golden Retriever',5,'Female',10.26,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('4039c919-dc1c-4cd5-a4aa-a62ffe7cb634','c3754e8d-0c38-443a-a150-41ca157a0c4f','Bella','Dog','Golden Retriever',13,'Female',29.19,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('408f0b01-1b53-4757-b074-0a9a386c656f','8VrsCA2dGbWh3IBVJuXM7QZV1OH2','wer','Cat','Cat',2,'Male',2,'https://storage.googleapis.com/pawhealth-6db18.firebasestorage.app/pets/861163bb-8dec-4fc4-849a-12f2218ef543.jpg','2026-05-07 04:26:43','2026-05-29 09:19:12'),('40b9e237-bab0-4fb6-93ca-8f78f3e4cca8','8VrsCA2dGbWh3IBVJuXM7QZV1OH2','lone','Cat','domestic',2,'Female',2,NULL,'2026-05-06 02:04:04','2026-05-06 02:04:04'),('4e3c8364-98fb-4e48-957c-2495bdd039f2','e7d132b9-ad7a-4d23-a09e-53cfee525291','Layne','Dog','Labrador',12,'Female',33.27,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('56542854-4cdc-4058-a7df-b7fe972d9269','57f111af-6daa-4510-bc69-2534deedf3ca','Mattie','Dog','Poodle',4,'Male',18.82,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('5b5c45aa-d386-4977-bfec-755670a6df8f','e7d132b9-ad7a-4d23-a09e-53cfee525291','Joan','Dog','Labrador',4,'Female',25.74,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('7d6a6a73-2c4f-427c-9de0-10ebeeba4daa','3d633c5d-8aa8-4069-abbd-6584b87d56f9','Hellen','Cat','Siamese',6,'Female',34.53,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('8359de84-783d-400d-b254-3a061f68329c','115851e1-177f-4f82-abea-15b0583b9630','Spencer','Cat','Maine Coon',9,'Male',31.08,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('8db37b87-0be2-465d-9502-6a8f4c97e778','9c578931-60f5-4a7c-b6e7-d805782da4b9','Dayton','Cat','Siamese',11,'Male',33.22,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('907a1d61-20f5-44ff-b094-5a331433d255','115851e1-177f-4f82-abea-15b0583b9630','Emely','Cat','Persian',11,'Male',27.89,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('acc33eec-4bff-49fc-80e6-bdcd385a422d','af68eb54-143b-403c-926c-a339f1d3e307','Jammie','Dog','Poodle',14,'Male',28.25,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('ad828ccd-eb40-4f5e-864f-2ed0ee58b234','1f3ae1a4-39c5-4f9f-8144-3c857b03d580','Caesar','Dog','Labrador',7,'Female',25.31,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('bf00d544-90a2-463e-ac3b-3a95f4f2b9de','2cf77955-b8f1-468b-85cf-ea6aac88a818','Caleigh','Dog','Poodle',7,'Male',14.7,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('c28dfa59-4a6e-4824-92fc-af041a019d3c','1f3ae1a4-39c5-4f9f-8144-3c857b03d580','Lafayette','Cat','Maine Coon',11,'Male',7.9,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('cd509e02-764d-45c0-b513-a78d147f2463','57f111af-6daa-4510-bc69-2534deedf3ca','Yasmeen','Cat','Siamese',12,'Female',5.86,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('d2c611e7-2c17-454d-b3f8-827230b909ef','1f3ae1a4-39c5-4f9f-8144-3c857b03d580','Herta','Cat','Siamese',2,'Female',2.01,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('d642deae-b81f-4a6f-a2fc-11674a5e0368','92027b09-528e-48df-adde-ed1980219f8b','Aidan','Cat','Ragdoll',6,'Male',34.26,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('f6512b54-a3fa-4b95-9ad9-e3b383f1c0e3','af68eb54-143b-403c-926c-a339f1d3e307','Kolby','Dog','Golden Retriever',11,'Male',10.02,NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12');
/*!40000 ALTER TABLE `pets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
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
INSERT INTO `sessions` VALUES ('32a25aqWvoX6VhE9h9XJ3jtPap3TtbHOOymFRBJU',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYVVxTFFlWVJEQ2U4cUFxU2xJU1o2ZTFZMExwUkY0V2dBMFdwc0VTVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tYW5hZ2VyL2xvZ2luIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1780045039),('aQFW7leT2uu91y4T5H3jCDvtUVwegLSkrc0Mp0Qu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaW9MYVZXUHJoRUVjS0lWODlBcm1WZmZOSWlhdEFrdGlycHBJbDBVeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tYW5hZ2VyL3ZldGVyaW5hcmlhbnMiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778146068),('DSaEhcVZ00LHnhEfaF9t1gCBY2qRyryyAtkpRHmt',NULL,'192.168.100.223','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzdJU2RPeE1YcktyMWZ3dDZDNWN5M2xCS25xY2dUMG5oWloyZ0d6QSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xOTIuMTY4LjEwMC4yMjM6ODAwMC9tYW5hZ2VyIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778079931),('Iu6Ke3ZwGxFT3nwsNmDcUOOB1Vx90NzANmZDZ1mS',NULL,'127.0.0.1','LenovoVantage/3.0.0.191','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUdaZ3RXNm1ScGt1MEN6a08zVWIzZDBnOW02OVpUSlVKR0NycklibCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tYW5hZ2VyIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778138210),('k9iFMX0aNxZuGxHZX9gWzYj0f2GpdUdoQiEiFr8w',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUzFZTzlBeVhqSjZLSEZWN1U5RUlLSVM2c0V5ZEgzcVlCcnNYSU52YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tYW5hZ2VyIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1780133157),('Njs70obdueE76vVoik1DW66mnpoRyrXGe9VAwB2s',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNkxRb3BEelBqc3NJVHZuTTZxaEpwVFI0SUlaUVRKSWxFUnphZWo3SSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tYW5hZ2VyL3ZldGVyaW5hcmlhbnMiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778679811),('T4JBIl8tE279aBKC2B0jAbMog6KmCdxxaq0Amtz8',NULL,'192.168.100.223','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieXl6N0lWUVdNWWZNdkNkbVVXcU5YcE9EM2kyNVNqRTJTUHlUZkcyaiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xOTIuMTY4LjEwMC4yMjM6ODAwMC9tYW5hZ2VyIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779167490),('wMXOe8eAEA6he3IenG84Re7QHi1IeWEsYDmzdUgx',NULL,'192.168.100.223','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWg3MGxJSnFsbXc2RTZBVzJXUmxOd2dwVHg4UlNWbU9Uc0JVRHJYRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTIuMTY4LjEwMC4yMjM6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1779166579);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `role` enum('owner','vet','manager','super_admin') DEFAULT 'owner',
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `clinic_id` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fcm_token` varchar(255) DEFAULT NULL,
  `profile_image_url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_clinic_id_foreign` (`clinic_id`),
  CONSTRAINT `users_clinic_id_foreign` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`clinic_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('115851e1-177f-4f82-abea-15b0583b9630','Abigayle Cummerata','fhowell@example.org','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','+1-818-333-6637',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('1dd58110-082d-4481-ac2a-78962d731f53','Janiya Bogisich','stella.rath@example.org','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','(463) 553-2168','382d82a0-5f3a-4a4b-aeb8-56c313011b33','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('1ecf99e2-80a9-4392-b31e-122fbff6a18d','Mylene Powlowski','dcormier@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','1-385-345-1544','c976f587-b7c9-4d2d-9c7e-32602b051616','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('1f3ae1a4-39c5-4f9f-8144-3c857b03d580','Mr. Nicola Leannon','rodriguez.abner@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','(443) 212-5278',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','brianne37@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','1-830-813-0609','1d012b8d-abe5-4598-be23-f92f7d107e1a','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('2cf77955-b8f1-468b-85cf-ea6aac88a818','Leann Champlin','hoppe.wayne@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','+13092666891',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('309af4b2-6f3f-4870-95e1-fc19310723cc','Princess Hammes','salma16@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1-830-584-3672','65498bd5-602b-4697-a930-429a4b56d531','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('37fd6f80-8b02-49d6-870d-e36317a6b38a','Casper Schaefer','pearline.lesch@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','+1-475-279-9654',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('3d633c5d-8aa8-4069-abbd-6584b87d56f9','Enrique Feil Jr.','alexandria.kohler@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','1-505-872-4424',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('3f5d5d50-e576-494d-bf4c-e46d5fa1c342','Wilhelmine Ullrich','nhegmann@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1.808.479.1218','c976f587-b7c9-4d2d-9c7e-32602b051616','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('4dd068ae-9523-439b-ad57-f88b7e4ea993','Miss Juana Lindgren III','durward.glover@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1.870.412.2378','382d82a0-5f3a-4a4b-aeb8-56c313011b33','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','aimanhakim@gmail.com','$2y$12$joUrd1.FlqT6naIjhXKBNuec2YIarXx.2/dd6jMvN6hyFTtdQlOvm','vet','01182726338','f1d3de08-ebf9-40a5-986e-fc6a9c589871','2026-05-18 22:15:55','2026-05-30 06:42:59','fuYcRQWsTsmtLMDa17FXWo:APA91bGKQZAOBfCfU5oxHtGpUNsIavA3qHGSXZsXPLd_6qx9NmExPBYx2opsJrTv_GItDN3JtE885hBIpi8KO4ysLIJKjALKlPCu1VCMGYNOR7XO3Aq4ltk','https://storage.googleapis.com/pawhealth-6db18.firebasestorage.app/users/fec6184a-fa2a-4cf8-9dbb-3bdca8e8aa9d.jpg'),('550081ba-c2cc-43a2-8846-f1ccc2532395','Lionel Marquardt IV','chammes@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','manager','805-590-5963','3bc3c37f-6490-48c1-a192-82a96db6c3c7','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('57f111af-6daa-4510-bc69-2534deedf3ca','Robbie Goyette','rlesch@example.org','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','+1 (248) 204-2563',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('5a039360-ebc7-4ec3-8903-6239940830f1','Alison Oberbrunner PhD','gusikowski.savion@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1-251-892-8487','1d012b8d-abe5-4598-be23-f92f7d107e1a','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('6S7DV71V3rdJaKHsO1wHxameSqw2','Super Admin','super-manager@pawhealth.com','$2y$12$7N3jGSwzPhRxBcBxIo4Lf.xLnoHE.LaxuNC9Y7qDjQ21tIV7UgLWu','super_admin','',NULL,'2026-05-18 22:11:01','2026-05-18 22:11:01',NULL,NULL),('803d9eb3-d45a-4869-8785-dad93ab4d807','Kelsi Kassulke','cpaucek@example.org','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','1-661-843-9160','65498bd5-602b-4697-a930-429a4b56d531','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('8c507e4c-4579-4519-9da3-9f876794fe83','Main Manager','manager@test.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','manager','1-619-815-2907','8f1b1e5c-2621-4373-97a5-b676386d33c2','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('8VrsCA2dGbWh3IBVJuXM7QZV1OH2','faw','faw@gmail.com','$2y$12$V.ceomCQ8qb0noEV.GLDuepqFa/ipwCOsUwkhE6lV0UZLXB5l3LZy','owner','01129566553',NULL,'2026-05-06 01:51:02','2026-05-29 22:45:14','fuYcRQWsTsmtLMDa17FXWo:APA91bGKQZAOBfCfU5oxHtGpUNsIavA3qHGSXZsXPLd_6qx9NmExPBYx2opsJrTv_GItDN3JtE885hBIpi8KO4ysLIJKjALKlPCu1VCMGYNOR7XO3Aq4ltk','https://storage.googleapis.com/pawhealth-6db18.firebasestorage.app/users/8c13d57e-d9db-4cc1-b479-62b7ea16a0c3.png'),('908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','vet@test.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1-540-716-8166','8f1b1e5c-2621-4373-97a5-b676386d33c2','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('92027b09-528e-48df-adde-ed1980219f8b','Marilyne Breitenberg','alexandre28@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','+1.817.425.2904',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('9c578931-60f5-4a7c-b6e7-d805782da4b9','Virginia Wilderman V','kory.farrell@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','(435) 556-8827',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('9fdd9354-71ec-42fb-8ffb-53bc77177068','Mrs. Mattie Hilpert','addison89@example.org','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','1-276-571-6650','3bc3c37f-6490-48c1-a192-82a96db6c3c7','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('af68eb54-143b-403c-926c-a339f1d3e307','Mrs. Polly Treutel MD','shane@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','(678) 251-2813',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('bad558f1-3b82-41e5-9758-49ff397dd53d','Mr. Brent McCullough MD','rdicki@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','manager','1-361-968-9116','c976f587-b7c9-4d2d-9c7e-32602b051616','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('bc93ed14-4f3e-4bf3-839b-21410897faef','Delphine Mosciski','terry.don@example.org','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','(385) 919-3006','3bc3c37f-6490-48c1-a192-82a96db6c3c7','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('c3754e8d-0c38-443a-a150-41ca157a0c4f','John Doe','owner@test.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','463-506-2516',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('c42bf65e-029a-4996-b846-7f7fb6d7c228','Blanca Leuschke','sbecker@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','918-943-2885','65498bd5-602b-4697-a930-429a4b56d531','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('c6822366-e2a0-4066-bad3-e750d510615c','Lia Lakin','fahey.adella@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1-713-999-1481','c976f587-b7c9-4d2d-9c7e-32602b051616','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('cb05ce02-5c70-4b71-b575-ecc2f6e97c38','Cortney Waelchi','michaela86@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','manager','971.883.2785','382d82a0-5f3a-4a4b-aeb8-56c313011b33','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('d14db8fc-f283-4a25-b7de-4846c7aa5dd3','Mr. Woodrow Lebsack','samantha.thompson@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','manager','+1 (302) 702-0960','65498bd5-602b-4697-a930-429a4b56d531','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('e7d132b9-ad7a-4d23-a09e-53cfee525291','Prof. Lavonne Koelpin IV','kylie.sipes@example.com','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','owner','272-960-6594',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('e9ce4895-fd6e-46bf-91d6-f4a342018194','Joey Ward','lrohan@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','manager','(283) 569-5830','1d012b8d-abe5-4598-be23-f92f7d107e1a','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('fba1e0e2-800b-4952-9330-ce2eff203e20','Dr. Heather Turcotte','becker.brice@example.net','$2y$12$Qngkl2hQXiYua5jYWnhgYO81EMk9rhAxDgUV7PZhlp/O.4s5kO4rC','vet','+1 (669) 779-9402','382d82a0-5f3a-4a4b-aeb8-56c313011b33','2026-05-06 01:47:12','2026-05-06 01:47:12',NULL,NULL),('PluKpWReSlfr1mYvqXFBaR1B29h1','Penang Vet Center','pvc@gmail.com','$2y$12$1ZAUCNSinemndAgc44iBq.Xvfz91bBFw1EjVzTIEO0jU./4OnBHfG','manager','','f1d3de08-ebf9-40a5-986e-fc6a9c589871','2026-05-13 06:52:59','2026-05-13 06:52:59',NULL,NULL),('SkXYaujAcXbpdFAceWq0PAAtCVJ3','John Smith','js@gmail.com','$2y$12$VygBYRwJn8NnhnWxP2zttOBxUhFHX72JTKnH9PopJ4Z3uS2FH0k8K','vet','01172862551','f1d3de08-ebf9-40a5-986e-fc6a9c589871','2026-05-30 02:18:20','2026-05-30 02:18:20',NULL,NULL),('UfIpuL5AG4hH7oSQqqMOQSM4lr23','Chewy Pets','chewy@gmail.com','$2y$12$JJGr5YB9QAAoAAZuha9aCOe0f.kPeQd9pQpbGI/x1vwmGD8eTQ0Ya','manager','','a6366208-0abe-44a2-b072-62998686e3e6','2026-05-07 01:50:03','2026-05-07 01:50:03',NULL,NULL),('UXGdLupyQncm47ntVwY3QC8D1sx2','Rashid Amin','rashid@gmail.com','$2y$12$qZMWDF4VDtt1mVr1TleW.uNZIh9UchHxhNWLdmC007xzbpOu.LtUC','vet','011928437112','a6366208-0abe-44a2-b072-62998686e3e6','2026-05-07 01:52:41','2026-05-07 01:52:41',NULL,NULL),('zEiUiyZ9lbTlyKEKgumjByXISfm2','Fawwaz Rasyad','fawwaz@gmail.com','$2y$12$XCp5kXnoNHaYWKuRd8J6cOaiSLJrONkE.1DSqnJua/v864lR.z8Hm','owner','',NULL,'2026-05-06 01:49:25','2026-05-06 01:49:25',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vet_available_slots`
--

DROP TABLE IF EXISTS `vet_available_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vet_available_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vet_id` char(36) NOT NULL,
  `slot_datetime` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vet_available_slots_vet_id_foreign` (`vet_id`),
  CONSTRAINT `vet_available_slots_vet_id_foreign` FOREIGN KEY (`vet_id`) REFERENCES `veterinarians` (`vet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vet_available_slots`
--

LOCK TABLES `vet_available_slots` WRITE;
/*!40000 ALTER TABLE `vet_available_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `vet_available_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veterinarians`
--

DROP TABLE IF EXISTS `veterinarians`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `veterinarians` (
  `vet_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `profile_image_url` varchar(500) NOT NULL DEFAULT '',
  `working_hours` varchar(255) NOT NULL DEFAULT '',
  `specialties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specialties`)),
  `bio` text DEFAULT NULL,
  `consultation_fee` decimal(8,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `weekly_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`weekly_schedule`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`vet_id`),
  CONSTRAINT `veterinarians_vet_id_foreign` FOREIGN KEY (`vet_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veterinarians`
--

LOCK TABLES `veterinarians` WRITE;
/*!40000 ALTER TABLE `veterinarians` DISABLE KEYS */;
INSERT INTO `veterinarians` VALUES ('1dd58110-082d-4481-ac2a-78962d731f53','Janiya Bogisich','','9:00 AM - 5:00 PM','\"[\\\"Dentistry\\\"]\"','Deserunt aut deserunt autem recusandae nulla illo. Quis sint labore quasi quis. Exercitationem dignissimos porro cum sunt enim. Non harum aperiam commodi iste repudiandae.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('1ecf99e2-80a9-4392-b31e-122fbff6a18d','Mylene Powlowski','','9:00 AM - 5:00 PM','\"[\\\"Dermatology\\\"]\"','Tenetur aut accusamus eos rerum et ea. Ea iste non voluptas dolore numquam sint sit. Qui deserunt excepturi et ut.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('2a539569-7b84-4810-801d-5fb1ffb88818','Dr. Katelyn Conn PhD','','9:00 AM - 5:00 PM','\"[\\\"Surgery\\\"]\"','Nobis enim est doloribus similique. Similique vel ea explicabo omnis itaque. Veritatis sunt vitae omnis quod vero aperiam quia.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('309af4b2-6f3f-4870-95e1-fc19310723cc','Princess Hammes','','9:00 AM - 5:00 PM','\"[\\\"Dermatology\\\"]\"','Ducimus id qui enim est. Temporibus quis ipsum iusto perferendis eligendi. Consectetur vel non labore numquam illo. Et illo sint at saepe fuga.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('3f5d5d50-e576-494d-bf4c-e46d5fa1c342','Wilhelmine Ullrich','','9:00 AM - 5:00 PM','\"[\\\"Dermatology\\\"]\"','At blanditiis in et eos tempore eos quo. Corporis saepe exercitationem eveniet aliquam.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('4dd068ae-9523-439b-ad57-f88b7e4ea993','Miss Juana Lindgren III','','9:00 AM - 5:00 PM','\"[\\\"Surgery\\\"]\"','Vel est est suscipit. Eos reiciendis voluptas et ut voluptatem eum aut commodi. Quae quia fugit aliquam nam laborum error.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('4mD8jSx5nfX6VuG1FpvXTUBVM6h2','Aiman Hakim','https://storage.googleapis.com/pawhealth-6db18.firebasestorage.app/vets/5ff46559-6546-4d23-9272-ddbc2a3635ab.jpg','09:00 - 18:00','[\"Surgery\"]','I am a trained surgeon for animals',20.00,'approved','{\"Monday\":[\"09:00\",\"10:00\",\"11:00\",\"12:00\",\"13:00\",\"14:00\",\"15:00\",\"16:00\"],\"Tuesday\":[\"09:00\",\"10:00\",\"11:00\",\"12:00\",\"13:00\"],\"Wednesday\":[\"09:00\",\"10:00\",\"11:00\",\"12:00\",\"13:00\",\"14:00\",\"16:00\"],\"Thursday\":[\"09:00\",\"10:00\",\"11:00\",\"12:00\",\"13:00\",\"15:00\",\"16:00\"],\"Friday\":[\"11:00\",\"12:00\",\"16:00\"]}','2026-05-18 22:15:55','2026-05-30 06:44:53'),('5a039360-ebc7-4ec3-8903-6239940830f1','Alison Oberbrunner PhD','','9:00 AM - 5:00 PM','\"[\\\"General Practice\\\"]\"','Reprehenderit tempore nam nihil error impedit incidunt. Beatae aperiam nihil quisquam. Voluptatem tempore facere sit ratione est. Quis exercitationem et deleniti aut occaecati.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('803d9eb3-d45a-4869-8785-dad93ab4d807','Kelsi Kassulke','','9:00 AM - 5:00 PM','\"[\\\"Surgery\\\"]\"','Tempora dolore et repellendus. Dolores sit dolorem fugit. Eligendi ab dicta aut. Ut animi est ipsum sapiente dicta vero.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('908aa9a2-e76d-48c0-a5c2-19f88f829e2a','Dr. Jane Smith','','9:00 AM - 5:00 PM','\"[\\\"General Practice\\\"]\"','Velit enim perspiciatis quis quasi ut dolor. Suscipit incidunt est culpa. Fugiat et vero a quidem.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('9fdd9354-71ec-42fb-8ffb-53bc77177068','Mrs. Mattie Hilpert','','9:00 AM - 5:00 PM','\"[\\\"Surgery\\\"]\"','Deserunt est laboriosam quo officiis quis assumenda debitis beatae. Nihil sequi facere quis iure. Eligendi distinctio assumenda sed ea tempore non ullam.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('bc93ed14-4f3e-4bf3-839b-21410897faef','Delphine Mosciski','','9:00 AM - 5:00 PM','\"[\\\"General Practice\\\"]\"','Dolorem laboriosam autem temporibus sint voluptatem rerum. Et quia rem dolor eveniet cupiditate rerum maiores. Ea quo quae repellat voluptas tenetur veniam numquam.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('c42bf65e-029a-4996-b846-7f7fb6d7c228','Blanca Leuschke','','9:00 AM - 5:00 PM','\"[\\\"Dentistry\\\"]\"','Quia voluptates quibusdam eaque autem debitis. Inventore ullam libero quibusdam rem ipsam enim. Commodi eum alias rerum porro. Voluptatem minima ipsum et vitae rerum aperiam iusto.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('c6822366-e2a0-4066-bad3-e750d510615c','Lia Lakin','','9:00 AM - 5:00 PM','\"[\\\"General Practice\\\"]\"','Voluptas ut animi explicabo repellat nulla cupiditate ex. Occaecati voluptatem quos atque. Qui quis omnis aliquid fugiat qui odio. Assumenda voluptatum molestiae rem voluptas dignissimos eveniet reprehenderit ut.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('fba1e0e2-800b-4952-9330-ce2eff203e20','Dr. Heather Turcotte','','9:00 AM - 5:00 PM','\"[\\\"Dermatology\\\"]\"','Sint omnis illo maxime quo. Delectus magnam occaecati reprehenderit voluptas quam consequuntur omnis. Qui non aliquid ad quo exercitationem.',NULL,'approved',NULL,'2026-05-06 01:47:12','2026-05-06 01:47:12'),('SkXYaujAcXbpdFAceWq0PAAtCVJ3','John Smith','','','[\"General Practice\"]',NULL,30.00,'approved',NULL,'2026-05-30 02:18:20','2026-05-30 02:18:26'),('UXGdLupyQncm47ntVwY3QC8D1sx2','Rashid Amin','','','[\"GP\"]',NULL,NULL,'approved','{\"Monday\":[\"09:00\",\"10:00\",\"11:00\",\"12:00\",\"13:00\",\"14:00\",\"15:00\",\"16:00\"]}','2026-05-07 01:52:41','2026-05-07 02:00:54');
/*!40000 ALTER TABLE `veterinarians` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-01 12:36:06
