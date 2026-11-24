<?php
// ============================================================
// admin/edit_guru.php  -  ubah data guru (U = Update dari CRUD)
// Alur: id guru dibawa lewat URL (?id=3) -> ambil data lama -> tampilkan di form
//       -> saat disubmit, UPDATE ke database.
// Email tidak bisa diubah. Password hanya diganti kalau kolomnya diisi.
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

// (int) = paksa jadi angka, supaya aman
$id_guru = (int)($_GET['id'] ?? 0);

$guru = ambil_satu($koneksi, "SELECT * FROM guru WHERE id_guru = ?", "i", [$id_guru]);

// Kalau id tidak ditemukan -> kembali ke daftar
if (!$guru) {
    set_pesan("Data guru tidak ditemukan!");
    header("Location: data_guru.php");
    exit;
}

$error = "";

// Isi nilai form dengan data lama dari database
$nama               = $guru['nama'];
$nip                = $guru['nip'];
$jabatan            = $guru['jabatan'];
$status_kepegawaian = $guru['status_kepegawaian'];
$mapel              = $guru['mapel'];
$tanggal_bergabung  = $guru['tanggal_bergabung'];
$no_hp              = $guru['no_hp'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Timpa dengan data baru dari form
    $nama               = trim($_POST['nama'] ?? '');
    $nip                = trim($_POST['nip'] ?? '');
    $jabatan            = trim($_POST['jabatan'] ?? '');
    $status_kepegawaian = $_POST['status_kepegawaian'] ?? '';
    $mapel              = trim($_POST['mapel'] ?? '');
    $tanggal_bergabung  = $_POST['tanggal_bergabung'] ?? '';
    $no_hp              = trim($_POST['no_hp'] ?? '');
    $password_baru      = $_POST['password_baru'] ?? '';

    if ($nama == "" || $nip == "" || $jabatan == "" || $mapel == "" || $tanggal_bergabung == "") {
        $error = "Semua kolom wajib diisi (kecuali No. HP dan password baru)!";
    } elseif (!in_array($status_kepegawaian, ['PNS', 'PPPK', 'Honorer'])) {
        $error = "Status kepegawaian tidak valid!";
    } else {
        // Cek NIP tidak bentrok dengan guru LAIN (id_guru <> ? artinya "bukan dia sendiri")
        $cek_nip = ambil_satu($koneksi,
            "SELECT id_guru FROM guru WHERE nip = ? AND id_guru <> ?",
            "si", [$nip, $id_guru]);

        if ($cek_nip) {
            $error = "NIP sudah dipakai guru lain!";
        } else {
            jalankan($koneksi,
                "UPDATE guru
                 SET nip = ?, nama = ?, jabatan = ?, status_kepegawaian = ?,
                     mapel = ?, tanggal_bergabung = ?, no_hp = ?
                 WHERE id_guru = ?",
                "sssssssi",
                [$nip, $nama, $jabatan, $status_kepegawaian, $mapel, $tanggal_bergabung, $no_hp, $id_guru]);

            // Ganti password hanya kalau admin mengisi kolom password baru
            if ($password_baru != "") {
                $hash = password_hash($password_baru, PASSWORD_DEFAULT);
                jalankan($koneksi,
                    "UPDATE guru SET password = ? WHERE id_guru = ?",
                    "si", [$hash, $id_guru]);
            }

            set_pesan("Data guru berhasil diperbarui!");
            header("Location: data_guru.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Guru - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Edit Guru</h2>

        <?php if ($error != "") { ?>
            <div class="notif error"><?= e($error) ?></div>
        <?php } ?>

        <form method="POST" class="form">
            <label>Email</label>
            <input type="email" value="<?= e($guru['email']) ?>" disabled>

            <label>Password Baru (kosongkan jika tidak diganti)</label>
            <input type="password" name="password_baru">

            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= e($nama) ?>" required>

            <label>NIP</label>
            <input type="text" name="nip" value="<?= e($nip) ?>" required>

            <label>Jabatan</label>
            <input type="text" name="jabatan" value="<?= e($jabatan) ?>" required>

            <label>Status Kepegawaian</label>
            <select name="status_kepegawaian">
                <?php foreach (['PNS', 'PPPK', 'Honorer'] as $s) { ?>
                    <option value="<?= $s ?>" <?= $status_kepegawaian == $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php } ?>
            </select>

            <label>Mata Pelajaran</label>
            <input type="text" name="mapel" value="<?= e($mapel) ?>" required>

            <label>Tanggal Bergabung</label>
            <input type="date" name="tanggal_bergabung" value="<?= e($tanggal_bergabung) ?>" required>

            <label>No. HP (opsional)</label>
            <input type="text" name="no_hp" value="<?= e($no_hp) ?>">

            <button type="submit">Simpan Perubahan</button>
            <a href="data_guru.php">Batal</a>
        </form>
    </div>
</body>
</html>
