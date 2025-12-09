<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengecekan jika belum login atau session tidak valid
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user']) || !isset($_SESSION['login_time'])) {
    echo '<script>alert("Session expired. Please login again."); window.close();</script>';
    exit();
}

require_once '../Config/koneksi.php';

$id = sanitize($conn, $_GET['id'] ?? '');

$transaksi = fetch_one($conn, "
    SELECT t.*, u.nama_lengkap 
    FROM transaksi t 
    JOIN user u ON t.id_user = u.id_user 
    WHERE t.id_transaksi = '$id'
");

if (!$transaksi) {
    exit('Transaksi tidak ditemukan');
}

$details = fetch_all($conn, "
    SELECT dt.*, p.nama_produk 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.id_produk = p.id_produk 
    WHERE dt.id_transaksi = '$id'
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - Toko Outdoor</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .receipt {
            max-width: 400px;
            background-color: white;
            padding: 20px;
            margin: 0 auto;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .info {
            font-size: 11px;
            margin-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .items {
            margin-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .item-row {
            font-size: 11px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            width: 40px;
            text-align: center;
        }

        .item-price {
            width: 60px;
            text-align: right;
        }

        .total-section {
            margin-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .total-row {
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .total-amount {
            font-size: 14px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }

        .footer p {
            margin: 3px 0;
        }

        @media print {
            body {
                background-color: white;
            }
            .no-print {
                display: none;
            }
            .receipt {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: none;
            }
        }

        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .no-print button {
            padding: 10px 20px;
            margin: 0 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: 500;
        }

        .btn-print {
            background-color: #6c757d;
        }

        .btn-print:hover {
            background-color: #5a6268;
        }

        .btn-close-window {
            background-color: #dc3545;
        }

        .btn-close-window:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php
    require_once '../Config/koneksi.php';

    $id = sanitize($conn, $_GET['id'] ?? '');

    $transaksi = fetch_one($conn, "
        SELECT t.*, u.nama_lengkap 
        FROM transaksi t 
        JOIN user u ON t.id_user = u.id_user 
        WHERE t.id_transaksi = '$id'
    ");

    if (!$transaksi) {
        exit('Transaksi tidak ditemukan');
    }

    $details = fetch_all($conn, "
        SELECT dt.*, p.nama_produk 
        FROM detail_transaksi dt 
        JOIN produk p ON dt.id_produk = p.id_produk 
        WHERE dt.id_transaksi = '$id'
    ");
    ?>

    <div class="receipt">
        <div class="header">
            <h2>TOKO OUTDOOR</h2>
        </div>

        <div class="info">
            <div class="info-row">
                <span>No. Transaksi:</span>
                <span>OTD-<?php echo $transaksi['id_transaksi']; ?></span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?php echo formatDate($transaksi['tanggal_transaksi']); ?></span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></span>
            </div>
            <div class="info-row">
                <span>Metode:</span>
                <span><?php echo ucfirst(str_replace('_', ' ', $transaksi['metode_pembayaran'])); ?></span>
            </div>
        </div>

        <div class="items">
            <div class="item-row" style="font-weight: bold; margin-bottom: 8px;">
                <span class="item-name">Produk</span>
                <span class="item-qty">Qty</span>
                <span class="item-price">Harga</span>
            </div>

            <?php foreach ($details as $detail): ?>
                <div class="item-row">
                    <span class="item-name"><?php echo substr(htmlspecialchars($detail['nama_produk']), 0, 15); ?></span>
                    <span class="item-qty"><?php echo $detail['jumlah']; ?></span>
                    <span class="item-price"><?php echo formatCurrencyShort($detail['subtotal']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="total-section">
            <div class="total-amount">
                <span>TOTAL</span>
                <span><?php echo formatCurrency($transaksi['total_harga']); ?></span>
            </div>
        </div>

        <div class="footer">
            <p>Terima kasih telah berbelanja</p>
            <p style="font-size: 10px;">Outdoor & Adventure</p>
            <p style="margin-top: 5px;">Cetak: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak Struk</button>
        <button class="btn-close-window" onclick="window.close()">Tutup</button>
    </div>

    <?php
    function formatCurrency($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    function formatCurrencyShort($value) {
        return number_format($value / 1000, 0) . 'K';
    }

    function formatDate($date) {
        $dateTime = new DateTime($date);
        return $dateTime->format('d/m/Y H:i');
    }
    ?>
</body>
</html>
