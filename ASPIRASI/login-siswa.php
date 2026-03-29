<?php
session_start();
include 'db.php';

if (isset($_SESSION['siswa_id'])) {
    header('Location: dashboard-siswa.php');
    exit;
}

$error = '';

if (isset($_POST['submit'])) {
    $nis   = trim($_POST['nis']   ?? '');
    $kelas = trim($_POST['kelas'] ?? '');

    if (empty($nis) || empty($kelas)) {
        $error = 'NIS dan kelas harus diisi.';
    } elseif (!$conn) {
        $error = 'Koneksi database gagal.';
    } else {
        $stmt = $conn->prepare(
            "SELECT ts.nis, ts.nama_siswa, ts.kelas, ts.jurusan FROM tb_siswa ts WHERE ts.nis = ?"
        );
        if ($stmt) {
            $stmt->bind_param("s", $nis);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row) {
                // Validasi tingkat kelas (X, XI, XII) cocok dengan data siswa
                if (stripos($row['kelas'], $kelas) !== false) {
                    $_SESSION['siswa_id']     = $row['nis'];
                    $_SESSION['status_login'] = true;
                    $_SESSION['siswa_global'] = (object)[
                        'nis'     => $row['nis'],
                        'nama'    => $row['nama_siswa'],
                        'kelas'   => $row['kelas'],
                        'jurusan' => $row['jurusan'],
                    ];
                    header('Location: dashboard-siswa.php');
                    exit;
                } else {
                    $error = 'NIS atau kelas tidak sesuai.';
                }
            } else {
                $error = 'NIS tidak ditemukan.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Siswa | ASPIRASI SMK Negeri 5 Telkom</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-page-wrap">
    <div class="login-card">
        <div class="login-logo-wrap">
            <img src="img/Logo smk telkom.png" alt="Logo SMK" class="login-logo">
        </div>
        <div class="login-card-header">
            <div class="login-title">Login Siswa</div>
            <p class="login-subtitle">ASPIRASI - SMK Negeri 5 Telkom Banda Aceh</p>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="nis">NIS (Nomor Induk Siswa)</label>
                <input type="text" id="nis" name="nis" class="input-control"
                       placeholder="Contoh: 2401234567" required
                       value="<?php echo htmlspecialchars($_POST['nis'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="kelas">Tingkat Kelas</label>
                <select id="kelas" name="kelas" class="input-control" required>
                    <option value="">-- Pilih Kelas --</option>
                    <option value="X"   <?php echo (($_POST['kelas'] ?? '') === 'X')   ? 'selected' : ''; ?>>X (Kelas 10)</option>
                    <option value="XI"  <?php echo (($_POST['kelas'] ?? '') === 'XI')  ? 'selected' : ''; ?>>XI (Kelas 11)</option>
                    <option value="XII" <?php echo (($_POST['kelas'] ?? '') === 'XII') ? 'selected' : ''; ?>>XII (Kelas 12)</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-full btn-lg">Masuk</button>
        </form>

        <div class="login-footer-link">
            <p>Admin? <a href="login-admin.php" class="link-primary">Login sebagai Admin</a></p>
        </div>
    </div>
</div>
</body>
</html>
