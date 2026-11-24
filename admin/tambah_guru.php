<?php
// ============================================================
// admin/tambah_guru.php  -  tambah guru baru (C = Create dari CRUD)
// Akun login (email + password yang di-hash) dan data kepegawaian guru
// sama-sama disimpan di tabel guru, jadi cukup 1 INSERT.
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

$error = "";

// Nilai awal form (kosong). Kalau ada error, nilai ini diisi ulang dari POST
// supaya admin tidak perlu mengetik ulang semuanya.
$email = $nama = $nip = $jabatan = $mapel = $tanggal_bergabung = $no_hp = "";
$status_kepegawaian = "PNS";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email              = trim($_POST['email'] ?? '');
    $password           = $_POST['password'] ?? '';
    $nama               = trim($_POST['nama'] ?? '');
    $nip                = trim($_POST['nip'] ?? '');
    $jabatan            = trim($_POST['jabatan'] ?? '');
    $status_kepegawaian = $_POST['status_kepegawaian'] ?? '';
    $mapel              = trim($_POST['mapel'] ?? '');
    $tanggal_bergabung  = $_POST['tanggal_bergabung'] ?? '';
    $no_hp              = trim($_POST['no_hp'] ?? '');

    // ---- Validasi ----
    if ($email == "" || $password == "" || $nama == "" || $nip == "" ||
        $jabatan == "" || $mapel == "" || $tanggal_bergabung == "") {
        $error = "Semua kolom wajib diisi (kecuali No. HP)!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (!in_array($status_kepegawaian, ['PNS', 'PPPK', 'Honorer'])) {
        $error = "Status kepegawaian tidak valid!";
    } else {
        // Cek apakah email / NIP sudah dipakai orang lain
        $cek_email = ambil_satu($koneksi, "SELECT id_guru FROM guru WHERE email = ?", "s", [$email]);
        $cek_nip   = ambil_satu($koneksi, "SELECT id_guru FROM guru WHERE nip = ?", "s", [$nip]);

        if ($cek_email) {
            $error = "Email sudah dipakai, pilih yang lain!";
        } elseif ($cek_nip) {
            $error = "NIP sudah terdaftar!";
        } else {
            // Password TIDAK disimpan polos, tapi di-hash.
            $hash = password_hash($password, PASSWORD_DEFAULT);

            jalankan($koneksi,
                "INSERT INTO guru (email, password, nip, nama, jabatan, status_kepegawaian, mapel, tanggal_bergabung, no_hp)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                "sssssssss",
                [$email, $hash, $nip, $nama, $jabatan, $status_kepegawaian, $mapel, $tanggal_bergabung, $no_hp]);

            set_pesan("Data guru berhasil ditambahkan!");
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
    <title>Tambah Guru - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Tambah Guru</h2>

        <?php if ($error != "") { ?>
            <div class="notif error"><?= e($error) ?></div>
        <?php } ?>

        <form method="POST" class="form">
            <label>Email (untuk login)</label>
            <input type="email" name="email" value="<?= e($email) ?>" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= e($nama) ?>" required>

            <label>NIP</label>
            <input type="text" name="nip" value="<?= e($nip) ?>" required>

            <label>Jabatan</label>
            <input type="text" name="jabatan" value="<?= e($jabatan) ?>" placeholder="Contoh: Guru Mapel, Wali Kelas" required>

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

            <button type="submit">Simpan</button>
            <a href="data_guru.php">Batal</a>
        </form>
    </div>
</body>
</html>
