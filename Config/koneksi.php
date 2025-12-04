<?php
/**
 * File Koneksi Database
 * Menggunakan mysqli_connect dengan error handling
 */

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'db_toko_outdoor';

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $password, $database);

// Pengecekan error jika koneksi gagal
if (!$conn) {
    die('
    <div style="
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 15px;
        margin: 20px;
        border-radius: 4px;
        font-family: Arial, sans-serif;
    ">
        <h3>Error Koneksi Database</h3>
        <p><strong>Pesan Error:</strong> ' . mysqli_connect_error() . '</p>
        <p><strong>Error Code:</strong> ' . mysqli_connect_errno() . '</p>
        <p style="margin-top: 10px; font-size: 12px; color: #555;">
            Pastikan:<br>
            - MySQL Server sudah berjalan<br>
            - Database "db_toko_outdoor" sudah dibuat<br>
            - Username dan password sudah benar<br>
            - Server database accessible dari localhost
        </p>
    </div>
    ');
}

// Set charset untuk mendukung Unicode (termasuk Bahasa Indonesia)
mysqli_set_charset($conn, 'utf8mb4');

// Fungsi untuk escape input dan mencegah SQL Injection
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, stripslashes(trim($input)));
}

// Fungsi untuk execute query
function execute_query($conn, $query) {
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return array(
            'success' => false,
            'message' => 'Query Error: ' . mysqli_error($conn),
            'query' => $query
        );
    }
    
    return array(
        'success' => true,
        'result' => $result
    );
}

// Fungsi untuk fetch data
function fetch_all($conn, $query) {
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return array();
    }
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

// Fungsi untuk fetch single data
function fetch_one($conn, $query) {
    $result = mysqli_query($conn, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        return null;
    }
    
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk count rows
function count_rows($conn, $query) {
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return 0;
    }
    
    return mysqli_num_rows($result);
}

// Fungsi untuk insert/update/delete
function execute_action($conn, $query) {
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return array(
            'success' => false,
            'message' => mysqli_error($conn),
            'affected_rows' => 0
        );
    }
    
    return array(
        'success' => true,
        'message' => 'Operation berhasil',
        'affected_rows' => mysqli_affected_rows($conn),
        'insert_id' => mysqli_insert_id($conn)
    );
}
?>
