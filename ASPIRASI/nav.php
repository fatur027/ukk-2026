<?php
/**
 * nav.php — Shared navigation helper
 * Usage: include 'nav.php'; then call nav_header($title, $active, $role)
 * $role: 'admin' | 'siswa'
 */
function nav_script(): string {
    return '<script>
document.addEventListener("DOMContentLoaded",function(){
    var t=document.querySelector(".nav-toggle"),n=document.querySelector(".header-nav");
    if(!t||!n)return;
    t.addEventListener("click",function(){n.classList.toggle("open");t.classList.toggle("open");});
    document.addEventListener("click",function(e){
        if(!n.contains(e.target)&&!t.contains(e.target)){n.classList.remove("open");t.classList.remove("open");}
    });
});
</script>';
}

function nav_header(string $title, string $active = '', string $role = 'admin'): void {
    echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . htmlspecialchars($title) . ' | ASPIRASI SMK Negeri 5 Telkom</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="logo-section">
            <img src="img/Logo smk telkom.png" alt="Logo SMK">
            <h1><a href="' . ($role === 'admin' ? 'dashboard-admin.php' : 'dashboard-siswa.php') . '">ASPIRASI SMK Negeri 5 Telkom</a></h1>
        </div>
        <button class="nav-toggle" type="button" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <nav class="header-nav">
            <ul>';

    if ($role === 'admin') {
        $links = [
            'dashboard-admin.php'       => 'Dashboard',
            'manage-aspirasi.php'       => 'Kelola Aspirasi',
            'manage-siswa.php'          => 'Kelola Siswa',
            'data_kategori.php'         => 'Kategori',
            'history-aspirasi-admin.php'=> 'History',
            'profil.php'                => 'Profil',
        ];
    } else {
        $links = [
            'dashboard-siswa.php'  => 'Dashboard',
            'aspirasi-siswa.php'   => 'Buat Aspirasi',
            'riwayat-aspirasi.php' => 'Riwayat Aspirasi',
            'profil.php'           => 'Profil',
        ];
    }

    foreach ($links as $href => $label) {
        $cls = ($href === $active) ? ' active' : '';
        echo "\n                <li><a href=\"$href\" class=\"nav-link$cls\">$label</a></li>";
    }

    echo '
                <li style="margin-left:auto"><a href="keluar.php" class="nav-link nav-logout">Keluar</a></li>
            </ul>
        </nav>
    </div>
</header>';
}

function nav_footer(): void {
    echo '
<footer>
    <div class="container">
        <small>&copy; ' . date('Y') . ' ASPIRASI SMK Negeri 5 Telkom Banda Aceh</small>
    </div>
</footer>' . nav_script() . '
</body>
</html>';
}
