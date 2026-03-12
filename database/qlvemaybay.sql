-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 05, 2025 at 01:14 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qlvemaybay`
--

-- --------------------------------------------------------

--
-- Table structure for table `chitietdatve`
--

DROP TABLE IF EXISTS `chitietdatve`;
CREATE TABLE IF NOT EXISTS `chitietdatve` (
  `MaChiTiet` int NOT NULL AUTO_INCREMENT,
  `MaDatVe` int NOT NULL,
  `HoTenHanhKhach` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `CMND` varchar(20) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `SoDienThoai` varchar(15) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL,
  `SoGhe` varchar(10) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL,
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaChiTiet`),
  KEY `idx_madatve` (`MaDatVe`),
  KEY `idx_cmnd` (`CMND`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_vietnamese_ci;

--
-- Dumping data for table `chitietdatve`
--

INSERT INTO `chitietdatve` (`MaChiTiet`, `MaDatVe`, `HoTenHanhKhach`, `CMND`, `SoDienThoai`, `Email`, `SoGhe`, `NgayTao`) VALUES
(1, 1, 'Nguyễn Văn A', '001234567890', '0987654321', 'nguyenvana@gmail.com', '1A', '2025-11-05 16:04:47'),
(2, 2, 'Trần Thị B', '001234567891', '0912345678', 'tranthib@gmail.com', '2B', '2025-11-05 16:04:47'),
(3, 2, 'Trần Văn D', '001234567893', '0912345679', 'tranvand@gmail.com', '2C', '2025-11-05 16:04:47'),
(4, 3, 'Lê Văn C', '001234567892', '0909090909', 'levanc@gmail.com', '3D', '2025-11-05 16:04:47'),
(10, 14, 'Nguyễn Văn A', '001234567890', '0901234567', NULL, '2F', '2025-11-05 16:55:20'),
(11, 15, 'Nguyễn Văn A', '001234567890', '0901234567', 'nguyenvana@gmail.com', '4D', '2025-11-05 17:25:16'),
(12, 15, 'Nguyễn Thị B', '000281272819', '0927163911', 'nguyenthib@gmail.com', '4E', '2025-11-05 17:25:16'),
(13, 16, 'Nguyễn Văn A', '001234567890', '0901234567', 'nguyenvana@gmail.com', '13C', '2025-11-05 17:48:28');

-- --------------------------------------------------------

--
-- Table structure for table `chuyenbay`
--

DROP TABLE IF EXISTS `chuyenbay`;
CREATE TABLE IF NOT EXISTS `chuyenbay` (
  `MaChuyenBay` int NOT NULL AUTO_INCREMENT,
  `MaChuyenBayText` varchar(20) COLLATE utf8mb3_vietnamese_ci NOT NULL COMMENT 'Mã chuyến bay dạng VN123',
  `HangBay` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL COMMENT 'Vietnam Airlines, VietJet...',
  `SanBayDi` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `SanBayDen` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `ThoiGianBay` datetime NOT NULL,
  `ThoiGianDen` datetime NOT NULL,
  `GiaVe` decimal(10,2) NOT NULL,
  `SoGheConLai` int NOT NULL DEFAULT '0',
  `TongSoGhe` int NOT NULL DEFAULT '0',
  `TrangThai` enum('concho','daybay','huy') COLLATE utf8mb3_vietnamese_ci DEFAULT 'concho',
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaChuyenBay`),
  UNIQUE KEY `MaChuyenBayText` (`MaChuyenBayText`),
  KEY `idx_machuyenbay` (`MaChuyenBayText`),
  KEY `idx_thoigianbay` (`ThoiGianBay`),
  KEY `idx_sanbaydiden` (`SanBayDi`,`SanBayDen`)
) ;

--
-- Dumping data for table `chuyenbay`
--

INSERT INTO `chuyenbay` (`MaChuyenBay`, `MaChuyenBayText`, `HangBay`, `SanBayDi`, `SanBayDen`, `ThoiGianBay`, `ThoiGianDen`, `GiaVe`, `SoGheConLai`, `TongSoGhe`, `TrangThai`, `NgayTao`) VALUES
(1, 'VN123', 'Vietnam Airlines', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 08:00:00', '2025-11-10 10:00:00', 1500000.00, 149, 180, 'concho', '2025-11-04 21:06:27'),
(2, 'VJ456', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 14:00:00', '2025-11-10 15:30:00', 800000.00, 118, 150, 'concho', '2025-11-04 21:06:27'),
(3, 'BB789', 'Bamboo Airways', 'Hà Nội (HAN)', 'Phú Quốc (PQC)', '2025-11-11 06:00:00', '2025-11-11 08:30:00', 2000000.00, 99, 120, 'concho', '2025-11-04 21:06:27'),
(4, 'VN321', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-12 16:00:00', '2025-11-12 17:30:00', 1200000.00, 80, 100, 'concho', '2025-11-04 21:06:27'),
(5, 'VJ654', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-13 20:00:00', '2025-11-13 22:00:00', 1600000.00, 140, 180, 'concho', '2025-11-04 21:06:27'),
(6, 'VN101', 'Vietnam Airlines', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 06:00:00', '2025-11-10 08:15:00', 2500000.00, 143, 180, 'concho', '2025-11-05 14:39:05'),
(7, 'VN102', 'Vietnam Airlines', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 09:30:00', '2025-11-10 11:45:00', 2300000.00, 165, 180, 'concho', '2025-11-05 14:39:05'),
(8, 'VN103', 'Vietnam Airlines', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 13:00:00', '2025-11-10 15:15:00', 2400000.00, 140, 180, 'concho', '2025-11-05 14:39:05'),
(9, 'VN104', 'Vietnam Airlines', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 16:30:00', '2025-11-10 18:45:00', 2600000.00, 155, 180, 'concho', '2025-11-05 14:39:05'),
(10, 'VN105', 'Vietnam Airlines', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 19:00:00', '2025-11-10 21:15:00', 2700000.00, 120, 180, 'concho', '2025-11-05 14:39:05'),
(11, 'VJ201', 'VietJet Air', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 07:00:00', '2025-11-10 09:15:00', 1800000.00, 170, 180, 'concho', '2025-11-05 14:39:05'),
(12, 'VJ202', 'VietJet Air', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 11:00:00', '2025-11-10 13:15:00', 1700000.00, 175, 180, 'concho', '2025-11-05 14:39:05'),
(13, 'VJ203', 'VietJet Air', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 15:30:00', '2025-11-10 17:45:00', 1900000.00, 160, 180, 'concho', '2025-11-05 14:39:05'),
(14, 'BB301', 'Bamboo Airways', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 08:00:00', '2025-11-10 10:15:00', 2200000.00, 145, 180, 'concho', '2025-11-05 14:39:05'),
(15, 'BB302', 'Bamboo Airways', 'Hà Nội (HAN)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 17:00:00', '2025-11-10 19:15:00', 2300000.00, 135, 180, 'concho', '2025-11-05 14:39:05'),
(16, 'VN201', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 06:30:00', '2025-11-10 08:45:00', 2500000.00, 148, 180, 'concho', '2025-11-05 14:39:05'),
(17, 'VN202', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 10:00:00', '2025-11-10 12:15:00', 2300000.00, 162, 180, 'concho', '2025-11-05 14:39:05'),
(18, 'VN203', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 13:30:00', '2025-11-10 15:45:00', 2400000.00, 138, 180, 'concho', '2025-11-05 14:39:05'),
(19, 'VN204', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 17:00:00', '2025-11-10 19:15:00', 2600000.00, 153, 180, 'concho', '2025-11-05 14:39:05'),
(20, 'VN205', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 20:00:00', '2025-11-10 22:15:00', 2700000.00, 125, 180, 'concho', '2025-11-05 14:39:05'),
(21, 'VJ211', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 07:30:00', '2025-11-10 09:45:00', 1800000.00, 172, 180, 'concho', '2025-11-05 14:39:05'),
(22, 'VJ212', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 12:00:00', '2025-11-10 14:15:00', 1700000.00, 177, 180, 'concho', '2025-11-05 14:39:05'),
(23, 'VJ213', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 16:00:00', '2025-11-10 18:15:00', 1900000.00, 158, 180, 'concho', '2025-11-05 14:39:05'),
(24, 'BB311', 'Bamboo Airways', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 08:30:00', '2025-11-10 10:45:00', 2200000.00, 143, 180, 'concho', '2025-11-05 14:39:05'),
(25, 'BB312', 'Bamboo Airways', 'TP. Hồ Chí Minh (SGN)', 'Hà Nội (HAN)', '2025-11-10 18:00:00', '2025-11-10 20:15:00', 2300000.00, 133, 180, 'concho', '2025-11-05 14:39:05'),
(26, 'VN301', 'Vietnam Airlines', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 06:30:00', '2025-11-10 07:50:00', 1500000.00, 164, 180, 'concho', '2025-11-05 14:39:05'),
(27, 'VN302', 'Vietnam Airlines', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 10:00:00', '2025-11-10 11:20:00', 1400000.00, 170, 180, 'concho', '2025-11-05 14:39:05'),
(28, 'VN303', 'Vietnam Airlines', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 14:30:00', '2025-11-10 15:50:00', 1600000.00, 158, 180, 'concho', '2025-11-05 14:39:05'),
(29, 'VN304', 'Vietnam Airlines', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 18:00:00', '2025-11-10 19:20:00', 1650000.00, 150, 180, 'concho', '2025-11-05 14:39:05'),
(30, 'VJ401', 'VietJet Air', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 07:30:00', '2025-11-10 08:50:00', 1200000.00, 175, 180, 'concho', '2025-11-05 14:39:05'),
(31, 'VJ402', 'VietJet Air', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 12:00:00', '2025-11-10 13:20:00', 1100000.00, 177, 180, 'concho', '2025-11-05 14:39:05'),
(32, 'BB501', 'Bamboo Airways', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 09:00:00', '2025-11-10 10:20:00', 1450000.00, 168, 180, 'concho', '2025-11-05 14:39:05'),
(33, 'BB502', 'Bamboo Airways', 'Hà Nội (HAN)', 'Đà Nẵng (DAD)', '2025-11-10 16:30:00', '2025-11-10 17:50:00', 1550000.00, 162, 180, 'concho', '2025-11-05 14:39:05'),
(34, 'VN311', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 07:00:00', '2025-11-10 08:20:00', 1500000.00, 163, 180, 'concho', '2025-11-05 14:39:06'),
(35, 'VN312', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 11:00:00', '2025-11-10 12:20:00', 1400000.00, 168, 180, 'concho', '2025-11-05 14:39:06'),
(36, 'VN313', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 15:30:00', '2025-11-10 16:50:00', 1600000.00, 156, 180, 'concho', '2025-11-05 14:39:06'),
(37, 'VN314', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 19:00:00', '2025-11-10 20:20:00', 1650000.00, 148, 180, 'concho', '2025-11-05 14:39:06'),
(38, 'VJ411', 'VietJet Air', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 08:30:00', '2025-11-10 09:50:00', 1200000.00, 173, 180, 'concho', '2025-11-05 14:39:06'),
(39, 'VJ412', 'VietJet Air', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 13:00:00', '2025-11-10 14:20:00', 1100000.00, 175, 180, 'concho', '2025-11-05 14:39:06'),
(40, 'BB511', 'Bamboo Airways', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 10:00:00', '2025-11-10 11:20:00', 1450000.00, 166, 180, 'concho', '2025-11-05 14:39:06'),
(41, 'BB512', 'Bamboo Airways', 'Đà Nẵng (DAD)', 'Hà Nội (HAN)', '2025-11-10 17:30:00', '2025-11-10 18:50:00', 1550000.00, 160, 180, 'concho', '2025-11-05 14:39:06'),
(42, 'VN401', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 06:30:00', '2025-11-10 07:45:00', 1300000.00, 172, 180, 'concho', '2025-11-05 14:39:06'),
(43, 'VN402', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 11:00:00', '2025-11-10 12:15:00', 1250000.00, 165, 180, 'concho', '2025-11-05 14:39:06'),
(44, 'VN403', 'Vietnam Airlines', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 16:00:00', '2025-11-10 17:15:00', 1400000.00, 158, 180, 'concho', '2025-11-05 14:39:06'),
(45, 'VJ501', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 08:00:00', '2025-11-10 09:15:00', 1000000.00, 177, 180, 'concho', '2025-11-05 14:39:06'),
(46, 'VJ502', 'VietJet Air', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 13:30:00', '2025-11-10 14:45:00', 950000.00, 179, 180, 'concho', '2025-11-05 14:39:06'),
(47, 'BB601', 'Bamboo Airways', 'TP. Hồ Chí Minh (SGN)', 'Đà Nẵng (DAD)', '2025-11-10 10:00:00', '2025-11-10 11:15:00', 1200000.00, 170, 180, 'concho', '2025-11-05 14:39:06'),
(48, 'VN411', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 07:00:00', '2025-11-10 08:15:00', 1300000.00, 170, 180, 'concho', '2025-11-05 14:39:06'),
(49, 'VN412', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 12:00:00', '2025-11-10 13:15:00', 1250000.00, 163, 180, 'concho', '2025-11-05 14:39:06'),
(50, 'VN413', 'Vietnam Airlines', 'Đà Nẵng (DAD)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 17:00:00', '2025-11-10 18:15:00', 1400000.00, 156, 180, 'concho', '2025-11-05 14:39:06'),
(51, 'VJ511', 'VietJet Air', 'Đà Nẵng (DAD)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 09:00:00', '2025-11-10 10:15:00', 1000000.00, 175, 180, 'concho', '2025-11-05 14:39:06'),
(52, 'VJ512', 'VietJet Air', 'Đà Nẵng (DAD)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 14:30:00', '2025-11-10 15:45:00', 950000.00, 177, 180, 'concho', '2025-11-05 14:39:06'),
(53, 'BB611', 'Bamboo Airways', 'Đà Nẵng (DAD)', 'TP. Hồ Chí Minh (SGN)', '2025-11-10 11:00:00', '2025-11-10 12:15:00', 1200000.00, 168, 180, 'concho', '2025-11-05 14:39:06'),
(54, 'VN501', 'Vietnam Airlines', 'Hà Nội (HAN)', 'Phú Quốc (PQC)', '2025-11-10 07:00:00', '2025-11-10 09:30:00', 3200000.00, 145, 180, 'concho', '2025-11-05 14:39:06'),
(55, 'VN502', 'Vietnam Airlines', 'Hà Nội (HAN)', 'Phú Quốc (PQC)', '2025-11-10 14:30:00', '2025-11-10 17:00:00', 3400000.00, 138, 180, 'concho', '2025-11-05 14:39:06'),
(56, 'VJ601', 'VietJet Air', 'Hà Nội (HAN)', 'Phú Quốc (PQC)', '2025-11-10 09:00:00', '2025-11-10 11:30:00', 2800000.00, 155, 180, 'concho', '2025-11-05 14:39:06'),
(57, 'BB701', 'Bamboo Airways', 'Hà Nội (HAN)', 'Phú Quốc (PQC)', '2025-11-10 12:00:00', '2025-11-10 14:30:00', 3000000.00, 150, 180, 'concho', '2025-11-05 14:39:06');

-- --------------------------------------------------------

--
-- Table structure for table `datve`
--

DROP TABLE IF EXISTS `datve`;
CREATE TABLE IF NOT EXISTS `datve` (
  `MaDatVe` int NOT NULL AUTO_INCREMENT,
  `MaNguoiDung` int NOT NULL,
  `MaChuyenBay` int NOT NULL,
  `NgayDat` datetime DEFAULT CURRENT_TIMESTAMP,
  `TenHanhKhach` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `CMND` varchar(20) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `SoDienThoai` varchar(15) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL,
  `SoLuongVe` int NOT NULL DEFAULT '1',
  `TongTien` decimal(10,2) NOT NULL,
  `TrangThai` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_vietnamese_ci DEFAULT 'choxacnhan',
  `GhiChu` text COLLATE utf8mb3_vietnamese_ci,
  `NgayCapNhat` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaDatVe`),
  KEY `idx_manguoidung` (`MaNguoiDung`),
  KEY `idx_machuyenbay` (`MaChuyenBay`),
  KEY `idx_ngaydat` (`NgayDat`),
  KEY `idx_trangthai` (`TrangThai`)
) ;

--
-- Dumping data for table `datve`
--

INSERT INTO `datve` (`MaDatVe`, `MaNguoiDung`, `MaChuyenBay`, `NgayDat`, `TenHanhKhach`, `CMND`, `SoDienThoai`, `Email`, `SoLuongVe`, `TongTien`, `TrangThai`, `GhiChu`, `NgayCapNhat`) VALUES
(1, 2, 1, '2025-11-04 21:06:27', 'Nguyễn Văn A', '001234567890', '0987654321', 'nguyenvana@gmail.com', 1, 1500000.00, 'daxacnhan', NULL, '2025-11-04 21:06:27'),
(2, 3, 2, '2025-11-04 21:06:27', 'Trần Thị B', '001234567891', '0912345678', 'tranthib@gmail.com', 2, 1600000.00, 'daxacnhan', NULL, '2025-11-04 21:06:27'),
(3, 4, 3, '2025-11-04 21:06:27', 'Lê Văn C', '001234567892', '0909090909', 'levanc@gmail.com', 1, 2000000.00, 'choxacnhan', NULL, '2025-11-04 21:06:27'),
(14, 2, 6, '2025-11-05 16:55:20', 'Nguyễn Văn A', '001234567890', '0901234567', '', 1, 2500000.00, 'dathanhtoan', NULL, '2025-11-05 16:55:28'),
(15, 2, 6, '2025-11-05 17:25:16', 'Nguyễn Văn A', '001234567890', '0901234567', 'nguyenvana@gmail.com', 2, 5000000.00, 'dathanhtoan', NULL, '2025-11-05 17:26:25'),
(16, 2, 6, '2025-11-05 17:48:28', 'Nguyễn Văn A', '001234567890', '0901234567', 'nguyenvana@gmail.com', 1, 2500000.00, 'dahuy', NULL, '2025-11-05 17:54:44');

-- --------------------------------------------------------

--
-- Table structure for table `nguoidung`
--

DROP TABLE IF EXISTS `nguoidung`;
CREATE TABLE IF NOT EXISTS `nguoidung` (
  `MaNguoiDung` int NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `MatKhau` varchar(255) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `VaiTro` enum('admin','khachhang') COLLATE utf8mb3_vietnamese_ci NOT NULL DEFAULT 'khachhang',
  `Email` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `HoTen` varchar(100) COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `SoDienThoai` varchar(15) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL,
  `DiaChi` text COLLATE utf8mb3_vietnamese_ci,
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  `TrangThai` enum('hoatdong','khoa') COLLATE utf8mb3_vietnamese_ci DEFAULT 'hoatdong',
  PRIMARY KEY (`MaNguoiDung`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_tendangnhap` (`TenDangNhap`),
  KEY `idx_email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_vietnamese_ci;

--
-- Dumping data for table `nguoidung`
--

INSERT INTO `nguoidung` (`MaNguoiDung`, `TenDangNhap`, `MatKhau`, `VaiTro`, `Email`, `HoTen`, `SoDienThoai`, `DiaChi`, `NgayTao`, `TrangThai`) VALUES
(1, 'admin', '1', 'admin', 'admin@airline.com', 'Quản Trị Viên', '0123456789', 'Hà Nội', '2025-11-04 21:06:27', 'hoatdong'),
(2, 'khach01', '1', 'khachhang', 'nguyenvana@gmail.com', 'Nguyễn Văn A', '0987654321', 'TP.HCM', '2025-11-04 21:06:27', 'hoatdong'),
(3, 'khach02', '2', 'khachhang', 'tranthib@gmail.com', 'Trần Thị B', '0912345678', 'Đà Nẵng', '2025-11-04 21:06:27', 'hoatdong'),
(4, 'khach03', '3', 'khachhang', 'levanc@gmail.com', 'Lê Văn C', '0909090909', 'Hải Phòng', '2025-11-04 21:06:27', 'hoatdong');

-- --------------------------------------------------------

--
-- Table structure for table `thanhtoan`
--

DROP TABLE IF EXISTS `thanhtoan`;
CREATE TABLE IF NOT EXISTS `thanhtoan` (
  `MaThanhToan` int NOT NULL AUTO_INCREMENT,
  `MaDatVe` int NOT NULL,
  `NgayThanhToan` datetime DEFAULT CURRENT_TIMESTAMP,
  `SoTien` decimal(10,2) NOT NULL,
  `PhuongThuc` enum('tienmat','chuyenkhoan','thevisa','momo','zalopay') COLLATE utf8mb3_vietnamese_ci NOT NULL,
  `MaGiaoDich` varchar(100) COLLATE utf8mb3_vietnamese_ci DEFAULT NULL COMMENT 'Mã giao dịch từ ngân hàng/ví điện tử',
  `TrangThai` enum('dangxuly','thanhcong','thatbai') COLLATE utf8mb3_vietnamese_ci DEFAULT 'dangxuly',
  `GhiChu` text COLLATE utf8mb3_vietnamese_ci,
  `NgayCapNhat` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaThanhToan`),
  UNIQUE KEY `MaDatVe` (`MaDatVe`),
  KEY `idx_madatve` (`MaDatVe`),
  KEY `idx_trangthai` (`TrangThai`)
) ;

--
-- Dumping data for table `thanhtoan`
--

INSERT INTO `thanhtoan` (`MaThanhToan`, `MaDatVe`, `NgayThanhToan`, `SoTien`, `PhuongThuc`, `MaGiaoDich`, `TrangThai`, `GhiChu`, `NgayCapNhat`) VALUES
(1, 1, '2025-11-04 21:06:27', 1500000.00, 'chuyenkhoan', 'TXN20251104001', 'thanhcong', NULL, '2025-11-04 21:06:27'),
(2, 2, '2025-11-04 21:06:27', 1600000.00, 'momo', 'MOMO20251104002', 'thanhcong', NULL, '2025-11-04 21:06:27');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitietdatve`
--
ALTER TABLE `chitietdatve`
  ADD CONSTRAINT `fk_chitietdatve_datve` FOREIGN KEY (`MaDatVe`) REFERENCES `datve` (`MaDatVe`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `datve`
--
ALTER TABLE `datve`
  ADD CONSTRAINT `fk_datve_chuyenbay` FOREIGN KEY (`MaChuyenBay`) REFERENCES `chuyenbay` (`MaChuyenBay`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_datve_nguoidung` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoidung` (`MaNguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `fk_thanhtoan_datve` FOREIGN KEY (`MaDatVe`) REFERENCES `datve` (`MaDatVe`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
