<?php
session_start();
include 'db.php';
include 'nav.php';

$is_admin = isset($_SESSION['a_global']) && $_SESSION['status_login'] === true;
$dashboard = $is_admin ? 'dashboard-admin.php' : 'dashboard-siswa.php';
$role      = $is_admin ? 'admin' : 'siswa';

$profile       = null;
$aspirasi_list = [];
$total_asp     = 0;
$asp_selesai   = 0;
$error         = '';
$success       = '';

// Update akun admin
if ($is_admin && isset($_POST['update_account'])) {
    $admin_id        = $_SESSION['a_global']->id_admin ?? 0;
    $username        = trim($_POST['username']         ?? '');
    $current_pass    = $_POST['current_password']      ?? '';
    $new_pass        = $_POST['new_password']          ?? '';
    $confirm_pass    = $_POST['confirm_password']      ?? '';

    if (!$username || !$current_pass) {
        $error = 'Username dan password saat ini harus diisi.';
    } elseif ($conn) {
        $stmt = $conn->prepare("SELECT password FROM tb_admin WHERE id_admin=?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($current_pass, $row['password'])) {
            $error = 'Password saat ini tidak sesuai.';
        } else {
            if ($new_pass && $new_pass !== $confirm_pass) {
                $error = 'Konfirmasi password baru tidak cocok.';
            } elseif ($new_pass && strlen($new_pass) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            }

            if (!$error) {
                // Cek keunikan username
                $stmt = $conn->prepare("SELECT id_admin FROM tb_admin WHERE username=? AND id_admin!=?");
                $stmt->bind_param("si", $username, $admin_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) $error = 'Username sudah dipakai.';
                $stmt->close();
            }

            if (!$error) {
                if ($new_pass) {
                    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE tb_admin SET username=?, password=? WHERE id_admin=?");
                    $stmt->bind_param("ssi", $username, $hashed, $admin_id);
                } else {
                    $stmt = $conn->prepare("UPDATE tb_admin SET username=? WHERE id_admin=?");
                    $stmt->bind_param("si", $username, $admin_id);
                }
                if ($stmt->execute()) {
                    $success = 'Akun berhasil diperbarui.';
                    $_SESSION['a_global']->username = $username;
                } else {
                    $error = 'Gagal menyimpan perubahan.';
                }
                $stmt->close();
            }
        }
    }
}

// Ambil data profil
if ($is_admin) {
    $admin_id = $_SESSION['a_global']->id_admin ?? 0;
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM tb_admin WHERE id_admin=?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_object();
        $stmt->close();
        if (!$profile) { header('Location: login-admin.php'); exit; }

        $r = $conn->query("SELECT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, ia.tanggal_input,
            tk.ket_kategori, ta.status
            FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
            LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
            ORDER BY ia.id_pelaporan DESC");
        if ($r) while ($row = $r->fetch_assoc()) $aspirasi_list[] = $row;
    }
} else {
    $siswa_nis    = $_SESSION['siswa_id']     ?? null;
    $siswa_global = $_SESSION['siswa_global'] ?? null;

    if ($siswa_nis && $conn) {
        $stmt = $conn->prepare("SELECT nis, nama_siswa, kelas, jurusan, created_at FROM tb_siswa WHERE nis=?");
        $stmt->bind_param("s", $siswa_nis);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_object();
        $stmt->close();

        $nis_esc = $conn->real_escape_string($siswa_nis);
        $r = $conn->query("SELECT COUNT(*) as c FROM input_aspirasi WHERE nis='$nis_esc'");
        if ($r) $total_asp = (int)$r->fetch_assoc()['c'];

        $r = $conn->query("SELECT COUNT(*) as c FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan=ta.id_pelaporan
            WHERE ia.nis='$nis_esc' AND ta.status='Selesai'");
        if ($r) $asp_selesai = (int)$r->fetch_assoc()['c'];

        $r = $conn->query("SELECT ia.id_pelaporan, ia.lokasi, ia.ket, ia.tanggal_input,
            tk.ket_kategori, ta.status
            FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan=ta.id_pelaporan
            LEFT JOIN tb_kategori tk ON ia.id_kategori=tk.id_kategori
            WHERE ia.nis='$nis_esc' ORDER BY ia.id_pelaporan DESC");
        if ($r) while ($row = $r->fetch_assoc()) $aspirasi_list[] = $row;
    }

    // Fallback dari session
    if (!$profile) {
        $profile = (object)[
            'nis'        => $siswa_nis ?? '-',
            'nama_siswa' => $siswa_global->nama    ?? '-',
            'kelas'      => $siswa_global->kelas   ?? '-',
            'jurusan'    => $siswa_global->jurusan ?? '-',
            'created_at' => null,
        ];
    }
}

nav_header('Profil', 'profil.php', $role);
?>

<div class="section">
    <div class="container">
        <div class="profile-header">
            <h2>
                <?php echo $is_admin
                    ? htmlspecialchars($profile->username)
                    : htmlspecialchars($profile->nama_siswa ?? $profile->nis ?? '-'); ?>
            </h2>
            <p><?php echo $is_admin ? 'Administrator' : 'Siswa'; ?> &mdash; SMK Negeri 5 Telkom Banda Aceh</p>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
        <!-- Info akun admin -->
        <div class="profile-card">
            <h3>Informasi Akun Admin</h3>
            <div class="profile-info"><label>Username</label><p><?php echo htmlspecialchars($profile->username); ?></p></div>
            <div class="profile-info"><label>Admin ID</label><p><?php echo htmlspecialchars($profile->id_admin); ?></p></div>
            <div class="profile-info"><label>Terdaftar</label><p><?php echo isset($profile->created_at) ? date('d M Y', strtotime($profile->created_at)) : '-'; ?></p></div>
            <div class="profile-info"><label>Status</label><p class="text-success-bold">Aktif</p></div>
        </div>

        <!-- Ubah password -->
        <div class="profile-card">
            <h3>Ubah Username / Password</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="input-control"
                           value="<?php echo htmlspecialchars($profile->username); ?>" required>
                </div>
                <div class="form-group">
                    <label for="current_password">Password Saat Ini <span class="required">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="input-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Password Baru <span style="font-weight:400;color:var(--text-muted)">(kosongkan jika tidak ubah)</span></label>
                    <input type="password" id="new_password" name="new_password" class="input-control">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="input-control">
                </div>
                <button type="submit" name="update_account" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>

        <div class="profile-card">
            <h3>Hak Akses</h3>
            <div class="profile-info"><label>Peran</label><p>Administrator Sistem</p></div>
            <div class="profile-info"><label>Akses</label><p>Kelola Aspirasi, Kelola Siswa, Kelola Kategori, History</p></div>
        </div>

        <?php else: ?>
        <!-- Info siswa -->
        <div class="profile-card">
            <h3>Informasi Siswa</h3>
            <div class="profile-info"><label>NIS</label><p><?php echo htmlspecialchars($profile->nis ?? '-'); ?></p></div>
            <div class="profile-info"><label>Nama</label><p><?php echo htmlspecialchars($profile->nama_siswa ?? '-'); ?></p></div>
            <div class="profile-info"><label>Kelas</label><p><?php echo htmlspecialchars($profile->kelas ?? '-'); ?></p></div>
            <div class="profile-info"><label>Jurusan</label><p><?php echo htmlspecialchars($profile->jurusan ?? '-'); ?></p></div>
            <div class="profile-info"><label>Status</label><p class="text-success-bold">Aktif</p></div>
        </div>

        <?php if ($total_asp > 0): ?>
        <div class="profile-card">
            <h3>Statistik Aspirasi</h3>
            <div class="stat-grid-2col">
                <div class="stat-item">
                    <div class="stat-item-value"><?php echo $total_asp; ?></div>
                    <div class="stat-item-label">Total Aspirasi</div>
                </div>
                <div class="stat-item stat-item-success">
                    <div class="stat-item-value"><?php echo $asp_selesai; ?></div>
                    <div class="stat-item-label">Selesai</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Riwayat aspirasi -->
        <?php if (!empty($aspirasi_list)): ?>
        <div class="profile-card">
            <h3><?php echo $is_admin ? 'Semua Keluhan Siswa' : 'Riwayat Aspirasi Anda'; ?></h3>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aspirasi_list as $asp):
                            $st = $asp['status'] ?? 'Menunggu';
                            $bc = $st === 'Selesai' ? 'badge-selesai' : ($st === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                        ?>
                        <tr>
                            <td><?php echo $asp['id_pelaporan']; ?></td>
                            <td><?php echo htmlspecialchars($asp['ket_kategori'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($asp['lokasi']); ?></td>
                            <td><?php echo $asp['tanggal_input'] ? date('d-m-Y', strtotime($asp['tanggal_input'])) : '-'; ?></td>
                            <td><span class="badge <?php echo $bc; ?>"><?php echo htmlspecialchars($st); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-box mt-20">
            <p><?php echo $is_admin
                ? 'Untuk mengubah data akun, gunakan form di atas.'
                : 'Jika ada data yang tidak sesuai, silakan hubungi admin sekolah.'; ?>
            </p>
        </div>

        <div class="mt-24 text-center">
            <a href="<?php echo $dashboard; ?>" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
</div>

<?php nav_footer(); ?>
