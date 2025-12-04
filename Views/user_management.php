<?php
session_start();

// Pengecekan jika belum login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login.php');
    exit();
}

// Hanya admin yang bisa akses halaman ini
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

require_once '../Config/koneksi.php';

$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Handle AJAX Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
        $action = $_POST['action'];

        if ($action === 'add' || $action === 'edit') {
            $username = sanitize($conn, $_POST['username'] ?? '');
            $nama = sanitize($conn, $_POST['nama_lengkap'] ?? '');
            $email = sanitize($conn, $_POST['email'] ?? '');
            $phone = sanitize($conn, $_POST['phone'] ?? '');
            $user_role = sanitize($conn, $_POST['role'] ?? 'kasir');
            $password = $_POST['password'] ?? '';
            $status = sanitize($conn, $_POST['status'] ?? 'aktif');

            // Validasi
            if (empty($username) || empty($nama) || empty($user_role)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Username, nama lengkap, dan role harus diisi!'
                ]);
                exit();
            }

            // Cek username sudah ada
            if ($action === 'add') {
                $cek = fetch_one($conn, "SELECT * FROM user WHERE username = '$username'");
                if ($cek) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Username sudah terdaftar!'
                    ]);
                    exit();
                }

                if (empty($password)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Password harus diisi!'
                    ]);
                    exit();
                }

                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $query = "INSERT INTO user (username, password, role, nama_lengkap, email, phone, status) 
                         VALUES ('$username', '$password_hash', '$user_role', '$nama', '$email', '$phone', '$status')";
                
                $result = execute_action($conn, $query);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'User berhasil ditambahkan!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal menambah user: ' . $result['message']
                    ]);
                }
            } else if ($action === 'edit') {
                $id_user = sanitize($conn, $_POST['id_user'] ?? '');

                // Cek username sudah ada (selain user saat ini)
                $cek = fetch_one($conn, "SELECT * FROM user WHERE username = '$username' AND id_user != '$id_user'");
                if ($cek) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Username sudah terdaftar!'
                    ]);
                    exit();
                }

                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $query = "UPDATE user SET username = '$username', password = '$password_hash', role = '$user_role', 
                             nama_lengkap = '$nama', email = '$email', phone = '$phone', status = '$status' 
                             WHERE id_user = '$id_user'";
                } else {
                    $query = "UPDATE user SET username = '$username', role = '$user_role', 
                             nama_lengkap = '$nama', email = '$email', phone = '$phone', status = '$status' 
                             WHERE id_user = '$id_user'";
                }

                $result = execute_action($conn, $query);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'User berhasil diubah!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal mengubah user: ' . $result['message']
                    ]);
                }
            }
        } else if ($action === 'delete') {
            $id_user = sanitize($conn, $_POST['id_user'] ?? '');

            // Cegah admin menghapus dirinya sendiri
            if ($id_user == $_SESSION['id_user']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Anda tidak bisa menghapus akun sendiri!'
                ]);
                exit();
            }

            $query = "DELETE FROM user WHERE id_user = '$id_user' AND id_user != '1'";
            $result = execute_action($conn, $query);

            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User berhasil dihapus!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menghapus user'
                ]);
            }
        }
        exit();
    }

    // Ambil data user
    $users = fetch_all($conn, "SELECT * FROM user ORDER BY id_user DESC");
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Toko Outdoor</title>
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
                <li><a href="user_management.php" class="active">Manajemen User</a></li>
                <li><a href="produk_management.php">Produk</a></li>
                <li><a href="transaksi_management.php">Transaksi</a></li>
                <li><a href="laporan.php">Laporan</a></li>
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
                <h1>Manajemen User</h1>
                <div class="header-user">
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></p>
                        <p class="user-role">ADMIN</p>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="content">
                <div class="page-title">Manajemen User</div>
                <p class="page-subtitle">Kelola pengguna sistem (Admin, Kasir, Owner)</p>

                <!-- ALERT PLACEHOLDER -->
                <div id="alert-container"></div>

                <!-- BUTTON TAMBAH USER -->
                <div style="margin-bottom: 20px;">
                    <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                        Tambah User Baru
                    </button>
                </div>

                <!-- TABEL USER -->
                <div class="card">
                    <div class="card-header">Daftar User</div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari user..." onkeyup="filterTable('searchInput', 'userTable')">
                        </div>

                        <?php if (count($users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th style="cursor: pointer;" onclick="sortTable('userTable', 0)">#</th>
                                            <th style="cursor: pointer;" onclick="sortTable('userTable', 1)">Username</th>
                                            <th>Nama Lengkap</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $index => $user): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getBadgeClass($user['role']); ?>">
                                                        <?php echo strtoupper($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $user['status'] === 'aktif' ? 'badge-success' : 'badge-danger'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-action" onclick="openEditUserModal(<?php echo $user['id_user']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['nama_lengkap'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['phone'], ENT_QUOTES); ?>', '<?php echo $user['role']; ?>', '<?php echo $user['status']; ?>')">Edit</button>
                                                    <button class="btn btn-danger btn-action" onclick="deleteUser(<?php echo $user['id_user']; ?>)">Hapus</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <p>📭 Belum ada user yang terdaftar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH/EDIT USER -->
    <div class="modal" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Tambah User Baru</h5>
                    <button type="button" class="btn-close" onclick="closeModal('userModal')"></button>
                </div>
                <form id="userForm" method="POST" onsubmit="handleUserSubmit(event)">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id_user" id="id_user" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                        </div>

                        <div class="form-group">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email">
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan nomor telepon">
                        </div>

                        <div class="form-group">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="kasir">Kasir</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password (min. 6 karakter)">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password (edit mode)</small>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Public/js/script.js"></script>
    <script>
        function openAddUserModal() {
            document.getElementById('userModalTitle').textContent = 'Tambah User Baru';
            document.getElementById('action').value = 'add';
            document.getElementById('id_user').value = '';
            document.getElementById('username').value = '';
            document.getElementById('nama_lengkap').value = '';
            document.getElementById('email').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('role').value = 'kasir';
            document.getElementById('password').value = '';
            document.getElementById('password').required = true;
            document.getElementById('status').value = 'aktif';
            openModal('userModal');
        }

        function openEditUserModal(id, username, nama, email, phone, role, status) {
            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('action').value = 'edit';
            document.getElementById('id_user').value = id;
            document.getElementById('username').value = username;
            document.getElementById('nama_lengkap').value = nama;
            document.getElementById('email').value = email;
            document.getElementById('phone').value = phone;
            document.getElementById('role').value = role;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('status').value = status;
            openModal('userModal');
        }

        function handleUserSubmit(event) {
            event.preventDefault();

            if (!validateFormInput('userForm')) {
                showAlert('Harap isi semua field yang diperlukan', 'warning');
                return;
            }

            const formData = new FormData(document.getElementById('userForm'));

            fetch('user_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeModal('userModal');
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

        function deleteUser(id) {
            if (confirmDelete('Apakah Anda yakin ingin menghapus user ini?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id_user', id);

                fetch('user_management.php', {
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
    function getBadgeClass($role) {
        switch ($role) {
            case 'admin':
                return 'badge-danger';
            case 'kasir':
                return 'badge-info';
            case 'owner':
                return 'badge-warning';
            default:
                return 'badge-secondary';
        }
    }
    ?>
</body>
</html>
