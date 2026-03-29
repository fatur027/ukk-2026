-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 12:40 PM
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
-- Database: `db_aspirasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `input_aspirasi`
--

CREATE TABLE `input_aspirasi` (
  `id_pelaporan` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `id_kategori` int(11) NOT NULL,
  `lokasi` varchar(200) NOT NULL,
  `ket` longtext NOT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'menunggu',
  `penyelesaian` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `input_aspirasi`
--

INSERT INTO `input_aspirasi` (`id_pelaporan`, `nis`, `jurusan`, `id_kategori`, `lokasi`, `ket`, `tanggal_input`, `status`, `penyelesaian`) VALUES
(11, '2041002', NULL, 2, 'Perpustakaan', 'Koleksi buku teknik perlu ditambah', '2026-03-05 05:50:04', 'menunggu', NULL),
(12, '2041002', NULL, 4, 'Kantin Sekolah', 'Menu makanan sehat kurang', '2026-03-05 05:50:04', 'menunggu', NULL),
(13, '2041003', NULL, 1, 'Mushola Sekolah', 'Mushola perlu pembersihan menyeluruh', '2026-03-05 05:50:04', 'menunggu', NULL),
(14, '2041003', NULL, 3, 'Halaman Sekolah', 'Air menggenang di halaman', '2026-03-05 05:50:04', 'menunggu', NULL),
(15, '2041004', NULL, 2, 'Lab Komputer', 'Beberapa komputer sudah rusak', '2026-03-05 05:50:04', 'menunggu', NULL),
(16, '2041004', NULL, 4, 'Kantin', 'Harga makanan terlalu mahal', '2026-03-05 05:50:04', 'menunggu', NULL),
(17, '2041005', NULL, 1, 'Toilet Putra', 'Toilet perlu renovasi total', '2026-03-05 05:50:04', 'menunggu', NULL),
(20, '2041002', NULL, 4, 'kantin', 'meja rusak makanan mahal', '2026-03-13 10:19:00', 'menunggu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id_admin`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'fatur', '$2y$10$3re4XD0iw2HwQieJwl6MS.pug5wjkIxeageQlo0QtjCQoZ2TgHxzu', NULL, '2026-03-07 02:56:39');

-- --------------------------------------------------------

--
-- Table structure for table `tb_aspirasi`
--

CREATE TABLE `tb_aspirasi` (
  `id_aspirasi` int(11) NOT NULL,
  `id_pelaporan` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'menunggu',
  `feedback` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_aspirasi`
--

INSERT INTO `tb_aspirasi` (`id_aspirasi`, `id_pelaporan`, `status`, `feedback`, `created_at`) VALUES
(5, 11, 'Menunggu', '', '2026-03-05 05:50:04'),
(6, 12, 'Proses', 'Sedang diproses', '2026-03-05 05:50:04'),
(7, 13, 'Selesai', 'Selesai', '2026-03-05 05:50:04'),
(8, 14, 'Menunggu', '', '2026-03-05 05:50:04'),
(9, 15, 'Proses', 'Sedang diproses', '2026-03-05 05:50:04'),
(10, 16, 'Selesai', 'Selesai', '2026-03-05 05:50:04'),
(11, 17, 'Proses', '', '2026-03-05 05:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `tb_history_aspirasi`
--

CREATE TABLE `tb_history_aspirasi` (
  `id_history` int(11) NOT NULL,
  `id_pelaporan` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `old_feedback` longtext DEFAULT NULL,
  `new_feedback` longtext DEFAULT NULL,
  `changed_by` varchar(50) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_history_aspirasi`
--

INSERT INTO `tb_history_aspirasi` (`id_history`, `id_pelaporan`, `old_status`, `new_status`, `old_feedback`, `new_feedback`, `changed_by`, `changed_at`) VALUES
(1, 17, 'Menunggu', 'Proses', NULL, '', 'fatur', '2026-03-28 11:19:51');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `id_kategori` int(11) NOT NULL,
  `ket_kategori` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`id_kategori`, `ket_kategori`, `created_at`) VALUES
(1, 'Fasilitas & Infrastruktur', '2026-03-05 05:50:04'),
(2, 'Kurikulum & Pembelajaran', '2026-03-05 05:50:04'),
(3, 'Keselamatan & Kesehatan', '2026-03-05 05:50:04'),
(4, 'Kantin & Makanan', '2026-03-05 05:50:04'),
(5, 'Lainnya', '2026-03-05 05:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `tb_siswa`
--

CREATE TABLE `tb_siswa` (
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `jurusan` varchar(50) DEFAULT 'Umum',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_siswa`
--

INSERT INTO `tb_siswa` (`nis`, `kelas`, `jurusan`, `created_at`) VALUES
('2041001', 'X TJA 1', 'Umum', '2026-03-05 05:50:04'),
('2041002', 'X TKJ 1', 'Umum', '2026-03-05 05:50:04'),
('2041003', 'X RPL 1', 'Umum', '2026-03-05 05:50:04'),
('2041004', 'X PF 1', 'Umum', '2026-03-05 05:50:04'),
('2041005', 'XI TJA 1', 'Umum', '2026-03-05 05:50:04'),
('2041006', 'XI TKJ 1', 'Umum', '2026-03-05 05:50:04'),
('2041007', 'XI RPL 1', 'Umum', '2026-03-05 05:50:04'),
('2041008', 'XI PF 1', 'Umum', '2026-03-05 05:50:04'),
('2041009', 'XII TKJ 1', 'Umum', '2026-03-05 05:50:04'),
('2041010', 'XII RPL 1', 'Umum', '2026-03-05 05:50:04'),
('2401234567', 'XII RPL 2', 'Umum', '2026-03-13 10:33:09'),
('2401234568', 'XI TKJ 2', 'Umum', '2026-03-13 10:35:26'),
('2401234569', 'X RPL 2', 'Umum', '2026-03-13 10:35:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD PRIMARY KEY (`id_pelaporan`),
  ADD KEY `fk_nis` (`nis`),
  ADD KEY `fk_kategori` (`id_kategori`);

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tb_aspirasi`
--
ALTER TABLE `tb_aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD UNIQUE KEY `id_pelaporan` (`id_pelaporan`),
  ADD KEY `fk_pelaporan` (`id_pelaporan`);

--
-- Indexes for table `tb_history_aspirasi`
--
ALTER TABLE `tb_history_aspirasi`
  ADD PRIMARY KEY (`id_history`),
  ADD KEY `fk_hist_pelaporan` (`id_pelaporan`);

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  ADD PRIMARY KEY (`nis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  MODIFY `id_pelaporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_aspirasi`
--
ALTER TABLE `tb_aspirasi`
  MODIFY `id_aspirasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tb_history_aspirasi`
--
ALTER TABLE `tb_history_aspirasi`
  MODIFY `id_history` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD CONSTRAINT `fk_asp_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori` (`id_kategori`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_input_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_input_nis` FOREIGN KEY (`nis`) REFERENCES `tb_siswa` (`nis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_aspirasi`
--
ALTER TABLE `tb_aspirasi`
  ADD CONSTRAINT `fk_tb_asp_pelaporan` FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tb_aspirasi_pelaporan` FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_history_aspirasi`
--
ALTER TABLE `tb_history_aspirasi`
  ADD CONSTRAINT `fk_hist_pelaporan` FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
