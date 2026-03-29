<?php
session_start();
include 'db.php';
include 'nav.php';

if (!isset($_SESSION['a_global']) || $_SESSION['status_login'] !== true) {
    header('Location: login-admin.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: manage-aspirasi.php'); exit; }

$asp     = null;
$history = [];

if ($conn) {
    $stmt = $conn->prepare("SELECT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, ia.tanggal_input, ia.jurusan,
        tk.ket_kategori, ta.status, ta.feedback, ts.kelas
        FROM input_aspirasi ia
        LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
        LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
        LEFT JOIN tb_siswa ts ON ia.nis = ts.nis
        WHERE ia.id_pelaporan = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $asp = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$asp) { header('Location: manage-aspirasi.php'); exit; }

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

// Update status dari halaman ini
if (isset($_POST['update_status'])) {
    $ns = $conn->real_escape_string($_POST['new_status'] ?? '');
    $fb = $conn->real_escape_string($_POST['feedback']   ?? '');
    $by = $conn->real_escape_string($_SESSION['a_global']->username ?? 'admin');
    if ($ns) {
        $old_s = $asp['status'];
        $old_f = $asp['feedback'];
        $os    = $old_s ? "'" . $conn->real_escape_string($old_s) . "'" : "NULL";
        $of    = $old_f ? "'" . $conn->real_escape_string($old_f) . "'" : "NULL";

        $exists = $conn->query("SELECT id_aspirasi FROM tb_aspirasi WHERE id_pelaporan=$id");
        if ($exists && $exists->num_rows) {
            $conn->query("UPDATE tb_aspirasi SET status='$ns', feedback='$fb' WHERE id_pelaporan=$id");
        } else {
            $conn->query("INSERT INTO tb_aspirasi (id_pelaporan, status, feedback) VALUES ($id,'$ns','$fb')");
        }
        $conn->query("INSERT INTO tb_history_aspirasi (id_pelaporan, old_status, new_status, old_feedback, new_feedback, changed_by)
            VALUES ($id,$os,'$ns',$of,'$fb','$by')");
        header("Location: detail-aspirasi-admin.php?id=$id"); exit;
    }
}

$status = $asp['status'] ?? 'Menunggu';
$badge  = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-menunggu');

nav_header('Detail Aspirasi', 'manage-aspirasi.php', 'admin');
?>

<div class="section">
    <div class="container">
        <div class="page-back">
            <a href="manage-aspirasi.php" class="btn btn-sm btn-secondary">← Kembali ke Kelola Aspirasi</a>
        </div>

        <h2>Detail Aspirasi #<?php echo $asp['id_pelaporan']; ?></h2>

        <div class="detail-card">
            <div class="detail-card-header">
                <div>
                    <div class="detail-title"><?php echo htmlspecialchars($asp['ket_kategori'] ?? 'Aspirasi'); ?></div>
                    <div class="detail-meta">
                        NIS: <?php echo htmlspecialchars($asp['nis']); ?> &nbsp;|&nbsp;
                        Kelas: <?php echo htmlspecialchars($asp['kelas'] ?? '-'); ?> &nbsp;|&nbsp;
                        Jurusan: <?php echo htmlspecialchars($asp['jurusan'] ?? '-'); ?> &nbsp;|&nbsp;
                        <?php echo $asp['tanggal_input'] ? date('d M Y, H:i', strtotime($asp['tanggal_input'])) : '-'; ?>
                    </div>
                </div>
                <span class="badge <?php echo $badge; ?> badge-lg"><?php echo htmlspecialchars($status); ?></span>
            </div>

            <div class="detail-section">
                <div class="detail-label">Lokasi / Tempat</div>
                <div class="detail-value"><?php echo htmlspecialchars((trim($asp['lokasi']) !== '' && $asp['lokasi'] !== '0') ? $asp['lokasi'] : 'Lokasi belum diisi'); ?></div>
            </div>

            <div class="detail-section">
                <div class="detail-label">Deskripsi Aspirasi</div>
                <div class="detail-value detail-desc"><?php echo nl2br(htmlspecialchars($asp['ket'])); ?></div>
            </div>

            <?php if (!empty($asp['feedback'])): ?>
            <div class="detail-section detail-feedback" style="border-bottom:none">
                <div class="detail-label">Umpan Balik Saat Ini</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($asp['feedback'])); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Form update status -->
        <div class="detail-card mt-24">
            <h3>Perbarui Status</h3>
            <form method="POST" action="">
                <input type="hidden" name="update_status" value="1">
                <div class="form-group">
                    <label>Status Baru <span class="required">*</span></label>
                    <select name="new_status" class="input-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Menunggu" <?php echo $status === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Proses"   <?php echo $status === 'Proses'   ? 'selected' : ''; ?>>Proses</option>
                        <option value="Selesai"  <?php echo $status === 'Selesai'  ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Umpan Balik / Catatan untuk Siswa</label>
                    <textarea name="feedback" class="input-control" rows="4"
                              placeholder="Tambahkan komentar atau tindakan yang sudah/akan dilakukan..."><?php echo htmlspecialchars($asp['feedback'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
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
                        Status:
                        <span class="badge badge-sm <?php echo $ob_cls; ?>"><?php echo htmlspecialchars($ob); ?></span>
                        &rarr;
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
