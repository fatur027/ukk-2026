<?php
session_start();
include 'db.php';

if (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true) {
    header('Location: ' . (isset($_SESSION['a_global']) ? 'dashboard-admin.php' : 'dashboard-siswa.php'));
    exit;
}

// Cek database sudah siap
if ($conn) {
    $r = $conn->query("SHOW TABLES");
    if (!$r || $r->num_rows === 0) {
        header('Location: status.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASPIRASI - SMK Negeri 5 Telkom Banda Aceh</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="landing-container">
    <div class="landing-content">
        <img src="img/Logo smk telkom.png" alt="Logo SMK" class="landing-logo">
        <h1>ASPIRASI</h1>
        <p>Sistem Manajemen Aspirasi Siswa<br>SMK Negeri 5 Telkom Banda Aceh</p>
        <p class="landing-jurusan">TJA &nbsp;|&nbsp; TKJ &nbsp;|&nbsp; RPL &nbsp;|&nbsp; PF</p>
        <div class="landing-buttons">
            <a href="dashboard-siswa.php" class="landing-btn landing-btn-primary">Masuk sebagai Siswa</a>
            <a href="login-admin.php" class="landing-btn landing-btn-secondary">Masuk sebagai Admin</a>
        </div>
    </div>
</div>
</body>
</html>
