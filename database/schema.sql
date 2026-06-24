CREATE DATABASE IF NOT EXISTS taniexpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taniexpress;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    satuan VARCHAR(30) NOT NULL,
    berat VARCHAR(30) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    gambar VARCHAR(500) DEFAULT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE petani (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    alamat TEXT DEFAULT NULL,
    cerita_petani TEXT DEFAULT NULL,
    profil_petani TEXT DEFAULT NULL,
    foto VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk_petani (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    petani_id INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (petani_id) REFERENCES petani(id) ON DELETE CASCADE,
    UNIQUE KEY unique_produk_petani (product_id, petani_id)
);

CREATE TABLE kurir (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    status ENUM('tersedia', 'sibuk') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kode_pesanan VARCHAR(20) NOT NULL UNIQUE,
    nama_penerima VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    ongkir DECIMAL(12,2) NOT NULL DEFAULT 0,
    biaya_platform DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    status ENUM(
        'menunggu_pembayaran',
        'menunggu_verifikasi',
        'diproses',
        'dikemas',
        'dikirim',
        'sampai',
        'selesai',
        'dibatalkan'
    ) NOT NULL DEFAULT 'menunggu_pembayaran',
    kurir_id INT DEFAULT NULL,
    bukti_bayar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (kurir_id) REFERENCES kurir(id) ON DELETE SET NULL
);

CREATE TABLE pesanan_detil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_petani_id INT NOT NULL,
    qty INT NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_petani_id) REFERENCES produk_petani(id)
);

CREATE TABLE app_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    badge VARCHAR(100) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(500) NOT NULL,
    gradient VARCHAR(80) NOT NULL DEFAULT 'from-primary/80',
    btn_utama_label VARCHAR(100) NOT NULL DEFAULT 'Mulai Belanja',
    btn_utama_url VARCHAR(255) NOT NULL DEFAULT 'home.php',
    btn_sekunder_label VARCHAR(100) DEFAULT 'Daftar Gratis',
    btn_sekunder_url VARCHAR(255) DEFAULT 'register.php',
    urutan INT NOT NULL DEFAULT 0,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE chat_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    guest_token VARCHAR(64) DEFAULT NULL,
    visitor_name VARCHAR(100) NOT NULL,
    visitor_type ENUM('customer', 'petani') NOT NULL DEFAULT 'customer',
    visitor_telepon VARCHAR(20) DEFAULT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unread_admin INT NOT NULL DEFAULT 0,
    unread_user INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_thread (user_id),
    UNIQUE KEY unique_guest_token (guest_token),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    sender_role ENUM('user', 'admin') NOT NULL,
    sender_user_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Akun default (password: admin123 / customer123)
INSERT INTO users (nama, email, password, role, telepon, alamat) VALUES
('Admin TaniExpress', 'admin@taniexpress.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890', 'Jl. Pertanian No. 88, Lembang'),
('Budi Santoso', 'customer@taniexpress.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081298765432', 'Jl. Melati No. 12, Bandung'),
('Siti Rahayu', 'siti@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '081311122233', 'Jl. Mawar No. 5, Jakarta');

INSERT INTO petani (nama, telepon, alamat, foto) VALUES
('Pak Tono', '08121110001', 'Lembang, Bandung', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB5bFSTLK8s8FHgjY4qRAChOcCLgEl6EYd0Btl6NDvGx3IcjDk2Kzwcq189laUscw9MAey8GdB5tTTRViWzzLt7Zulra1rOoWVLJMjMPqkEAJkHZgwDBQa9At4AZ0tby9TCMLMeyVtvtntn9Oxb7BwvjMFPnILVxlL8Sbrb7y6fxZ9t2pbZO2mAncAiduRUwBCJ1pDXoFmKtsQ7iSh_7xrKHJgBi0SEsSi6AHGkugrylVrti6p0KCMXDN1bhecVYN3Hc6BrP9V58O1w'),
('Pak Slamet', '08121110002', 'Cianjur, Jawa Barat', NULL),
('Ibu Ani', '08121110003', 'Sukabumi, Jawa Barat', NULL),
('Pak Yusuf', '08121110004', 'Bogor, Jawa Barat', NULL),
('Ibu Sari', '08121110005', 'Lembang, Bandung', NULL);

INSERT INTO products (sku, nama, kategori, satuan, berat, deskripsi, gambar, stok) VALUES
('VEG-001', 'Bayam Hijau Segar', 'Sayuran Hijau', 'ikat', '200g', 'Bayam organik tanpa pestisida, dipetik pagi hari. Kaya zat besi dan nutrisi penting.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBM7_SB2qxuQSfJSd5ZmafP-cpUksEdKnj6AlGqIGpnlRa68QIt-qicZGFaufMK9wv32H_DsaL3kaU7qSwr92FfxeUPinQ_T5zCMHI7EegDKcdiGuSviNlb3Wkgz0zJ8V7jghjZ7yvozkhAc82s8KSf43AcEDBRi0m1ooCkj2O21YmwLEJmzTNbFm3nxNs16PHgHYoYeW3ro3kYBUzKcv3FZlUMb2_EECkbrlLq54lwMM83OJOrI2AfrKPX4j-Jd_XqXEteMN524lhp', 50),
('VEG-002', 'Tomat Cherry Organik', 'Buah', 'pack', '250g', 'Tomat cherry segar dari kebun organik, manis dan renyah.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZJQRnJnitFBQ_EBAG5_XlVEu6oY6jmcfVCreiYeXIMmR1GsRr98NZ6yj58nRBLNHpJmZQ7E1HSkTzPbZZcRHjEPpF3xlos7OFbchN96tboTF_acuKqGYzn1oxrUeDQ81X6TT3x615KUfpklAkCCQq3xXlRY74XOOjPdlF0uueMTcNHsbcwEY9xqjRgae0F0xIPBIuDukeAU1FnUFhJMPHo3ZUcK8fILsHG7iFL1ui8hBIDFQTiQ6QxAgKUgeeQ7K_Krrlt7MIYFty', 30),
('VEG-003', 'Wortel Baby Lokal', 'Umbi & Akar', 'pack', '500g', 'Wortel baby dari pegunungan, manis dan segar.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBO71NTwicSRgb86K7syQ3n7_BcYTkKGrekG8pRyxc16L3_cFPKeyIvZChRIzu6olXq6UPYngemEU9hS5ed2wLhGYKgRz5eXGNnyLQNFNDfJwy4XGHHm9FsYRb9e52LHr5C7psC0YQP2KLhPboClz1b6RwmuqNflYelL9o4oJPsV-L9aaZs3w_2liIIthhlm6IjVLz9hvDrAK8xPRDI1Y5t-0X6N6XBi6YDLU9Wm0AGTMu5rSmnafv3OvrRikdfd7oyLeoHHAwPGRsx', 40),
('VEG-004', 'Bayam Hidroponik', 'Sayuran Hijau', 'pack', '300g', 'Bayam hidroponik tanpa pestisida, tekstur renyah.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuABOodECwZm20k0HMVgzPKTZoid3PiLXySHKjSJsQ05BW0Tk4EKoQrrrLewydrcvbtaM9qHqsK_hBrUXL1vGFErF7QcwJf04r4FrMGR46kY-w0LQVBVyMxV5g5ioA7lNPI-kSgQTNGTmLLcF983A8052yanTppLTxsY6gCnW7G6vSTI7p-FcFzm738hT74Mbb2G9xCCk8SZCfchFyrYBC86ZON9D-BVy6hqcD4hwlYPn-qQYwb6YW4hGzl6sPKG9Q9sD8DYJx7pKSmU', 25),
('VEG-005', 'Brokoli Segar', 'Sayuran Hijau', 'pack', '300g', 'Brokoli segar kualitas premium, kaya vitamin.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAI-COcCfKPJSsnO-86bfaEHXgZ2jol3XPQVf_auOxU2k3TdL0Y3kZlxEAlyUZKssfC-GmlQl-2AfN1fDJBFPrDjmxneZJLOqG2HzGqZDpDwpoEX1JsuDny3MNzP4yy7YnXsG1F79KPKYpITe9aJFbSawYrAYjcd6wO5tHyuiQS_uIfk-Le2bRiuiboM2oPBUIziXveLJmHvqvnihR9kAqZPUq9dIBf9Wu1GBxH-nYouBw3_W2gRYC1vjSW6HRbBgQ7jC1aPTxqUYmr', 20),
('VEG-006', 'Bok Choy Hydro', 'Sayuran Hijau', 'pack', '400g', 'Bok choy hidroponik segar, stalk putih dan daun hijau.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB5QuD4oMP8TnUBsFKPR1Oic1RKdgttwvKEg6bU5R2qS7-Vx2S1Ff902L8EdcL8lQ3SMUyUdi2fN_PUKeCRHLxRvIY017q3NZYcxMakRrSTrAAhdGjrMwmWG-JtbDSpFTjNptRR-304D_mekqcDRUL490WzR5_TiEDLDCnAJFZ9omPqsSaE5Hqo6PeEF-EvNap5B7C868jdNC4dvGhGTStPmFPMpoZADUUr55DA0-dzl1rs-ZIkXmNzRircVW8gbGnkWthkDYuBozE1', 35),
('VEG-007', 'Ubi Merah', 'Umbi & Akar', 'kg', '1kg', 'Ubi merah manis dari pegunungan.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAiJM3ka0oaeUm5aJD_nQSOxiSyTKzdAUlhHGzFZ6lnfWOx8lNubMqC1__NjyH72JSZim0SDp7sL6N2MpWncEB8w4I-L_3H90SDsIQVb_Vm7A1CO1dmXlMizEkZZzJmbPoL0ndR3NPGf0_xYWZ3lnfgL8Hms3JSTRJK2oRjubaY__A71DFXWksP3z2TzpriCMM9pm3gdw3Ab_luAkwSLQCZ39-HpsbxvgpwAPMWZ_iJj6fuVB7ep9bDSdnq-jH5a1VXKeEyGG9IpVPd', 60),
('VEG-008', 'Jamur Campur', 'Jamur', 'pack', '250g', 'Jamur shiitake dan tiram segar.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBQnSAnpqoUIYkbvthmoULDw4pwWWmFFGoH95CtYMYz-fRsw9qcbjpJbH3Bvi5u17hxcE5smT2Aed6JwGag8rOx1yFE1OHtsomsz7XY3BYr3BBxonGVWIlFeO2IUFCFMBIY4yF-NK9-IVs0KL27L3Bt3Fp6E0DV2AAbaQj_GRX8mS9qgnVrMOyOCFuXTE7sdjsVU9N9sPo5Tl2k9BVFbw6MOMDqBw-5US9pUL4w4joygLdkY-Y4Df-iNDQ1qpsXV6iMTw7SnnTPLR5h', 15);

INSERT INTO produk_petani (product_id, petani_id, stok, harga) VALUES
(1, 1, 50, 5000),
(2, 2, 30, 12500),
(3, 3, 40, 8000),
(4, 4, 25, 15000),
(5, 5, 20, 18000),
(6, 1, 35, 9800),
(7, 2, 60, 22000),
(8, 3, 15, 25500);

INSERT INTO kurir (nama, telepon, status) VALUES
('Andi Wijaya', '08130001111', 'tersedia'),
('Bambang Sutrisno', '08130002222', 'tersedia'),
('Candra Pratama', '08130003333', 'sibuk'),
('Dedi Kurniawan', '08130004444', 'tersedia');
