<?php
// Enable error reporting untuk debugging (nonaktifkan di production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error di output
ini_set('log_errors', 1); // Log error ke file

ob_start(); // Start output buffering to prevent any accidental output

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

// Hanya admin dan kasir yang bisa akses halaman ini
if (!in_array($_SESSION['role'], ['admin', 'kasir'])) {
    header('Location: dashboard.php', true, 303);
    exit();
}

require_once '../Config/koneksi.php';

$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];
$is_admin = ($role === 'admin');
$is_kasir = ($role === 'kasir');

// Handle AJAX Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean(); // Clear any output buffer before sending JSON
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'];

        if ($action === 'add' && $is_kasir) {
            $total_harga = floatval($_POST['total_harga'] ?? 0);
            $metode_pembayaran = sanitize($conn, $_POST['metode_pembayaran'] ?? 'tunai');
            $keterangan = sanitize($conn, $_POST['keterangan'] ?? '');

            if ($total_harga <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Total harga harus lebih dari 0!'
                ]);
                exit();
            }

            // Decode products JSON
            $products_json = $_POST['products'] ?? '[]';
            $products = json_decode($products_json, true);
            
            if (!is_array($products) || empty($products)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Data produk tidak valid!'
                ]);
                exit();
            }

            // Insert transaksi
            $query = "INSERT INTO transaksi (id_user, total_harga, metode_pembayaran, keterangan, status) 
                     VALUES ('$id_user', '$total_harga', '$metode_pembayaran', '$keterangan', 'selesai')";
            
            $result = execute_action($conn, $query);
            if ($result['success']) {
                $id_transaksi = $result['insert_id'];

                // Insert detail transaksi dari POST
                foreach ($products as $product) {
                    $id_produk = intval($product['id_produk']);
                    $jumlah = intval($product['jumlah']);
                    $harga_satuan = floatval($product['harga_satuan']);
                    $subtotal = floatval($product['subtotal']);

                    if ($jumlah > 0) {
                        $detail_query = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal) 
                                       VALUES ('$id_transaksi', '$id_produk', '$jumlah', '$harga_satuan', '$subtotal')";
                        execute_action($conn, $detail_query);

                        // Kurangi stok produk
                        execute_action($conn, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk = '$id_produk'");
                    }
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan!',
                    'id_transaksi' => $id_transaksi
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi: ' . $result['message']
                ]);
            }
        } else if ($action === 'cancel' && $is_kasir) {
            $id_transaksi = sanitize($conn, $_POST['id_transaksi'] ?? '');

            // Cek apakah transaksi milik kasir saat ini atau admin
            $transaksi = fetch_one($conn, "SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi'");
            if (!$transaksi || ($transaksi['id_user'] != $id_user && !$is_admin)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk membatalkan transaksi ini'
                ]);
                exit();
            }

            // Update status transaksi
            $query = "UPDATE transaksi SET status = 'dibatalkan' WHERE id_transaksi = '$id_transaksi'";
            $result = execute_action($conn, $query);

            if ($result['success']) {
                // Return stok produk
                $details = fetch_all($conn, "SELECT * FROM detail_transaksi WHERE id_transaksi = '$id_transaksi'");
                foreach ($details as $detail) {
                    execute_action($conn, "UPDATE produk SET stok = stok + {$detail['jumlah']} WHERE id_produk = '{$detail['id_produk']}'");
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Transaksi berhasil dibatalkan dan stok dikembalikan!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal membatalkan transaksi'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Action tidak valid atau Anda tidak memiliki akses'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit();
}

    // Ambil data transaksi dengan info kasir
    if ($is_kasir) {
        $transaksi = fetch_all($conn, "
            SELECT t.*, u.nama_lengkap as nama_kasir 
            FROM transaksi t 
            JOIN user u ON t.id_user = u.id_user 
            WHERE t.id_user = '$id_user'
            ORDER BY t.id_transaksi DESC
        ");
    } else {
        $transaksi = fetch_all($conn, "
            SELECT t.*, u.nama_lengkap as nama_kasir 
            FROM transaksi t 
            JOIN user u ON t.id_user = u.id_user 
            ORDER BY t.id_transaksi DESC
        ");
    }

    // Ambil data produk untuk dropdown
    $produk_list = fetch_all($conn, "SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk ASC");
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - Toko Outdoor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/css/style.css">
    <style>
        .detail-item {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .detail-item .remove-btn {
            float: right;
            cursor: pointer;
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
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
                <li><a href="../dashboard.php">Dashboard</a></li>
                <?php if ($role === 'admin'): ?>
                    <li><a href="user_management.php">Manajemen User</a></li>
                <?php endif; ?>
                <li><a href="produk_management.php">Produk</a></li>
                <li><a href="transaksi_management.php" class="active">Transaksi</a></li>
                <?php if ($role !== 'kasir'): ?>
                    <li><a href="laporan.php">Laporan</a></li>
                <?php endif; ?>
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
                <div class="page-title">Manajemen Transaksi</div>
                <p class="page-subtitle">Kelola transaksi penjualan produk outdoor</p>

                <!-- ALERT PLACEHOLDER -->
                <div id="alert-container"></div>

                <!-- BUTTON TAMBAH TRANSAKSI (Hanya Kasir) -->
                <?php if ($is_kasir): ?>
                    <div style="margin-bottom: 20px;">
                        <button type="button" class="btn btn-primary" onclick="openAddTransaksiModal()">
                            Transaksi Baru
                        </button>
                    </div>
                <?php endif; ?>

                <!-- TABEL TRANSAKSI -->
                <div class="card">
                    <div class="card-header">Daftar Transaksi</div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari transaksi..." onkeyup="filterTable('searchInput', 'transaksiTable')">
                        </div>

                        <?php if (count($transaksi) > 0): ?>
                            <div class="table-responsive">
                                <table class="table" id="transaksiTable">
                                    <thead>
                                        <tr>
                                            <th style="cursor: pointer;" onclick="sortTable('transaksiTable', 0)">No</th>
                                            <th style="cursor: pointer;" onclick="sortTable('transaksiTable', 1)">Tanggal</th>
                                            <th>Kasir</th>
                                            <th style="cursor: pointer;" onclick="sortTable('transaksiTable', 3)">Total</th>
                                            <th>Metode Pembayaran</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transaksi as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo formatDate($item['tanggal_transaksi']); ?></td>
                                                <td><?php echo htmlspecialchars($item['nama_kasir']); ?></td>
                                                <td><strong><?php echo formatCurrency($item['total_harga']); ?></strong></td>
                                                <td><?php echo ucfirst(str_replace('_', ' ', $item['metode_pembayaran'])); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getStatusBadge($item['status']); ?>">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-info btn-action" onclick="viewDetail(<?php echo $item['id_transaksi']; ?>)">Detail</button>
                                                    <?php if ($is_kasir && $item['status'] === 'selesai'): ?>
                                                        <button class="btn btn-danger btn-action" onclick="cancelTransaksi(<?php echo $item['id_transaksi']; ?>)">Batalkan</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <p>📭 Belum ada transaksi yang tercatat</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL TRANSAKSI BARU -->
    <div class="modal" id="transaksiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🛒 Transaksi Baru</h5>
                    <button type="button" class="btn-close" onclick="closeModal('transaksiModal')"></button>
                </div>
                <form id="transaksiForm" method="POST" onsubmit="handleTransaksiSubmit(event)">
                    <input type="hidden" name="action" value="add">

                    <div class="modal-body">
                        <!-- FORM PRODUK -->
                        <div class="card mb-3">
                            <div class="card-header">Tambah Produk</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_produk" class="form-label">Produk</label>
                                            <select class="form-control" id="id_produk" onchange="updateProdukInfo()">
                                                <option value="">-- Pilih Produk --</option>
                                                <?php foreach ($produk_list as $p): ?>
                                                    <option value="<?php echo $p['id_produk']; ?>" data-harga="<?php echo $p['harga']; ?>" data-stok="<?php echo $p['stok']; ?>">
                                                        <?php echo htmlspecialchars($p['nama_produk']); ?> (Stok: <?php echo $p['stok']; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="jumlah_item" class="form-label">Jumlah</label>
                                            <input type="number" class="form-control" id="jumlah_item" min="1" value="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Harga Satuan</label>
                                            <input type="text" class="form-control" id="harga_satuan" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Subtotal</label>
                                            <input type="text" class="form-control" id="subtotal_item" readonly>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-success" onclick="addItemToCart()">Tambah Ke Keranjang</button>
                            </div>
                        </div>

                        <!-- DETAIL ITEM -->
                        <div class="card mb-3">
                            <div class="card-header">Keranjang Belanja</div>
                            <div class="card-body" id="cartItems">
                                <p class="text-muted">Belum ada item yang ditambahkan</p>
                            </div>
                        </div>

                        <!-- TOTAL DAN PEMBAYARAN -->
                        <div class="card">
                            <div class="card-header">Total Transaksi</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Grand Total: <span id="grand_total_display">Rp 0</span></h5>
                                        <input type="hidden" id="total_harga" name="total_harga" value="0">
                                        <input type="hidden" id="productsData" name="products" value="[]">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                                            <select class="form-control" id="metode_pembayaran" name="metode_pembayaran" required>
                                                <option value="tunai">Tunai</option>
                                                <option value="kartu_kredit">Kartu Kredit</option>
                                                <option value="transfer_bank">Transfer Bank</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" placeholder="Masukkan keterangan transaksi jika ada"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('transaksiModal')">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL TRANSAKSI -->
    <div class="modal" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" onclick="closeModal('detailModal')"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('detailModal')">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/script.js"></script>
    <script>
        let cartItems = [];

        function openAddTransaksiModal() {
            cartItems = [];
            document.getElementById('transaksiForm').reset();
            document.getElementById('cartItems').innerHTML = '<p class="text-muted">Belum ada item yang ditambahkan</p>';
            updateGrandTotal();
            openModal('transaksiModal');
        }

        function updateProdukInfo() {
            const select = document.getElementById('id_produk');
            const selectedOption = select.options[select.selectedIndex];
            const harga = selectedOption.getAttribute('data-harga') || 0;
            
            document.getElementById('harga_satuan').value = formatCurrency(harga);
            document.getElementById('jumlah_item').value = 1;
            updateSubtotal();
        }

        function updateSubtotal() {
            const harga = document.getElementById('id_produk').options[document.getElementById('id_produk').selectedIndex].getAttribute('data-harga') || 0;
            const jumlah = parseInt(document.getElementById('jumlah_item').value) || 0;
            const subtotal = parseFloat(harga) * jumlah;
            
            document.getElementById('subtotal_item').value = formatCurrency(subtotal);
        }

        document.getElementById('jumlah_item').addEventListener('input', updateSubtotal);

        function addItemToCart() {
            const select = document.getElementById('id_produk');
            const id_produk = select.value;
            
            if (!id_produk) {
                showAlert('Pilih produk terlebih dahulu!', 'warning');
                return;
            }

            const selectedOption = select.options[select.selectedIndex];
            const nama_produk = selectedOption.text;
            const harga_satuan = parseFloat(selectedOption.getAttribute('data-harga'));
            const stok = parseInt(selectedOption.getAttribute('data-stok'));
            const jumlah = parseInt(document.getElementById('jumlah_item').value) || 1;

            if (jumlah > stok) {
                showAlert('Jumlah melebihi stok tersedia!', 'warning');
                return;
            }

            const subtotal = harga_satuan * jumlah;

            // Cek apakah item sudah ada di cart
            const existingItem = cartItems.find(item => item.id_produk === parseInt(id_produk));
            
            if (existingItem) {
                if (existingItem.jumlah + jumlah > stok) {
                    showAlert('Total jumlah melebihi stok tersedia!', 'warning');
                    return;
                }
                existingItem.jumlah += jumlah;
                existingItem.subtotal = existingItem.harga_satuan * existingItem.jumlah;
            } else {
                cartItems.push({
                    id_produk: parseInt(id_produk),
                    nama_produk: nama_produk,
                    harga_satuan: harga_satuan,
                    jumlah: jumlah,
                    subtotal: subtotal
                });
            }

            renderCart();
            updateGrandTotal();

            // Reset form
            document.getElementById('id_produk').value = '';
            document.getElementById('harga_satuan').value = '';
            document.getElementById('jumlah_item').value = 1;
            document.getElementById('subtotal_item').value = '';
        }

        function removeFromCart(index) {
            cartItems.splice(index, 1);
            renderCart();
            updateGrandTotal();
        }

        function renderCart() {
            const cartContainer = document.getElementById('cartItems');
            
            if (cartItems.length === 0) {
                cartContainer.innerHTML = '<p class="text-muted">Belum ada item yang ditambahkan</p>';
                return;
            }

            let html = '';
            cartItems.forEach((item, index) => {
                html += `
                    <div class="detail-item">
                        <div class="remove-btn" onclick="removeFromCart(${index})">✕</div>
                        <p><strong>${item.nama_produk}</strong></p>
                        <p>Harga: ${formatCurrency(item.harga_satuan)} × ${item.jumlah} = <strong>${formatCurrency(item.subtotal)}</strong></p>
                    </div>
                `;
            });

            cartContainer.innerHTML = html;
        }

        function updateGrandTotal() {
            const total = cartItems.reduce((sum, item) => sum + item.subtotal, 0);
            document.getElementById('total_harga').value = total;
            document.getElementById('grand_total_display').textContent = formatCurrency(total);
            
            // Set products data as JSON string
            const productsInput = document.getElementById('productsData');
            if (productsInput) {
                productsInput.value = JSON.stringify(cartItems);
            }
            
            // Also set in form directly untuk backup
            const form = document.getElementById('transaksiForm');
            if (form) {
                let productsField = form.querySelector('input[name="products"]');
                if (!productsField) {
                    productsField = document.createElement('input');
                    productsField.type = 'hidden';
                    productsField.name = 'products';
                    form.appendChild(productsField);
                }
                productsField.value = JSON.stringify(cartItems);
            }
        }

        function handleTransaksiSubmit(event) {
            event.preventDefault();

            if (cartItems.length === 0) {
                showAlert('Tambahkan minimal 1 produk ke keranjang!', 'warning');
                return;
            }

            // Buat FormData dari form
            const form = document.getElementById('transaksiForm');
            const formData = new FormData(form);
            
            // Pastikan products di-set dengan benar
            formData.set('products', JSON.stringify(cartItems));
            
            // Debug: Log semua form data
            console.log('=== Form Data Debug ===');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value);
            }
            console.log('Cart Items:', cartItems);

            fetch('transaksi_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                console.log('Response Content-Type:', contentType);
                console.log('Response Status:', response.status);
                
                return response.text();
            })
            .then(text => {
                console.log('=== Raw Response ===');
                console.log(text);
                console.log('=== End Raw Response ===');
                
                // Cek apakah response kosong
                if (!text || text.trim() === '') {
                    showAlert('Response kosong dari server', 'danger');
                    return;
                }
                
                // Cek apakah ada HTML tag di response (berarti ada error PHP)
                if (text.trim().startsWith('<')) {
                    console.error('Response mengandung HTML, bukan JSON!');
                    showAlert('Server mengembalikan HTML error. Periksa console untuk detail.', 'danger');
                    return;
                }
                
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed JSON:', data);
                    
                    if (data.success) {
                        showAlert(data.message, 'success');
                        closeModal('transaksiModal');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'Terjadi kesalahan', 'danger');
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Failed to parse:', text.substring(0, 200));
                    showAlert('Response bukan JSON valid. Periksa console.', 'danger');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showAlert('Terjadi kesalahan koneksi: ' + error.message, 'danger');
            });
        }

        function viewDetail(id) {
            fetch(`get_detail_transaksi.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('detailContent').innerHTML = html;
                openModal('detailModal');
            });
        }

        function cancelTransaksi(id) {
            if (confirmDelete('Batalkan transaksi ini? Stok akan dikembalikan.')) {
                const formData = new FormData();
                formData.append('action', 'cancel');
                formData.append('id_transaksi', id);

                fetch('transaksi_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan', 'danger');
                });
            }
        }
    </script>

    <?php
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

    function formatCurrency($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    function formatDate($date) {
        $dateTime = new DateTime($date);
        return $dateTime->format('d/m/Y H:i');
    }
    ?>
</body>
</html>
