<?php
// ============================================================
// admin/dashboard.php  -  ringkasan kehadiran guru HARI INI
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

// Jumlah seluruh guru
$total = ambil_satu($koneksi, "SELECT COUNT(*) AS total FROM guru");

// Jumlah hadir/izin/sakit hari ini
$hari_ini = ambil_satu(
    $koneksi,
    "SELECT COALESCE(SUM(status = 'hadir'), 0) AS hadir,
            COALESCE(SUM(status = 'izin'), 0)  AS izin,
            COALESCE(SUM(status = 'sakit'), 0) AS sakit
     FROM absensi
     WHERE tanggal = CURDATE()"
);

// Guru yang belum absen = total guru dikurangi yang sudah absen
$sudah  = $hari_ini['hadir'] + $hari_ini['izin'] + $hari_ini['sakit'];
$belum  = $total['total'] - $sudah;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Dashboard Admin</h2>
        <p class="tanggal">Kehadiran guru hari ini (<?= date('d-m-Y') ?>)</p>

        <?php tampilkan_pesan(); ?>

        <div class="kartu-ringkas">
            <div class="kartu total">Total Guru<br><strong><?= (int)$total['total'] ?></strong></div>
            <div class="kartu hadir">Hadir<br><strong><?= (int)$hari_ini['hadir'] ?></strong></div>
            <div class="kartu izin">Izin<br><strong><?= (int)$hari_ini['izin'] ?></strong></div>
            <div class="kartu sakit">Sakit<br><strong><?= (int)$hari_ini['sakit'] ?></strong></div>
            <div class="kartu belum">Belum Absen<br><strong><?= (int)$belum ?></strong></div>
        </div>
    </div>
</body>
</html>
