<?php
session_start();
include 'db.php';
include 'nav.php';

if (!isset($_SESSION['a_global']) || $_SESSION['status_login'] !== true) {
    header('Location: login-admin.php'); exit;
}

$error   = '';
$success = '';

// Tambah kategori
if (isset($_POST['add_kategori'])) {
    $ket = trim($conn->real_escape_string($_POST['ket_kategori'] ?? ''));
    if (!$ket) {
        $error = 'Nama kategori harus diisi.';
    } else {
        $chk = $conn->query("SELECT COUNT(*) as c FROM tb_kategori WHERE ket_kategori='$ket'");
        if ($chk && (int)$chk->fetch_assoc()['c'] > 0) {
            $error = 'Kategori sudah ada.';
        } else {
            $conn->query("INSERT INTO tb_kategori (ket_kategori) VALUES ('$ket')");
            header("Location: data_kategori.php"); exit;
        }
    }
}

// Hapus kategori
if (isset($_POST['delete_kategori'])) {
    $id  = (int)$_POST['id_kategori'];
    $chk = $conn->query("SELECT COUNT(*) as c FROM input_aspirasi WHERE id_kategori=$id");
    if ($chk && (int)$chk->fetch_assoc()['c'] > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih digunakan oleh aspirasi.';
    } else {
        $conn->query("DELETE FROM tb_kategori WHERE id_kategori=$id");
        header("Location: data_kategori.php"); exit;
    }
}

$kategori_list = [];
$r = $conn->query("SELECT tk.id_kategori, tk.ket_kategori,
    (SELECT COUNT(*) FROM input_aspirasi WHERE id_kategori=tk.id_kategori) as jumlah
    FROM tb_kategori tk ORDER BY tk.ket_kategori");
if ($r) while ($row = $r->fetch_assoc()) $kategori_list[] = $row;

nav_header('Kelola Kategori', 'data_kategori.php', 'admin');
?>

<div class="section">
    <div class="container">
        <h2>Kelola Kategori Aspirasi</h2>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="form-card mb-20">
            <h3 style="margin:0 0 16px;font-size:16px;font-weight:700">Tambah Kategori Baru</h3>
            <form method="POST" action="" class="inline-add-form">
                <input type="text" name="ket_kategori" class="input-control"
                       placeholder="Nama kategori aspirasi..." required>
                <button type="submit" name="add_kategori" class="btn btn-primary">Tambah</button>
            </form>
        </div>

        <?php if (!empty($kategori_list)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Aspirasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategori_list as $kat): ?>
                    <tr>
                        <td><?php echo $kat['id_kategori']; ?></td>
                        <td><?php echo htmlspecialchars($kat['ket_kategori']); ?></td>
                        <td><?php echo $kat['jumlah']; ?> aspirasi</td>
                        <td>
                            <?php if ($kat['jumlah'] == 0): ?>
                            <form method="POST" action="" class="inline-form">
                                <input type="hidden" name="id_kategori" value="<?php echo $kat['id_kategori']; ?>">
                                <button type="submit" name="delete_kategori" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus kategori <?php echo htmlspecialchars($kat['ket_kategori'], ENT_QUOTES); ?>?')">
                                    Hapus
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled
                                    title="Tidak dapat dihapus (sedang digunakan)">Hapus</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-title">Belum ada kategori</div>
            <div class="empty-state-desc">Tambahkan kategori aspirasi di atas.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php nav_footer(); ?>
