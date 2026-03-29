<?php
session_start();
include 'db.php';
include 'nav.php';

if (!isset($_SESSION['a_global']) || $_SESSION['status_login'] !== true) {
    header('Location: login-admin.php'); exit;
}

$admin_name = $_SESSION['a_global']->username ?? 'Admin';
$total_aspirasi = $status_menunggu = $status_proses = $status_selesai = $total_siswa = 0;
$jurusan_stats = [];
$aspirasi_list = [];

if ($conn) {
    $r = $conn->query("SELECT COUNT(*) as c FROM input_aspirasi");
    if ($r) $total_aspirasi = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT COUNT(*) as c FROM tb_siswa");
    if ($r) $total_siswa = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT status, COUNT(*) as total FROM tb_aspirasi GROUP BY status");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            if ($row['status'] === 'Menunggu') $status_menunggu = (int)$row['total'];
            elseif ($row['status'] === 'Proses') $status_proses = (int)$row['total'];
            elseif ($row['status'] === 'Selesai') $status_selesai = (int)$row['total'];
        }
    }

    // Bagian statistik jurusan dihapus karena tidak diperlukan
    // $r = $conn->query("SELECT ...");
    // if ($r) while ($row = $r->fetch_assoc()) $jurusan_stats[] = $row;

    $r = $conn->query("SELECT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, ia.tanggal_input,
        tk.ket_kategori, ta.status, ts.kelas
        FROM input_aspirasi ia
        LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
        LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
        LEFT JOIN tb_siswa ts ON ia.nis = ts.nis
        ORDER BY ia.id_pelaporan DESC");
    if ($r) while ($row = $r->fetch_assoc()) $aspirasi_list[] = $row;
}

nav_header('Dashboard Admin', 'dashboard-admin.php', 'admin');
?>

<div class="section">
    <div class="container">
        <div class="dashboard-header">
            <div class="dashboard-header-content">
                <h2>Dashboard Admin</h2>
                <p>Selamat datang, <?php echo htmlspecialchars($admin_name); ?>
                   &nbsp;|&nbsp; <?php echo date('d F Y'); ?></p>
            </div>
            <div class="dashboard-header-actions">
                <a href="manage-aspirasi.php" class="btn btn-primary">Kelola Aspirasi</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card stat-border-primary">
                <div class="stat-number"><?php echo $total_aspirasi; ?></div>
                <div class="stat-label">Total Aspirasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_siswa; ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
            <div class="stat-card stat-border-warning">
                <div class="stat-number"><?php echo $status_menunggu; ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
            <div class="stat-card stat-border-info">
                <div class="stat-number"><?php echo $status_proses; ?></div>
                <div class="stat-label">Proses</div>
            </div>
            <div class="stat-card stat-border-success">
                <div class="stat-number"><?php echo $status_selesai; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>


        <div class="dashboard-section">
            <div class="section-header">
                <h3>Semua Aspirasi</h3>
                <a href="manage-aspirasi.php" class="btn btn-sm btn-secondary">Kelola</a>
            </div>

            <?php if (!empty($aspirasi_list)):
                foreach ($aspirasi_list as $asp):
                    $status = $asp['status'] ?? 'Menunggu';
                    $badge  = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-menunggu');
            ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <div class="dashboard-card-title">
                            <?php echo htmlspecialchars($asp['ket_kategori'] ?? 'Aspirasi'); ?>
                            &mdash; NIS <?php echo htmlspecialchars($asp['nis']); ?>
                        </div>
                        <div class="dashboard-card-meta">
                            Kelas: <?php echo htmlspecialchars($asp['kelas'] ?? '-'); ?> &nbsp;|&nbsp;
                            <?php echo $asp['tanggal_input'] ? date('d-m-Y H:i', strtotime($asp['tanggal_input'])) : '-'; ?>
                        </div>
                    </div>
                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                </div>
                <div class="card-lokasi"><?php echo htmlspecialchars((trim($asp['lokasi']) !== '' && $asp['lokasi'] !== '0') ? $asp['lokasi'] : 'Lokasi belum diisi'); ?></div>
                <p class="card-desc">
                    <?php echo htmlspecialchars(mb_substr($asp['ket'], 0, 150));
                    if (mb_strlen($asp['ket']) > 150) echo '...'; ?>
                </p>
                <a href="detail-aspirasi-admin.php?id=<?php echo $asp['id_pelaporan']; ?>"
                   class="btn btn-sm btn-primary btn-auto">Lihat Detail</a>
            </div>
            <?php endforeach; else: ?>
            <div class="empty-state">
                <div class="empty-state-title">Belum ada aspirasi</div>
                <div class="empty-state-desc">Aspirasi siswa akan muncul di sini.</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php nav_footer(); ?>
