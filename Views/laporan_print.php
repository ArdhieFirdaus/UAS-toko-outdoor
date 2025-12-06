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

require_once '../Config/koneksi.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Ambil data untuk laporan
$transaksi_count = count_rows($conn, "
    SELECT * FROM transaksi 
    WHERE status = 'selesai' 
    AND DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
");

$revenue = fetch_one($conn, "
    SELECT SUM(total_harga) as total FROM transaksi 
    WHERE status = 'selesai' 
    AND DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
    ");
    $total_revenue = $revenue['total'] ?? 0;

    $produk_terlaris = fetch_all($conn, "
        SELECT p.id_produk, p.nama_produk, p.kategori, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_penjualan
        FROM detail_transaksi dt
        JOIN produk p ON dt.id_produk = p.id_produk
        JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status = 'selesai'
        AND DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
        GROUP BY p.id_produk
        ORDER BY total_terjual DESC
        LIMIT 10
    ");

    $transaksi_list = fetch_all($conn, "
        SELECT t.*, u.nama_lengkap 
        FROM transaksi t 
        JOIN user u ON t.id_user = u.id_user 
        WHERE t.status = 'selesai'
        AND DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'
        ORDER BY t.tanggal_transaksi DESC
    ");
    
    function formatCurrency($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
    
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
    <title>Cetak Laporan - Toko Outdoor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
            .table {
                font-size: 12px;
            }
        }
        .header-print {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .footer-print {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
        <div class="header-print">
            <h1>TOKO OUTDOOR</h1>
            <h3>Laporan Penjualan</h3>
            <p>Periode: <?php echo formatDate($start_date); ?> - <?php echo formatDate($end_date); ?></p>
            <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <!-- RINGKASAN -->
        <h4>RINGKASAN</h4>
        <table class="table table-bordered">
            <tr>
                <td><strong>Total Transaksi:</strong></td>
                <td><?php echo $transaksi_count; ?></td>
            </tr>
            <tr>
                <td><strong>Total Pendapatan:</strong></td>
                <td><?php echo formatCurrency($total_revenue); ?></td>
            </tr>
        </table>

        <!-- PRODUK TERLARIS -->
        <h4 style="margin-top: 30px;">PRODUK TERLARIS</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th style="text-align: right;">Terjual</th>
                    <th style="text-align: right;">Penjualan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($produk_terlaris) > 0): ?>
                    <?php foreach ($produk_terlaris as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                            <td><?php echo htmlspecialchars($item['kategori']); ?></td>
                            <td style="text-align: right;"><?php echo $item['total_terjual']; ?> pcs</td>
                            <td style="text-align: right;"><?php echo formatCurrency($item['total_penjualan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Tidak ada data</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- DETAIL TRANSAKSI -->
        <h4 style="margin-top: 30px;">DETAIL TRANSAKSI</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th style="text-align: right;">Total</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transaksi_list) > 0): ?>
                    <?php foreach ($transaksi_list as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo formatDate($item['tanggal_transaksi']); ?></td>
                            <td><?php echo htmlspecialchars($item['nama_lengkap']); ?></td>
                            <td style="text-align: right;"><?php echo formatCurrency($item['total_harga']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $item['metode_pembayaran'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Tidak ada transaksi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer-print">
            <p>&copy; 2025 Toko Outdoor - Sistem Informasi Manajemen</p>
            <p class="no-print">
                <button onclick="window.print()" class="btn btn-primary">Cetak</button>
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
