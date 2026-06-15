# TaniExpress

Aplikasi e-commerce sayuran segar dari petani lokal. Dibangun dengan **PHP**, **MySQL**, dan **Tailwind CSS**.

---

## Daftar Isi

1. [Fitur](#fitur)
2. [Instalasi](#instalasi)
3. [Akun Default](#akun-default)
4. [Struktur Folder](#struktur-folder)
5. [Diagram Alur Lengkap](#diagram-alur-lengkap)
   - [Aktor & Peran Sistem](#1-aktor--peran-sistem)
   - [Arsitektur Data](#2-arsitektur-data)
   - [Autentikasi](#3-alur-autentikasi)
   - [Belanja Customer](#4-alur-belanja-customer)
   - [Status Pesanan](#5-alur-status-pesanan)
   - [Kelola Pesanan Admin](#6-alur-kelola-pesanan-admin)
   - [Setup Katalog Admin](#7-alur-setup-katalog-admin)
   - [Sinkronisasi Stok](#8-alur-sinkronisasi-stok)
   - [Laporan Admin](#9-alur-laporan-admin)

---

## Fitur

### Pembeli (Customer)
- Landing page dengan hero slider dinamis
- Beranda produk, pencarian, filter kategori & petani
- Detail produk, produk terpopuler, produk serupa
- Keranjang belanja (session)
- Checkout, pembayaran QRIS, upload bukti bayar
- Lacak status pesanan
- Registrasi, login, kelola akun & ubah password

### Admin
- Dashboard (stok, pesanan perlu aksi, status kurir)
- CRUD Produk, Petani, Listing Produk-Petani (modal), Kurir
- Kelola pesanan & verifikasi pembayaran
- Assign kurir & update status pengiriman berurutan
- Laporan pesanan per periode (export/cetak PDF)
- Kelola akun admin & ubah password

---

## Instalasi

### 1. Persyaratan
- PHP 8.0+
- MySQL / MariaDB
- Extension PHP: `pdo_mysql`

### 2. Konfigurasi Database

Edit `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'taniexpress');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Install Database

```bash
cd taniexpress
php install.php
```

Reset database (hapus semua data):

```bash
php install.php --fresh
```

### 4. Jalankan Server

```bash
php -S localhost:8000
```

Buka: **http://localhost:8000**

| URL | Keterangan |
|-----|------------|
| `/` | Landing page |
| `/home.php` | Toko / katalog |
| `/admin/login.php` | Panel admin |

---

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@taniexpress.com | admin123 |
| Customer | customer@taniexpress.com | admin123 |

---

## Struktur Folder

```
taniexpress/
├── index.php              # Landing page
├── home.php               # Katalog produk (toko)
├── product.php            # Detail produk
├── cart.php               # Keranjang
├── checkout.php           # Checkout
├── payment.php            # Pembayaran QRIS + upload bukti
├── orders.php             # Riwayat pesanan
├── order.php              # Detail & tracking pesanan
├── account.php            # Profil customer
├── login.php / register.php
├── admin/                 # Panel admin
│   ├── index.php          # Dashboard
│   ├── products.php       # CRUD produk master
│   ├── farmers.php        # CRUD petani
│   ├── produk-petani.php  # Listing stok & harga per petani
│   ├── kurir.php          # CRUD kurir
│   ├── pesanan.php        # Kelola pesanan
│   ├── laporan.php        # Laporan & export
│   └── account.php        # Profil admin
├── actions/cart.php       # Handler keranjang
├── config/config.php      # Konfigurasi DB & app
├── includes/              # Bootstrap, auth, UI, helpers
├── database/schema.sql    # Schema + seed data
├── install.php            # Installer
└── uploads/               # Foto produk, petani, bukti bayar
```

---

## Diagram Alur Lengkap

### 1. Aktor & Peran Sistem

```mermaid
flowchart TB
    subgraph Public["Akses Publik"]
        Guest[Pengunjung / Guest]
    end

    subgraph Customer["Customer"]
        C1[Jelajahi katalog]
        C2[Belanja & checkout]
        C3[Bayar & lacak pesanan]
    end

    subgraph Admin["Administrator"]
        A1[Kelola master data]
        A2[Verifikasi & proses pesanan]
        A3[Laporan penjualan]
    end

    subgraph System["TaniExpress"]
        WEB[Web App PHP]
        DB[(MySQL)]
        UP[Folder uploads/]
    end

    Guest -->|Landing, lihat produk| WEB
    C1 --> WEB
    C2 --> WEB
    C3 --> WEB
    A1 --> WEB
    A2 --> WEB
    A3 --> WEB
    WEB --> DB
    WEB --> UP
```

---

### 2. Arsitektur Data

Relasi utama antar entitas:

```mermaid
erDiagram
    users ||--o{ pesanan : "membuat"
    products ||--o{ produk_petani : "punya listing"
    petani ||--o{ produk_petani : "menjual"
    produk_petani ||--o{ pesanan_detil : "dipesan"
    pesanan ||--|{ pesanan_detil : "berisi"
    kurir ||--o{ pesanan : "mengantar"
    pesanan }o--|| users : "milik"

    users {
        int id PK
        string email UK
        enum role "admin|customer"
    }
    products {
        int id PK
        string sku UK
        string nama
        int stok "total sinkron"
    }
    petani {
        int id PK
        string nama
    }
    produk_petani {
        int id PK
        int product_id FK
        int petani_id FK
        int stok
        decimal harga
    }
    pesanan {
        int id PK
        string kode_pesanan UK
        enum status
        int kurir_id FK
        string bukti_bayar
    }
    pesanan_detil {
        int pesanan_id FK
        int produk_petani_id FK
        int qty
    }
    kurir {
        int id PK
        enum status "tersedia|sibuk"
    }
```

**Konsep penting:**
- **products** = master produk (SKU, kategori, foto)
- **produk_petani** = listing di toko (stok & harga per petani)
- Produk tampil di toko customer hanya jika ada listing `produk_petani` dengan `stok > 0`

---

### 3. Alur Autentikasi

```mermaid
flowchart TD
    Start([User membuka login]) --> Choose{Halaman login?}

    Choose -->|/login.php| CL[Login Customer]
    Choose -->|/admin/login.php| AL[Login Admin]

    CL --> CForm[Input email + password]
    AL --> AForm[Input email + password]

    CForm --> Auth{password_verify?}
    AForm --> AuthA{password_verify + role admin?}

    Auth -->|Tidak| Err[Pesan error]
    Auth -->|Ya| CRole{role = customer?}
    CRole -->|Ya| CHome[Redirect home.php]
    CRole -->|Admin| APanel[Redirect admin/index.php]

    AuthA -->|Tidak| ErrA[Kredensial tidak valid]
    AuthA -->|Ya| ADash[Redirect admin/index.php]

    Reg([/register.php]) --> RForm[Isi nama, email, telepon, alamat, password]
    RForm --> RCheck{Email sudah ada?}
    RCheck -->|Ya| RErr[Error: email terdaftar]
    RCheck -->|Tidak| RCreate[Buat user role customer]
    RCreate --> CHome

    Logout([/logout.php]) --> Destroy[Session dihapus]
    Destroy --> Landing[Redirect index.php]
```

---

### 4. Alur Belanja Customer

```mermaid
flowchart TD
    A([Mulai]) --> B[index.php Landing]
    B --> C[home.php Katalog]
    C --> D{Filter / search?}
    D --> C
    C --> E[product.php Detail produk]
    E --> F[actions/cart.php Tambah ke keranjang]
    F --> G[(Session cart)]

    G --> H[cart.php Lihat keranjang]
    H --> I{Update / hapus item?}
    I --> H
    H --> J[checkout.php]

    J --> K[Isi alamat pengiriman]
    K --> L{Stok cukup?}
    L -->|Tidak| ErrStok[Error: stok tidak cukup]
    L -->|Ya| M[Buat record pesanan]
    M --> N[Insert pesanan_detil]
    N --> O[Kurangi stok produk_petani]
    O --> P[Sync stok products]
    P --> Q[Kosongkan keranjang]
    Q --> R[payment.php QRIS]

    R --> S[Customer scan & bayar]
    S --> T[Upload bukti pembayaran]
    T --> U[Status: menunggu_verifikasi]
    U --> V[order.php / orders.php Lacak pesanan]

    ErrStok --> H
```

---

### 5. Alur Status Pesanan

State machine status pesanan (alur normal + cabang):

```mermaid
stateDiagram-v2
    [*] --> menunggu_pembayaran: Checkout selesai

    menunggu_pembayaran --> menunggu_verifikasi: Customer upload bukti bayar
    menunggu_pembayaran --> dibatalkan: Admin/customer batalkan

    menunggu_verifikasi --> diproses: Admin verifikasi pembayaran
    menunggu_verifikasi --> menunggu_pembayaran: Admin tolak bukti
    menunggu_verifikasi --> dibatalkan: Batalkan pesanan

    diproses --> dikemas: Admin lanjutkan status
    diproses --> dibatalkan: Batalkan pesanan

    dikemas --> dikirim: Admin assign kurir tersedia
    dikemas --> dibatalkan: Batalkan pesanan

    dikirim --> sampai: Admin/kurir tandai sampai
    dikirim --> dibatalkan: Batalkan (jarang)

    sampai --> selesai: Admin/customer konfirmasi selesai

    selesai --> [*]
    dibatalkan --> [*]: Stok dikembalikan
```

| Status | Keterangan |
|--------|------------|
| `menunggu_pembayaran` | Pesanan dibuat, menunggu bayar QRIS |
| `menunggu_verifikasi` | Bukti bayar diupload, menunggu admin |
| `diproses` | Pembayaran diverifikasi |
| `dikemas` | Pesanan sedang disiapkan |
| `dikirim` | Kurir ditugaskan & barang dalam perjalanan |
| `sampai` | Barang sampai di customer |
| `selesai` | Transaksi selesai |
| `dibatalkan` | Dibatalkan, stok dikembalikan |

**Aturan transisi:**
- Status hanya bisa maju **satu langkah** (tidak boleh loncat)
- Ke `dikirim` wajib pilih **kurir tersedia**
- Ke `dibatalkan` mengembalikan stok via `restoreOrderStock()`
- Kurir dilepas (`tersedia`) saat pesanan `selesai` atau `dibatalkan`

---

### 6. Alur Kelola Pesanan Admin

```mermaid
flowchart TD
    A([admin/pesanan.php]) --> B[Filter status / lihat daftar]
    B --> C[Klik pesanan → detail]

    C --> D{Status saat ini?}

    D -->|menunggu_pembayaran| W1[Menunggu customer bayar]
    D -->|menunggu_verifikasi| V1{Lihat bukti bayar}
    V1 -->|Valid| V2[Verifikasi → diproses]
    V1 -->|Tidak valid| V3[Tolak bukti → menunggu_pembayaran]

    D -->|diproses| P1[Lanjut → dikemas]
    D -->|dikemas| K1[Pilih kurir tersedia]
    K1 --> K2[Lanjut → dikirim]
    D -->|dikirim| S1[Lanjut → sampai]
    D -->|sampai| F1[Tandai → selesai]

    C --> X{Batalkan?}
    X -->|Ya & boleh dibatalkan| X1[dibatalkan + restore stok]

    V2 --> UP[updateOrderStatus]
    V3 --> UP
    P1 --> UP
    K2 --> UP
    S1 --> UP
    F1 --> UP
    X1 --> UP
    UP --> B
```

---

### 7. Alur Setup Katalog Admin

Data harus disiapkan agar produk muncul di toko:

```mermaid
flowchart LR
    subgraph Master
        P[admin/products.php<br/>Master Produk]
        F[admin/farmers.php<br/>Data Petani]
    end

    subgraph Listing
        PP[admin/produk-petani.php<br/>Listing Produk-Petani]
    end

    subgraph Toko
        H[home.php<br/>Katalog Customer]
    end

    P -->|product_id| PP
    F -->|petani_id| PP
    PP -->|stok > 0 & harga| H

    P -.->|stok master| Sync[syncProdukPetaniStock]
    PP -.->|perubahan stok| Refresh[refreshProductStockTotal]
    Sync --> PP
    Refresh --> P
```

**Langkah setup (urutan disarankan):**

```mermaid
flowchart TD
    S1[1. Tambah Produk Master<br/>SKU, nama, kategori, foto, stok] --> S2[2. Tambah Data Petani<br/>nama, telepon, alamat, foto]
    S2 --> S3[3. Buat Listing Produk-Petani<br/>modal: pilih produk + petani + stok + harga]
    S3 --> S4[4. Produk muncul di toko customer]
    S4 --> S5[5. Kelola stok/harga via edit listing]
```

**Catatan listing:**
- Satu kombinasi **produk + petani** hanya boleh **1 baris** (`unique_produk_petani`)
- Tambah/edit listing memakai **modal** di halaman Produk-Petani

---

### 8. Alur Sinkronisasi Stok

```mermaid
flowchart TD
    subgraph SumberPerubahan["Pemicu perubahan stok"]
        A1[Admin ubah stok di Produk Master]
        A2[Admin ubah stok di Listing]
        A3[Checkout customer]
        A4[Pesanan dibatalkan]
    end

    A1 --> B1[syncProdukPetaniStock<br/>Push stok master ke semua listing petani]
    A2 --> B2[refreshProductStockTotal<br/>Jumlah stok listing → products.stok]
    A3 --> B3[Kurangi produk_petani.stok per item pesanan]
    A4 --> B4[restoreOrderStock<br/>Kembalikan qty ke produk_petani]

    B3 --> B2
    B4 --> B2
    B1 --> PP[(produk_petani)]
    B2 --> PR[(products)]
    B3 --> PP
    B4 --> PP
```

---

### 9. Alur Laporan Admin

```mermaid
flowchart TD
    A([admin/laporan.php]) --> B[Pilih rentang tanggal]
    B --> C[Filter pesanan by created_at]
    C --> D[Tampilkan ringkasan]
    D --> E[Total pesanan]
    D --> F[Total pendapatan<br/>exclude batal & menunggu bayar]
    C --> G[Tabel detail pesanan]
    G --> H{Export PDF?}
    H -->|Ya| I[Halaman cetak ?export=1]
    I --> J[Browser Print / Save as PDF]
```

---

## Ringkasan Alur Pesanan (Teks)

1. Customer checkout → `menunggu_pembayaran` (stok langsung dikurangi)
2. Upload bukti QRIS → `menunggu_verifikasi`
3. Admin verifikasi → `diproses` → `dikemas` → `dikirim` (wajib kurir) → `sampai` → `selesai`
4. Jika dibatalkan → stok dikembalikan, kurir dilepas

---

## Teknologi

| Layer | Stack |
|-------|-------|
| Backend | PHP 8 (native, PDO) |
| Database | MySQL / MariaDB |
| Frontend | Tailwind CSS (CDN), Material Symbols |
| Session | Keranjang belanja PHP session |
| Upload | Foto produk/petani, bukti pembayaran |

---

## Lisensi

Proyek edukasi / internal — AA Enterprise.
