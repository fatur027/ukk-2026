<?php
session_start();
include 'db.php';
include 'nav.php';

$error   = '';
$success = '';

$default_nis     = $_SESSION['siswa_id']                ?? '';
$default_jurusan = $_SESSION['siswa_global']->jurusan   ?? '';
$default_kelas   = $_SESSION['siswa_global']->kelas     ?? '';

$jurusan_list = ['TJA', 'TKJ', 'RPL', 'PF'];
$kelas_list   = ['X', 'XI', 'XII'];

$kategori_list = [];
if ($conn) {
    $r = $conn->query("SELECT id_kategori, ket_kategori FROM tb_kategori ORDER BY ket_kategori");
    if ($r) while ($row = $r->fetch_assoc()) $kategori_list[] = $row;
}

if (isset($_POST['submit_aspirasi'])) {
    $nis_input   = trim($_POST['nis']          ?? '');
    $kelas_input = trim($_POST['kelas']        ?? '');
    $jur_input   = trim($_POST['jurusan']      ?? '');
    $id_kat      = (int)($_POST['id_kategori'] ?? 0);
    $lokasi      = trim($_POST['lokasi']        ?? '');
    $ket         = trim($_POST['ket']           ?? '');

    if (!$nis_input || !$kelas_input || !$jur_input || !$id_kat || !$lokasi || !$ket) {
        $error = 'Semua field wajib diisi.';
    } elseif (!$conn) {
        $error = 'Koneksi database gagal.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO input_aspirasi (nis, jurusan, id_kategori, lokasi, ket) VALUES (?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            // id_kategori INTEGER; lokasi & ket Teks, jadi parameter tipe menunjuk ssiss.
            $stmt->bind_param("ssiss", $nis_input, $jur_input, $id_kat, $lokasi, $ket);
            if ($stmt->execute()) {
                $success = 'Aspirasi berhasil dikirim. Terima kasih atas masukan Anda.';
                $_SESSION['siswa_id']     = $nis_input;
                $_SESSION['status_login'] = true;
                $_SESSION['siswa_global'] = (object)[
                    'nis'     => $nis_input,
                    'nama'    => $nis_input,
                    'kelas'   => $kelas_input,
                    'jurusan' => $jur_input,
                ];
                $default_nis     = $nis_input;
                $default_jurusan = $jur_input;
                $default_kelas   = $kelas_input;
            } else {
                $error = 'Gagal menyimpan aspirasi.';
            }
            $stmt->close();
        }
    }
}

nav_header('Buat Aspirasi', 'aspirasi-siswa.php', 'siswa');
?>

<div class="section">
    <div class="container">
        <h2>Buat Aspirasi Baru</h2>
        <p class="page-subtitle">Sampaikan aspirasi Anda untuk SMK Negeri 5 Telkom Banda Aceh</p>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="">
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="nis">NIS <span class="required">*</span></label>
                        <input type="text" id="nis" name="nis" class="input-control"
                               placeholder="Contoh: 2401234567"
                               value="<?php echo htmlspecialchars($default_nis); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="kelas">Kelas <span class="required">*</span></label>
                        <select id="kelas" name="kelas" class="input-control" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelas_list as $k): ?>
                                <option value="<?php echo $k; ?>"
                                    <?php echo ($default_kelas === $k) ? 'selected' : ''; ?>>
                                    <?php echo $k; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="jurusan">Jurusan <span class="required">*</span></label>
                        <select id="jurusan" name="jurusan" class="input-control" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <?php foreach ($jurusan_list as $jur): ?>
                                <option value="<?php echo $jur; ?>"
                                    <?php echo ($default_jurusan === $jur) ? 'selected' : ''; ?>>
                                    <?php echo $jur; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_kategori">Kategori Aspirasi <span class="required">*</span></label>
                        <select id="id_kategori" name="id_kategori" class="input-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_list as $kat): ?>
                                <option value="<?php echo $kat['id_kategori']; ?>">
                                    <?php echo htmlspecialchars($kat['ket_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="lokasi">Lokasi / Tempat <span class="required">*</span></label>
                    <input type="text" id="lokasi" name="lokasi" class="input-control"
                           placeholder="Contoh: Ruang Kelas X RPL, Lab Komputer, Kantin, Toilet Lantai 2..."
                           required>
                </div>

                <div class="form-group">
                    <label for="ket">Deskripsi Aspirasi <span class="required">*</span></label>
                    <textarea id="ket" name="ket" class="input-control" rows="5"
                              placeholder="Jelaskan aspirasi Anda secara detail..." required></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" name="submit_aspirasi" class="btn btn-primary">Kirim Aspirasi</button>
                    <a href="dashboard-siswa.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php nav_footer(); ?>
