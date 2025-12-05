<?php
// Test file untuk memastikan JSON encoding bekerja
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'JSON encoding bekerja dengan baik',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
