<?php
/**
 * PANDUAN INSTALASI & SETUP
 * Sistem Informasi Toko Outdoor
 * ====================================
 */

// Step 1: Pastikan semua file sudah di-copy ke folder toko-outdoor2

// Step 2: Import Database
echo "
╔════════════════════════════════════════════╗
║  SISTEM INFORMASI TOKO OUTDOOR             ║
║  Panduan Setup Lengkap                     ║
╚════════════════════════════════════════════╝

LANGKAH 1: SETUP DATABASE
========================

1. Buka phpMyAdmin: http://localhost/phpmyadmin
2. Login dengan akun MySQL Anda
3. Buat database baru atau gunakan import:
   - Pilih 'Import' atau 'SQL'
   - Upload file: Database/user_table.sql
   - Klik 'Go'

Atau gunakan Command Line MySQL:
$ mysql -u root -p < Database/user_table.sql

LANGKAH 2: VERIFIKASI KONEKSI
============================

File Config/koneksi.php sudah dikonfigurasi:
  - Host: localhost
  - User: root
  - Password: (kosong - default XAMPP)
  - Database: db_toko_outdoor

Jika MySQL Anda berbeda, edit Config/koneksi.php:

\$host = 'localhost';        // Sesuaikan
\$user = 'root';              // Sesuaikan
\$password = '';              // Sesuaikan
\$database = 'db_toko_outdoor';

LANGKAH 3: AKSES APLIKASI
========================

Buka browser:
  http://localhost/toko-outdoor2

Anda akan otomatis redirect ke:
  http://localhost/toko-outdoor2/login.php

LANGKAH 4: LOGIN
==============

Gunakan salah satu akun default:

┌─────────┬──────────┬──────────┐
│ Role    │ Username │ Password │
├─────────┼──────────┼──────────┤
│ Admin   │ admin    │ password │
│ Kasir   │ kasir1   │ password │
│ Owner   │ owner    │ password │
└─────────┴──────────┴──────────┘

LANGKAH 5: TESTING
================

Setelah login, test fitur:

[Admin]
  ✓ Dashboard
  ✓ Manajemen User (Tambah/Edit/Hapus)
  ✓ Manajemen Produk (Tambah/Edit/Hapus)
  ✓ Lihat Transaksi
  ✓ Laporan Penjualan

[Kasir]
  ✓ Dashboard
  ✓ Lihat Produk
  ✓ Buat Transaksi Baru
  ✓ Batalkan Transaksi

[Owner]
  ✓ Dashboard
  ✓ Laporan (Read-Only)

TROUBLESHOOTING
==============

1. Error: 'Gagal koneksi ke database'
   → Pastikan MySQL running
   → Check Config/koneksi.php
   → Restart Apache & MySQL di XAMPP Control Panel

2. Error: 'Access Denied'
   → Logout dan login dengan akun yang tepat
   → Cek role di database

3. Halaman tidak load
   → Check browser console untuk JavaScript error
   → Pastikan folder Public/css dan Public/js ada
   → Clear browser cache (Ctrl+Shift+Delete)

4. Transaksi tidak menyimpan
   → Check browser console untuk AJAX error
   → Pastikan detail_transaksi table created
   → Verify database permissions

DATABASE STRUCTURE
=================

Tabel: user
  - id_user (INT, PK, Auto Increment)
  - username (VARCHAR, UNIQUE)
  - password (VARCHAR, Hashed dengan Bcrypt)
  - role (ENUM: admin, kasir, owner)
  - nama_lengkap (VARCHAR)
  - email (VARCHAR)
  - phone (VARCHAR)
  - created_at (TIMESTAMP)
  - updated_at (TIMESTAMP)
  - status (ENUM: aktif, nonaktif)

Tabel: produk
  - id_produk (INT, PK)
  - nama_produk (VARCHAR)
  - kategori (VARCHAR)
  - deskripsi (TEXT)
  - harga (DECIMAL)
  - stok (INT)
  - gambar (VARCHAR)
  - created_at (TIMESTAMP)
  - updated_at (TIMESTAMP)
  - status (ENUM: tersedia, habis, tidak_diproduksi)

Tabel: transaksi
  - id_transaksi (INT, PK)
  - id_user (INT, FK)
  - tanggal_transaksi (TIMESTAMP)
  - total_harga (DECIMAL)
  - metode_pembayaran (ENUM)
  - status (ENUM: selesai, pending, dibatalkan)
  - keterangan (TEXT)

Tabel: detail_transaksi
  - id_detail (INT, PK)
  - id_transaksi (INT, FK)
  - id_produk (INT, FK)
  - jumlah (INT)
  - harga_satuan (DECIMAL)
  - subtotal (DECIMAL)

FILE STRUCTURE
==============

toko-outdoor2/
├── Config/
│   └── koneksi.php                   # Database Connection & Helper Functions
├── Database/
│   └── user_table.sql                # SQL untuk buat database
├── Public/
│   ├── css/
│   │   └── style.css                 # CSS Custom + Bootstrap 5
│   ├── js/
│   │   └── script.js                 # JavaScript untuk interaksi
│   └── img/                          # Folder untuk gambar
├── Views/
│   ├── user_management.php           # CRUD User
│   ├── produk_management.php         # CRUD Produk
│   ├── transaksi_management.php      # CRUD Transaksi
│   ├── laporan.php                   # Laporan Dashboard
│   ├── laporan_print.php             # Print Laporan
│   ├── print_receipt.php             # Print Struk
│   └── get_detail_transaksi.php      # API Detail Transaksi
├── .htaccess                         # Rewrite Rules (Apache)
├── index.php                         # Redirect ke login
├── login.php                         # Halaman Login
├── dashboard.php                     # Dashboard Utama
├── logout.php                        # Logout Handler
├── SETUP.php                         # File ini
└── README.md                         # Dokumentasi

FITUR KEAMANAN
=============

✓ Password Hashing dengan Bcrypt
✓ Session Validation
✓ SQL Injection Prevention (sanitize)
✓ XSS Protection (htmlspecialchars)
✓ CSRF Token Ready
✓ Role-Based Access Control
✓ Soft Delete Protection
✓ Input Validation

PERFORMANCE TIPS
===============

1. Database Indexes sudah dibuat di:
   - idx_user_role
   - idx_produk_kategori
   - idx_transaksi_user
   - idx_transaksi_tanggal

2. Clear browser cache jika ada issue:
   - Ctrl + Shift + Delete

3. Optimize database:
   - OPTIMIZE TABLE user;
   - OPTIMIZE TABLE produk;
   - OPTIMIZE TABLE transaksi;
   - OPTIMIZE TABLE detail_transaksi;

CUSTOMIZATION
============

1. Ubah brand name: Edit sidebar-header di Views
2. Ubah warna: Edit CSS variables di Public/css/style.css
3. Ubah database name: Edit Config/koneksi.php dan Database/user_table.sql
4. Tambah field user: Edit database table dan Views/user_management.php

NEXT STEPS (FUTURE DEVELOPMENT)
==============================

□ Upload gambar produk
□ Dashboard dengan Chart
□ Email notification
□ SMS notification
□ Multi-warehouse support
□ Customer loyalty program
□ Inventory forecasting
□ Mobile app version
□ API REST untuk integrasi
□ Backup automation

SUPPORT & TROUBLESHOOTING
========================

Jika ada masalah:

1. Cek file SETUP.php ini (Anda sedang membacanya!)
2. Baca README.md untuk dokumentasi lengkap
3. Check komentar di setiap file PHP
4. Lihat browser console untuk error messages

EMAIL SUPPORT (Jika ada):
info@toko-outdoor.com

HAPPY CODING! 🚀
================

© 2025 Toko Outdoor - Sistem Informasi Manajemen
Version 1.0 | Educational Project

";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup - Toko Outdoor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            padding: 40px;
            line-height: 1.8;
        }

        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }

        h2 {
            color: #764ba2;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        p, li {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 15px 0;
            overflow-x: auto;
            border-radius: 5px;
        }

        code {
            background-color: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            color: #c7254e;
        }

        .step {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }

        .warning {
            background-color: #fff3cd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .success {
            background-color: #d4edda;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #667eea;
            color: white;
        }

        ul {
            margin-left: 20px;
        }

        .btn-group {
            text-align: center;
            margin-top: 40px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⛰️ TOKO OUTDOOR - Setup Guide</h1>

        <div class="success">
            <strong>✅ Instalasi Berhasil!</strong><br>
            Semua file telah disiapkan. Ikuti langkah-langkah di bawah untuk menyelesaikan setup.
        </div>

        <h2>🔧 Langkah 1: Setup Database</h2>
        <div class="step">
            <p><strong>Option A: Menggunakan phpMyAdmin</strong></p>
            <ol>
                <li>Buka: <code>http://localhost/phpmyadmin</code></li>
                <li>Login dengan akun MySQL Anda</li>
                <li>Klik 'Import'</li>
                <li>Upload file: <code>Database/user_table.sql</code></li>
                <li>Klik 'Go'</li>
            </ol>

            <p style="margin-top: 20px;"><strong>Option B: Menggunakan Command Line</strong></p>
            <pre>mysql -u root -p &lt; Database/user_table.sql</pre>

            <p style="margin-top: 20px;"><strong>Option C: Copy-Paste ke MySQL Console</strong></p>
            <pre>-- Buka file Database/user_table.sql
-- Copy-paste semua isi ke MySQL Console
-- Tekan Enter</pre>
        </div>

        <h2>🔑 Langkah 2: Verifikasi Akun Login</h2>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Akses</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Admin</strong></td>
                    <td>admin</td>
                    <td>password</td>
                    <td>Semua fitur</td>
                </tr>
                <tr>
                    <td><strong>Kasir</strong></td>
                    <td>kasir1</td>
                    <td>password</td>
                    <td>Produk & Transaksi</td>
                </tr>
                <tr>
                    <td><strong>Owner</strong></td>
                    <td>owner</td>
                    <td>password</td>
                    <td>Laporan (Read-Only)</td>
                </tr>
            </tbody>
        </table>

        <div class="warning">
            <strong>⚠️ Catatan:</strong> Password sudah di-hash dengan Bcrypt. Jika login gagal, pastikan database sudah diimport dengan benar.
        </div>

        <h2>🚀 Langkah 3: Akses Aplikasi</h2>
        <div class="step">
            <p><strong>Pastikan folder sudah di-copy ke:</strong></p>
            <pre>C:\xampp\htdocs\toko-outdoor2\</pre>

            <p style="margin-top: 15px;"><strong>Buka browser dan akses:</strong></p>
            <pre>http://localhost/toko-outdoor2</pre>

            <p style="margin-top: 15px;"><strong>Anda akan redirect ke:</strong></p>
            <pre>http://localhost/toko-outdoor2/login.php</pre>
        </div>

        <h2>📋 Langkah 4: Testing Setiap Role</h2>

        <h3>👨‍💼 Admin Login</h3>
        <ul>
            <li>✅ Dashboard dengan statistik lengkap</li>
            <li>✅ Manajemen User (Tambah, Edit, Hapus)</li>
            <li>✅ Manajemen Produk (Tambah, Edit, Hapus)</li>
            <li>✅ Lihat Transaksi</li>
            <li>✅ Laporan Penjualan</li>
        </ul>

        <h3>💳 Kasir Login</h3>
        <ul>
            <li>✅ Dashboard kasir dengan stok real-time</li>
            <li>✅ Lihat daftar produk</li>
            <li>✅ Buat transaksi baru dengan keranjang</li>
            <li>✅ Batalkan transaksi (return stok otomatis)</li>
            <li>❌ Tidak bisa akses manajemen user</li>
        </ul>

        <h3>👔 Owner Login</h3>
        <ul>
            <li>✅ Dashboard overview</li>
            <li>✅ Laporan penjualan (read-only)</li>
            <li>✅ Filter laporan berdasarkan tanggal</li>
            <li>❌ Tidak bisa edit atau delete apapun</li>
        </ul>

        <h2>🔍 Troubleshooting</h2>

        <h3>❌ Error: "Gagal koneksi ke database"</h3>
        <div class="warning">
            <p><strong>Solusi:</strong></p>
            <ul>
                <li>Pastikan MySQL Server sudah running (lihat XAMPP Control Panel)</li>
                <li>Pastikan database <code>db_toko_outdoor</code> sudah dibuat</li>
                <li>Check file <code>Config/koneksi.php</code> - sesuaikan username/password jika berbeda</li>
                <li>Restart Apache & MySQL di XAMPP</li>
            </ul>
        </div>

        <h3>❌ Error: "Access Denied" di halaman</h3>
        <div class="warning">
            <p><strong>Solusi:</strong></p>
            <ul>
                <li>Login dengan akun yang sesuai untuk halaman tersebut</li>
                <li>Cek role Anda: Admin > Kasir > Owner (dari level akses tertinggi)</li>
                <li>Logout dan login ulang</li>
                <li>Clear session browser (Ctrl+Shift+Delete)</li>
            </ul>
        </div>

        <h3>❌ Transaksi tidak menyimpan</h3>
        <div class="warning">
            <p><strong>Solusi:</strong></p>
            <ul>
                <li>Buka browser Developer Tools (F12)</li>
                <li>Lihat tab Console untuk JavaScript error</li>
                <li>Pastikan tabel <code>detail_transaksi</code> sudah dibuat di database</li>
                <li>Coba refresh page dan coba lagi</li>
            </ul>
        </div>

        <h2>📚 Dokumentasi Lengkap</h2>
        <p>Untuk dokumentasi lebih detail, baca file: <code>README.md</code></p>

        <div class="btn-group">
            <a href="login.php" class="btn">🔓 Go to Login</a>
            <a href="README.md" class="btn">📖 Read Documentation</a>
        </div>

        <div class="footer">
            <p>© 2025 Toko Outdoor - Sistem Informasi Manajemen | Version 1.0</p>
            <p>Educational Project | Feel free to modify and redistribute</p>
        </div>
    </div>
</body>
</html>
