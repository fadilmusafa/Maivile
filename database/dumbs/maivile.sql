-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: maivile
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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `subject_type` varchar(100) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activity_logs_action_index` (`action`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-10-19 11:58:43','2026-10-19 11:58:43'),(2,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 06:47:48','2026-11-02 06:47:48'),(3,1,'transaction_reviewed','App\\Models\\Transaction',1,'Pengajuan MVL-DEMO-0001: approve','127.0.0.1','2026-11-02 06:53:05','2026-11-02 06:53:05'),(4,1,'transaction_handover','App\\Models\\Transaction',1,'Penyerahan MVL-DEMO-0001 dicatat','127.0.0.1','2026-11-02 06:53:47','2026-11-02 06:53:47'),(5,1,'transaction_returned','App\\Models\\Transaction',1,'Pengembalian MVL-DEMO-0001 dicatat','127.0.0.1','2026-11-02 06:53:48','2026-11-02 06:53:48'),(6,1,'transaction_advanced','App\\Models\\Transaction',1,'Pengajuan MVL-DEMO-0001 menjadi selesai','127.0.0.1','2026-11-02 06:53:50','2026-11-02 06:53:50'),(7,1,'transaction_advanced','App\\Models\\Transaction',2,'Pengajuan MVL-DEMO-0002 menjadi siap_diambil','127.0.0.1','2026-11-02 06:54:03','2026-11-02 06:54:03'),(8,1,'transaction_handover','App\\Models\\Transaction',2,'Penyerahan MVL-DEMO-0002 dicatat','127.0.0.1','2026-11-02 06:54:23','2026-11-02 06:54:23'),(9,1,'transaction_advanced','App\\Models\\Transaction',2,'Pengajuan MVL-DEMO-0002 menjadi selesai','127.0.0.1','2026-11-02 06:54:25','2026-11-02 06:54:25'),(10,1,'report.pdf',NULL,NULL,'Mengunduh laporan pengajuan (PDF).','127.0.0.1','2026-11-02 06:57:03','2026-11-02 06:57:03'),(11,1,'report.pdf',NULL,NULL,'Mengunduh laporan pengajuan (PDF).','127.0.0.1','2026-11-02 06:57:48','2026-11-02 06:57:48'),(12,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 07:11:46','2026-11-02 07:11:46'),(13,2,'login','App\\Models\\User',2,'Login berhasil','127.0.0.1','2026-11-02 07:11:58','2026-11-02 07:11:58'),(14,2,'transaction_submitted','App\\Models\\Transaction',5,'Pengajuan MVL-20261102-0001 (legalisir) dikirim','127.0.0.1','2026-11-02 07:13:46','2026-11-02 07:13:46'),(15,2,'transaction_file_viewed','App\\Models\\TransactionFile',3,'Lampiran ijazah-alya.jpg','127.0.0.1','2026-11-02 07:13:53','2026-11-02 07:13:53'),(16,2,'logout','App\\Models\\User',2,'Logout','127.0.0.1','2026-11-02 07:14:24','2026-11-02 07:14:24'),(17,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 07:15:19','2026-11-02 07:15:19'),(18,1,'transaction_handover','App\\Models\\Transaction',3,'Penyerahan MVL-DEMO-0003 dicatat','127.0.0.1','2026-11-02 07:16:42','2026-11-02 07:16:42'),(19,1,'transaction_advanced','App\\Models\\Transaction',3,'Pengajuan MVL-DEMO-0003 menjadi selesai','127.0.0.1','2026-11-02 07:16:43','2026-11-02 07:16:43'),(20,1,'transaction_reviewed','App\\Models\\Transaction',5,'Pengajuan MVL-20261102-0001: approve','127.0.0.1','2026-11-02 07:16:55','2026-11-02 07:16:55'),(21,1,'transaction_advanced','App\\Models\\Transaction',5,'Pengajuan MVL-20261102-0001 menjadi sedang_diproses','127.0.0.1','2026-11-02 07:16:57','2026-11-02 07:16:57'),(22,1,'transaction_advanced','App\\Models\\Transaction',5,'Pengajuan MVL-20261102-0001 menjadi siap_diambil','127.0.0.1','2026-11-02 07:17:05','2026-11-02 07:17:05'),(23,1,'transaction_handover','App\\Models\\Transaction',5,'Penyerahan MVL-20261102-0001 dicatat','127.0.0.1','2026-11-02 07:17:31','2026-11-02 07:17:31'),(24,1,'transaction_advanced','App\\Models\\Transaction',5,'Pengajuan MVL-20261102-0001 menjadi selesai','127.0.0.1','2026-11-02 07:17:35','2026-11-02 07:17:35'),(25,2,'login','App\\Models\\User',2,'Login berhasil','127.0.0.1','2026-11-02 07:18:09','2026-11-02 07:18:09'),(26,1,'report.excel',NULL,NULL,'Mengunduh laporan pengajuan (Excel).','127.0.0.1','2026-11-02 07:20:30','2026-11-02 07:20:30'),(27,1,'report.pdf',NULL,NULL,'Mengunduh laporan pengajuan (PDF).','127.0.0.1','2026-11-02 07:23:51','2026-11-02 07:23:51'),(28,1,'report.excel',NULL,NULL,'Mengunduh laporan pengajuan (Excel).','127.0.0.1','2026-11-02 07:25:55','2026-11-02 07:25:55'),(29,1,'report.excel',NULL,NULL,'Mengunduh laporan pengajuan (Excel).','127.0.0.1','2026-11-02 07:26:14','2026-11-02 07:26:14'),(30,1,'transaction_receipt_downloaded','App\\Models\\Transaction',5,'Bukti pengajuan MVL-20261102-0001','127.0.0.1','2026-11-02 07:27:08','2026-11-02 07:27:08'),(31,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 07:30:16','2026-11-02 07:30:16'),(32,2,'login','App\\Models\\User',2,'Login berhasil','127.0.0.1','2026-11-02 07:33:08','2026-11-02 07:33:08'),(33,NULL,'registration_submitted','App\\Models\\User',5,'Pendaftaran akun siswa Muhammad Fadhil Musyaffa (NIS 20261006)','127.0.0.1','2026-11-02 10:26:26','2026-11-02 10:26:26'),(34,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 10:30:20','2026-11-02 10:30:20'),(35,1,'registration_approved','App\\Models\\User',5,'Pendaftaran Muhammad Fadhil Musyaffa disetujui','127.0.0.1','2026-11-02 10:30:40','2026-11-02 10:30:40'),(36,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 10:38:41','2026-11-02 10:38:41'),(37,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-02 10:38:55','2026-11-02 10:38:55'),(38,5,'logout','App\\Models\\User',5,'Logout','127.0.0.1','2026-11-02 10:39:58','2026-11-02 10:39:58'),(39,2,'login','App\\Models\\User',2,'Login berhasil','127.0.0.1','2026-11-02 10:40:10','2026-11-02 10:40:10'),(40,2,'logout','App\\Models\\User',2,'Logout','127.0.0.1','2026-11-02 11:49:38','2026-11-02 11:49:38'),(41,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 11:50:08','2026-11-02 11:50:08'),(42,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 11:52:11','2026-11-02 11:52:11'),(43,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 11:52:27','2026-11-02 11:52:27'),(44,1,'user_created','App\\Models\\User',6,'Akun student Muhammad Fadhil dibuat','127.0.0.1','2026-11-02 11:53:34','2026-11-02 11:53:34'),(45,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 11:53:50','2026-11-02 11:53:50'),(46,6,'login','App\\Models\\User',6,'Login berhasil','127.0.0.1','2026-11-02 11:53:59','2026-11-02 11:53:59'),(47,6,'password_changed','App\\Models\\User',6,'Kata sandi diganti','127.0.0.1','2026-11-02 11:55:17','2026-11-02 11:55:17'),(48,6,'password_changed','App\\Models\\User',6,'Kata sandi diganti','127.0.0.1','2026-11-02 11:57:17','2026-11-02 11:57:17'),(49,6,'logout','App\\Models\\User',6,'Logout','127.0.0.1','2026-11-02 12:16:45','2026-11-02 12:16:45'),(50,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 12:17:01','2026-11-02 12:17:01'),(51,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 12:21:05','2026-11-02 12:21:05'),(52,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 12:22:52','2026-11-02 12:22:52'),(53,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 12:24:07','2026-11-02 12:24:07'),(54,3,'login','App\\Models\\User',3,'Login berhasil','127.0.0.1','2026-11-02 12:24:22','2026-11-02 12:24:22'),(55,3,'transaction_submitted','App\\Models\\Transaction',6,'Pengajuan MVL-20261102-0002 (peminjaman rapor) dikirim','127.0.0.1','2026-11-02 12:26:26','2026-11-02 12:26:26'),(56,3,'logout','App\\Models\\User',3,'Logout','127.0.0.1','2026-11-02 12:26:35','2026-11-02 12:26:35'),(57,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-02 12:26:49','2026-11-02 12:26:49'),(58,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-02 12:35:18','2026-11-02 12:35:18'),(59,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-03 22:06:39','2026-11-03 22:06:39'),(60,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-03 22:36:04','2026-11-03 22:36:04'),(61,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-03 22:58:52','2026-11-03 22:58:52'),(62,5,'logout','App\\Models\\User',5,'Logout','127.0.0.1','2026-11-03 22:59:08','2026-11-03 22:59:08'),(63,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-03 23:02:14','2026-11-03 23:02:14'),(64,1,'document_created','App\\Models\\Document',7,'Dokumen Rapor Semester 1 - Muhammad Fadhil Muyaffa ditambahkan','127.0.0.1','2026-11-03 23:06:08','2026-11-03 23:06:08'),(65,1,'document_created','App\\Models\\Document',8,'Dokumen Rapor Semester 2 - Muhammad Fadhil Muyaffa ditambahkan','127.0.0.1','2026-11-03 23:07:30','2026-11-03 23:07:30'),(66,1,'document_updated','App\\Models\\Document',7,'Dokumen Rapor Semester 1 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:07:54','2026-11-03 23:07:54'),(67,1,'document_created','App\\Models\\Document',9,'Dokumen Rapor Semester 2 - Muhammad Fadhil Musyaffa ditambahkan','127.0.0.1','2026-11-03 23:08:48','2026-11-03 23:08:48'),(68,1,'document_created','App\\Models\\Document',10,'Dokumen Rapor Semester 2 - Muhammad Fadhil Musyaffa ditambahkan','127.0.0.1','2026-11-03 23:09:37','2026-11-03 23:09:37'),(69,1,'document_created','App\\Models\\Document',11,'Dokumen Rapor Semester 2 - Muhammad Fadhil Musyaffa ditambahkan','127.0.0.1','2026-11-03 23:10:16','2026-11-03 23:10:16'),(70,1,'document_updated','App\\Models\\Document',8,'Dokumen Rapor Semester 2 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:10:31','2026-11-03 23:10:31'),(71,1,'document_updated','App\\Models\\Document',9,'Dokumen Rapor Semester 3- Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:10:45','2026-11-03 23:10:45'),(72,1,'document_updated','App\\Models\\Document',9,'Dokumen Rapor Semester 3 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:10:57','2026-11-03 23:10:57'),(73,1,'document_updated','App\\Models\\Document',10,'Dokumen Rapor Semester 4- Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:11:13','2026-11-03 23:11:13'),(74,1,'document_updated','App\\Models\\Document',10,'Dokumen Rapor Semester 4 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:11:48','2026-11-03 23:11:48'),(75,1,'document_updated','App\\Models\\Document',11,'Dokumen Rapor Semester 5 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:12:03','2026-11-03 23:12:03'),(76,1,'document_created','App\\Models\\Document',12,'Dokumen Ijazah Asli - Muhammad fadhil Musyaffa ditambahkan','127.0.0.1','2026-11-03 23:14:04','2026-11-03 23:14:04'),(77,1,'document_updated','App\\Models\\Document',12,'Dokumen Ijazah Asli - Muhammad fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:28:51','2026-11-03 23:28:51'),(78,1,'document_viewed','App\\Models\\Document',12,'Berkas Ijazah Asli - Muhammad fadhil Musyaffa','127.0.0.1','2026-11-03 23:28:59','2026-11-03 23:28:59'),(79,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-03 23:30:29','2026-11-03 23:30:29'),(80,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-03 23:30:51','2026-11-03 23:30:51'),(81,5,'logout','App\\Models\\User',5,'Logout','127.0.0.1','2026-11-03 23:31:25','2026-11-03 23:31:25'),(82,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-03 23:31:48','2026-11-03 23:31:48'),(83,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-03 23:32:42','2026-11-03 23:32:42'),(84,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-03 23:33:03','2026-11-03 23:33:03'),(85,1,'document_updated','App\\Models\\Document',12,'Dokumen Ijazah Asli - Muhammad fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:33:14','2026-11-03 23:33:14'),(86,1,'document_updated','App\\Models\\Document',11,'Dokumen Rapor Semester 5 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:33:29','2026-11-03 23:33:29'),(87,1,'document_updated','App\\Models\\Document',10,'Dokumen Rapor Semester 4 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:33:47','2026-11-03 23:33:47'),(88,1,'document_updated','App\\Models\\Document',9,'Dokumen Rapor Semester 3 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:34:05','2026-11-03 23:34:05'),(89,1,'document_updated','App\\Models\\Document',8,'Dokumen Rapor Semester 2 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:34:20','2026-11-03 23:34:20'),(90,1,'document_updated','App\\Models\\Document',7,'Dokumen Rapor Semester 1 - Muhammad Fadhil Musyaffa diperbarui','127.0.0.1','2026-11-03 23:34:36','2026-11-03 23:34:36'),(91,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-03 23:34:46','2026-11-03 23:34:46'),(92,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-03 23:35:05','2026-11-03 23:35:05'),(93,1,'user_deleted','App\\Models\\User',6,'Akun Muhammad Fadhil dihapus','127.0.0.1','2026-11-03 23:35:17','2026-11-03 23:35:17'),(94,1,'logout','App\\Models\\User',1,'Logout','127.0.0.1','2026-11-03 23:35:21','2026-11-03 23:35:21'),(95,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-03 23:35:32','2026-11-03 23:35:32'),(96,5,'transaction_submitted','App\\Models\\Transaction',7,'Pengajuan MVL-20261104-0001 (peminjaman rapor) dikirim','127.0.0.1','2026-11-03 23:36:37','2026-11-03 23:36:37'),(97,5,'transaction_submitted','App\\Models\\Transaction',8,'Pengajuan MVL-20261104-0002 (legalisir) dikirim','127.0.0.1','2026-11-03 23:42:23','2026-11-03 23:42:23'),(98,5,'transaction_submitted','App\\Models\\Transaction',9,'Pengajuan MVL-20261104-0003 (pengambilan ijazah) dikirim','127.0.0.1','2026-11-03 23:43:08','2026-11-03 23:43:08'),(99,5,'logout','App\\Models\\User',5,'Logout','127.0.0.1','2026-11-03 23:44:10','2026-11-03 23:44:10'),(100,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-03 23:44:40','2026-11-03 23:44:40'),(101,1,'transaction_reviewed','App\\Models\\Transaction',9,'Pengajuan MVL-20261104-0003: approve','127.0.0.1','2026-11-03 23:46:48','2026-11-03 23:46:48'),(102,1,'transaction_handover','App\\Models\\Transaction',9,'Penyerahan MVL-20261104-0003 dicatat','127.0.0.1','2026-11-03 23:47:35','2026-11-03 23:47:35'),(103,1,'transaction_advanced','App\\Models\\Transaction',9,'Pengajuan MVL-20261104-0003 menjadi selesai','127.0.0.1','2026-11-03 23:48:23','2026-11-03 23:48:23'),(104,1,'transaction_reviewed','App\\Models\\Transaction',8,'Pengajuan MVL-20261104-0002: approve','127.0.0.1','2026-11-03 23:48:35','2026-11-03 23:48:35'),(105,1,'transaction_advanced','App\\Models\\Transaction',8,'Pengajuan MVL-20261104-0002 menjadi sedang_diproses','127.0.0.1','2026-11-03 23:48:41','2026-11-03 23:48:41'),(106,1,'transaction_advanced','App\\Models\\Transaction',8,'Pengajuan MVL-20261104-0002 menjadi siap_diambil','127.0.0.1','2026-11-03 23:48:46','2026-11-03 23:48:46'),(107,1,'transaction_file_viewed','App\\Models\\TransactionFile',7,'Lampiran Lampiran.png','127.0.0.1','2026-11-03 23:49:29','2026-11-03 23:49:29'),(108,1,'transaction_handover','App\\Models\\Transaction',8,'Penyerahan MVL-20261104-0002 dicatat','127.0.0.1','2026-11-03 23:49:35','2026-11-03 23:49:35'),(109,1,'transaction_advanced','App\\Models\\Transaction',8,'Pengajuan MVL-20261104-0002 menjadi selesai','127.0.0.1','2026-11-03 23:49:38','2026-11-03 23:49:38'),(110,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-04 05:26:55','2026-11-04 05:26:55'),(111,5,'logout','App\\Models\\User',5,'Logout','127.0.0.1','2026-11-04 05:37:45','2026-11-04 05:37:45'),(112,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-04 05:38:17','2026-11-04 05:38:17'),(113,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-04 16:26:32','2026-11-04 16:26:32'),(114,1,'transaction_reviewed','App\\Models\\Transaction',7,'Pengajuan MVL-20261104-0001: approve','127.0.0.1','2026-11-04 16:27:00','2026-11-04 16:27:00'),(115,1,'transaction_handover','App\\Models\\Transaction',7,'Penyerahan MVL-20261104-0001 dicatat','127.0.0.1','2026-11-04 16:40:42','2026-11-04 16:40:42'),(116,1,'transaction_returned','App\\Models\\Transaction',7,'Pengembalian MVL-20261104-0001 dicatat','127.0.0.1','2026-11-04 16:42:18','2026-11-04 16:42:18'),(117,1,'transaction_file_viewed','App\\Models\\TransactionFile',10,'Lampiran Bukti Serah Terima.png','127.0.0.1','2026-11-04 16:42:31','2026-11-04 16:42:31'),(118,1,'transaction_advanced','App\\Models\\Transaction',7,'Pengajuan MVL-20261104-0001 menjadi selesai','127.0.0.1','2026-11-04 16:42:43','2026-11-04 16:42:43'),(119,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-09 12:54:41','2026-11-09 12:54:41'),(120,5,'logout','App\\Models\\User',5,'Logout','127.0.0.1','2026-11-09 13:02:08','2026-11-09 13:02:08'),(121,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-09 13:05:23','2026-11-09 13:05:23'),(122,5,'login','App\\Models\\User',5,'Login berhasil','127.0.0.1','2026-11-09 13:20:33','2026-11-09 13:20:33'),(123,5,'transaction_submitted','App\\Models\\Transaction',10,'Pengajuan MVL-20261109-0001 (peminjaman rapor) dikirim','127.0.0.1','2026-11-09 13:23:56','2026-11-09 13:23:56'),(124,1,'transaction_reviewed','App\\Models\\Transaction',10,'Pengajuan MVL-20261109-0001: reject','127.0.0.1','2026-11-09 13:25:52','2026-11-09 13:25:52'),(125,1,'login','App\\Models\\User',1,'Login berhasil','127.0.0.1','2026-11-10 07:01:56','2026-11-10 07:01:56');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'document',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Rapor','document',NULL,'2026-10-19 11:20:44','2026-10-19 11:20:44'),(2,'Ijazah','document',NULL,'2026-10-19 11:20:44','2026-10-19 11:20:44'),(3,'Sertifikat','document',NULL,'2026-10-19 11:20:44','2026-10-19 11:20:44'),(4,'Surat Keterangan','document',NULL,'2026-10-19 11:20:44','2026-10-19 11:20:44'),(5,'Dokumen Lainnya','document',NULL,'2026-10-19 11:20:44','2026-10-19 11:20:44');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `owner_user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `document_kind` varchar(20) NOT NULL DEFAULT 'physical',
  `availability` varchar(20) NOT NULL DEFAULT 'tersedia',
  `physical_location` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_mime` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_category_id_foreign` (`category_id`),
  KEY `documents_owner_user_id_category_id_index` (`owner_user_id`,`category_id`),
  KEY `documents_availability_index` (`availability`),
  CONSTRAINT `documents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `documents_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,1,2,'Rapor Semester 5 - Alya Putri Maharani','RPT-2024001-5','2025/2026','Dokumen fisik; belum ada file digital.','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-10-19 11:20:45','2026-11-02 06:53:48',NULL),(2,2,2,'Ijazah Asli - Alya Putri Maharani','IJZ-2024001','2025/2026','Dokumen fisik; belum ada file digital.','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45',NULL),(3,1,3,'Rapor Semester 5 - Bima Satria Nugroho','RPT-2024002-5','2025/2026','Dokumen fisik; belum ada file digital.','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45',NULL),(4,2,3,'Ijazah Asli - Bima Satria Nugroho','IJZ-2024002','2025/2026','Dokumen fisik; belum ada file digital.','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45',NULL),(5,1,4,'Rapor Semester 5 - Citra Dewi Lestari','RPT-2024003-5','2025/2026','Dokumen fisik; belum ada file digital.','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45',NULL),(6,2,4,'Ijazah Asli - Citra Dewi Lestari','IJZ-2024003','2025/2026','Dokumen fisik; belum ada file digital.','physical','tidak_tersedia','Lemari Arsip TU',NULL,NULL,'2026-10-19 11:20:45','2026-11-02 07:16:42',NULL),(7,1,5,'Rapor Semester 1 - Muhammad Fadhil Musyaffa','RPL-2026100-6','2026/2027','Dokumen fisik','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-11-03 23:06:08','2026-11-03 23:34:36',NULL),(8,1,5,'Rapor Semester 2 - Muhammad Fadhil Musyaffa','RPL-2026100-6','2026/2027','Dokumen fisik','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-11-03 23:07:30','2026-11-03 23:34:20',NULL),(9,1,5,'Rapor Semester 3 - Muhammad Fadhil Musyaffa','RPL-2026100-6','2026/2027','Dokumen fisik','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-11-03 23:08:48','2026-11-03 23:34:05',NULL),(10,1,5,'Rapor Semester 4 - Muhammad Fadhil Musyaffa','RPL-2026100-6','2026/2027','Dokumen fisik','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-11-03 23:09:37','2026-11-03 23:33:47',NULL),(11,1,5,'Rapor Semester 5 - Muhammad Fadhil Musyaffa','RPL-2026100-6','2026/2027','Dokumen fisik','physical','tersedia','Lemari Arsip TU',NULL,NULL,'2026-11-03 23:10:16','2026-11-04 16:42:18',NULL),(12,2,5,'Ijazah Asli - Muhammad fadhil Musyaffa','IJZ-2026100-6','2026/2027','Dokumen fisik; ada juga file digital.','digital','tidak_tersedia','Lemari Arsip TU','documents/c6f3115f-654d-4c87-b30d-d3ad5ce029ac.png','image/png','2026-11-03 23:14:04','2026-11-03 23:47:35',NULL);
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_19_024118_create_notifications_table',1),(5,'2026_10_19_100001_create_categories_table',1),(6,'2026_10_19_100002_create_documents_table',1),(7,'2026_10_19_100003_create_service_types_table',1),(8,'2026_10_19_100004_create_transactions_table',1),(9,'2026_10_19_100005_create_transaction_files_table',1),(10,'2026_10_19_100006_create_transaction_details_table',1),(11,'2026_10_19_100007_create_transaction_status_histories_table',1),(12,'2026_10_19_100008_create_activity_logs_table',1),(13,'2026_12_31_000001_ensure_notifications_table',2),(14,'2027_01_10_000001_add_registration_status_to_users_table',2);
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
  `notifiable_id` bigint(20) unsigned NOT NULL,
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
INSERT INTO `notifications` VALUES ('17281d09-0ca2-4af9-9992-de6aeabc4515','App\\Notifications\\TransactionSubmitted','App\\Models\\User',1,'{\"transaction_id\":10,\"transaction_code\":\"MVL-20261109-0001\",\"title\":\"Pengajuan baru\",\"message\":\"Pengajuan MVL-20261109-0001 menunggu verifikasi.\"}',NULL,'2026-11-09 13:23:56','2026-11-09 13:23:56'),('2b96ca75-6cc6-4a87-96ef-68291d8d8018','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":5,\"transaction_code\":\"MVL-20261102-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261102-0001: Siap diambil.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 07:17:05','2026-11-02 07:18:43'),('2d731985-c613-430b-9d72-15a51f9593c7','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":5,\"transaction_code\":\"MVL-20261102-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261102-0001: Sedang diproses.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 07:16:57','2026-11-02 07:18:43'),('2f954854-28e4-4fb0-ba32-22d467401fd1','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":9,\"transaction_code\":\"MVL-20261104-0003\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0003: Jadwal dikonfirmasi.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:46:48','2026-11-09 12:54:52'),('38295f84-731b-4b14-b51f-15c2ca384624','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":7,\"transaction_code\":\"MVL-20261104-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0001: Selesai.\",\"note\":null}','2026-11-09 12:54:52','2026-11-04 16:42:43','2026-11-09 12:54:52'),('3d70f405-1595-4f83-a34c-aeafbd3132fe','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":5,\"transaction_code\":\"MVL-20261102-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261102-0001: Diserahkan.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 07:17:31','2026-11-02 07:18:43'),('4d01bdaf-f123-4704-aab1-5852ff196ec0','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',3,'{\"transaction_id\":2,\"transaction_code\":\"MVL-DEMO-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0002: Selesai.\",\"note\":null}',NULL,'2026-11-02 06:54:25','2026-11-02 06:54:25'),('52fd3355-33ff-4e5b-9b8e-bce8e0f1e642','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":7,\"transaction_code\":\"MVL-20261104-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0001: Disetujui.\",\"note\":null}','2026-11-09 12:54:52','2026-11-04 16:27:00','2026-11-09 12:54:52'),('6b54e615-571a-44b4-b824-6287756a23b0','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":8,\"transaction_code\":\"MVL-20261104-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0002: Selesai.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:49:38','2026-11-09 12:54:52'),('6ef7b336-0769-4fcd-90c8-8458eb1fea34','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":8,\"transaction_code\":\"MVL-20261104-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0002: Disetujui.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:48:35','2026-11-09 12:54:52'),('7479500a-426a-4a72-a604-42e979d88599','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":1,\"transaction_code\":\"MVL-DEMO-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0001: Dipinjam.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 06:53:47','2026-11-02 07:18:43'),('82337249-299e-451a-973f-702be7924b9d','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":5,\"transaction_code\":\"MVL-20261102-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261102-0001: Selesai.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 07:17:35','2026-11-02 07:18:43'),('83adc64c-f677-4b69-8456-174108a73c28','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":7,\"transaction_code\":\"MVL-20261104-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0001: Dipinjam.\",\"note\":null}','2026-11-09 12:54:52','2026-11-04 16:40:42','2026-11-09 12:54:52'),('88a05d73-36f0-4b1e-8958-4b8e11f440ee','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":8,\"transaction_code\":\"MVL-20261104-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0002: Sedang diproses.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:48:41','2026-11-09 12:54:52'),('93c6893d-3f17-4954-a37b-813b8c5a7bde','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":9,\"transaction_code\":\"MVL-20261104-0003\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0003: Diserahkan.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:47:35','2026-11-09 12:54:52'),('a6b4ca6a-1c5f-40c8-8436-0322084a7424','App\\Notifications\\TransactionSubmitted','App\\Models\\User',1,'{\"transaction_id\":9,\"transaction_code\":\"MVL-20261104-0003\",\"title\":\"Pengajuan baru\",\"message\":\"Pengajuan MVL-20261104-0003 menunggu verifikasi.\"}','2026-11-04 16:42:53','2026-11-03 23:43:08','2026-11-04 16:42:53'),('a6ef2070-72be-464e-ac68-4a6d1d4a16aa','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',4,'{\"transaction_id\":3,\"transaction_code\":\"MVL-DEMO-0003\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0003: Selesai.\",\"note\":null}',NULL,'2026-11-02 07:16:43','2026-11-02 07:16:43'),('ac71303e-7ced-46ec-86e0-ce7c934e2705','App\\Notifications\\TransactionSubmitted','App\\Models\\User',1,'{\"transaction_id\":5,\"transaction_code\":\"MVL-20261102-0001\",\"title\":\"Pengajuan baru\",\"message\":\"Pengajuan MVL-20261102-0001 menunggu verifikasi.\"}','2026-11-02 07:16:46','2026-11-02 07:13:46','2026-11-02 07:16:46'),('adb02af7-aa7d-4526-b82b-bcf629f47be2','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":8,\"transaction_code\":\"MVL-20261104-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0002: Siap diambil.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:48:46','2026-11-09 12:54:52'),('aee75338-9271-4ba5-9ff1-2b6e8e9d3349','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',3,'{\"transaction_id\":2,\"transaction_code\":\"MVL-DEMO-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0002: Diserahkan.\",\"note\":null}',NULL,'2026-11-02 06:54:23','2026-11-02 06:54:23'),('b2c49f55-dafa-4166-a711-616c06ea0f67','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":1,\"transaction_code\":\"MVL-DEMO-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0001: Dikembalikan.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 06:53:48','2026-11-02 07:18:43'),('bb8e64e1-132b-4448-8ca9-3f99ce9df6b5','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":1,\"transaction_code\":\"MVL-DEMO-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0001: Disetujui.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 06:53:05','2026-11-02 07:18:43'),('ce9fb62f-4111-47e9-89d5-96839441ea01','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',3,'{\"transaction_id\":2,\"transaction_code\":\"MVL-DEMO-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0002: Siap diambil.\",\"note\":null}',NULL,'2026-11-02 06:54:03','2026-11-02 06:54:03'),('d170ec33-7c68-4951-ab14-a01dd7e42123','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":9,\"transaction_code\":\"MVL-20261104-0003\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0003: Selesai.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:48:23','2026-11-09 12:54:52'),('d1a49692-df24-455b-96e1-e1ce966047ca','App\\Notifications\\TransactionSubmitted','App\\Models\\User',1,'{\"transaction_id\":6,\"transaction_code\":\"MVL-20261102-0002\",\"title\":\"Pengajuan baru\",\"message\":\"Pengajuan MVL-20261102-0002 menunggu verifikasi.\"}','2026-11-04 16:42:53','2026-11-02 12:26:26','2026-11-04 16:42:53'),('d437c70a-7d53-4608-bae2-393a925c11fa','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":10,\"transaction_code\":\"MVL-20261109-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261109-0001: Ditolak.\",\"note\":\"Dokumen tidak vald\"}',NULL,'2026-11-09 13:25:52','2026-11-09 13:25:52'),('d4d81170-d668-4010-bf47-698363924f43','App\\Notifications\\TransactionSubmitted','App\\Models\\User',1,'{\"transaction_id\":8,\"transaction_code\":\"MVL-20261104-0002\",\"title\":\"Pengajuan baru\",\"message\":\"Pengajuan MVL-20261104-0002 menunggu verifikasi.\"}','2026-11-04 16:42:53','2026-11-03 23:42:23','2026-11-04 16:42:53'),('de833093-3cbb-48db-9249-8c362fc58b98','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":5,\"transaction_code\":\"MVL-20261102-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261102-0001: Disetujui.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 07:16:55','2026-11-02 07:18:43'),('e386cc59-509f-4f68-a744-9ebdc4070925','App\\Notifications\\TransactionSubmitted','App\\Models\\User',1,'{\"transaction_id\":7,\"transaction_code\":\"MVL-20261104-0001\",\"title\":\"Pengajuan baru\",\"message\":\"Pengajuan MVL-20261104-0001 menunggu verifikasi.\"}','2026-11-04 16:42:53','2026-11-03 23:36:37','2026-11-04 16:42:53'),('e753d3b9-5481-4db5-b1ba-200599bd381d','App\\Notifications\\StudentRegistered','App\\Models\\User',1,'{\"type\":\"registration\",\"user_id\":5,\"title\":\"Pendaftar baru\",\"message\":\"Muhammad Fadhil Musyaffa (NIS 20261006) menunggu persetujuan akun.\"}','2026-11-02 10:30:26','2026-11-02 10:26:26','2026-11-02 10:30:26'),('ec0e2221-8f95-4a24-9665-6448c81bd792','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":7,\"transaction_code\":\"MVL-20261104-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0001: Dikembalikan.\",\"note\":null}','2026-11-09 12:54:52','2026-11-04 16:42:18','2026-11-09 12:54:52'),('fa1a5bf5-1306-45f3-b516-9ac78b0c8d55','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',5,'{\"transaction_id\":8,\"transaction_code\":\"MVL-20261104-0002\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-20261104-0002: Diserahkan.\",\"note\":null}','2026-11-09 12:54:52','2026-11-03 23:49:35','2026-11-09 12:54:52'),('faa7ba7e-8adb-4f47-9d7c-8f596d817264','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',2,'{\"transaction_id\":1,\"transaction_code\":\"MVL-DEMO-0001\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0001: Selesai.\",\"note\":null}','2026-11-02 07:18:43','2026-11-02 06:53:50','2026-11-02 07:18:43'),('fbf7c87c-8b8a-40e3-b0c9-ad1a40e075e7','App\\Notifications\\TransactionStatusChanged','App\\Models\\User',4,'{\"transaction_id\":3,\"transaction_code\":\"MVL-DEMO-0003\",\"title\":\"Status pengajuan diperbarui\",\"message\":\"Pengajuan MVL-DEMO-0003: Diserahkan.\",\"note\":null}',NULL,'2026-11-02 07:16:42','2026-11-02 07:16:42');
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
-- Table structure for table `service_types`
--

DROP TABLE IF EXISTS `service_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_types_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_types`
--

LOCK TABLES `service_types` WRITE;
/*!40000 ALTER TABLE `service_types` DISABLE KEYS */;
INSERT INTO `service_types` VALUES (1,'Peminjaman Rapor','pinjam_rapor','Peminjaman rapor fisik oleh siswa.',1,'2026-10-19 11:20:44','2026-10-19 11:20:44'),(2,'Legalisir Dokumen','legalisir','Legalisir ijazah, rapor, atau dokumen akademik lain.',1,'2026-10-19 11:20:44','2026-10-19 11:20:44'),(3,'Pengambilan Ijazah','pengambilan_ijazah','Pengambilan ijazah asli sesuai jadwal.',1,'2026-10-19 11:20:44','2026-10-19 11:20:44');
/*!40000 ALTER TABLE `service_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
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
INSERT INTO `sessions` VALUES ('ckAV8yxVaHqTrRPqN1ut9fOW7HDHfSvfiIdluG50',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoic3MwZnJJY0o1ZXZBWEpPaHJrNVN1bVNxUFJpTVFqeEFXeFRkc1d0NiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjE2Y2M0MGEzMjczNDQ1YTkwMGVmMDY1OThmMTYyNWY0ODVmYzQwYjEwYjJlYTkwZWEwZDE5MTY0NDFiNDBlNDQiO30=',1794212976),('vnLikU8EyfOCV2E9Fs0buJND7P1g7XhEXl7qAbhu',5,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaXBiUm1tVlVRc0M1MHRaNzVLeGk1dnA5U2EyWnBPS1Q1b0RsOTFQNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdHVkZW50L2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czoxNzoic3R1ZGVudC5kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiJjOTY2NWViYzY4NzVlNTE4ODkxYTMxYWU3NWM5OGY3ZmMxMzk5YmM1M2M2YjdhNjNlNmIwNzRkNjQ5YzNiNzcyIjt9',1794213014),('ZwVDZeNVTZOW0U4pF8F3FpU4YxWoJYPLdyex93GL',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVzR2QjVkQVV3Z1lUeVZEZUNSOVVUODdzNko4SVhkakRMQnpqOFp0YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi90cmFuc2FjdGlvbnMvNiI7czo1OiJyb3V0ZSI7czoyMzoiYWRtaW4udHJhbnNhY3Rpb25zLnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIxNmNjNDBhMzI3MzQ0NWE5MDBlZjA2NTk4ZjE2MjVmNDg1ZmM0MGIxMGIyZWE5MGVhMGQxOTE2NDQxYjQwZTQ0Ijt9',1794276220);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_details`
--

DROP TABLE IF EXISTS `transaction_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `legalization_doc_type` varchar(50) DEFAULT NULL,
  `copies` smallint(5) unsigned DEFAULT NULL,
  `pickup_time_start` time DEFAULT NULL,
  `pickup_time_end` time DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `receiver_relation` varchar(50) DEFAULT NULL,
  `receiver_id_number` varchar(50) DEFAULT NULL,
  `handover_note` text DEFAULT NULL,
  `return_condition_note` text DEFAULT NULL,
  `handover_proof_file_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_details_transaction_id_unique` (`transaction_id`),
  KEY `transaction_details_handover_proof_file_id_foreign` (`handover_proof_file_id`),
  CONSTRAINT `transaction_details_handover_proof_file_id_foreign` FOREIGN KEY (`handover_proof_file_id`) REFERENCES `transaction_files` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_details_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_details`
--

LOCK TABLES `transaction_details` WRITE;
/*!40000 ALTER TABLE `transaction_details` DISABLE KEYS */;
INSERT INTO `transaction_details` VALUES (1,1,NULL,NULL,NULL,NULL,'08120002024001','Alya Putri Maharani','Siswa sendiri',NULL,NULL,NULL,1,'2026-10-19 11:20:45','2026-11-02 06:53:46'),(2,2,'Ijazah',3,NULL,NULL,'08120002024002','Bima Satria Nugroho','Siswa sendiri',NULL,NULL,NULL,2,'2026-10-19 11:20:45','2026-11-02 06:54:23'),(3,3,NULL,NULL,NULL,NULL,'08120002024003','Citra Dewi Lestari','Siswa sendiri','NIS 2024003',NULL,NULL,4,'2026-10-19 11:20:45','2026-11-02 07:16:42'),(4,4,'Ijazah',3,NULL,NULL,'08120002024001',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45'),(5,5,'Rapor',2,NULL,NULL,'08120002024001','Alya Putri Maharani','Siswa sendiri','NIS 2024001',NULL,NULL,5,'2026-11-02 07:13:46','2026-11-02 07:17:31'),(6,6,NULL,NULL,NULL,NULL,'08120002024002',NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-02 12:26:26','2026-11-02 12:26:26'),(7,7,NULL,NULL,NULL,NULL,'1234567890','Muhammad Fadhil Musyaffa','Siswa sendiri','20261006',NULL,'Kondisi rapor dalam keadaan baik',10,'2026-11-03 23:36:37','2026-11-04 16:42:18'),(8,8,'Ijazah',1,NULL,NULL,'1234567890','Muhammad Fadhil Musyaffa','Siswa sendiri','NIS 20261006',NULL,NULL,9,'2026-11-03 23:42:23','2026-11-03 23:49:35'),(9,9,NULL,NULL,'12:00:00','14:00:00','1234567890','Muhammad Fadhil Musyaffa','Siswa sendiri','Muhammad Fadhil Musyaffa (NIS 20261006, XII-RPL-A)',NULL,NULL,8,'2026-11-03 23:43:08','2026-11-03 23:47:35'),(10,10,NULL,NULL,NULL,NULL,'1234567890',NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-09 13:23:56','2026-11-09 13:23:56');
/*!40000 ALTER TABLE `transaction_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_files`
--

DROP TABLE IF EXISTS `transaction_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'requirement',
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_files_uploaded_by_foreign` (`uploaded_by`),
  KEY `transaction_files_transaction_id_type_index` (`transaction_id`,`type`),
  CONSTRAINT `transaction_files_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_files`
--

LOCK TABLES `transaction_files` WRITE;
/*!40000 ALTER TABLE `transaction_files` DISABLE KEYS */;
INSERT INTO `transaction_files` VALUES (1,1,1,'handover_proof','ijazah-alya-putri.jpg','transaction-files/a7c0c9ec-22dd-439c-8714-f54ad1708ae7.jpg','image/jpeg',10730,'2026-11-02 06:53:46','2026-11-02 06:53:46'),(2,2,1,'handover_proof','ijazah-bima.jpg','transaction-files/53a3476a-aae9-4ad0-87dd-a3ba3246c715.jpg','image/jpeg',10730,'2026-11-02 06:54:23','2026-11-02 06:54:23'),(3,5,2,'requirement','ijazah-alya.jpg','transaction-files/5fc05e3e-e69e-4916-895f-b196ed5d3c43.jpg','image/jpeg',10730,'2026-11-02 07:13:46','2026-11-02 07:13:46'),(4,3,1,'handover_proof','ijazah-citra.jpg','transaction-files/491b4e39-197d-4674-8183-e1dd3b9150dc.jpg','image/jpeg',10730,'2026-11-02 07:16:42','2026-11-02 07:16:42'),(5,5,1,'handover_proof','ijazah-citra.jpg','transaction-files/533b8deb-1f16-4697-a781-bbf45ff27157.jpg','image/jpeg',10730,'2026-11-02 07:17:31','2026-11-02 07:17:31'),(6,6,3,'requirement','ijazah-bima.jpg','transaction-files/40f36c5b-fc17-4ef3-89d6-4e7b19c342e8.jpg','image/jpeg',10730,'2026-11-02 12:26:26','2026-11-02 12:26:26'),(7,8,5,'requirement','Lampiran.png','transaction-files/5cf091d4-ea16-4224-9d43-d9fb3379afb0.png','image/png',173479,'2026-11-03 23:42:23','2026-11-03 23:42:23'),(8,9,1,'handover_proof','Lampiran.png','transaction-files/a8b874f3-338d-4638-8d51-c3727437a14c.png','image/png',173479,'2026-11-03 23:47:35','2026-11-03 23:47:35'),(9,8,1,'handover_proof','Lampiran.png','transaction-files/b8866041-4326-4e50-9931-841bcf47fe9d.png','image/png',173479,'2026-11-03 23:49:35','2026-11-03 23:49:35'),(10,7,1,'handover_proof','Bukti Serah Terima.png','transaction-files/bdcc4abc-e448-44fd-92ea-89d63fcea8d9.png','image/png',175752,'2026-11-04 16:40:42','2026-11-04 16:40:42');
/*!40000 ALTER TABLE `transaction_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_status_histories`
--

DROP TABLE IF EXISTS `transaction_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `changed_by` bigint(20) unsigned NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transaction_status_histories_changed_by_foreign` (`changed_by`),
  KEY `transaction_status_histories_transaction_id_changed_at_index` (`transaction_id`,`changed_at`),
  CONSTRAINT `transaction_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `transaction_status_histories_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_status_histories`
--

LOCK TABLES `transaction_status_histories` WRITE;
/*!40000 ALTER TABLE `transaction_status_histories` DISABLE KEYS */;
INSERT INTO `transaction_status_histories` VALUES (1,1,NULL,'menunggu_verifikasi','Pengajuan dikirim.',2,'2026-10-16 11:20:45'),(2,2,NULL,'menunggu_verifikasi','Pengajuan dikirim.',3,'2026-10-16 11:20:45'),(3,2,'menunggu_verifikasi','sedang_diproses','Berkas lengkap, sedang diproses.',1,'2026-10-17 11:20:45'),(4,3,NULL,'menunggu_verifikasi','Pengajuan dikirim.',4,'2026-10-16 11:20:45'),(5,3,'menunggu_verifikasi','jadwal_dikonfirmasi','Bawa KTP/kartu pelajar asli.',1,'2026-10-17 11:20:45'),(6,4,NULL,'menunggu_verifikasi','Pengajuan dikirim.',2,'2026-10-16 11:20:45'),(7,4,'menunggu_verifikasi','ditolak','Lampiran tidak terbaca, silakan ajukan ulang.',1,'2026-10-17 11:20:45'),(8,1,'menunggu_verifikasi','disetujui',NULL,1,'2026-11-02 06:53:04'),(9,1,'disetujui','dipinjam',NULL,1,'2026-11-02 06:53:46'),(10,1,'dipinjam','dikembalikan',NULL,1,'2026-11-02 06:53:48'),(11,1,'dikembalikan','selesai',NULL,1,'2026-11-02 06:53:50'),(12,2,'sedang_diproses','siap_diambil',NULL,1,'2026-11-02 06:54:03'),(13,2,'siap_diambil','diserahkan',NULL,1,'2026-11-02 06:54:23'),(14,2,'diserahkan','selesai',NULL,1,'2026-11-02 06:54:25'),(15,5,NULL,'menunggu_verifikasi','Pengajuan dikirim.',2,'2026-11-02 07:13:46'),(16,3,'jadwal_dikonfirmasi','diserahkan',NULL,1,'2026-11-02 07:16:42'),(17,3,'diserahkan','selesai',NULL,1,'2026-11-02 07:16:43'),(18,5,'menunggu_verifikasi','disetujui',NULL,1,'2026-11-02 07:16:55'),(19,5,'disetujui','sedang_diproses',NULL,1,'2026-11-02 07:16:57'),(20,5,'sedang_diproses','siap_diambil',NULL,1,'2026-11-02 07:17:05'),(21,5,'siap_diambil','diserahkan',NULL,1,'2026-11-02 07:17:31'),(22,5,'diserahkan','selesai',NULL,1,'2026-11-02 07:17:35'),(23,6,NULL,'menunggu_verifikasi','Pengajuan dikirim.',3,'2026-11-02 12:26:26'),(24,7,NULL,'menunggu_verifikasi','Pengajuan dikirim.',5,'2026-11-03 23:36:37'),(25,8,NULL,'menunggu_verifikasi','Pengajuan dikirim.',5,'2026-11-03 23:42:23'),(26,9,NULL,'menunggu_verifikasi','Pengajuan dikirim.',5,'2026-11-03 23:43:08'),(27,9,'menunggu_verifikasi','jadwal_dikonfirmasi',NULL,1,'2026-11-03 23:46:48'),(28,9,'jadwal_dikonfirmasi','diserahkan',NULL,1,'2026-11-03 23:47:35'),(29,9,'diserahkan','selesai',NULL,1,'2026-11-03 23:48:23'),(30,8,'menunggu_verifikasi','disetujui',NULL,1,'2026-11-03 23:48:35'),(31,8,'disetujui','sedang_diproses',NULL,1,'2026-11-03 23:48:41'),(32,8,'sedang_diproses','siap_diambil',NULL,1,'2026-11-03 23:48:46'),(33,8,'siap_diambil','diserahkan',NULL,1,'2026-11-03 23:49:35'),(34,8,'diserahkan','selesai',NULL,1,'2026-11-03 23:49:38'),(35,7,'menunggu_verifikasi','disetujui',NULL,1,'2026-11-04 16:27:00'),(36,7,'disetujui','dipinjam',NULL,1,'2026-11-04 16:40:42'),(37,7,'dipinjam','dikembalikan','Kondisi rapor dalam keadaan baik',1,'2026-11-04 16:42:18'),(38,7,'dikembalikan','selesai',NULL,1,'2026-11-04 16:42:43'),(39,10,NULL,'menunggu_verifikasi','Pengajuan dikirim.',5,'2026-11-09 13:23:56'),(40,10,'menunggu_verifikasi','ditolak','Dokumen tidak vald',1,'2026-11-09 13:25:52');
/*!40000 ALTER TABLE `transaction_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(30) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_type_id` bigint(20) unsigned NOT NULL,
  `document_id` bigint(20) unsigned DEFAULT NULL,
  `request_date` date NOT NULL,
  `planned_date` date DEFAULT NULL,
  `planned_return_date` date DEFAULT NULL,
  `actual_handover_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `approval_status` varchar(20) NOT NULL DEFAULT 'pending',
  `process_status` varchar(30) NOT NULL DEFAULT 'menunggu_verifikasi',
  `admin_note` text DEFAULT NULL,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  KEY `transactions_document_id_foreign` (`document_id`),
  KEY `transactions_processed_by_foreign` (`processed_by`),
  KEY `transactions_user_id_process_status_index` (`user_id`,`process_status`),
  KEY `transactions_service_type_id_approval_status_index` (`service_type_id`,`approval_status`),
  KEY `transactions_request_date_index` (`request_date`),
  CONSTRAINT `transactions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `transactions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_service_type_id_foreign` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'MVL-DEMO-0001',2,1,1,'2026-10-16','2026-10-24','2026-10-29','2026-11-02','2026-11-02','Data demo untuk pengujian.','approved','selesai',NULL,1,'2026-11-02 06:53:50','2026-10-19 11:20:45','2026-11-02 06:53:50',NULL),(2,'MVL-DEMO-0002',3,2,NULL,'2026-10-16',NULL,NULL,'2026-11-02',NULL,'Data demo untuk pengujian.','approved','selesai','Berkas lengkap, sedang diproses.',1,'2026-11-02 06:54:25','2026-10-19 11:20:45','2026-11-02 06:54:25',NULL),(3,'MVL-DEMO-0003',4,3,6,'2026-10-16','2026-10-26',NULL,'2026-11-02',NULL,'Data demo untuk pengujian.','approved','selesai','Bawa KTP/kartu pelajar asli.',1,'2026-11-02 07:16:43','2026-10-19 11:20:45','2026-11-02 07:16:43',NULL),(4,'MVL-DEMO-0004',2,2,NULL,'2026-10-16',NULL,NULL,NULL,NULL,'Data demo untuk pengujian.','rejected','ditolak','Lampiran tidak terbaca, silakan ajukan ulang.',1,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45',NULL),(5,'MVL-20261102-0001',2,2,NULL,'2026-11-02',NULL,NULL,'2026-11-02',NULL,'Diminta perusahaan','approved','selesai',NULL,1,'2026-11-02 07:17:35','2026-11-02 07:13:46','2026-11-02 07:17:35',NULL),(6,'MVL-20261102-0002',3,1,3,'2026-11-02','2026-11-03','2026-11-10',NULL,NULL,'Keperluan pendaftaran perusahaan muvi','pending','menunggu_verifikasi',NULL,NULL,NULL,'2026-11-02 12:26:26','2026-11-02 12:26:26',NULL),(7,'MVL-20261104-0001',5,1,11,'2026-11-04','2026-11-04','2026-11-11','2026-11-04','2026-11-04','Perusahaan meminta rapor saya','approved','selesai','Kondisi rapor dalam keadaan baik',1,'2026-11-04 16:42:43','2026-11-03 23:36:37','2026-11-04 16:42:43',NULL),(8,'MVL-20261104-0002',5,2,12,'2026-11-04',NULL,NULL,'2026-11-04',NULL,'Keperluan kuliah, kampus meminta ijazah berlegalisir saya.','approved','selesai',NULL,1,'2026-11-03 23:49:38','2026-11-03 23:42:23','2026-11-03 23:49:38',NULL),(9,'MVL-20261104-0003',5,3,12,'2026-11-04','2026-11-04',NULL,'2026-11-04',NULL,NULL,'approved','selesai',NULL,1,'2026-11-03 23:48:23','2026-11-03 23:43:08','2026-11-03 23:48:23',NULL),(10,'MVL-20261109-0001',5,1,7,'2026-11-09','2026-11-10','2026-11-16',NULL,NULL,'Permintaan perusahaan','rejected','ditolak','Dokumen tidak vald',1,NULL,'2026-11-09 13:23:56','2026-11-09 13:25:52',NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `nis` varchar(30) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `class_name` varchar(30) DEFAULT NULL,
  `major` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `registration_status` varchar(20) NOT NULL DEFAULT 'approved',
  `registration_note` text DEFAULT NULL,
  `registration_reviewed_at` timestamp NULL DEFAULT NULL,
  `registration_reviewed_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nis_unique` (`nis`),
  UNIQUE KEY `users_nip_unique` (`nip`),
  KEY `users_role_index` (`role`),
  KEY `users_registration_reviewed_by_foreign` (`registration_reviewed_by`),
  KEY `users_registration_status_index` (`registration_status`),
  CONSTRAINT `users_registration_reviewed_by_foreign` FOREIGN KEY (`registration_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Petugas Tata Usaha','admin@maivile.test','$2y$12$b0b8MFj99Se3RUIEFs3XNOWUSwjzs4sFSF6qS2g87ICb2qM.cNHOO','admin',NULL,'198001012005011001',NULL,NULL,'081200000001',1,0,'2026-11-10 07:01:56',NULL,'2026-10-19 11:20:44','2026-11-10 07:01:56',NULL,'approved',NULL,NULL,NULL),(2,'Alya Putri Maharani','siswa2024001@maivile.test','$2y$12$ZsK7KU1bpItuhnYuWBPMouP1keRfRCvb.1D2zDWrq8FCN55XR6ANO','student','2024001',NULL,'XII-IPA-1','IPA','08120002024001',1,0,'2026-11-02 10:40:10',NULL,'2026-10-19 11:20:45','2026-11-02 10:40:10',NULL,'approved',NULL,NULL,NULL),(3,'Bima Satria Nugroho','siswa2024002@maivile.test','$2y$12$r76hysgYpmTAcrdL7A97xeSj5PnPovnzUG8YynlctnPW3WG8IAyjK','student','2024002',NULL,'XII-IPS-2','IPS','08120002024002',1,0,'2026-11-02 12:24:22',NULL,'2026-10-19 11:20:45','2026-11-02 12:24:22',NULL,'approved',NULL,NULL,NULL),(4,'Citra Dewi Lestari','siswa2024003@maivile.test','$2y$12$5uX1oc4W5xQbQqLDvvV2DOos6q8S.VbeqLpp0mL1wIHoQRXqByLoi','student','2024003',NULL,'XII-IPA-2','IPA','08120002024003',1,0,NULL,NULL,'2026-10-19 11:20:45','2026-10-19 11:20:45',NULL,'approved',NULL,NULL,NULL),(5,'Muhammad Fadhil Musyaffa','fadil@gmail.com','$2y$12$jW/xvZvpTSAMBjtbeLFtvuhqGJXwD8ssvPTWfdesRKjsDhGxQ073q','student','20261006',NULL,'XII-RPL-A','RPL','1234567890',1,0,'2026-11-09 13:20:33',NULL,'2026-11-02 10:26:26','2026-11-09 13:20:33',NULL,'approved',NULL,'2026-11-02 10:30:40',1),(6,'Muhammad Fadhil',NULL,'$2y$12$Vl4EFrfOMFrRqDs1u4qD0OEjFwrjSZJs9N6Q08.CuFzAdbKz6Fb1.','student','20261007',NULL,'v','e',NULL,1,0,'2026-11-02 11:53:59',NULL,'2026-11-02 11:53:34','2026-11-03 23:35:17','2026-11-03 23:35:17','approved',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'maivile'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12  1:20:21
