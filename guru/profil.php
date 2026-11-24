<?php
// ============================================================
// guru/profil.php  -  menampilkan data diri guru yang sedang login
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('guru');

$id_guru = $_SESSION['id_guru'];

// Data akun dan kepegawaian guru ada di satu tabel (guru), jadi cukup 1 SELECT
$guru = ambil_satu($koneksi, "SELECT * FROM guru WHERE id_guru = ?", "i", [$id_guru]);

// Ringkasan absensi bulan ini (hanya menghitung yang sudah disetujui)
$ringkas = ambil_satu(
    $koneksi,
    "SELECT COALESCE(SUM(status = 'hadir' AND status_approval = 'disetujui'), 0) AS hadir,
            COALESCE(SUM(status = 'izin'  AND status_approval = 'disetujui'), 0) AS izin,
            COALESCE(SUM(status = 'sakit' AND status_approval = 'disetujui'), 0) AS sakit
     FROM absensi
     WHERE id_guru = ?
       AND MONTH(tanggal) = MONTH(CURDATE())
       AND YEAR(tanggal)  = YEAR(CURDATE())",
    "i",
    [$id_guru]
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Profil Saya</h2>

        <table class="tabel-detail">
            <tr><th>Nama</th><td><?= e($guru['nama']) ?></td></tr>
            <tr><th>Email</th><td><?= e($guru['email']) ?></td></tr>
            <tr><th>NIP</th><td><?= e($guru['nip']) ?></td></tr>
            <tr><th>Jabatan</th><td><?= e($guru['jabatan']) ?></td></tr>
            <tr><th>Status Kepegawaian</th><td><?= e($guru['status_kepegawaian']) ?></td></tr>
            <tr><th>Mata Pelajaran</th><td><?= e($guru['mapel']) ?></td></tr>
            <tr><th>Tanggal Bergabung</th><td><?= e(date('d-m-Y', strtotime($guru['tanggal_bergabung']))) ?></td></tr>
            <tr><th>No. HP</th><td><?= e($guru['no_hp'] != '' ? $guru['no_hp'] : '-') ?></td></tr>
        </table>

        <h3>Absensi Bulan Ini</h3>
        <div class="kartu-ringkas">
            <div class="kartu hadir">Hadir<br><strong><?= (int)$ringkas['hadir'] ?></strong></div>
            <div class="kartu izin">Izin<br><strong><?= (int)$ringkas['izin'] ?></strong></div>
            <div class="kartu sakit">Sakit<br><strong><?= (int)$ringkas['sakit'] ?></strong></div>
        </div>
    </div>
</body>
</html>