<?php
session_start();
include 'db.php';
include 'nav.php';

$siswa_nis    = $_SESSION['siswa_id'] ?? null;
$filter_status = trim($_GET['status'] ?? '');
$search        = trim($_GET['search'] ?? '');
$nis_cari      = trim($_GET['nis_cari'] ?? '');
$aspirasi_list = [];

if ($conn) {
    // Tentukan NIS yang dipakai
    $nis_query = $siswa_nis ?? ($nis_cari ?: null);

    if ($nis_query || $siswa_nis) {
        $where = ["1=1"];
        if ($nis_query) $where[] = "ia.nis = '" . $conn->real_escape_string($nis_query) . "'";
        if ($filter_status) {
            $fs = $conn->real_escape_string($filter_status);
            $where[] = $fs === 'Menunggu' ? "(ta.status IS NULL OR ta.status='Menunggu')" : "ta.status='$fs'";
        }
        if ($search) {
            $s = $conn->real_escape_string($search);
            $where[] = "(ia.lokasi LIKE '%$s%' OR ia.ket LIKE '%$s%' OR tk.ket_kategori LIKE '%$s%')";
        }

        $r = $conn->query("SELECT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, ia.tanggal_input,
            tk.ket_kategori, ta.status, ta.feedback
            FROM input_aspirasi ia
            LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
            LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
            WHERE " . implode(' AND ', $where) . " ORDER BY ia.id_pelaporan DESC");
        if ($r) while ($row = $r->fetch_assoc()) $aspirasi_list[] = $row;
    }
}

nav_header('Riwayat Aspirasi', 'riwayat-aspirasi.php', 'siswa');
?>

<div class="section">
    <div class="container">
        <h2>Riwayat Aspirasi</h2>
        <p class="page-subtitle">Pantau status aspirasi yang sudah Anda kirimkan</p>

        <?php if (!$siswa_nis): ?>
        <!-- Cari berdasarkan NIS jika belum login -->
        <div class="form-card mb-20">
            <p style="margin:0 0 12px;color:var(--text-muted);font-size:14px">
                Masukkan NIS untuk melihat riwayat aspirasi Anda.
            </p>
            <form method="GET" action="" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
                <div class="form-group" style="flex:1;min-width:200px;margin:0">
                    <label>NIS</label>
                    <input type="text" name="nis_cari" class="input-control"
                           placeholder="Contoh: 2401234567"
                           value="<?php echo htmlspecialchars($nis_cari); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Cari Riwayat</button>
            </form>
        </div>
        <?php else: ?>
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" class="input-control filter-search"
                       placeholder="Cari lokasi, deskripsi, kategori..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" class="input-control filter-select">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" <?php echo $filter_status === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Proses"   <?php echo $filter_status === 'Proses'   ? 'selected' : ''; ?>>Proses</option>
                    <option value="Selesai"  <?php echo $filter_status === 'Selesai'  ? 'selected' : ''; ?>>Selesai</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($filter_status || $search): ?>
                <a href="riwayat-aspirasi.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <?php if (!empty($aspirasi_list)): ?>
            <div class="list-info">Menampilkan <?php echo count($aspirasi_list); ?> aspirasi</div>
            <?php foreach ($aspirasi_list as $asp):
                $status = $asp['status'] ?? 'Menunggu';
                $badge  = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-menunggu');
            ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <div class="dashboard-card-title">
                            <?php echo htmlspecialchars($asp['ket_kategori'] ?? 'Aspirasi'); ?>
                            <span style="font-size:12px;color:var(--text-muted);font-weight:400">
                                &mdash; NIS: <?php echo htmlspecialchars($asp['nis']); ?>
                            </span>
                        </div>
                        <div class="dashboard-card-meta">
                            <?php echo $asp['tanggal_input'] ? date('d-m-Y H:i', strtotime($asp['tanggal_input'])) : '-'; ?>
                            &nbsp;|&nbsp; <?php echo htmlspecialchars($asp['lokasi']); ?>
                        </div>
                    </div>
                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                </div>
                <p class="card-desc">
                    <?php echo htmlspecialchars(mb_substr($asp['ket'], 0, 150));
                    if (mb_strlen($asp['ket']) > 150) echo '...'; ?>
                </p>
                <?php if (!empty($asp['feedback'])): ?>
                <div class="feedback-preview">
                    <strong>Umpan Balik Admin:</strong> <?php echo htmlspecialchars($asp['feedback']); ?>
                </div>
                <?php endif; ?>
                <a href="detail-aspirasi.php?id=<?php echo $asp['id_pelaporan']; ?>"
                   class="btn btn-sm btn-primary btn-auto mt-10">Lihat Detail</a>
            </div>
            <?php endforeach; ?>
        <?php elseif ($siswa_nis || $nis_cari): ?>
        <div class="empty-state">
            <div class="empty-state-title">Belum ada aspirasi</div>
            <div class="empty-state-desc">NIS ini belum pernah mengirimkan aspirasi.</div>
            <a href="aspirasi-siswa.php" class="btn btn-primary mt-16">Buat Aspirasi Sekarang</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php nav_footer(); ?>
