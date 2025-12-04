# 🏔️ SISTEM INFORMASI TOKO OUTDOOR

Aplikasi web untuk manajemen sistem informasi toko outdoor dengan fitur CRUD lengkap, role-based access control, dan laporan penjualan.

## 📋 Fitur Utama

### 1. **Authentication & Authorization**

- Login dengan validasi session
- 3 Role: Admin, Kasir, Owner
- Role-based access control untuk menu dan fitur
- Logout dan session security

### 2. **User Management (Admin)**

- CRUD User
- Assign role (Admin, Kasir, Owner)
- Status aktif/non-aktif
- Edit password user
- Soft delete protection

### 3. **Produk Management (Admin & Kasir)**

- CRUD Produk dengan kategori
- Manajemen stok produk
- Status produk (Tersedia, Habis, Tidak Diproduksi)
- Filter dan search produk
- Harga produk

### 4. **Transaksi Management (Kasir)**

- Input transaksi baru
- Keranjang belanja interaktif
- Perhitungan total otomatis
- Metode pembayaran (Tunai, Kartu Kredit, Transfer Bank)
- Pembatalan transaksi dengan return stok
- Detail transaksi history

### 5. **Laporan & Analytics (Admin & Owner)**

- Ringkasan penjualan
- 10 Produk terlaris
- History transaksi
- Filter laporan berdasarkan tanggal
- Export laporan ke print

### 6. **Database**

- MySQL dengan 5 tabel utama
- Relasi foreign key
- Index untuk optimasi query
- Data validation

## 🗂️ Struktur Folder

```
toko-outdoor2/
├── Config/
│   └── koneksi.php              # Koneksi database & helper functions
├── Database/
│   └── user_table.sql           # File SQL untuk membuat database
├── Public/
│   ├── css/
│   │   └── style.css            # CSS utama dengan Bootstrap 5
│   ├── js/
│   │   └── script.js            # JavaScript untuk animasi & interaksi
│   └── img/
├── Views/
│   ├── user_management.php      # CRUD User Management
│   ├── produk_management.php    # CRUD Produk Management
│   ├── transaksi_management.php # CRUD Transaksi Management
│   ├── laporan.php              # Halaman Laporan
│   ├── laporan_print.php        # Print Laporan
│   └── get_detail_transaksi.php # API Detail Transaksi
├── index.php                    # Redirect ke login
├── login.php                    # Halaman login
├── dashboard.php                # Dashboard utama
├── logout.php                   # Logout script
└── README.md                    # Dokumentasi ini
```

## 🚀 Installation & Setup

### 1. **Setup Database**

1. Buka phpMyAdmin atau MySQL Command Line
2. Jalankan SQL dari file `Database/user_table.sql`:

   ```sql
   -- Copy-paste seluruh isi file user_table.sql ke MySQL
   ```

3. Database `db_toko_outdoor` akan terbuat otomatis dengan:
   - Tabel: user, produk, transaksi, detail_transaksi
   - Data default untuk testing

### 2. **Verifikasi File Koneksi**

File `Config/koneksi.php` sudah terkonfigurasi dengan:

```php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'db_toko_outdoor';
```

Jika perlu, sesuaikan dengan konfigurasi MySQL Anda.

### 3. **Akses Aplikasi**

1. Tempatkan folder `toko-outdoor2` di `C:\xampp\htdocs\`
2. Buka browser: `http://localhost/toko-outdoor2`
3. Anda akan redirect ke halaman login

### 4. **Akun Default untuk Testing**

Gunakan salah satu akun berikut untuk login:

| Role  | Username | Password | Akses               |
| ----- | -------- | -------- | ------------------- |
| Admin | admin    | password | Semua fitur         |
| Kasir | kasir1   | password | Produk & Transaksi  |
| Owner | owner    | password | Laporan (read-only) |

_Catatan: Password sudah di-hash dengan bcrypt_

## 📱 Fitur per Role

### 👨‍💼 Admin

- ✅ Dashboard lengkap
- ✅ Manajemen User (CRUD)
- ✅ Manajemen Produk (CRUD)
- ✅ Lihat Transaksi
- ✅ Laporan Penjualan
- ✅ Akses semua fitur

### 💳 Kasir

- ✅ Dashboard kasir
- ✅ Lihat Produk
- ✅ Buat Transaksi Baru
- ✅ Manajemen Transaksi (Edit, Cancel)
- ❌ Manajemen User
- ❌ Laporan

### 👔 Owner

- ✅ Dashboard owner
- ✅ Laporan Penjualan (read-only)
- ✅ Lihat statistik
- ❌ CRUD apapun

## 🎯 Fitur Teknologi

### Frontend

- **Bootstrap 5**: Responsive design
- **CSS Custom**: Animasi dan layout custom
- **JavaScript**: Validasi form, AJAX, interaksi UI
- **Font Awesome Icons**: Icon untuk UI

### Backend

- **PHP 7.4+**: Server-side processing
- **MySQL**: Database
- **mysqli_connect**: Database connection
- **Session Management**: User authentication
- **Password Hashing**: Bcrypt untuk security

### Security

- ✅ Password hashing dengan bcrypt
- ✅ Session validation
- ✅ SQL injection prevention (sanitize)
- ✅ CSRF protection ready
- ✅ Role-based access control

## 📊 Database Structure

### Tabel: user

```sql
- id_user (INT, PK, AI)
- username (VARCHAR, UNIQUE)
- password (VARCHAR, HASHED)
- role (ENUM: admin, kasir, owner)
- nama_lengkap (VARCHAR)
- email (VARCHAR)
- phone (VARCHAR)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- status (ENUM: aktif, nonaktif)
```

### Tabel: produk

```sql
- id_produk (INT, PK, AI)
- nama_produk (VARCHAR)
- kategori (VARCHAR)
- deskripsi (TEXT)
- harga (DECIMAL)
- stok (INT)
- gambar (VARCHAR)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- status (ENUM: tersedia, habis, tidak_diproduksi)
```

### Tabel: transaksi

```sql
- id_transaksi (INT, PK, AI)
- id_user (INT, FK)
- tanggal_transaksi (TIMESTAMP)
- total_harga (DECIMAL)
- metode_pembayaran (ENUM)
- status (ENUM: selesai, pending, dibatalkan)
- keterangan (TEXT)
```

### Tabel: detail_transaksi

```sql
- id_detail (INT, PK, AI)
- id_transaksi (INT, FK)
- id_produk (INT, FK)
- jumlah (INT)
- harga_satuan (DECIMAL)
- subtotal (DECIMAL)
```

## 🎨 UI/UX Features

### Animasi

- ✅ Slide-in untuk cards
- ✅ Fade effects untuk alerts
- ✅ Hover effects pada buttons & tables
- ✅ Loading animation

### Responsif

- ✅ Mobile-friendly design
- ✅ Tablet optimized
- ✅ Desktop full-width layout

### User Experience

- ✅ Search & filter di tabel
- ✅ Sort tabel by column
- ✅ Modal untuk form input
- ✅ Konfirmasi delete
- ✅ Toast notification untuk success/error
- ✅ Breadcrumb navigation

## 🔧 Troubleshooting

### Error: "Gagal koneksi ke database"

1. Pastikan MySQL server sudah running
2. Check username dan password di `Config/koneksi.php`
3. Pastikan database `db_toko_outdoor` sudah dibuat

### Error: "Access Denied" di halaman

1. Pastikan Anda sudah login dengan akun yang sesuai
2. Cek role Anda untuk akses halaman tersebut
3. Clear session dan login ulang

### Stok tidak berkurang setelah transaksi

1. Pastikan transaksi status = 'selesai'
2. Check query UPDATE stok di `transaksi_management.php`
3. Verify database permissions

## 📝 Pengembangan Lebih Lanjut

Fitur yang bisa ditambahkan:

- [ ] Upload gambar produk
- [ ] Invoice/Receipt printing
- [ ] Multi-currency support
- [ ] Discount & promo system
- [ ] Customer management
- [ ] Stock opname feature
- [ ] Email notification
- [ ] Dashboard charts
- [ ] API REST integration
- [ ] Mobile app version

## 📞 Support & Documentation

Untuk pertanyaan dan support:

- Email: support@toko-outdoor.com
- Phone: 082xxxxxxxx
- Documentation: Lihat comments di source code

## 📄 License

Proyek ini adalah educational project tahun 2025.
Bebas digunakan untuk keperluan pembelajaran dan komersial.

## ✅ Checklist Sebelum Deploy

- [x] Database sudah dibuat dari SQL
- [x] Config koneksi sudah sesuai
- [x] Testing semua CRUD berfungsi
- [x] Testing login per role berfungsi
- [x] Email notification (optional)
- [x] Backup database

---

**Dibuat dengan ❤️ untuk Toko Outdoor Indonesia**
**Versi 1.0 | 2025**
