<?php
session_start();
include 'db.php';
include 'nav.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: riwayat-aspirasi.php'); exit; }

$asp     = null;
$history = [];

if ($conn) {
    $stmt = $conn->prepare("SELECT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, ia.tanggal_input, ia.jurusan,
        tk.ket_kategori, ta.status, ta.feedback
        FROM input_aspirasi ia
        LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
        LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
        WHERE ia.id_pelaporan = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $asp = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$asp) { header('Location: riwayat-aspirasi.php'); exit; }

    $hs = $conn->prepare("SELECT old_status, new_status, new_feedback, changed_by, changed_at
        FROM tb_history_aspirasi WHERE id_pelaporan=? ORDER BY changed_at DESC");
    if ($hs) {
        $hs->bind_param("i", $id);
        $hs->execute();
        $hr = $hs->get_result();
        while ($row = $hr->fetch_assoc()) $history[] = $row;
        $hs->close();
    }
}

$status    = $asp['status'] ?? 'Menunggu';
$badge     = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-menunggu');
$steps     = ['Menunggu', 'Proses', 'Selesai'];
$step_idx  = array_search($status, $steps);
if ($step_idx === false) $step_idx = 0;

nav_header('Detail Aspirasi', 'riwayat-aspirasi.php', 'siswa');
?>

<div class="section">
    <div class="container">
        <div class="page-back">
            <a href="riwayat-aspirasi.php" class="btn btn-sm btn-secondary">← Kembali ke Riwayat</a>
        </div>

        <h2>Detail Aspirasi #<?php echo $asp['id_pelaporan']; ?></h2>

        <div class="detail-card">
            <div class="detail-card-header">
                <div>
                    <div class="detail-title"><?php echo htmlspecialchars($asp['ket_kategori'] ?? 'Aspirasi'); ?></div>
                    <div class="detail-meta">
                        NIS: <?php echo htmlspecialchars($asp['nis']); ?> &nbsp;|&nbsp;
                        Jurusan: <?php echo htmlspecialchars($asp['jurusan'] ?? '-'); ?> &nbsp;|&nbsp;
                        <?php echo $asp['tanggal_input'] ? date('d M Y, H:i', strtotime($asp['tanggal_input'])) : '-'; ?>
                    </div>
                </div>
                <span class="badge <?php echo $badge; ?> badge-lg"><?php echo htmlspecialchars($status); ?></span>
            </div>

            <div class="detail-section">
                <div class="detail-label">Lokasi / Tempat</div>
                <div class="detail-value"><?php echo htmlspecialchars($asp['lokasi']); ?></div>
            </div>

            <div class="detail-section">
                <div class="detail-label">Deskripsi Aspirasi</div>
                <div class="detail-value detail-desc"><?php echo nl2br(htmlspecialchars($asp['ket'])); ?></div>
            </div>

            <?php if (!empty($asp['feedback'])): ?>
            <div class="detail-section detail-feedback">
                <div class="detail-label">Umpan Balik Admin</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($asp['feedback'])); ?></div>
            </div>
            <?php endif; ?>

            <div class="detail-section" style="border-bottom:none">
                <div class="detail-label">Progres Penanganan</div>
                <div class="progress-steps" style="margin-top:10px">
                    <?php foreach ($steps as $i => $step): ?>
                    <div class="progress-step <?php echo $i <= $step_idx ? 'progress-step-done' : ''; ?>">
                        <div class="progress-dot"></div>
                        <div class="progress-label"><?php echo $step; ?></div>
                    </div>
                    <?php if ($i < count($steps) - 1): ?>
                    <div class="progress-line <?php echo $i < $step_idx ? 'progress-line-done' : ''; ?>"></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($history)): ?>
        <div class="detail-card mt-24">
            <h3>Riwayat Perubahan Status</h3>
            <div class="history-list">
                <?php foreach ($history as $h):
                    $ob = $h['old_status'] ?? 'Menunggu';
                    $nb = $h['new_status'];
                    $ob_cls = $ob === 'Selesai' ? 'badge-selesai' : ($ob === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                    $nb_cls = $nb === 'Selesai' ? 'badge-selesai' : ($nb === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                ?>
                <div class="history-item">
                    <div class="history-meta">
                        <?php echo $h['changed_at'] ? date('d M Y H:i', strtotime($h['changed_at'])) : '-'; ?>
                        &mdash; oleh <?php echo htmlspecialchars($h['changed_by'] ?? 'Admin'); ?>
                    </div>
                    <div class="history-change">
                        Status berubah dari
                        <span class="badge badge-sm <?php echo $ob_cls; ?>"><?php echo htmlspecialchars($ob); ?></span>
                        menjadi
                        <span class="badge badge-sm <?php echo $nb_cls; ?>"><?php echo htmlspecialchars($nb); ?></span>
                    </div>
                    <?php if (!empty($h['new_feedback'])): ?>
                    <div class="history-feedback">Catatan: <?php echo htmlspecialchars($h['new_feedback']); ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php nav_footer(); ?>
