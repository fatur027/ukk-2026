<?php
session_start();
include 'db.php';
include 'nav.php';

if (!isset($_SESSION['a_global']) || $_SESSION['status_login'] !== true) {
    header('Location: login-admin.php'); exit;
}

$error   = '';
$success = '';
$search_nis   = trim($_GET['search'] ?? '');
$filter_kelas = trim($_GET['kelas']  ?? '');

// Tambah siswa
if (isset($_POST['add_siswa'])) {
    $nis     = trim($conn->real_escape_string($_POST['nis']        ?? ''));
    $nama    = trim($conn->real_escape_string($_POST['nama_siswa'] ?? ''));
    $kelas   = trim($conn->real_escape_string($_POST['kelas']      ?? ''));
    $jurusan = trim($conn->real_escape_string($_POST['jurusan']    ?? ''));

    if (!$nis || !$kelas || !$jurusan) {
        $error = 'NIS, Kelas, dan Jurusan harus diisi.';
    } else {
        $chk = $conn->query("SELECT COUNT(*) as c FROM tb_siswa WHERE nis='$nis'");
        if ($chk && (int)$chk->fetch_assoc()['c'] > 0) {
            $error = 'NIS sudah terdaftar.';
        } elseif ($conn->query("INSERT INTO tb_siswa (nis, nama_siswa, kelas, jurusan) VALUES ('$nis','$nama','$kelas','$jurusan')")) {
            header("Location: manage-siswa.php"); exit;
        } else {
            $error = 'Gagal menambahkan siswa.';
        }
    }
}

// Edit siswa
if (isset($_POST['edit_siswa'])) {
    $nis     = trim($conn->real_escape_string($_POST['nis']        ?? ''));
    $nama    = trim($conn->real_escape_string($_POST['nama_siswa'] ?? ''));
    $kelas   = trim($conn->real_escape_string($_POST['kelas']      ?? ''));
    $jurusan = trim($conn->real_escape_string($_POST['jurusan']    ?? ''));

    if (!$nis || !$kelas || !$jurusan) {
        $error = 'NIS, Kelas, dan Jurusan harus diisi.';
    } elseif ($conn->query("UPDATE tb_siswa SET nama_siswa='$nama', kelas='$kelas', jurusan='$jurusan' WHERE nis='$nis'")) {
        header("Location: manage-siswa.php"); exit;
    } else {
        $error = 'Gagal memperbarui data siswa.';
    }
}

// Hapus siswa
if (isset($_POST['delete_nis'])) {
    $del = $conn->real_escape_string($_POST['delete_nis']);
    $chk = $conn->query("SELECT COUNT(*) as c FROM input_aspirasi WHERE nis='$del'");
    if ($chk && (int)$chk->fetch_assoc()['c'] > 0) {
        $error = 'Tidak dapat menghapus siswa yang masih memiliki aspirasi.';
    } elseif ($conn->query("DELETE FROM tb_siswa WHERE nis='$del'")) {
        header("Location: manage-siswa.php"); exit;
    }
}

// Ambil data siswa
$where = "WHERE 1=1";
if ($search_nis)   $where .= " AND (ts.nis LIKE '%" . $conn->real_escape_string($search_nis) . "%' OR ts.nama_siswa LIKE '%" . $conn->real_escape_string($search_nis) . "%')";
if ($filter_kelas) $where .= " AND ts.kelas LIKE '%" . $conn->real_escape_string($filter_kelas) . "%'";

$siswa_data = [];
$r = $conn->query("SELECT ts.nis, ts.nama_siswa, ts.kelas, ts.jurusan,
    (SELECT COUNT(*) FROM input_aspirasi WHERE nis=ts.nis) as jumlah_aspirasi
    FROM tb_siswa ts $where ORDER BY ts.nis DESC");
if ($r) while ($row = $r->fetch_assoc()) $siswa_data[] = $row;

// Daftar kelas & jurusan dari data yang ada
$kelas_list = $jurusan_list = [];
$q = $conn->query("SELECT DISTINCT kelas FROM tb_siswa ORDER BY kelas");
if ($q) while ($row = $q->fetch_assoc()) $kelas_list[] = $row['kelas'];
$q = $conn->query("SELECT DISTINCT jurusan FROM tb_siswa ORDER BY jurusan");
if ($q) while ($row = $q->fetch_assoc()) $jurusan_list[] = $row['jurusan'];

// Fallback jika belum ada data
if (empty($kelas_list))   $kelas_list   = ['X', 'XI', 'XII'];
if (empty($jurusan_list)) $jurusan_list = ['TJA', 'TKJ', 'RPL', 'PF'];

$total_siswa = 0; $total_asp = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM tb_siswa");
if ($r) $total_siswa = (int)$r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM input_aspirasi");
if ($r) $total_asp = (int)$r->fetch_assoc()['c'];

nav_header('Kelola Siswa', 'manage-siswa.php', 'admin');
?>

<div class="section">
    <div class="container">
        <div class="section-header-main">
            <h2>Kelola Siswa</h2>
            <button class="btn btn-primary"
                    onclick="document.getElementById('addModal').style.display='flex'">
                Tambah Siswa
            </button>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card stat-border-primary">
                <div class="stat-number"><?php echo $total_siswa; ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
            <div class="stat-card stat-border-warning">
                <div class="stat-number"><?php echo $total_asp; ?></div>
                <div class="stat-label">Total Aspirasi</div>
            </div>
        </div>

        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" class="input-control filter-search"
                       placeholder="Cari NIS atau nama..."
                       value="<?php echo htmlspecialchars($search_nis); ?>">
                <select name="kelas" class="input-control filter-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                    <option value="<?php echo htmlspecialchars($k); ?>"
                        <?php echo $filter_kelas == $k ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($k); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($search_nis || $filter_kelas): ?>
                <a href="manage-siswa.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($siswa_data)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Aspirasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswa_data as $s): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($s['nis']); ?></strong></td>
                        <td><?php echo htmlspecialchars($s['nama_siswa'] ?? '-'); ?></td>
                        <td><span class="badge badge-proses"><?php echo htmlspecialchars($s['kelas'] ?? '-'); ?></span></td>
                        <td><span class="badge badge-menunggu"><?php echo htmlspecialchars($s['jurusan'] ?? '-'); ?></span></td>
                        <td>
                            <?php if ($s['jumlah_aspirasi'] > 0): ?>
                                <span class="badge badge-selesai"><?php echo $s['jumlah_aspirasi']; ?> aspirasi</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-primary"
                                    onclick="openEditSiswa(
                                        '<?php echo htmlspecialchars($s['nis'],ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($s['nama_siswa']??'',ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($s['kelas'],ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($s['jurusan'],ENT_QUOTES); ?>')">
                                    Edit
                                </button>
                                <?php if ($s['jumlah_aspirasi'] == 0): ?>
                                <form method="POST" action="" class="inline-form">
                                    <input type="hidden" name="delete_nis" value="<?php echo htmlspecialchars($s['nis']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin hapus siswa ini?')">Hapus</button>
                                </form>
                                <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled
                                        title="Tidak dapat dihapus karena memiliki aspirasi">Hapus</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-title">Belum ada data siswa</div>
            <div class="empty-state-desc">Tambahkan siswa menggunakan tombol "Tambah Siswa" di atas.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <div class="modal-header">Tambah Siswa Baru</div>
        <form method="POST" action="">
            <input type="hidden" name="add_siswa" value="1">
            <div class="form-group">
                <label>NIS <span class="required">*</span></label>
                <input type="text" name="nis" class="input-control" placeholder="Contoh: 2401234567" required>
            </div>
            <div class="form-group">
                <label>Nama Siswa</label>
                <input type="text" name="nama_siswa" class="input-control" placeholder="Nama lengkap (opsional)">
            </div>
            <div class="form-group">
                <label>Kelas <span class="required">*</span></label>
                <select name="kelas" class="input-control" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_list as $k): ?>
                    <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($k); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jurusan <span class="required">*</span></label>
                <select name="jurusan" class="input-control" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php foreach ($jurusan_list as $j): ?>
                    <option value="<?php echo htmlspecialchars($j); ?>"><?php echo htmlspecialchars($j); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">Tambah</button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addModal').style.display='none'">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <div class="modal-header">Edit Data Siswa</div>
        <form method="POST" action="">
            <input type="hidden" name="edit_siswa" value="1">
            <input type="hidden" id="edit_nis" name="nis">
            <div class="form-group">
                <label>NIS</label>
                <input type="text" id="display_nis" class="input-control" readonly>
            </div>
            <div class="form-group">
                <label>Nama Siswa</label>
                <input type="text" id="edit_nama" name="nama_siswa" class="input-control">
            </div>
            <div class="form-group">
                <label>Kelas <span class="required">*</span></label>
                <select id="edit_kelas" name="kelas" class="input-control" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_list as $k): ?>
                    <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($k); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jurusan <span class="required">*</span></label>
                <select id="edit_jurusan" name="jurusan" class="input-control" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php foreach ($jurusan_list as $j): ?>
                    <option value="<?php echo htmlspecialchars($j); ?>"><?php echo htmlspecialchars($j); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editModal').style.display='none'">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSiswa(nis, nama, kelas, jurusan) {
    document.getElementById('edit_nis').value     = nis;
    document.getElementById('display_nis').value   = nis;
    document.getElementById('edit_nama').value     = nama;
    document.getElementById('edit_kelas').value    = kelas;
    document.getElementById('edit_jurusan').value  = jurusan;
    document.getElementById('editModal').style.display = 'flex';
}
window.addEventListener('click', function(e) {
    ['addModal','editModal'].forEach(function(id) {
        var m = document.getElementById(id);
        if (e.target === m) m.style.display = 'none';
    });
});
</script>

<?php nav_footer(); ?>
