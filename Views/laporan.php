<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengecekan jika belum login atau session tidak valid
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user']) || !isset($_SESSION['login_time'])) {
    session_unset();
    session_destroy();
    header('Location: login.php', true, 303);
    exit();
}

// Hanya admin dan owner yang bisa akses halaman ini
if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: dashboard.php', true, 303);
    exit();
}

require_once '../Config/koneksi.php';

$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

// Ambil data untuk laporan
$total_transaksi = count_rows($conn, "SELECT * FROM transaksi WHERE status = 'selesai'");
$total_pendapatan = fetch_one($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE status = 'selesai'");
$revenue = $total_pendapatan['total'] ?? 0;

$total_user = count_rows($conn, "SELECT * FROM user");
$total_produk = count_rows($conn, "SELECT * FROM produk");
$produk_habis = count_rows($conn, "SELECT * FROM produk WHERE stok = 0");

// Hitung pendapatan per bulan
$monthly_revenue = fetch_all($conn, "
    SELECT DATE_FORMAT(tanggal_transaksi, '%Y-%m') as bulan, SUM(total_harga) as total 
    FROM transaksi 
    WHERE status = 'selesai' 
    GROUP BY DATE_FORMAT(tanggal_transaksi, '%Y-%m')
    ORDER BY bulan DESC
    LIMIT 12
");

// Ambil 10 produk terlaris
$produk_terlaris = fetch_all($conn, "
    SELECT p.id_produk, p.nama_produk, p.kategori, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_penjualan
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
    WHERE t.status = 'selesai'
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 10
");

// Ambil 10 transaksi terakhir
$transaksi_terakhir = fetch_all($conn, "
    SELECT t.*, u.nama_lengkap 
    FROM transaksi t 
    JOIN user u ON t.id_user = u.id_user 
    WHERE t.status = 'selesai'
    ORDER BY t.id_transaksi DESC 
    LIMIT 10
");

// Filter tanggal untuk laporan
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Hitung filter laporan
$filter_transaksi = count_rows($conn, "
    SELECT * FROM transaksi 
    WHERE status = 'selesai' 
    AND DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
");

$filter_revenue = fetch_one($conn, "
    SELECT SUM(total_harga) as total FROM transaksi 
    WHERE status = 'selesai' 
    AND DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
");
$filter_revenue = $filter_revenue['total'] ?? 0;

// Helper function untuk format currency
function formatCurrency($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

// Helper function untuk format date
function formatDate($date) {
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y H:i');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Toko Outdoor</title>
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
                <h2>Toko Outdoor</h2>
                <p>Sistem Informasi</p>
            </div>

            <ul class="sidebar-menu">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <?php if ($role === 'admin'): ?>
                    <li><a href="user_management.php">Manajemen User</a></li>
                    <li><a href="produk_management.php">Produk</a></li>
                    <li><a href="transaksi_management.php">Transaksi</a></li>
                <?php endif; ?>
                <li><a href="laporan.php" class="active">Laporan</a></li>
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
                <h1>Laporan</h1>
                <div class="header-user">
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></p>
                        <p class="user-role"><?php echo strtoupper($role); ?></p>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="content">
                <div class="page-title">Laporan Sistem</div>
                <p class="page-subtitle">Analisis data penjualan dan manajemen toko outdoor</p>

                <!-- FILTER TANGGAL -->
                <div class="card mb-3">
                    <div class="card-header">Filter Laporan</div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary w-100" onclick="printReport()">Cetak</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- STATISTIK UMUM -->
                <div class="stats-container">
                    <div class="stat-card primary">
                        <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="18" height="14" rx="1" stroke="currentColor" stroke-width="2"/>
                            <path d="M3 9H21" stroke="currentColor" stroke-width="2"/>
                            <circle cx="8" cy="13" r="1.5" fill="currentColor"/>
                            <circle cx="13" cy="13" r="1.5" fill="currentColor"/>
                            <circle cx="18" cy="13" r="1.5" fill="currentColor"/>
                        </svg>
                        <div class="stat-number"><?php echo $filter_transaksi; ?></div>
                        <div class="stat-label">Transaksi (Filter)</div>
                    </div>
                    <div class="stat-card success">
                        <svg class="stat-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <div class="stat-number"><?php echo formatCurrency($filter_revenue); ?></div>
                        <div class="stat-label">Pendapatan (Filter)</div>
                    </div>
                    <div class="stat-card info">
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
                            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
                            <line x1="12" y1="6" x2="12" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <div class="stat-number"><?php echo $produk_habis; ?></div>
                        <div class="stat-label">Stok Habis</div>
                    </div>
                </div>

                <!-- INFORMASI GENERAL -->
                <div class="card mb-3">
                    <div class="card-header">
                        Ringkasan Keseluruhan
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>Total Transaksi:</strong> <?php echo $total_transaksi; ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Total Pendapatan:</strong> <?php echo formatCurrency($revenue); ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Total User:</strong> <?php echo $total_user; ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Total Produk:</strong> <?php echo $total_produk; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUK TERLARIS -->
                <div class="card mb-3">
                    <div class="card-header">10 Produk Terlaris</div>
                    <div class="card-body">
                        <?php if (count($produk_terlaris) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center"><strong>No</strong></th>
                                            <th width="30%"><strong>Nama Produk</strong></th>
                                            <th width="20%"><strong>Kategori</strong></th>
                                            <th width="20%"><strong>Terjual</strong></th>
                                            <th width="25%"><strong>Total Penjualan</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produk_terlaris as $index => $item): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td class="align-middle"><strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong></td>
                                                <td class="align-middle"><?php echo htmlspecialchars($item['kategori']); ?></td>
                                                <td class="align-middle"><?php echo $item['total_terjual']; ?> pcs</td>
                                                <td class="align-middle"><strong><?php echo formatCurrency($item['total_penjualan']); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada data produk terjual</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TRANSAKSI TERAKHIR -->
                <div class="card mb-3">
                    <div class="card-header">10 Transaksi Terakhir</div>
                    <div class="card-body">
                        <?php if (count($transaksi_terakhir) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center"><strong>No</strong></th>
                                            <th width="20%"><strong>Tanggal</strong></th>
                                            <th width="25%"><strong>Kasir</strong></th>
                                            <th width="25%"><strong>Total</strong></th>
                                            <th width="25%"><strong>Metode Pembayaran</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transaksi_terakhir as $index => $item): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td class="align-middle"><?php echo formatDate($item['tanggal_transaksi']); ?></td>
                                                <td class="align-middle"><?php echo htmlspecialchars($item['nama_lengkap']); ?></td>
                                                <td class="align-middle"><strong><?php echo formatCurrency($item['total_harga']); ?></strong></td>
                                                <td class="align-middle"><?php echo ucfirst(str_replace('_', ' ', $item['metode_pembayaran'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada transaksi</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/script.js"></script>
    <script>
        function printReport() {
            const start_date = document.getElementById('start_date').value;
            const end_date = document.getElementById('end_date').value;
            const url = `laporan_print.php?start_date=${start_date}&end_date=${end_date}`;
            const printWindow = window.open(url, '', 'width=1000,height=800');
            printWindow.focus();
        }
    </script>

</body>
</html>
