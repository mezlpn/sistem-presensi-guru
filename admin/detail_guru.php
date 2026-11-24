<?php
// ============================================================
// admin/detail_guru.php  -  halaman personal satu guru (+ validasi izin/sakit)
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

$nama_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$id_guru = (int)($_GET['id'] ?? 0);
$bulan   = (int)($_GET['bulan'] ?? date('n'));
$tahun   = (int)($_GET['tahun'] ?? date('Y'));
if ($bulan < 1 || $bulan > 12) { $bulan = (int)date('n'); }
if ($tahun < 2000 || $tahun > 2100) { $tahun = (int)date('Y'); }

$guru = ambil_satu($koneksi, "SELECT * FROM guru WHERE id_guru = ?", "i", [$id_guru]);

if (!$guru) {
    set_pesan("Data guru tidak ditemukan!");
    header("Location: data_guru.php");
    exit;
}

// Ambil semua catatan absensi guru ini pada bulan & tahun terpilih
$daftar_absen = ambil_semua(
    $koneksi,
    "SELECT id_absensi, tanggal, status, status_approval, keterangan, bukti_foto, waktu_absen
     FROM absensi
     WHERE id_guru = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
     ORDER BY tanggal",
    "iii",
    [$id_guru, $bulan, $tahun]
);

// Hitung jumlah hadir/izin/sakit yang sudah disetujui
$jumlah = ['hadir' => 0, 'izin' => 0, 'sakit' => 0];
foreach ($daftar_absen as $a) {
    if ($a['status_approval'] === 'disetujui' && isset($jumlah[$a['status']])) {
        $jumlah[$a['status']]++;
    }
}

$link_kembali = "detail_guru.php?id=" . $id_guru . "&bulan=" . $bulan . "&tahun=" . $tahun;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Guru - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Detail Guru</h2>

        <?php tampilkan_pesan(); ?>

        <table class="tabel-detail">
            <tr><th>Nama</th><td><?= e($guru['nama']) ?></td></tr>
            <tr><th>NIP</th><td><?= e($guru['nip']) ?></td></tr>
            <tr><th>Jabatan</th><td><?= e($guru['jabatan']) ?></td></tr>
            <tr><th>Status Kepegawaian</th><td><?= e($guru['status_kepegawaian']) ?></td></tr>
            <tr><th>Mata Pelajaran</th><td><?= e($guru['mapel']) ?></td></tr>
            <tr><th>Tanggal Bergabung</th><td><?= e(date('d-m-Y', strtotime($guru['tanggal_bergabung']))) ?></td></tr>
            <tr><th>No. HP</th><td><?= e($guru['no_hp'] != '' ? $guru['no_hp'] : '-') ?></td></tr>
        </table>

        <h3>Rincian Absensi</h3>

        <!-- Filter bulan & tahun. Input hidden membawa id guru supaya tidak hilang. -->
        <form method="GET" class="filter">
            <input type="hidden" name="id" value="<?= $id_guru ?>">
            <select name="bulan">
                <?php foreach ($nama_bulan as $angka => $nama) { ?>
                    <option value="<?= $angka ?>" <?= $bulan == $angka ? 'selected' : '' ?>><?= $nama ?></option>
                <?php } ?>
            </select>
            <input type="number" name="tahun" value="<?= $tahun ?>" min="2000" max="2100">
            <button type="submit">Tampilkan</button>
        </form>

        <div class="kartu-ringkas">
            <div class="kartu hadir">Hadir<br><strong><?= $jumlah['hadir'] ?></strong></div>
            <div class="kartu izin">Izin<br><strong><?= $jumlah['izin'] ?></strong></div>
            <div class="kartu sakit">Sakit<br><strong><?= $jumlah['sakit'] ?></strong></div>
        </div>

        <div class="tabel-wrap">
        <table class="tabel">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Persetujuan</th>
                <th>Alasan</th>
                <th>Bukti Foto</th>
                <th>Jam Absen</th>
                <th>Aksi</th>
            </tr>

            <?php if (count($daftar_absen) == 0) { ?>
                <tr><td colspan="8">Tidak ada data absensi pada <?= $nama_bulan[$bulan] ?> <?= $tahun ?>.</td></tr>
            <?php } ?>

            <?php $no = 1; foreach ($daftar_absen as $a) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e(date('d-m-Y', strtotime($a['tanggal']))) ?></td>
                <td class="status-<?= e($a['status']) ?>"><?= e(ucfirst($a['status'])) ?></td>
                <td>
                    <span class="badge badge-<?= e($a['status_approval']) ?>">
                        <?= e(ucfirst($a['status_approval'])) ?>
                    </span>
                </td>
                <td><?= e($a['keterangan'] ?: '-') ?></td>
                <td>
                    <?php if (!empty($a['bukti_foto'])) { ?>
                        <a href="../uploads/bukti/<?= e(basename($a['bukti_foto'])) ?>" target="_blank">
                            <img src="../uploads/bukti/<?= e(basename($a['bukti_foto'])) ?>" alt="Bukti" class="bukti-img">
                        </a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td><?= e(date('H:i', strtotime($a['waktu_absen']))) ?></td>
                <td>
                    <?php if (in_array($a['status'], ['izin', 'sakit']) && $a['status_approval'] === 'pending') { ?>
                        <form method="POST" action="approval_absen.php" class="form-inline">
                            <input type="hidden" name="id_absensi" value="<?= (int)$a['id_absensi'] ?>">
                            <input type="hidden" name="keputusan" value="disetujui">
                            <input type="hidden" name="kembali" value="<?= e($link_kembali) ?>">
                            <button type="submit" class="btn-setuju">Setujui</button>
                        </form>
                        <form method="POST" action="approval_absen.php" class="form-inline">
                            <input type="hidden" name="id_absensi" value="<?= (int)$a['id_absensi'] ?>">
                            <input type="hidden" name="keputusan" value="ditolak">
                            <input type="hidden" name="kembali" value="<?= e($link_kembali) ?>">
                            <button type="submit" class="btn-tolak">Tolak</button>
                        </form>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        </div>

        <a href="data_guru.php">&laquo; Kembali ke Data Guru</a>
    </div>
</body>
</html>