<?php
session_start();
include 'db.php';

if (isset($_SESSION['a_global']) && $_SESSION['status_login'] === true) {
    header('Location: dashboard-admin.php');
    exit;
}

$error = '';

if (isset($_POST['submit'])) {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if (empty($user) || empty($pass)) {
        $error = 'Username dan password harus diisi.';
    } elseif (!$conn) {
        $error = 'Koneksi database gagal.';
    } else {
        $stmt = $conn->prepare("SELECT id_admin, username, password FROM tb_admin WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row && password_verify($pass, $row['password'])) {
                $_SESSION['status_login'] = true;
                $_SESSION['a_global']     = (object)$row;
                header("Location: dashboard-admin.php");
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } else {
            $error = 'Kesalahan database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | ASPIRASI SMK Negeri 5 Telkom</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-page-wrap">
    <div class="login-card">
        <div class="login-card-header">
            <div class="login-title">Login Admin</div>
            <p class="login-subtitle">Masukkan kredensial untuk mengakses dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="user">Username</label>
                <input type="text" id="user" name="user" class="input-control"
                       placeholder="Masukkan username" required
                       value="<?php echo htmlspecialchars($_POST['user'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="pass">Password</label>
                <input type="password" id="pass" name="pass" class="input-control"
                       placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-full btn-lg">Masuk</button>
        </form>

        <div class="login-footer-link">
            <p>Siswa? <a href="login-siswa.php" class="link-primary">Login sebagai Siswa</a></p>
        </div>
    </div>
</div>
</body>
</html>
