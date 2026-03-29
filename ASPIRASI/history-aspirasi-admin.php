<?php
session_start();
include 'db.php';
include 'nav.php';

if (!isset($_SESSION['a_global']) || $_SESSION['status_login'] !== true) {
    header('Location: login-admin.php'); exit;
}

$filter_nis     = trim($_GET['nis']             ?? '');
$filter_status  = trim($_GET['status']          ?? '');
$filter_dari    = trim($_GET['tanggal_dari']    ?? '');
$filter_sampai  = trim($_GET['tanggal_sampai']  ?? '');

$where = "WHERE 1=1";
if ($filter_nis)    $where .= " AND ia.nis LIKE '%" . $conn->real_escape_string($filter_nis) . "%'";
if ($filter_status) $where .= " AND th.new_status='" . $conn->real_escape_string($filter_status) . "'";
if ($filter_dari)   $where .= " AND DATE(th.changed_at)>='" . $conn->real_escape_string($filter_dari) . "'";
if ($filter_sampai) $where .= " AND DATE(th.changed_at)<='" . $conn->real_escape_string($filter_sampai) . "'";

$history_list      = [];
$aspirasi_fallback = [];

$r = $conn->query("SELECT th.id_history, th.id_pelaporan, th.old_status, th.new_status,
    th.new_feedback, th.changed_by, th.changed_at,
    ia.nis, ia.lokasi, ia.ket, tk.ket_kategori, ts.kelas
    FROM tb_history_aspirasi th
    LEFT JOIN input_aspirasi ia ON th.id_pelaporan = ia.id_pelaporan
    LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
    LEFT JOIN tb_siswa ts ON ia.nis = ts.nis
    $where ORDER BY th.changed_at DESC");
if ($r) while ($row = $r->fetch_assoc()) $history_list[] = $row;

// Fallback: tampilkan aspirasi biasa jika belum ada history
if (empty($history_list) && !$filter_nis && !$filter_status && !$filter_dari && !$filter_sampai) {
    $r2 = $conn->query("SELECT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, ia.tanggal_input,
        tk.ket_kategori, COALESCE(ta.status,'Menunggu') as status, ts.kelas
        FROM input_aspirasi ia
        LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
        LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
        LEFT JOIN tb_siswa ts ON ia.nis = ts.nis
        ORDER BY ia.tanggal_input DESC");
    if ($r2) while ($row = $r2->fetch_assoc()) $aspirasi_fallback[] = $row;
}

nav_header('History Aspirasi', 'history-aspirasi-admin.php', 'admin');
?>

<div class="section">
    <div class="container">
        <h2>History Perubahan Aspirasi</h2>
        <p class="page-subtitle">Riwayat seluruh perubahan status aspirasi oleh admin</p>

        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="nis" class="input-control filter-search"
                       placeholder="Cari NIS..." value="<?php echo htmlspecialchars($filter_nis); ?>">
                <select name="status" class="input-control filter-select">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" <?php echo $filter_status === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Proses"   <?php echo $filter_status === 'Proses'   ? 'selected' : ''; ?>>Proses</option>
                    <option value="Selesai"  <?php echo $filter_status === 'Selesai'  ? 'selected' : ''; ?>>Selesai</option>
                </select>
                <input type="date" name="tanggal_dari" class="input-control"
                       value="<?php echo htmlspecialchars($filter_dari); ?>" title="Dari tanggal">
                <input type="date" name="tanggal_sampai" class="input-control"
                       value="<?php echo htmlspecialchars($filter_sampai); ?>" title="Sampai tanggal">
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($filter_nis || $filter_status || $filter_dari || $filter_sampai): ?>
                <a href="history-aspirasi-admin.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($history_list)): ?>
        <div class="list-info">Menampilkan <?php echo count($history_list); ?> riwayat perubahan</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Aspirasi</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>Kategori</th>
                        <th>Status Lama</th>
                        <th>Status Baru</th>
                        <th>Oleh</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history_list as $h):
                        $ob_cls = ($h['old_status'] ?? '') === 'Selesai' ? 'badge-selesai' : (($h['old_status'] ?? '') === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                        $nb_cls = $h['new_status'] === 'Selesai' ? 'badge-selesai' : ($h['new_status'] === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                    ?>
                    <tr>
                        <td>#<?php echo $h['id_pelaporan']; ?></td>
                        <td><?php echo htmlspecialchars($h['nis'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($h['kelas'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($h['ket_kategori'] ?? '-'); ?></td>
                        <td>
                            <?php if ($h['old_status']): ?>
                            <span class="badge <?php echo $ob_cls; ?>"><?php echo htmlspecialchars($h['old_status']); ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?php echo $nb_cls; ?>"><?php echo htmlspecialchars($h['new_status']); ?></span></td>
                        <td><?php echo htmlspecialchars($h['changed_by'] ?? 'Admin'); ?></td>
                        <td><?php echo $h['changed_at'] ? date('d-m-Y H:i', strtotime($h['changed_at'])) : '-'; ?></td>
                    </tr>
                    <?php if (!empty($h['new_feedback'])): ?>
                    <tr class="history-feedback-row">
                        <td colspan="8">
                            <span class="feedback-label">Catatan:</span>
                            <?php echo htmlspecialchars($h['new_feedback']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php elseif (!empty($aspirasi_fallback)): ?>
        <div class="list-info">Menampilkan <?php echo count($aspirasi_fallback); ?> aspirasi (belum ada riwayat perubahan)</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Aspirasi</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aspirasi_fallback as $h):
                        $nb_cls = $h['status'] === 'Selesai' ? 'badge-selesai' : ($h['status'] === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                    ?>
                    <tr>
                        <td>#<?php echo $h['id_pelaporan']; ?></td>
                        <td><?php echo htmlspecialchars($h['nis'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($h['kelas'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($h['ket_kategori'] ?? '-'); ?></td>
                        <td><span class="badge <?php echo $nb_cls; ?>"><?php echo htmlspecialchars($h['status']); ?></span></td>
                        <td><?php echo htmlspecialchars(mb_substr($h['ket'] ?? '-', 0, 80)); ?></td>
                        <td><?php echo $h['tanggal_input'] ? date('d-m-Y', strtotime($h['tanggal_input'])) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-title">Belum ada riwayat perubahan</div>
            <div class="empty-state-desc">Riwayat akan muncul saat admin mengubah status aspirasi.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php nav_footer(); ?>
