<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengecekan jika belum login atau session tidak valid
if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user']) || !isset($_SESSION['login_time'])) {
    http_response_code(401);
    exit('Unauthorized - Session expired');
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
                <td>OTD-<?php echo $transaksi['id_transaksi']; ?></td>
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

        <!-- Tombol Cetak Struk -->
        <hr>
        <div style="text-align: center; margin-top: 20px;">
            <button type="button" class="btn btn-primary" onclick="printReceipt(<?php echo $transaksi['id_transaksi']; ?>)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 5px;">
                    <path d="M6 9V2H18V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 18H4C3.46957 18 2.96086 17.7893 2.58579 17.4142C2.21071 17.0391 2 16.5304 2 16V11C2 10.4696 2.21071 9.96086 2.58579 9.58579C2.96086 9.21071 3.46957 9 4 9H20C20.5304 9 21.0391 9.21071 21.4142 9.58579C21.7893 9.96086 22 10.4696 22 11V16C22 16.5304 21.7893 17.0391 21.4142 17.4142C21.0391 17.7893 20.5304 18 20 18H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18 14H6V22H18V14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Cetak Struk PDF
            </button>
        </div>
    </div>
</div>

<script>
function printReceipt(id) {
    const url = `print_receipt.php?id=${id}`;
    window.open(url, '_blank', 'width=800,height=600');
}
</script>

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
