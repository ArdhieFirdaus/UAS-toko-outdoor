<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengecekan jika belum login atau session tidak valid
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user']) || !isset($_SESSION['login_time'])) {
    // Clear session jika ada session rusak
    session_unset();
    session_destroy();
    header('Location: login.php', true, 303);
    exit();
}

require_once '../Config/koneksi.php';

    // Ambil informasi user
    $id_user = $_SESSION['id_user'];
    $username = $_SESSION['username'];
    $nama_lengkap = $_SESSION['nama_lengkap'];
    $role = $_SESSION['role'];

    // Fungsi untuk mendapatkan akses menu berdasarkan role
    function getMenuByRole($role) {
        $menus = array(
            'admin' => array(
                'dashboard' => array('label' => 'Dashboard', 'url' => 'dashboard.php', 'active' => true),
                'user' => array('label' => 'Manajemen User', 'url' => 'user_management.php', 'active' => false),
                'produk' => array('label' => 'Produk', 'url' => 'produk_management.php', 'active' => false),
                'transaksi' => array('label' => 'Transaksi', 'url' => 'transaksi_management.php', 'active' => false),
                'laporan' => array('label' => 'Laporan', 'url' => 'laporan.php', 'active' => false),
            ),
            'kasir' => array(
                'dashboard' => array('label' => 'Dashboard', 'url' => 'dashboard.php', 'active' => true),
                'produk' => array('label' => 'Produk', 'url' => 'produk_management.php', 'active' => false),
                'transaksi' => array('label' => 'Transaksi', 'url' => 'transaksi_management.php', 'active' => false),
            ),
            'owner' => array(
                'dashboard' => array('label' => 'Dashboard', 'url' => 'dashboard.php', 'active' => true),
                'laporan' => array('label' => 'Laporan', 'url' => 'laporan.php', 'active' => false),
            )
        );

        return $menus[$role] ?? $menus['kasir'];
    }

    // Hitung statistik
    $total_user = count_rows($conn, "SELECT * FROM user");
    $total_produk = count_rows($conn, "SELECT * FROM produk");
    $total_transaksi = count_rows($conn, "SELECT * FROM transaksi");
    
    // Hitung total pendapatan hari ini
    $today = date('Y-m-d');
    $total_revenue = fetch_one($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE DATE(tanggal_transaksi) = '$today' AND status = 'selesai'");
    $revenue_today = $total_revenue['total'] ?? 0;

    // Hitung stok produk yang habis
    $stok_habis = count_rows($conn, "SELECT * FROM produk WHERE stok = 0");

    $menus = getMenuByRole($role);

    // Helper function untuk format currency
    function formatCurrency($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Outdoor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-header">
                <svg width="32" height="32" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 10px;">
                    <path d="M10 35L20 20L30 28L40 15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="15" cy="17" r="2" fill="white"/>
                </svg>
                <h2>Arnathea Outdoor</h2>
                <p>Sistem Informasi</p>
            </div>

            <ul class="sidebar-menu">
                <?php foreach ($menus as $menu): ?>
                    <li>
                        <a href="<?php echo $menu['url']; ?>" class="<?php echo $menu['active'] ? 'active' : ''; ?>">
                            <?php echo $menu['label']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="sidebar-footer">
                <button onclick="logout()" title="Logout" class="btn-logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3H5C3.895 3 3 3.895 3 5V19C3 20.105 3.895 21 5 21H10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17 16L21 12M21 12L17 8M21 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Logout
                </button>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <main>
            <!-- HEADER -->
            <div class="header">
                <h1></h1>
                <div class="header-user">
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></p>
                        <p class="user-role"><?php echo strtoupper($role); ?></p>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="content">
                <div class="page-title">Selamat Datang!</div>
                <p class="page-subtitle">Dashboard Sistem Informasi Arnathea Outdoor</p>

                <?php if ($role === 'admin'): ?>
                    <!-- ADMIN DASHBOARD -->
                    <div class="stats-container">
                        <div class="stat-card primary">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 20C4 16.134 7.582 13 12 13C16.418 13 20 16.134 20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_user; ?></div>
                            <div class="stat-label">Total User</div>
                        </div>
                        <div class="stat-card success">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7H19L18 18C18 19.105 17.105 20 16 20H8C6.895 20 6 19.105 6 18L5 7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M10 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_produk; ?></div>
                            <div class="stat-label">Total Produk</div>
                        </div>
                        <div class="stat-card warning">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="14" rx="1" stroke="currentColor" stroke-width="2"/>
                                <path d="M3 9H21" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8" cy="13" r="1.5" fill="currentColor"/>
                                <circle cx="13" cy="13" r="1.5" fill="currentColor"/>
                                <circle cx="18" cy="13" r="1.5" fill="currentColor"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_transaksi; ?></div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                        <div class="stat-card danger">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                                <line x1="12" y1="6" x2="12" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo $stok_habis; ?></div>
                            <div class="stat-label">Stok Habis</div>
                        </div>
                    </div>

                    <!-- QUICK ACTIONS ADMIN -->
                    <div class="card mb-20">
                        <div class="card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                                <line x1="12" y1="6" x2="12" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Aksi Cepat
                        </div>
                        <div class="card-body">
                            <a href="user_management.php" class="btn btn-primary">Kelola User</a>
                            <a href="produk_management.php" class="btn btn-success">Kelola Produk</a>
                            <a href="transaksi_management.php" class="btn btn-info">Lihat Transaksi</a>
                            <a href="laporan.php" class="btn btn-warning">Lihat Laporan</a>
                        </div>
                    </div>

                    <!-- INFORMASI PENTING -->
                    <div class="card">
                        <div class="card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                <text x="12" y="16" font-size="14" font-weight="bold" text-anchor="middle" fill="currentColor">i</text>
                                </circle>
                            </svg>
                            Informasi Penting
                        </div>
                        <div class="card-body">
                            <p><strong>Total Pendapatan Hari Ini:</strong> <?php echo formatCurrency($revenue_today); ?></p>
                            <p><strong>Produk Stok Habis:</strong> <?php echo $stok_habis; ?> produk</p>
                            <p><strong>Total Transaksi:</strong> <?php echo $total_transaksi; ?> transaksi</p>
                        </div>
                    </div>

                <?php elseif ($role === 'kasir'): ?>
                    <!-- KASIR DASHBOARD -->
                    <div class="stats-container">
                        <div class="stat-card primary">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7H19L18 18C18 19.105 17.105 20 16 20H8C6.895 20 6 19.105 6 18L5 7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M10 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_produk; ?></div>
                            <div class="stat-label">Total Produk</div>
                        </div>
                        <div class="stat-card success">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="14" rx="1" stroke="currentColor" stroke-width="2"/>
                                <path d="M3 9H21" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8" cy="13" r="1.5" fill="currentColor"/>
                                <circle cx="13" cy="13" r="1.5" fill="currentColor"/>
                                <circle cx="18" cy="13" r="1.5" fill="currentColor"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_transaksi; ?></div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                        <div class="stat-card warning">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo formatCurrency($revenue_today); ?></div>
                            <div class="stat-label">Pendapatan Hari Ini</div>
                        </div>
                    </div>

                    <!-- QUICK ACTIONS KASIR -->
                    <div class="card mb-20">
                        <div class="card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                                <line x1="12" y1="6" x2="12" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Aksi Cepat
                        </div>
                        <div class="card-body">
                            <a href="produk_management.php" class="btn btn-primary">Lihat Produk</a>
                            <a href="transaksi_management.php" class="btn btn-success">Kelola Transaksi</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                <text x="12" y="16" font-size="14" font-weight="bold" text-anchor="middle" fill="currentColor">i</text>
                            </circle>
                            </svg>
                            Informasi
                        </div>
                        <div class="card-body">
                            <p>Selamat datang, <strong><?php echo htmlspecialchars($nama_lengkap); ?></strong>!</p>
                            <p>Anda dapat mengelola transaksi dan melihat data produk di menu di atas.</p>
                        </div>
                    </div>

                <?php elseif ($role === 'owner'): ?>
                    <!-- OWNER DASHBOARD -->
                    <div class="stats-container">
                        <div class="stat-card primary">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="14" rx="1" stroke="currentColor" stroke-width="2"/>
                                <path d="M3 9H21" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8" cy="13" r="1.5" fill="currentColor"/>
                                <circle cx="13" cy="13" r="1.5" fill="currentColor"/>
                                <circle cx="18" cy="13" r="1.5" fill="currentColor"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_transaksi; ?></div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                        <div class="stat-card success">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo formatCurrency($revenue_today); ?></div>
                            <div class="stat-label">Pendapatan Hari Ini</div>
                        </div>
                        <div class="stat-card warning">
                            <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7H19L18 18C18 19.105 17.105 20 16 20H8C6.895 20 6 19.105 6 18L5 7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M10 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="stat-number"><?php echo $total_produk; ?></div>
                            <div class="stat-label">Total Produk</div>
                        </div>
                    </div>

                    <!-- QUICK ACTIONS OWNER -->
                    <div class="card mb-20">
                        <div class="card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                                <line x1="12" y1="6" x2="12" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Aksi Cepat
                        </div>
                        <div class="card-body">
                            <a href="laporan.php" class="btn btn-primary">Lihat Laporan</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                <path d="M4 3H20C20.552 3 21 3.448 21 4V20C21 20.552 20.552 21 20 21H4C3.448 21 3 20.552 3 20V4C3 3.448 3.448 3 4 3Z" stroke="currentColor" stroke-width="2"/>
                                <line x1="7" y1="7" x2="17" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="7" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="7" y1="15" x2="13" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Ringkasan
                        </div>
                        <div class="card-body">
                            <p>Sebagai pemilik, Anda dapat melihat laporan lengkap sistem di menu Laporan.</p>
                            <p><strong>Total Transaksi:</strong> <?php echo $total_transaksi; ?></p>
                            <p><strong>Pendapatan Hari Ini:</strong> <?php echo formatCurrency($revenue_today); ?></p>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/script.js"></script>
</body>
</html>
