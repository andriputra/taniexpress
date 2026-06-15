# TaniExpress - Web Application

Aplikasi e-commerce sayuran segar dari petani lokal. Dibangun dengan **PHP**, **MySQL**, dan **Tailwind CSS**.

## Fitur

### Pembeli (Customer)
- Landing page & beranda produk
- Pencarian, filter kategori & petani
- Detail produk & keranjang belanja
- Checkout & pembayaran QRIS (upload bukti)
- Lacak status pesanan
- Registrasi & login

### Admin
- Dashboard (stok, pesanan baru, kurir)
- CRUD Produk, Petani, Produk-Petani, Kurir
- Kelola pesanan & verifikasi pembayaran
- Assign kurir & update status pengiriman
- Laporan pesanan per periode (export PDF via print)

## Instalasi

### 1. Persyaratan
- PHP 8.0+
- MySQL / MariaDB
- Extension PHP: `pdo_mysql`

### 2. Konfigurasi Database
Edit file `config/config.php` sesuai kredensial MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'taniexpress');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Install Database
```bash
cd stitch_prd_application_builder
php install.php
```

### 4. Jalankan Server
```bash
php -S localhost:8000
```

Buka browser: **http://localhost:8000**

## Akun Default

| Role     | Email                      | Password  |
|----------|----------------------------|-----------|
| Admin    | admin@taniexpress.com      | admin123  |
| Pembeli  | customer@taniexpress.com   | admin123  |

## Struktur Folder

```
├── index.php          # Landing page
├── home.php           # Beranda produk
├── product.php        # Detail produk
├── cart.php           # Keranjang
├── checkout.php       # Checkout
├── payment.php        # Pembayaran QRIS
├── orders.php         # Riwayat pesanan
├── order.php          # Detail & tracking pesanan
├── login.php          # Login customer
├── register.php       # Daftar akun
├── admin/             # Panel admin
├── config/            # Konfigurasi
├── includes/          # Helper & layout
├── database/          # Schema SQL
├── actions/           # Handler aksi
└── uploads/           # Bukti pembayaran
```

## Alur Pesanan

1. Customer checkout → status `menunggu_pembayaran`
2. Upload bukti QRIS → `menunggu_verifikasi`
3. Admin verifikasi → `diproses` → `dikemas` → `dikirim` (assign kurir) → `sampai` → `selesai`
