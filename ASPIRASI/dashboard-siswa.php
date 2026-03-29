<?php
session_start();
include 'db.php';
include 'nav.php';

$siswa_nis    = $_SESSION['siswa_id']     ?? null;
$siswa_global = $_SESSION['siswa_global'] ?? null;
$siswa_jurusan = $siswa_global->jurusan ?? '-';
$siswa_kelas   = $siswa_global->kelas   ?? '-';

$stats = ['total' => 0, 'menunggu' => 0, 'proses' => 0, 'selesai' => 0];
$aspirasi_list = [];

if ($conn) {
    // Ringkasan aspirasi per jurusan dihapus karena tidak diperlukan

    if ($siswa_nis) {
        $nis_esc = $conn->real_escape_string($siswa_nis);

        $r = $conn->query("SELECT
            COUNT(*) as total,
            SUM(CASE WHEN ta.status='Menunggu' OR ta.status IS NULL THEN 1 ELSE 0 END) as menunggu,
            SUM(CASE WHEN ta.status='Proses'   THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN ta.status='Selesai'  THEN 1 ELSE 0 END) as selesai
            FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
            WHERE ia.nis = '$nis_esc'");
        if ($r) {
            $row = $r->fetch_assoc();
            $stats['total']    = (int)$row['total'];
            $stats['menunggu'] = (int)$row['menunggu'];
            $stats['proses']   = (int)$row['proses'];
            $stats['selesai']  = (int)$row['selesai'];
        }

        $r = $conn->query("SELECT ia.id_pelaporan, ia.lokasi, ia.ket, ia.tanggal_input,
            tk.ket_kategori, ta.status, ta.feedback
            FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
            LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
            WHERE ia.nis = '$nis_esc' ORDER BY ia.id_pelaporan DESC LIMIT 5");
        if ($r) while ($row = $r->fetch_assoc()) $aspirasi_list[] = $row;
    } else {
        $r = $conn->query("SELECT ia.id_pelaporan, ia.lokasi, ia.ket, ia.tanggal_input,
            tk.ket_kategori, ta.status, ta.feedback
            FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
            LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
            ORDER BY ia.id_pelaporan DESC LIMIT 5");
        if ($r) while ($row = $r->fetch_assoc()) $aspirasi_list[] = $row;
    }
}

nav_header('Dashboard Siswa', 'dashboard-siswa.php', 'siswa');
?>

<div class="section">
    <div class="container">
        <div class="dashboard-header">
            <div class="dashboard-header-content">
                <h2>Dashboard Siswa</h2>
                <p>
                    <?php if ($siswa_nis): ?>
                        NIS: <?php echo htmlspecialchars($siswa_nis); ?> &nbsp;|&nbsp;
                        Kelas: <?php echo htmlspecialchars($siswa_kelas); ?> &nbsp;|&nbsp;
                        Jurusan: <?php echo htmlspecialchars($siswa_jurusan); ?> &nbsp;|&nbsp;
                    <?php endif; ?>
                    <?php echo date('d F Y'); ?>
                </p>
            </div>
            <div class="dashboard-header-actions">
                <a href="aspirasi-siswa.php" class="btn btn-primary">Buat Aspirasi</a>
                <a href="riwayat-aspirasi.php" class="btn btn-info">Riwayat</a>
            </div>
        </div>

        <?php if ($siswa_nis): ?>
        <div class="stats-grid">
            <div class="stat-card stat-border-primary">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Aspirasi</div>
            </div>
            <div class="stat-card stat-border-warning">
                <div class="stat-number"><?php echo $stats['menunggu']; ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
            <div class="stat-card stat-border-info">
                <div class="stat-number"><?php echo $stats['proses']; ?></div>
                <div class="stat-label">Proses</div>
            </div>
            <div class="stat-card stat-border-success">
                <div class="stat-number"><?php echo $stats['selesai']; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="dashboard-section">
            <div class="section-header">
                <h3><?php echo $siswa_nis ? 'Aspirasi Terbaru Anda' : 'Aspirasi Terbaru'; ?></h3>
                <a href="riwayat-aspirasi.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>

            <?php if (!empty($aspirasi_list)): ?>
                <?php foreach ($aspirasi_list as $asp):
                    $status = $asp['status'] ?? 'Menunggu';
                    $badge  = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                ?>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <div class="dashboard-card-title">
                                <?php echo htmlspecialchars($asp['ket_kategori'] ?? 'Aspirasi'); ?>
                            </div>
                            <div class="dashboard-card-meta">
                                <?php echo $asp['tanggal_input'] ? date('d-m-Y H:i', strtotime($asp['tanggal_input'])) : '-'; ?>
                                &nbsp;|&nbsp; <?php echo htmlspecialchars($asp['lokasi']); ?>
                            </div>
                        </div>
                        <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                    </div>
                    <p class="card-desc">
                        <?php echo htmlspecialchars(mb_substr($asp['ket'], 0, 130));
                        if (mb_strlen($asp['ket']) > 130) echo '...'; ?>
                    </p>
                    <?php if (!empty($asp['feedback'])): ?>
                    <div class="feedback-preview">
                        <strong>Umpan Balik Admin:</strong>
                        <?php echo htmlspecialchars(mb_substr($asp['feedback'], 0, 100));
                        if (mb_strlen($asp['feedback']) > 100) echo '...'; ?>
                    </div>
                    <?php endif; ?>
                    <a href="detail-aspirasi.php?id=<?php echo $asp['id_pelaporan']; ?>"
                       class="btn btn-sm btn-primary btn-auto mt-10">Lihat Detail</a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-title">Belum ada aspirasi</div>
                <div class="empty-state-desc">Mulai sampaikan aspirasi Anda untuk sekolah yang lebih baik.</div>
                <a href="aspirasi-siswa.php" class="btn btn-primary mt-16">Buat Aspirasi Sekarang</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php nav_footer(); ?>
