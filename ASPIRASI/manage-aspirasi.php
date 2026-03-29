<?php
session_start();
include 'db.php';
include 'nav.php';

if (!isset($_SESSION['a_global']) || $_SESSION['status_login'] !== true) {
    header('Location: login-admin.php'); exit;
}

$error   = '';
$success = '';
$search_nis      = trim($_GET['search']   ?? '');
$filter_status   = trim($_GET['status']   ?? '');
$filter_kategori = trim($_GET['kategori'] ?? '');

// Hapus aspirasi
if (isset($_POST['delete_id'])) {
    $del = (int)$_POST['delete_id'];
    if ($del > 0) {
        $conn->query("DELETE FROM tb_history_aspirasi WHERE id_pelaporan=$del");
        $conn->query("DELETE FROM tb_aspirasi WHERE id_pelaporan=$del");
        $conn->query("DELETE FROM input_aspirasi WHERE id_pelaporan=$del");
        header("Location: manage-aspirasi.php"); exit;
    }
}

// Update status
if (isset($_POST['update_status'])) {
    $id = (int)($_POST['id_pelaporan'] ?? 0);
    $ns = trim($_POST['new_status']   ?? '');
    $fb = trim($_POST['feedback']     ?? '');
    $by = $conn->real_escape_string($_SESSION['a_global']->username ?? 'admin');

    if ($id > 0 && $ns) {
        $ns_e = $conn->real_escape_string($ns);
        $fb_e = $conn->real_escape_string($fb);

        $old = $conn->query("SELECT status, feedback FROM tb_aspirasi WHERE id_pelaporan=$id");
        $old_status = $old_fb = null;
        if ($old && $old->num_rows) {
            $od = $old->fetch_assoc();
            $old_status = $od['status'];
            $old_fb     = $od['feedback'];
        }
        $os = $old_status ? "'" . $conn->real_escape_string($old_status) . "'" : "NULL";
        $of = $old_fb     ? "'" . $conn->real_escape_string($old_fb) . "'"     : "NULL";

        $exists = $conn->query("SELECT id_aspirasi FROM tb_aspirasi WHERE id_pelaporan=$id");
        if ($exists && $exists->num_rows) {
            $conn->query("UPDATE tb_aspirasi SET status='$ns_e', feedback='$fb_e' WHERE id_pelaporan=$id");
        } else {
            $conn->query("INSERT INTO tb_aspirasi (id_pelaporan, status, feedback) VALUES ($id,'$ns_e','$fb_e')");
        }
        $conn->query("INSERT INTO tb_history_aspirasi (id_pelaporan, old_status, new_status, old_feedback, new_feedback, changed_by)
            VALUES ($id,$os,'$ns_e',$of,'$fb_e','$by')");
        header("Location: manage-aspirasi.php"); exit;
    }
}

// Ambil data
$where = "WHERE 1=1";
if ($search_nis)      $where .= " AND ia.nis LIKE '%" . $conn->real_escape_string($search_nis) . "%'";
if ($filter_status)   $where .= " AND ta.status='" . $conn->real_escape_string($filter_status) . "'";
if ($filter_kategori) $where .= " AND ia.id_kategori=" . (int)$filter_kategori;

$data = [];
$r = $conn->query("SELECT ia.id_pelaporan, ia.nis, ia.id_kategori, ia.lokasi, ia.ket, ia.tanggal_input,
    tk.ket_kategori, ts.kelas, ta.id_aspirasi, ta.status, ta.feedback
    FROM input_aspirasi ia
    LEFT JOIN tb_aspirasi ta ON ia.id_pelaporan = ta.id_pelaporan
    LEFT JOIN tb_kategori tk ON ia.id_kategori = tk.id_kategori
    LEFT JOIN tb_siswa ts ON ia.nis = ts.nis
    $where ORDER BY ia.id_pelaporan DESC");
if ($r) while ($row = $r->fetch_assoc()) $data[] = $row;

$kategori_list = [];
$q = $conn->query("SELECT id_kategori, ket_kategori FROM tb_kategori ORDER BY ket_kategori");
if ($q) while ($row = $q->fetch_assoc()) $kategori_list[] = $row;

nav_header('Kelola Aspirasi', 'manage-aspirasi.php', 'admin');
?>

<div class="section">
    <div class="container">
        <h2>Kelola Aspirasi</h2>

        <div class="stats-grid">
            <div class="stat-card stat-border-primary">
                <div class="stat-number"><?php echo count($data); ?></div>
                <div class="stat-label">Ditampilkan</div>
            </div>
            <div class="stat-card stat-border-warning">
                <div class="stat-number"><?php echo count(array_filter($data, fn($a) => !$a['status'] || $a['status'] === 'Menunggu')); ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
            <div class="stat-card stat-border-info">
                <div class="stat-number"><?php echo count(array_filter($data, fn($a) => $a['status'] === 'Proses')); ?></div>
                <div class="stat-label">Proses</div>
            </div>
            <div class="stat-card stat-border-success">
                <div class="stat-number"><?php echo count(array_filter($data, fn($a) => $a['status'] === 'Selesai')); ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>

        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" class="input-control filter-search"
                       placeholder="Cari NIS..." value="<?php echo htmlspecialchars($search_nis); ?>">
                <select name="status" class="input-control filter-select">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" <?php echo $filter_status === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Proses"   <?php echo $filter_status === 'Proses'   ? 'selected' : ''; ?>>Proses</option>
                    <option value="Selesai"  <?php echo $filter_status === 'Selesai'  ? 'selected' : ''; ?>>Selesai</option>
                </select>
                <select name="kategori" class="input-control filter-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?php echo $kat['id_kategori']; ?>"
                        <?php echo $filter_kategori == $kat['id_kategori'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kat['ket_kategori']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($search_nis || $filter_status || $filter_kategori): ?>
                <a href="manage-aspirasi.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($data)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $asp):
                        $status = $asp['status'] ?? 'Menunggu';
                        $badge  = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-menunggu');
                    ?>
                    <tr>
                        <td><?php echo $asp['id_pelaporan']; ?></td>
                        <td><?php echo htmlspecialchars($asp['nis']); ?></td>
                        <td><?php echo htmlspecialchars($asp['kelas'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($asp['ket_kategori'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars((trim($asp['lokasi']) !== '' && $asp['lokasi'] !== '0') ? $asp['lokasi'] : 'Lokasi belum diisi'); ?></td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                        <td><?php echo $asp['tanggal_input'] ? date('d-m-Y', strtotime($asp['tanggal_input'])) : '-'; ?></td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-primary"
                                        onclick="openModal(<?php echo htmlspecialchars(json_encode($asp), ENT_QUOTES); ?>)">Edit</button>
                                <a href="detail-aspirasi-admin.php?id=<?php echo $asp['id_pelaporan']; ?>"
                                   class="btn btn-sm btn-info">Detail</a>
                                <form method="POST" action="" class="inline-form">
                                    <input type="hidden" name="delete_id" value="<?php echo $asp['id_pelaporan']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin hapus aspirasi ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-title">Tidak ada data aspirasi</div>
            <div class="empty-state-desc">Coba ubah filter atau tunggu siswa mengirimkan aspirasi.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Status -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-header">Perbarui Status Aspirasi</div>
        <form method="POST" action="">
            <input type="hidden" id="modal_id" name="id_pelaporan">
            <input type="hidden" name="update_status" value="1">
            <div class="form-group">
                <label>NIS Siswa</label>
                <input type="text" id="modal_nis" class="input-control" readonly>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <input type="text" id="modal_kat" class="input-control" readonly>
            </div>
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" id="modal_lokasi" class="input-control" readonly>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="modal_ket" class="input-control" readonly rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="new_status">Status Baru <span class="required">*</span></label>
                <select id="new_status" name="new_status" class="input-control" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Proses">Proses</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modal_feedback">Umpan Balik untuk Siswa</label>
                <textarea id="modal_feedback" name="feedback" class="input-control" rows="3"
                          placeholder="Tambahkan catatan atau tindakan..."></textarea>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(a) {
    document.getElementById('modal_id').value       = a.id_pelaporan || '';
    document.getElementById('modal_nis').value       = a.nis           || '';
    document.getElementById('modal_kat').value       = a.ket_kategori  || '-';
    document.getElementById('modal_lokasi').value    = a.lokasi        || '';
    document.getElementById('modal_ket').value       = a.ket           || '';
    document.getElementById('new_status').value      = a.status        || '';
    document.getElementById('modal_feedback').value  = a.feedback      || '';
    document.getElementById('editModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
window.addEventListener('click', function(e) {
    var m = document.getElementById('editModal');
    if (e.target === m) closeModal();
});
</script>

<?php nav_footer(); ?>
