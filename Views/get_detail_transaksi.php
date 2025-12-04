<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    exit('Unauthorized');
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

<div class="card">
    <div class="card-body">
        <h5>Informasi Transaksi</h5>
        <table class="table table-sm">
            <tr>
                <td><strong>ID Transaksi:</strong></td>
                <td>#<?php echo $transaksi['id_transaksi']; ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal:</strong></td>
                <td><?php echo formatDate($transaksi['tanggal_transaksi']); ?></td>
            </tr>
            <tr>
                <td><strong>Kasir:</strong></td>
                <td><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></td>
            </tr>
            <tr>
                <td><strong>Metode Pembayaran:</strong></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $transaksi['metode_pembayaran'])); ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td><span class="badge <?php echo getStatusBadge($transaksi['status']); ?>"><?php echo ucfirst($transaksi['status']); ?></span></td>
            </tr>
        </table>

        <h5 style="margin-top: 20px;">Detail Produk</h5>
        <?php if (count($details) > 0): ?>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="text-align: right;">Harga</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $detail): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detail['nama_produk']); ?></td>
                            <td style="text-align: right;"><?php echo formatCurrency($detail['harga_satuan']); ?></td>
                            <td style="text-align: center;"><?php echo $detail['jumlah']; ?></td>
                            <td style="text-align: right;"><strong><?php echo formatCurrency($detail['subtotal']); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Tidak ada detail produk</p>
        <?php endif; ?>

        <hr>
        <h5 style="text-align: right;">
            Total: <strong><?php echo formatCurrency($transaksi['total_harga']); ?></strong>
        </h5>

        <?php if (!empty($transaksi['keterangan'])): ?>
            <hr>
            <p><strong>Keterangan:</strong> <?php echo htmlspecialchars($transaksi['keterangan']); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php
function formatCurrency($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

function formatDate($date) {
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y H:i');
}

function getStatusBadge($status) {
    switch ($status) {
        case 'selesai':
            return 'badge-success';
        case 'pending':
            return 'badge-warning';
        case 'dibatalkan':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}
?>
