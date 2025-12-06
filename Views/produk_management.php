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

// Hanya admin dan kasir yang bisa akses halaman ini
if (!in_array($_SESSION['role'], ['admin', 'kasir'])) {
    header('Location: dashboard.php', true, 303);
    exit();
}

require_once '../Config/koneksi.php';

$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

// Handle AJAX Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
        $action = $_POST['action'];

        if ($is_admin && ($action === 'add' || $action === 'edit')) {
            $nama = sanitize($conn, $_POST['nama_produk'] ?? '');
            $kategori = sanitize($conn, $_POST['kategori'] ?? '');
            $deskripsi = sanitize($conn, $_POST['deskripsi'] ?? '');
            $harga = floatval($_POST['harga'] ?? 0);
            $stok = intval($_POST['stok'] ?? 0);
            $status = sanitize($conn, $_POST['status'] ?? 'tersedia');

            if (empty($nama) || empty($kategori) || $harga <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Nama, kategori, dan harga harus diisi dengan benar!'
                ]);
                exit();
            }

            if ($action === 'add') {
                $query = "INSERT INTO produk (nama_produk, kategori, deskripsi, harga, stok, status) 
                         VALUES ('$nama', '$kategori', '$deskripsi', '$harga', '$stok', '$status')";
                
                $result = execute_action($conn, $query);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Produk berhasil ditambahkan!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal menambah produk'
                    ]);
                }
            } else if ($action === 'edit') {
                $id_produk = sanitize($conn, $_POST['id_produk'] ?? '');

                $query = "UPDATE produk SET nama_produk = '$nama', kategori = '$kategori', 
                         deskripsi = '$deskripsi', harga = '$harga', stok = '$stok', status = '$status' 
                         WHERE id_produk = '$id_produk'";

                $result = execute_action($conn, $query);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Produk berhasil diubah!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal mengubah produk'
                    ]);
                }
            }
        } else if ($is_admin && $action === 'delete') {
            $id_produk = sanitize($conn, $_POST['id_produk'] ?? '');

            $query = "DELETE FROM produk WHERE id_produk = '$id_produk'";
            $result = execute_action($conn, $query);

            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Produk berhasil dihapus!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menghapus produk'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk action ini'
            ]);
        }
        exit();
    }

    // Ambil data produk
    $produk = fetch_all($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Toko Outdoor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/css/style.css">
</head>
<body>
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
                <?php endif; ?>
                <li><a href="produk_management.php" class="active">Produk</a></li>
                <li><a href="transaksi_management.php">Transaksi</a></li>
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
                <h1>Manajemen Produk</h1>
                <div class="header-user">
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></p>
                        <p class="user-role"><?php echo strtoupper($role); ?></p>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="content">
                <div class="page-title">Manajemen Produk</div>
                <p class="page-subtitle">Kelola data produk outdoor</p>

                <!-- ALERT PLACEHOLDER -->
                <div id="alert-container"></div>

                <!-- BUTTON TAMBAH PRODUK -->
                <?php if ($role === 'admin'): ?>
                    <div style="margin-bottom: 20px;">
                        <button type="button" class="btn btn-primary" onclick="openAddProdukModal()">
                            Tambah Produk Baru
                        </button>
                    </div>
                <?php endif; ?>

                <!-- TABEL PRODUK -->
                <div class="card">
                    <div class="card-header">Daftar Produk</div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari produk..." onkeyup="filterTable('searchInput', 'produkTable')">
                        </div>

                        <?php if (count($produk) > 0): ?>
                            <div class="table-responsive">
                                <table class="table" id="produkTable">
                                    <thead>
                                        <tr>
                                            <th style="cursor: pointer;" onclick="sortTable('produkTable', 0)">No</th>
                                            <th style="cursor: pointer;" onclick="sortTable('produkTable', 1)">Nama Produk</th>
                                            <th>Kategori</th>
                                            <th style="cursor: pointer;" onclick="sortTable('produkTable', 3)">Harga</th>
                                            <th style="cursor: pointer;" onclick="sortTable('produkTable', 4)">Stok</th>
                                            <th>Status</th>
                                            <?php if ($is_admin): ?>
                                                <th>Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produk as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($item['kategori']); ?></td>
                                                <td><?php echo formatCurrency($item['harga']); ?></td>
                                                <td>
                                                    <strong><?php echo $item['stok']; ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($item['stok'] == 0): ?>
                                                        <span class="badge badge-danger">Habis</span>
                                                    <?php elseif ($item['stok'] < 10): ?>
                                                        <span class="badge badge-warning">Sedikit</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success">Tersedia</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($is_admin): ?>
                                                    <td>
                                                        <button class="btn btn-warning btn-action" onclick="openEditProdukModal(<?php echo $item['id_produk']; ?>, '<?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($item['kategori'], ENT_QUOTES); ?>', <?php echo $item['harga']; ?>, <?php echo $item['stok']; ?>, '<?php echo htmlspecialchars($item['deskripsi'], ENT_QUOTES); ?>', '<?php echo $item['status']; ?>')">Edit</button>
                                                        <button class="btn btn-danger btn-action" onclick="deleteProduk(<?php echo $item['id_produk']; ?>)">Hapus</button>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <p>📭 Belum ada produk yang terdaftar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH/EDIT PRODUK -->
    <div class="modal" id="produkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="produkModalTitle">Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" onclick="closeModal('produkModal')"></button>
                </div>
                <form id="produkForm" method="POST" onsubmit="handleProdukSubmit(event)">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id_produk" id="id_produk" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_produk" class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" id="nama_produk" name="nama_produk" placeholder="Masukkan nama produk" required>
                        </div>

                        <div class="form-group">
                            <label for="kategori" class="form-label">Kategori</label>
                            <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Masukkan kategori (Tenda, Sleeping Bag, Tas, dll)" required>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" placeholder="Masukkan deskripsi produk"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga" class="form-label">Harga (Rp)</label>
                                    <input type="number" class="form-control currency-input" id="harga" name="harga" placeholder="0" min="0" step="1000" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stok" class="form-label">Stok</label>
                                    <input type="number" class="form-control" id="stok" name="stok" placeholder="0" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="tersedia">Tersedia</option>
                                <option value="habis">Habis</option>
                                <option value="tidak_diproduksi">Tidak Diproduksi</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('produkModal')">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/script.js"></script>
    <script>
        function openAddProdukModal() {
            document.getElementById('produkModalTitle').textContent = 'Tambah Produk Baru';
            document.getElementById('action').value = 'add';
            document.getElementById('id_produk').value = '';
            document.getElementById('nama_produk').value = '';
            document.getElementById('kategori').value = '';
            document.getElementById('deskripsi').value = '';
            document.getElementById('harga').value = '';
            document.getElementById('stok').value = '';
            document.getElementById('status').value = 'tersedia';
            openModal('produkModal');
        }

        function openEditProdukModal(id, nama, kategori, harga, stok, deskripsi, status) {
            document.getElementById('produkModalTitle').textContent = 'Edit Produk';
            document.getElementById('action').value = 'edit';
            document.getElementById('id_produk').value = id;
            document.getElementById('nama_produk').value = nama;
            document.getElementById('kategori').value = kategori;
            document.getElementById('harga').value = harga;
            document.getElementById('stok').value = stok;
            document.getElementById('deskripsi').value = deskripsi;
            document.getElementById('status').value = status;
            openModal('produkModal');
        }

        function handleProdukSubmit(event) {
            event.preventDefault();

            if (!validateFormInput('produkForm')) {
                showAlert('Harap isi semua field yang diperlukan', 'warning');
                return;
            }

            const formData = new FormData(document.getElementById('produkForm'));

            fetch('produk_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeModal('produkModal');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan: ' + error.message, 'danger');
            });
        }

        function deleteProduk(id) {
            if (confirmDelete('Apakah Anda yakin ingin menghapus produk ini?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id_produk', id);

                fetch('produk_management.php', {
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
            case 'tersedia':
                return 'badge-success';
            case 'habis':
                return 'badge-danger';
            case 'tidak_diproduksi':
                return 'badge-warning';
            default:
                return 'badge-secondary';
        }
    }

    function formatCurrency($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
    ?>
</body>
</html>
