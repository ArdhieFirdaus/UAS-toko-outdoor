<?php
// Prevent any output before headers
ob_start();

// Anti-cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sun, 19 Nov 1978 05:00:00 GMT');

// Start session
session_start();

// Unset all session variables
$_SESSION = array();

// Delete ALL possible session cookies
$cookie_params = session_get_cookie_params();

// Try multiple cookie paths to ensure deletion
$paths_to_try = ['/', '/toko-outdoor2/', '/toko-outdoor2/Views/', $cookie_params["path"]];
$names_to_try = [session_name(), 'PHPSESSID'];

foreach ($names_to_try as $name) {
    foreach ($paths_to_try as $path) {
        setcookie($name, '', time() - 42000, $path);
        setcookie($name, '', time() - 42000, $path, '');
        setcookie($name, '', time() - 42000, $path, 'localhost');
    }
}

// Destroy session
session_destroy();

// Clear output buffer
ob_end_clean();

// Force redirect with meta refresh as backup
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=login.php">
    <script>window.location.href = 'login.php';</script>
</head>
<body>
Redirecting to login...
</body>
</html>
<?php
exit();
