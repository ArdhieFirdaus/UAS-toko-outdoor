# SISTEM INFORMASI TOKO OUTDOOR

## 📋 DESKRIPSI APLIKASI

Sistem Informasi Toko Outdoor adalah aplikasi berbasis web yang dirancang untuk mengelola operasional toko perlengkapan outdoor/camping. Aplikasi ini dibangun menggunakan **PHP Native** dengan **MySQL** sebagai database dan **Bootstrap 5** untuk tampilan antarmuka yang responsive dan modern.

Aplikasi ini menerapkan sistem **Role-Based Access Control (RBAC)** dengan 3 level pengguna yang memiliki hak akses berbeda:

- **Admin**: Memiliki akses penuh ke seluruh sistem
- **Kasir**: Fokus pada transaksi penjualan dan manajemen produk
- **Owner**: Dapat melihat laporan dan analisis bisnis

---

## ✨ FITUR-FITUR APLIKASI

### 1. **Sistem Autentikasi**

- Login dengan username dan password
- Password terenkripsi menggunakan bcrypt
- Session management dengan timeout otomatis (24 jam)
- Role-based access control (RBAC)
- Proteksi terhadap session hijacking

### 2. **Dashboard**

- **Untuk Admin & Kasir:**
  - Statistik total user, produk, transaksi
  - Total pendapatan
  - Grafik penjualan bulanan
  - Daftar produk dengan stok rendah (≤ 10)
  - Daftar transaksi terbaru
- **Untuk Owner:**
  - Ringkasan bisnis keseluruhan
  - Grafik analisis penjualan
  - Laporan pendapatan

### 3. **Manajemen User** (Khusus Admin)

- Tambah, edit, dan hapus user
- Pengaturan role (admin/kasir/owner)
- Manajemen status user (aktif/nonaktif)
- Ubah password user
- Tampilan data dalam tabel interaktif dengan pencarian dan pagination

### 4. **Manajemen Produk** (Admin & Kasir)

- **Admin:** Full CRUD (Create, Read, Update, Delete)
- **Kasir:** Hanya dapat melihat data produk
- Informasi produk meliputi:
  - Nama produk
  - Kategori (Tenda, Sleeping Bag, Tas, Pakaian, Sepatu, dll)
  - Deskripsi
  - Harga
  - Stok
  - Status (tersedia/habis/tidak_diproduksi)
- Pencarian dan filter produk
- Alert otomatis untuk produk dengan stok rendah

### 5. **Manajemen Transaksi** (Admin & Kasir)

- **Kasir:** Input transaksi penjualan baru
- **Admin:** Lihat semua transaksi dan dapat menghapus
- Fitur Point of Sale (POS):
  - Keranjang belanja dinamis
  - Pilih produk dari daftar
  - Kalkulasi otomatis subtotal dan total
  - Pilihan metode pembayaran (Tunai, Kartu Kredit, Transfer Bank)
  - Cetak struk/receipt transaksi (PDF)
- Update stok otomatis setelah transaksi
- Lihat detail transaksi lengkap
- Pencarian transaksi berdasarkan tanggal atau ID

### 6. **Laporan** (Admin & Owner)

- **Statistik Keseluruhan:**
  - Total transaksi
  - Total pendapatan
  - Total user dan produk
  - Produk dengan stok habis
- **Grafik Pendapatan Bulanan**
  - Visualisasi dalam bentuk bar chart
  - Data 12 bulan terakhir
- **Produk Terlaris:**
  - Top 10 produk berdasarkan jumlah terjual
  - Total penjualan per produk
- **Export Laporan ke Excel:**
  - Laporan transaksi
  - Laporan produk
  - Laporan pendapatan
- **Cetak Laporan (PDF):**
  - Laporan harian, bulanan, atau berdasarkan periode
  - Menggunakan library FPDF

### 7. **Fitur Tambahan**

- Interface responsive (mobile-friendly)
- Loading indicator untuk operasi async
- Toast notification untuk feedback user
- Konfirmasi sebelum delete data
- Validasi input di sisi client dan server
- Proteksi SQL Injection
- Pretty URL dan clean code structure

---

## 🗄️ STRUKTUR DATABASE

Database: **db_toko_outdoor**

### Tabel 1: `user`

Menyimpan data pengguna sistem dengan role-based access.

| Field        | Type         | Keterangan                    |
| ------------ | ------------ | ----------------------------- |
| id_user      | INT (PK)     | ID unik user (Auto Increment) |
| username     | VARCHAR(50)  | Username untuk login (Unique) |
| password     | VARCHAR(255) | Password terenkripsi (bcrypt) |
| role         | ENUM         | admin / kasir / owner         |
| nama_lengkap | VARCHAR(100) | Nama lengkap user             |
| email        | VARCHAR(100) | Email user                    |
| phone        | VARCHAR(15)  | Nomor telepon                 |
| created_at   | TIMESTAMP    | Tanggal dibuat                |
| updated_at   | TIMESTAMP    | Tanggal terakhir diupdate     |
| status       | ENUM         | aktif / nonaktif              |

### Tabel 2: `produk`

Menyimpan data produk perlengkapan outdoor.

| Field       | Type          | Keterangan                          |
| ----------- | ------------- | ----------------------------------- |
| id_produk   | INT (PK)      | ID unik produk (Auto Increment)     |
| nama_produk | VARCHAR(100)  | Nama produk                         |
| kategori    | VARCHAR(50)   | Kategori produk                     |
| deskripsi   | TEXT          | Deskripsi produk                    |
| harga       | DECIMAL(10,2) | Harga satuan produk                 |
| stok        | INT           | Jumlah stok tersedia                |
| gambar      | VARCHAR(255)  | Path gambar produk                  |
| created_at  | TIMESTAMP     | Tanggal dibuat                      |
| updated_at  | TIMESTAMP     | Tanggal terakhir diupdate           |
| status      | ENUM          | tersedia / habis / tidak_diproduksi |

### Tabel 3: `transaksi`

Menyimpan data header transaksi penjualan.

| Field             | Type          | Keterangan                           |
| ----------------- | ------------- | ------------------------------------ |
| id_transaksi      | INT (PK)      | ID unik transaksi (Auto Increment)   |
| id_user           | INT (FK)      | ID user yang melakukan transaksi     |
| tanggal_transaksi | TIMESTAMP     | Tanggal dan waktu transaksi          |
| total_harga       | DECIMAL(10,2) | Total harga transaksi                |
| metode_pembayaran | ENUM          | tunai / kartu_kredit / transfer_bank |
| status            | ENUM          | selesai / pending / dibatalkan       |
| keterangan        | TEXT          | Catatan transaksi                    |

**Relasi:** `id_user` → `user.id_user` (RESTRICT)

### Tabel 4: `detail_transaksi`

Menyimpan detail item produk dalam setiap transaksi.

| Field        | Type          | Keterangan                       |
| ------------ | ------------- | -------------------------------- |
| id_detail    | INT (PK)      | ID unik detail (Auto Increment)  |
| id_transaksi | INT (FK)      | ID transaksi                     |
| id_produk    | INT (FK)      | ID produk                        |
| jumlah       | INT           | Jumlah produk dibeli             |
| harga_satuan | DECIMAL(10,2) | Harga satuan saat transaksi      |
| subtotal     | DECIMAL(10,2) | Subtotal (jumlah × harga_satuan) |

**Relasi:**

- `id_transaksi` → `transaksi.id_transaksi` (CASCADE)
- `id_produk` → `produk.id_produk` (RESTRICT)

### Diagram Relasi Database (ERD)

```
┌─────────────┐         ┌──────────────────┐         ┌─────────────────┐
│    user     │         │    transaksi     │         │ detail_transaksi│
├─────────────┤         ├──────────────────┤         ├─────────────────┤
│ id_user (PK)│────┐    │ id_transaksi (PK)│────┐    │ id_detail (PK)  │
│ username    │    │    │ id_user (FK)     │    │    │ id_transaksi(FK)│
│ password    │    └───>│ tanggal_transaksi│    └───>│ id_produk (FK)  │
│ role        │         │ total_harga      │         │ jumlah          │
│ nama_lengkap│         │ metode_pembayaran│    ┌───>│ harga_satuan    │
│ email       │         │ status           │    │    │ subtotal        │
│ phone       │         │ keterangan       │    │    └─────────────────┘
│ status      │         └──────────────────┘    │
└─────────────┘                                 │
                                                │
┌─────────────┐                                 │
│   produk    │                                 │
├─────────────┤                                 │
│ id_produk(PK)│─────────────────────────────────┘
│ nama_produk │
│ kategori    │
│ deskripsi   │
│ harga       │
│ stok        │
│ gambar      │
│ status      │
└─────────────┘
```

### Index Database

Untuk meningkatkan performa query, database dilengkapi dengan index pada kolom yang sering di-query:

- `idx_user_role` pada `user.role`
- `idx_produk_kategori` pada `produk.kategori`
- `idx_produk_status` pada `produk.status`
- `idx_transaksi_user` pada `transaksi.id_user`
- `idx_transaksi_tanggal` pada `transaksi.tanggal_transaksi`
- `idx_detail_transaksi` pada `detail_transaksi.id_transaksi`

---

## 🔐 AKUN DEMO

Berikut adalah akun demo yang dapat digunakan untuk testing aplikasi:

### 1. **ADMIN**

```
Username: admin
Password: password
```

**Akses Fitur:**

- ✅ Dashboard
- ✅ Manajemen User (CRUD)
- ✅ Manajemen Produk (CRUD)
- ✅ Manajemen Transaksi (Lihat & Hapus)
- ✅ Laporan Lengkap
- ✅ Export & Print Laporan

### 2. **KASIR**

```
Username: kasir
Password: password
```

**Akses Fitur:**

- ✅ Dashboard
- ✅ Lihat Data Produk (Read Only)
- ✅ Input Transaksi Penjualan
- ✅ Cetak Struk Transaksi
- ❌ Tidak dapat akses Manajemen User
- ❌ Tidak dapat akses Laporan

### 3. **OWNER**

```
Username: owner
Password: password
```

**Akses Fitur:**

- ✅ Dashboard
- ✅ Laporan & Analisis Bisnis
- ✅ Export & Print Laporan
- ❌ Tidak dapat akses Manajemen User
- ❌ Tidak dapat akses Manajemen Produk
- ❌ Tidak dapat input Transaksi

---

## 📂 STRUKTUR FOLDER

```
toko-outdoor2/
├── Config/
│   └── koneksi.php              # Koneksi database & helper functions
├── Database/
│   └── db_toko_outdoor.sql      # File SQL untuk import database
├── exports/                      # Folder untuk file export (Excel, PDF)
├── includes/
│   └── fpdf/
│       └── fpdf.php             # Library untuk generate PDF
├── Public/
│   ├── css/
│   │   └── style.css            # Custom stylesheet
│   ├── icons/                   # Folder untuk icon
│   ├── img/                     # Folder untuk gambar
│   └── js/
│       └── script.js            # Custom JavaScript
├── Views/
│   ├── dashboard.php            # Halaman dashboard
│   ├── login.php                # Halaman login
│   ├── logout.php               # Proses logout
│   ├── user_management.php      # Manajemen user
│   ├── produk_management.php    # Manajemen produk
│   ├── transaksi_management.php # Manajemen transaksi (POS)
│   ├── laporan.php              # Halaman laporan
│   ├── laporan_print.php        # Print laporan PDF
│   ├── print_receipt.php        # Print struk transaksi
│   ├── get_detail_transaksi.php # AJAX get detail transaksi
│   └── test_json.php            # File testing
├── index.php                    # Entry point aplikasi
└── README.md                    # File dokumentasi ini
```

---

## 🚀 CARA INSTALASI

### Prasyarat:

- **XAMPP** (atau server lokal lainnya dengan PHP 7.4+ dan MySQL)
- **Web Browser** (Chrome, Firefox, Edge, dll)

### Langkah Instalasi:

1. **Download atau Clone Repository**

   ```
   Clone repository atau extract file ke folder htdocs XAMPP
   Lokasi: C:\xampp\htdocs\toko-outdoor2\
   ```

2. **Jalankan XAMPP**

   - Buka XAMPP Control Panel
   - Start **Apache** dan **MySQL**

3. **Buat Database**

   - Buka browser, akses: `http://localhost/phpmyadmin`
   - Buat database baru dengan nama: `db_toko_outdoor`
   - Atau jalankan query: `CREATE DATABASE db_toko_outdoor;`

4. **Import Database**

   - Pilih database `db_toko_outdoor`
   - Klik tab **Import**
   - Pilih file: `Database/db_toko_outdoor.sql`
   - Klik **Go** untuk mengimport

5. **Konfigurasi Database (Opsional)**

   - Buka file: `Config/koneksi.php`
   - Sesuaikan konfigurasi jika diperlukan:

   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db = "db_toko_outdoor";
   ```

6. **Akses Aplikasi**
   - Buka browser
   - Akses: `http://localhost/toko-outdoor2`
   - Aplikasi akan redirect otomatis ke halaman login
   - Gunakan salah satu akun demo di atas untuk login

---

## 💻 TEKNOLOGI YANG DIGUNAKAN

### Backend:

- **PHP 7.4+** - Server-side scripting
- **MySQL/MariaDB** - Database management system
- **FPDF** - Library untuk generate PDF

### Frontend:

- **HTML5** - Markup language
- **CSS3** - Styling
- **Bootstrap 5.3.0** - CSS framework untuk responsive design
- **JavaScript (Vanilla)** - Client-side scripting
- **jQuery** - JavaScript library untuk AJAX
- **Chart.js** - Library untuk visualisasi grafik
- **DataTables** - Plugin untuk tabel interaktif

### Fitur Keamanan:

- **Password Hashing** menggunakan `password_hash()` dengan bcrypt
- **Prepared Statements** untuk mencegah SQL Injection
- **Session Management** dengan regenerate ID
- **Input Sanitization** pada semua input user
- **Role-Based Access Control (RBAC)**

---

## 📸 PANDUAN SCREENSHOT UNTUK DOKUMENTASI WORD

Berikut adalah panduan halaman yang perlu di-screenshot untuk dokumentasi:

### 1. **Halaman Login**

- Screenshot form login
- Tampilkan field username dan password
- Tombol login

### 2. **Dashboard Admin**

- Screenshot tampilan dashboard dengan:
  - Statistik cards (Total User, Produk, Transaksi, Pendapatan)
  - Grafik penjualan bulanan
  - Tabel produk stok rendah
  - Tabel transaksi terbaru

### 3. **Dashboard Kasir**

- Screenshot dashboard kasir (tanpa menu User Management & Laporan)
- Statistik yang ditampilkan untuk kasir

### 4. **Dashboard Owner**

- Screenshot dashboard owner
- Fokus pada laporan dan grafik

### 5. **Manajemen User (Admin)**

- Screenshot tabel daftar user
- Screenshot form tambah user
- Screenshot form edit user
- Screenshot konfirmasi hapus user

### 6. **Manajemen Produk**

- Screenshot tabel daftar produk
- Screenshot form tambah produk
- Screenshot form edit produk
- Screenshot fitur pencarian produk
- Screenshot produk dengan status berbeda

### 7. **Manajemen Transaksi (POS)**

- Screenshot halaman input transaksi
- Screenshot memilih produk
- Screenshot keranjang belanja terisi
- Screenshot pilihan metode pembayaran
- Screenshot konfirmasi transaksi berhasil
- Screenshot struk transaksi (PDF)

### 8. **Daftar Transaksi**

- Screenshot tabel transaksi
- Screenshot detail transaksi
- Screenshot fitur filter tanggal

### 9. **Laporan**

- Screenshot halaman laporan lengkap
- Screenshot grafik pendapatan bulanan
- Screenshot tabel produk terlaris
- Screenshot tombol export Excel
- Screenshot tombol print PDF
- Screenshot hasil export Excel
- Screenshot hasil print PDF laporan

### 10. **Fitur Responsive**

- Screenshot tampilan mobile/tablet
- Screenshot menu hamburger di mobile

### 11. **Notifikasi & Alert**

- Screenshot toast notification sukses
- Screenshot toast notification error
- Screenshot konfirmasi delete

### 12. **Halaman Logout**

- Screenshot proses logout

---

## 📊 SAMPLE DATA PRODUK

Database sudah dilengkapi dengan 10 sample produk outdoor:

1. **Tenda Camping 4 Orang** - Rp 450.000 (Stok: 15)
2. **Sleeping Bag Premium** - Rp 350.000 (Stok: 20)
3. **Tas Gunung 50L** - Rp 550.000 (Stok: 10)
4. **Jaket Outdoor Waterproof** - Rp 450.000 (Stok: 25)
5. **Sepatu Hiking Profesional** - Rp 650.000 (Stok: 12)
6. **Headlamp LED 5W** - Rp 150.000 (Stok: 30)
7. **Nesting Cookware Set** - Rp 280.000 (Stok: 18)
8. **Kompas Digital** - Rp 120.000 (Stok: 22)
9. **Rope 50 Meter** - Rp 200.000 (Stok: 14)
10. **Camel Water Bottle 2L** - Rp 85.000 (Stok: 40)

---

## 🔧 TROUBLESHOOTING

### Masalah: Tidak bisa login

**Solusi:**

- Pastikan database sudah diimport dengan benar
- Cek koneksi database di `Config/koneksi.php`
- Gunakan password: `password` (semua lowercase)

### Masalah: Error 404 Not Found

**Solusi:**

- Pastikan folder aplikasi ada di `C:\xampp\htdocs\toko-outdoor2\`
- Akses menggunakan URL yang benar: `http://localhost/toko-outdoor2`

### Masalah: PDF tidak bisa diprint

**Solusi:**

- Pastikan folder `includes/fpdf/` ada dan berisi file `fpdf.php`
- Cek permission folder `exports/`

### Masalah: Grafik tidak muncul

**Solusi:**

- Pastikan koneksi internet aktif (untuk load Chart.js dari CDN)
- Cek console browser untuk error JavaScript

### Masalah: Export Excel gagal

**Solusi:**

- Pastikan folder `exports/` memiliki permission write
- Cek apakah ada data yang akan diexport

---

## 📝 CATATAN PENTING

1. **Password Default**: Semua akun demo menggunakan password `password`
2. **Database Charset**: UTF-8 (utf8mb4_unicode_ci)
3. **Session Timeout**: 24 jam
4. **Environment**: Development (untuk production, ubah error reporting di `koneksi.php`)
5. **Browser Support**: Chrome, Firefox, Edge, Safari (versi terbaru)

---

## 👨‍💻 INFORMASI PENGEMBANG

- **Nama Aplikasi**: Sistem Informasi Toko Outdoor
- **Versi**: 1.0
- **Tanggal**: 2025
- **Framework**: PHP Native + Bootstrap 5
- **Database**: MySQL
- **License**: Educational Purpose

---

## 📞 KONTAK & SUPPORT

Untuk pertanyaan atau bantuan terkait aplikasi, silakan hubungi:

- **Email**: admin@toko-outdoor.com
- **GitHub**: [Repository Link]

---

## ✅ CHECKLIST UNTUK DOKUMENTASI WORD

Gunakan checklist ini untuk memastikan dokumentasi Word lengkap:

- [ ] Cover halaman dengan judul aplikasi
- [ ] Daftar isi
- [ ] BAB 1: Pendahuluan
  - [ ] Latar belakang
  - [ ] Tujuan aplikasi
  - [ ] Ruang lingkup
- [ ] BAB 2: Deskripsi Aplikasi
  - [ ] Penjelasan aplikasi
  - [ ] Fitur-fitur lengkap
  - [ ] Teknologi yang digunakan
- [ ] BAB 3: Struktur Database
  - [ ] Diagram ERD
  - [ ] Penjelasan setiap tabel
  - [ ] Relasi antar tabel
- [ ] BAB 4: Panduan Instalasi
  - [ ] Langkah-langkah instalasi
  - [ ] Konfigurasi
- [ ] BAB 5: Panduan Penggunaan (dengan Screenshot)
  - [ ] Login
  - [ ] Dashboard (untuk setiap role)
  - [ ] Manajemen User
  - [ ] Manajemen Produk
  - [ ] Transaksi (POS)
  - [ ] Laporan
- [ ] BAB 6: Akun Demo
  - [ ] Tabel akun demo dengan username, password, dan hak akses
- [ ] BAB 7: Troubleshooting
- [ ] Penutup

---

## 🎯 FITUR UNGGULAN

1. **Role-Based Access Control** - Sistem yang fleksibel dengan 3 level user
2. **Real-time Stock Update** - Stok otomatis berkurang saat transaksi
3. **Point of Sale (POS)** - Interface kasir yang user-friendly
4. **Print Receipt** - Cetak struk otomatis setelah transaksi
5. **Business Analytics** - Grafik dan laporan untuk analisis bisnis
6. **Export & Print** - Export ke Excel dan print ke PDF
7. **Responsive Design** - Dapat diakses dari berbagai device
8. **Security Features** - Password encryption, SQL injection prevention

---

**Terima kasih telah menggunakan Sistem Informasi Toko Outdoor!**

Semoga dokumentasi ini membantu dalam penyelesaian tugas Anda. 🚀
