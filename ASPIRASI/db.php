<?php
$hostname = '127.0.0.1';
$username = 'root';
$password = '';
$dbname   = 'db_aspirasi';
$port     = 3306;

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = mysqli_connect($hostname, $username, $password, $dbname, $port);
    if (!$conn) throw new Exception(mysqli_connect_error());
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Koneksi database gagal: ' . $e->getMessage();
    $conn = null;
    exit;
}
