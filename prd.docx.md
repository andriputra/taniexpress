# PRD — Project Requirements Document TaniExpress

# 1\. Overview

Aplikasi TaniExpress adalah aplikasi e-commerce sederhana yang mengelola penjualan produk sayuran dari para petani. 

Petani akan menitipkan produk sayurannya ke admin untuk dibantu dipasarkan, admin akan menayangkan produk dari setiap petani di aplikasi supaya dapat dijangkau pembeli melalui aplikasi.

Setiap petani akan menetapkan harga jualnya masing-masing. Untuk setiap penjualan produk, admin akan mengenakan biaya platform dan ongkos kirim.

pembeli bisa membuat pesanan, melakukan checkout pesanan, melakukan pembayaran dan monitoring proses pengiriman pesanan

# 2\. Requirements

Berikut adalah persyaratan tingkat tinggi untuk pengembangan sistem:

1. Aksesibilitas: Aplikasi harus dapat diakses melalui web browser responsive (desktop/laptop diutamakan untuk input data manual).  
2. Pengguna: Sistem dirancang untuk satu pengguna (Admin) dengan akses penuh, dan User pembeli  
3. Data Input: Input data dilakukan secara manual (diketik).  
4. Spesifisitas Data produk: Setiap produk harus mencatat informasi mendetail seperti nama produk, nama petani, jumlah stok, harga jual  
5. Spesifisitas Data kurir: harus mencatat nama dan nomor telepon kurir yang ditugaskan untuk mengirim pesanan

# 3\. Core Features

Fitur-fitur kunci yang harus ada dalam versi pertama (MVP):

## 3.1 Akses Admin:

1. Login Admin  
2. Dasboard Admin, berisi informasi:  
   1. Ringkasan total jumlah stok produk yang dimiliki petani  
   2. Daftar pesanan baru dan status pesanan.  
   3. Daftar Kurir yang sedang mengantar pesanan dan yang tidak  
3. Manajemen master data:  
   1. Manajemen Produk: crud master data produk  
   2. Manajemen Petani: crud master data petani  
   3. Manajemen Produk \- Petani : berisi data produk, petani, harga produk dari setiap petani  
   4. Manajemen Kurir: crud master data kurir  
4. Laporan pesanan: ditampilkan dalam bentuk tabel dan bisa di ekspor ke fi pdf

3.2 Akses User Pembeli

1. Login User menggunakan email dan password, jika belum mempunya akun harus daftar terlebih dahulu.  
2. Daftar produk menampilkan semua produk yang dijual taniExpress, informasi yang ditampilkan dari setiap produk gambar produk, nama produk, penjual (nama Petani), harga  
3. Detail produk menampilkan informasi detil dari setiap produk jika produk di klik oleh user. Informasi yang ditampilkan :  gambar produk, nama produk, penjual (nama Petani), harga, Qty, Deskripsi  
4. Keranjang: untuk menampung produk yang sudah dipilih dan dimasukan ke keranjang oleh user.  
5. Checkout: proses membuat pesanan sampai pembayaranTracking pesanan  
6. Pesanan: history semua pesanan yang sudah dilakukan user baik itu selesai maupun dibatalkan

# 4\. User Flow

## 4.1 Alur Kerja Admin 

Alur kerja sederhana bagi Admin saat menggunakan aplikasi:

1. Login: Admin masuk menggunakan email dan password.  
2. Monitoring: Admin melihat Dashboard untuk mengecek apakah ada pesanan baru, jika ada admin harus memproses pesanan tersebut dan set kurir yang akan mengantar pesanan tersebut  
3. Setup Produk (Awal): Jika barang baru, Admin membuat data produk baru.  
4. Setup Petani(Awal): jika ada petani baru, Admin membuat data petani baru.  
5. Setup Kurir(Awal): jika ada kurir baru, Admin akan membuat data kurir baru.  
6. Setup Produk Petani: Admin akan setup harga produk berdasarkan pemiliknya (Petani) dan membuat data stok nya.  
7. Pesanan: Admin harus menyiapkan pesanan pembeli dan mengatur siapa kurir yang akan mengantarkannya  
8. laporan pesanan:Admin bisa melihat laporan pesanan per periode tanggal/minggu/bulanan

## 4.2 Alur Kerja User

Alur kerja sederhana bagi Admin saat menggunakan aplikasi:

1. Login: Costumer akan menemukan tombol “Start now” kemudian mulai mendaftar menggunakan Email, password, keterangan diri seperti nama, alamat, dan nomor telepon  
2. Landing page: halaman khusus yang bertujuan untuk memberikan informasi singkat tentang Tanixpress dan tombol “mulai belanja”  
3. Output: masuk ke halaman home  
4. Homepage: menampilkan produk, search bar dan pengkategorian produk; jenis, petani, lalu menampilkan keranjang, akun, riwayat belanja dan pesanan.  
5. pengguna bisa Mencari produk,Memilih kategori,Menekan salah satu produk  
6. Output: masuk ke halaman detail produk   
7. Detail produk: menampilkan Foto produk, nama produk, harga produk, berat, stok, deskripsi, nama petani.  
8. costumer menambahkan jumlah pemesana produk, lalu menekan tombol “masukan ke keranjang”  
9. Output: Produk masuk ke keranjang   
10. Cart (keranjang) : menampilkan Daftar produk, jumlah produk, total harga produk  
11. Pengguna bisa mengurangi atau menambahkan produk, menghapus produk dan menekan tombol “checkout”  
12. Checkout: menampilkan alamat yang di dapat dari input user di halaman login, namun user dapat mengubah alamatnya dengan input user; nama penerima, nomor telepon, alamat. menampilkan Total harga produk, ongkos kirim, dan total bayar  
13. output: costumer menekan tombol “bayar”  
14. Payment: menampilkan Qris statis milik tanixpress, total pembayaran dan instruksi pembayaran   
15. Customer melakukan pembayaran menggunakan Qris, e-wallet. Setelah selesai melakukan pembayaran, pengguna akan diminta untuk meng-submit bukti pembayaran berupa screenshot.  
16. Output : setelah pesanan berubah menjadi “menunggu verifikasi dari admin”  
17. Status : memunculkan menunggu verifikasi, pesanan diproses, sedang dikemas, Driver, sedang dikirm, dan pesanan sampai, user dapat memantau pesananan secara berkala  
18. setelah pengguna menerima pesanan, halaman akan berubah menjadi ucapan terimakasih telah memercayakan dan menggunakan tanixpress, dengan foto foto petani;lahan dan hasil tani, lalu masuk ke user feedback

# 5\. Architecture

Berikut adalah gambaran arsitektur sistem:

1. Database: MySql  
2. PHP dan HTML 

# 6\. Database Schema

Berikut adalah Entity Relationship Diagram (ERD) yang menggambarkan struktur database utama:

| Tabel | Deskripsi |
| :---- | :---- |
| products | Master data produk, menyimpan info SKU, nama produk, satuan |
| petani | Master data petani, menyimpan info nama petani, no telp, alamat |
| produk\_petani | Master data produk yang dimiliki petani, stok, harga |
| kurir | Master data kurir, menyimpan info kurir, nama dan no telp kurir |
| pesanan | Menyimpan daftar pesanan dari pembeli, beserta status pesanan, kurir |
| Pesanan\_detil | Menyimpan detil pesanan yang berisi header pesanan, produk\_petani, qty, harga |

## DATA

1. buatkan data dummy untuk setiap tabel tersebut  
2. buatkan akun untuk admin dan pembeli

