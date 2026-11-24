<?php
// ============================================================
// admin/data_guru.php  -  daftar guru (R = Read dari CRUD)
// Dari sini admin bisa ke: Tambah, Detail, Edit, Hapus.
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

// Ambil semua guru, diurutkan berdasarkan nama
$daftar_guru = ambil_semua($koneksi, "SELECT * FROM guru ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Guru - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Data Guru</h2>

        <?php tampilkan_pesan(); ?>

        <a href="tambah_guru.php" class="btn">+ Tambah Guru</a>

        <div class="tabel-wrap">
        <table class="tabel">
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Mapel</th>
                <th>Aksi</th>
            </tr>

            <?php if (count($daftar_guru) == 0) { ?>
                <tr><td colspan="7">Belum ada data guru.</td></tr>
            <?php } ?>

            <?php $no = 1; foreach ($daftar_guru as $g) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($g['nip']) ?></td>
                <td><?= e($g['nama']) ?></td>
                <td><?= e($g['jabatan']) ?></td>
                <td><?= e($g['status_kepegawaian']) ?></td>
                <td><?= e($g['mapel']) ?></td>
                <td>
                    <a href="detail_guru.php?id=<?= (int)$g['id_guru'] ?>">Detail</a>
                    <a href="edit_guru.php?id=<?= (int)$g['id_guru'] ?>">Edit</a>

                    <!-- Hapus memakai form POST (bukan link) karena menghapus data
                         tidak boleh bisa dilakukan hanya dengan membuka URL.
                         onsubmit confirm() = muncul kotak konfirmasi dulu. -->
                    <form method="POST" action="hapus_guru.php" class="form-inline"
                          onsubmit="return confirm('Yakin hapus guru ini? Data absensinya juga ikut terhapus.');">
                        <input type="hidden" name="id_guru" value="<?= (int)$g['id_guru'] ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
        </div>
    </div>
</body>
</html>
