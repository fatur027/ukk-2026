<?php
session_start();
if (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true) {
    header('Location: ' . (isset($_SESSION['a_global']) ? 'dashboard-admin.php' : 'dashboard-siswa.php'));
} else {
    header('Location: index.php');
}
exit;
