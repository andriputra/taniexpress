-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 04:13 AM
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
-- Database: `taniexpress`
--

-- --------------------------------------------------------

--
-- Table structure for table `kurir`
--

CREATE TABLE `kurir` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `status` enum('tersedia','sibuk') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kurir`
--

INSERT INTO `kurir` (`id`, `nama`, `telepon`, `status`, `created_at`) VALUES
(1, 'Andi Wijaya', '08130001111', 'sibuk', '2026-06-15 05:55:08'),
(2, 'Bambang Sutrisno', '08130002222', 'sibuk', '2026-06-15 05:55:08'),
(3, 'Candra Pratama', '08130003333', 'tersedia', '2026-06-15 05:55:08'),
(4, 'Dedi Kurniawan', '08130004444', 'tersedia', '2026-06-15 05:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kode_pesanan` varchar(20) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `ongkir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `biaya_platform` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `status` enum('menunggu_pembayaran','menunggu_verifikasi','diproses','dikemas','dikirim','sampai','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `kurir_id` int(11) DEFAULT NULL,
  `bukti_bayar` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `user_id`, `kode_pesanan`, `nama_penerima`, `telepon`, `alamat`, `subtotal`, `ongkir`, `biaya_platform`, `total`, `status`, `kurir_id`, `bukti_bayar`, `created_at`, `updated_at`) VALUES
(1, 2, 'TE2606150E13', 'Budi Santoso', '081298765432', 'Jl. Melati No. 12, Bandung', 22000.00, 15000.00, 5000.00, 42000.00, 'diproses', NULL, 'uploads/bukti_1781515134_6b251d1f.png', '2026-06-15 08:58:04', '2026-06-15 09:19:31'),
(2, 4, 'TE2606152A10', 'Zafran', '08595585525', 'Jalan kenangan', 35500.00, 15000.00, 5000.00, 55500.00, 'menunggu_verifikasi', NULL, 'uploads/bukti_1781515251_7f79b58b.jpg', '2026-06-15 09:20:39', '2026-06-15 09:20:51'),
(3, 4, 'TE260615232A', 'Zafran', '08595585525', 'Jalan kenangan', 8000.00, 15000.00, 5000.00, 28000.00, 'menunggu_pembayaran', NULL, NULL, '2026-06-15 09:35:04', '2026-06-15 09:35:04'),
(4, 5, 'TE2606157CB7', 'Nakeisha', '08132654987', 'Jl. Mata Air', 25500.00, 15000.00, 5000.00, 45500.00, 'dikirim', 1, 'uploads/bukti_1781517174_43443828.png', '2026-06-15 09:52:30', '2026-06-15 09:56:06'),
(5, 5, 'TE260615BD35', 'Nakeisha', '08132654987', 'Jl. Mata Air kemiling', 15000.00, 15000.00, 5000.00, 35000.00, 'dikirim', 2, 'uploads/bukti_1781527345_922ec66b.png', '2026-06-15 12:42:10', '2026-06-15 12:45:53'),
(6, 5, 'TE260616C37C', 'Nakeisha', '08132654987', 'Jl. Mata Air', 20000.00, 10000.00, 5000.00, 35000.00, 'dikirim', 1, 'uploads/bukti_1781572767_b7420ac5.jpeg', '2026-06-16 01:18:59', '2026-06-16 01:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_detil`
--

CREATE TABLE `pesanan_detil` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `produk_petani_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan_detil`
--

INSERT INTO `pesanan_detil` (`id`, `pesanan_id`, `produk_petani_id`, `qty`, `harga`) VALUES
(1, 1, 7, 1, 22000.00),
(2, 2, 2, 1, 12500.00),
(3, 2, 3, 1, 8000.00),
(4, 2, 4, 1, 15000.00),
(5, 3, 3, 1, 8000.00),
(6, 4, 8, 1, 25500.00),
(7, 5, 4, 1, 15000.00),
(8, 6, 1, 1, 5000.00),
(9, 6, 4, 1, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `petani`
--

CREATE TABLE `petani` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petani`
--

INSERT INTO `petani` (`id`, `nama`, `telepon`, `alamat`, `foto`, `created_at`) VALUES
(1, 'Pak Tono', '08121110001', 'Pinang Jaya-Kemiling, Bandar Lampung', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB5bFSTLK8s8FHgjY4qRAChOcCLgEl6EYd0Btl6NDvGx3IcjDk2Kzwcq189laUscw9MAey8GdB5tTTRViWzzLt7Zulra1rOoWVLJMjMPqkEAJkHZgwDBQa9At4AZ0tby9TCMLMeyVtvtntn9Oxb7BwvjMFPnILVxlL8Sbrb7y6fxZ9t2pbZO2mAncAiduRUwBCJ1pDXoFmKtsQ7iSh_7xrKHJgBi0SEsSi6AHGkugrylVrti6p0KCMXDN1bhecVYN3Hc6BrP9V58O1w', '2026-06-15 05:55:08'),
(2, 'Pak Slamet', '08121110002', 'Pinang Jaya-Kemiling, Bandar Lampung', 'uploads/petani_1781527695_7504eed2.png', '2026-06-15 05:55:08'),
(3, 'Ibu Ani', '08121110003', 'Pinang Jaya-Kemiling, Bandar Lampung', 'uploads/petani_1781527777_d0abf896.png', '2026-06-15 05:55:08'),
(4, 'Pak Agus', '08121110004', 'Pinang Jaya-Kemiling, Bandar Lampung', 'uploads/petani_1781527899_b6edbfbe.png', '2026-06-15 05:55:08'),
(5, 'Ibu Sari', '08121110005', 'Pinang Jaya-Kemiling, Bandar Lampung', 'uploads/petani_1781527818_83105dbd.png', '2026-06-15 05:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `berat` varchar(30) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(500) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `nama`, `kategori`, `satuan`, `berat`, `deskripsi`, `gambar`, `stok`, `created_at`) VALUES
(1, 'VEG-001', 'Bayam Hijau Segar', 'Sayuran Hijau', 'ikat', '200g', 'Bayam organik tanpa pestisida, dipetik pagi hari. Kaya zat besi dan nutrisi penting.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBM7_SB2qxuQSfJSd5ZmafP-cpUksEdKnj6AlGqIGpnlRa68QIt-qicZGFaufMK9wv32H_DsaL3kaU7qSwr92FfxeUPinQ_T5zCMHI7EegDKcdiGuSviNlb3Wkgz0zJ8V7jghjZ7yvozkhAc82s8KSf43AcEDBRi0m1ooCkj2O21YmwLEJmzTNbFm3nxNs16PHgHYoYeW3ro3kYBUzKcv3FZlUMb2_EECkbrlLq54lwMM83OJOrI2AfrKPX4j-Jd_XqXEteMN524lhp', 49, '2026-06-15 05:55:08'),
(2, 'VEG-002', 'Tomat Cherry Organik', 'Buah', 'pack', '250g', 'Tomat cherry segar dari kebun organik, manis dan renyah.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZJQRnJnitFBQ_EBAG5_XlVEu6oY6jmcfVCreiYeXIMmR1GsRr98NZ6yj58nRBLNHpJmZQ7E1HSkTzPbZZcRHjEPpF3xlos7OFbchN96tboTF_acuKqGYzn1oxrUeDQ81X6TT3x615KUfpklAkCCQq3xXlRY74XOOjPdlF0uueMTcNHsbcwEY9xqjRgae0F0xIPBIuDukeAU1FnUFhJMPHo3ZUcK8fILsHG7iFL1ui8hBIDFQTiQ6QxAgKUgeeQ7K_Krrlt7MIYFty', 29, '2026-06-15 05:55:08'),
(3, 'VEG-003', 'Wortel Baby Lokal', 'Umbi & Akar', 'pack', '500g', 'Wortel baby dari pegunungan, manis dan segar.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBO71NTwicSRgb86K7syQ3n7_BcYTkKGrekG8pRyxc16L3_cFPKeyIvZChRIzu6olXq6UPYngemEU9hS5ed2wLhGYKgRz5eXGNnyLQNFNDfJwy4XGHHm9FsYRb9e52LHr5C7psC0YQP2KLhPboClz1b6RwmuqNflYelL9o4oJPsV-L9aaZs3w_2liIIthhlm6IjVLz9hvDrAK8xPRDI1Y5t-0X6N6XBi6YDLU9Wm0AGTMu5rSmnafv3OvrRikdfd7oyLeoHHAwPGRsx', 38, '2026-06-15 05:55:08'),
(4, 'VEG-004', 'Bayam Hidroponik', 'Sayuran Hijau', 'pack', '300g', 'Bayam hidroponik tanpa pestisida, tekstur renyah.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuABOodECwZm20k0HMVgzPKTZoid3PiLXySHKjSJsQ05BW0Tk4EKoQrrrLewydrcvbtaM9qHqsK_hBrUXL1vGFErF7QcwJf04r4FrMGR46kY-w0LQVBVyMxV5g5ioA7lNPI-kSgQTNGTmLLcF983A8052yanTppLTxsY6gCnW7G6vSTI7p-FcFzm738hT74Mbb2G9xCCk8SZCfchFyrYBC86ZON9D-BVy6hqcD4hwlYPn-qQYwb6YW4hGzl6sPKG9Q9sD8DYJx7pKSmU', 22, '2026-06-15 05:55:08'),
(5, 'VEG-005', 'Brokoli Segar', 'Sayuran Hijau', 'pack', '300g', 'Brokoli segar kualitas premium, kaya vitamin.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAI-COcCfKPJSsnO-86bfaEHXgZ2jol3XPQVf_auOxU2k3TdL0Y3kZlxEAlyUZKssfC-GmlQl-2AfN1fDJBFPrDjmxneZJLOqG2HzGqZDpDwpoEX1JsuDny3MNzP4yy7YnXsG1F79KPKYpITe9aJFbSawYrAYjcd6wO5tHyuiQS_uIfk-Le2bRiuiboM2oPBUIziXveLJmHvqvnihR9kAqZPUq9dIBf9Wu1GBxH-nYouBw3_W2gRYC1vjSW6HRbBgQ7jC1aPTxqUYmr', 20, '2026-06-15 05:55:08'),
(6, 'VEG-006', 'Bok Choy Hydro', 'Sayuran Hijau', 'pack', '400g', 'Bok choy hidroponik segar, stalk putih dan daun hijau.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB5QuD4oMP8TnUBsFKPR1Oic1RKdgttwvKEg6bU5R2qS7-Vx2S1Ff902L8EdcL8lQ3SMUyUdi2fN_PUKeCRHLxRvIY017q3NZYcxMakRrSTrAAhdGjrMwmWG-JtbDSpFTjNptRR-304D_mekqcDRUL490WzR5_TiEDLDCnAJFZ9omPqsSaE5Hqo6PeEF-EvNap5B7C868jdNC4dvGhGTStPmFPMpoZADUUr55DA0-dzl1rs-ZIkXmNzRircVW8gbGnkWthkDYuBozE1', 35, '2026-06-15 05:55:08'),
(7, 'VEG-007', 'Ubi Merah', 'Umbi & Akar', 'kg', '1kg', 'Ubi merah manis dari pegunungan.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAiJM3ka0oaeUm5aJD_nQSOxiSyTKzdAUlhHGzFZ6lnfWOx8lNubMqC1__NjyH72JSZim0SDp7sL6N2MpWncEB8w4I-L_3H90SDsIQVb_Vm7A1CO1dmXlMizEkZZzJmbPoL0ndR3NPGf0_xYWZ3lnfgL8Hms3JSTRJK2oRjubaY__A71DFXWksP3z2TzpriCMM9pm3gdw3Ab_luAkwSLQCZ39-HpsbxvgpwAPMWZ_iJj6fuVB7ep9bDSdnq-jH5a1VXKeEyGG9IpVPd', 59, '2026-06-15 05:55:08'),
(8, 'VEG-008', 'Jamur Campur', 'Jamur', 'pack', '250g', 'Jamur shiitake dan tiram segar.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBQnSAnpqoUIYkbvthmoULDw4pwWWmFFGoH95CtYMYz-fRsw9qcbjpJbH3Bvi5u17hxcE5smT2Aed6JwGag8rOx1yFE1OHtsomsz7XY3BYr3BBxonGVWIlFeO2IUFCFMBIY4yF-NK9-IVs0KL27L3Bt3Fp6E0DV2AAbaQj_GRX8mS9qgnVrMOyOCFuXTE7sdjsVU9N9sPo5Tl2k9BVFbw6MOMDqBw-5US9pUL4w4joygLdkY-Y4Df-iNDQ1qpsXV6iMTw7SnnTPLR5h', 14, '2026-06-15 05:55:08'),
(9, 'RMP-001', 'Jahe Merah Dieng', 'Rempah', 'Kg', '2000g', 'Ini jahe berkualitas', 'uploads/produk_1781515386_d509c58d.png', 10, '2026-06-15 09:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `produk_petani`
--

CREATE TABLE `produk_petani` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `petani_id` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk_petani`
--

INSERT INTO `produk_petani` (`id`, `product_id`, `petani_id`, `stok`, `harga`, `created_at`) VALUES
(1, 1, 1, 49, 5000.00, '2026-06-15 05:55:08'),
(2, 2, 2, 29, 12500.00, '2026-06-15 05:55:08'),
(3, 3, 3, 38, 8000.00, '2026-06-15 05:55:08'),
(4, 4, 4, 22, 15000.00, '2026-06-15 05:55:08'),
(5, 5, 5, 20, 18000.00, '2026-06-15 05:55:08'),
(6, 6, 1, 35, 9800.00, '2026-06-15 05:55:08'),
(7, 7, 2, 59, 22000.00, '2026-06-15 05:55:08'),
(8, 8, 3, 14, 25500.00, '2026-06-15 05:55:08'),
(9, 9, 4, 10, 10000.00, '2026-06-15 09:23:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `telepon`, `alamat`, `created_at`) VALUES
(1, 'Admin TaniExpress', 'admin@taniexpress.com', '$2y$10$zq5QPOg8mm2yjL67bTBH5ubJ041n.jBrFIyDheAA73rnx5gtEc1JG', 'admin', '081234567890', 'Jl. Pertanian No. 88, Lembang', '2026-06-15 05:55:08'),
(2, 'Budi Santoso', 'customer@taniexpress.com', '$2y$10$zq5QPOg8mm2yjL67bTBH5ubJ041n.jBrFIyDheAA73rnx5gtEc1JG', 'customer', '081298765432', 'Jl. Melati No. 12, Bandung', '2026-06-15 05:55:08'),
(3, 'Siti Rahayu', 'siti@email.com', '$2y$10$zq5QPOg8mm2yjL67bTBH5ubJ041n.jBrFIyDheAA73rnx5gtEc1JG', 'customer', '081311122233', 'Jl. Mawar No. 5, Jakarta', '2026-06-15 05:55:08'),
(4, 'Zafran', 'zaf@email.com', '$2y$10$FdEwdpZY740ri3Fk/K/J8uLCYtTPtR8SASwZ0TsIuYnIe0aEVpSXS', 'customer', '08595585525', 'Jalan kenangan', '2026-06-15 09:20:12'),
(5, 'Nakeisha', 'Nakeisha@mail.com', '$2y$10$Tj8pGgBH539yo.kQ5SyfTuAZtVAKTfFNfNYzRRBTv1C6dZjl.KeI.', 'customer', '08132654987', 'Jl. Mata Air', '2026-06-15 09:52:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kurir`
--
ALTER TABLE `kurir`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kurir_id` (`kurir_id`);

--
-- Indexes for table `pesanan_detil`
--
ALTER TABLE `pesanan_detil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_petani_id` (`produk_petani_id`);

--
-- Indexes for table `petani`
--
ALTER TABLE `petani`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `produk_petani`
--
ALTER TABLE `produk_petani`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_produk_petani` (`product_id`,`petani_id`),
  ADD KEY `petani_id` (`petani_id`);

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
-- AUTO_INCREMENT for table `kurir`
--
ALTER TABLE `kurir`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pesanan_detil`
--
ALTER TABLE `pesanan_detil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `petani`
--
ALTER TABLE `petani`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `produk_petani`
--
ALTER TABLE `produk_petani`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`kurir_id`) REFERENCES `kurir` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesanan_detil`
--
ALTER TABLE `pesanan_detil`
  ADD CONSTRAINT `pesanan_detil_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_detil_ibfk_2` FOREIGN KEY (`produk_petani_id`) REFERENCES `produk_petani` (`id`);

--
-- Constraints for table `produk_petani`
--
ALTER TABLE `produk_petani`
  ADD CONSTRAINT `produk_petani_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produk_petani_ibfk_2` FOREIGN KEY (`petani_id`) REFERENCES `petani` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
