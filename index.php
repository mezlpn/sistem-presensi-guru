<?php
// ============================================================
// index.php : pintu masuk website.
// Isinya kotak pilihan: masuk sebagai Admin atau sebagai Guru.
//   - Pilih Admin -> loginAdmin.php
//   - Pilih Guru  -> loginGuru.php
// Kalau user SUDAH login, tidak perlu memilih lagi -> langsung ke dashboard.
// ============================================================
require 'connection.php';
require 'cek_login.php';

if (isset($_SESSION['role'])) {
    // Nama folder = nama role ('admin' atau 'guru'), jadi bisa digabung langsung
    header("Location: " . $_SESSION['role'] . "/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen SDM Guru</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="halaman-login">
    <div class="kotak-login">
        <h2>Manajemen SDM Guru</h2>
        <p>Silakan pilih masuk sebagai:</p>

        <!-- Dua pilihan. Masing-masing hanyalah link ke halaman login yang berbeda. -->
        <div class="pilihan-role">
            <a href="loginAdmin.php" class="pilihan admin">Masuk sebagai Admin</a>
            <a href="loginGuru.php" class="pilihan guru">Masuk sebagai Guru</a>
        </div>
    </div>
</body>
</html>
