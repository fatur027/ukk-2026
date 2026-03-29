<?php
include 'db.php';
include 'nav.php';

$info        = ['PHP Version' => phpversion()];
$tables_info = [];

if ($conn) {
    $info['Koneksi'] = 'Terhubung';
    $r = $conn->query("SELECT VERSION() as v");
    if ($r) $info['MySQL'] = $r->fetch_assoc()['v'];
    $r = $conn->query("SHOW TABLES");
    if ($r) {
        while ($row = $r->fetch_row()) {
            $t = $row[0];
            $c = $conn->query("SELECT COUNT(*) as n FROM `$t`");
            $tables_info[$t] = $c ? (int)$c->fetch_assoc()['n'] : 0;
        }
    }
} else {
    $info['Koneksi'] = 'Gagal';
}

$setup_ok = count($tables_info) >= 6;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Sistem | ASPIRASI SMK Negeri 5 Telkom</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="section">
<div class="container" style="max-width:800px">
    <h2>Status Sistem ASPIRASI</h2>
    <p class="page-subtitle">SMK Negeri 5 Telkom Banda Aceh</p>

    <div class="stats-grid">
        <?php foreach ($info as $k => $v): ?>
        <div class="stat-card stat-border-primary">
            <div class="stat-label"><?php echo htmlspecialchars($k); ?></div>
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-top:6px">
                <?php echo htmlspecialchars($v); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($tables_info)): ?>
    <div class="dashboard-section">
        <div class="section-header"><h3>Tabel Database</h3></div>
        <div class="jurusan-list">
            <?php foreach ($tables_info as $tbl => $cnt): ?>
            <div class="jurusan-item">
                <div class="jurusan-name"><?php echo htmlspecialchars($tbl); ?></div>
                <div class="jurusan-count"><?php echo $cnt; ?> record</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap">
        <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
        <?php if ($setup_ok): ?>
            <a href="login-admin.php" class="btn btn-primary">Login Admin</a>
            <a href="dashboard-siswa.php" class="btn btn-info">Masuk sebagai Siswa</a>
        <?php endif; ?>
    </div>
</div>
</div>
<footer>
    <div class="container">
        <small>&copy; <?php echo date('Y'); ?> ASPIRASI SMK Negeri 5 Telkom Banda Aceh</small>
    </div>
</footer>
</body>
</html>
