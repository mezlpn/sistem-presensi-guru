<?php
// ============================================================
// guru/dashboard.php  -  halaman absensi guru (+ alasan & foto bukti)
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('guru');

$id_guru = $_SESSION['id_guru'];

$absen = ambil_satu(
    $koneksi,
    "SELECT status, status_approval, keterangan, bukti_foto, waktu_absen,
            TIMESTAMPDIFF(SECOND, waktu_absen, NOW()) AS detik
     FROM absensi
     WHERE id_guru = ? AND tanggal = CURDATE()",
    "i",
    [$id_guru]
);

$sudah_absen = ($absen != null);
$bisa_undo   = $sudah_absen && $absen['detik'] <= 300;
$sisa_menit  = $sudah_absen ? ceil((300 - $absen['detik']) / 60) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Absensi - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Absensi Hari Ini</h2>
        <p class="tanggal"><?= date('d-m-Y') ?></p>

        <?php tampilkan_pesan(); ?>

        <?php if ($sudah_absen) { ?>
            <div class="notif sukses">
                Kamu sudah melakukan absen hari ini dengan status
                <strong><?= e(strtoupper($absen['status'])) ?></strong>
                (pukul <?= e(date('H:i', strtotime($absen['waktu_absen']))) ?>),
                <span class="badge badge-<?= e($absen['status_approval']) ?>">
                    <?= e(strtoupper($absen['status_approval'])) ?>
                </span>.
                <?php if (!empty($absen['keterangan'])) { ?>
                    <br><small>Alasan: <?= e($absen['keterangan']) ?></small>
                <?php } ?>
                <?php if (!empty($absen['bukti_foto'])) { ?>
                    <br><img src="../uploads/bukti/<?= e(basename($absen['bukti_foto'])) ?>" alt="Bukti" class="bukti-img">
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="notif">Kamu belum absen hari ini.</div>
        <?php } ?>

        <form method="POST" action="absen.php" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="absen">

            <div class="box-bukti">
                <label for="keterangan">Alasan / Keterangan Izin atau Sakit (opsional jika sudah upload foto)</label>
                <textarea id="keterangan" name="keterangan" rows="2" placeholder="Contoh: Ada keperluan keluarga / demam sejak pagi" <?= $sudah_absen ? 'disabled' : '' ?>></textarea>

                <label for="bukti_foto">Foto Bukti (opsional jika sudah isi alasan. Format JPG, PNG, WEBP, maks. 2 MB)</label>
                <input type="file" id="bukti_foto" name="bukti_foto" accept=".jpg,.jpeg,.png,.webp" <?= $sudah_absen ? 'disabled' : '' ?>>
            </div>

            <div class="tombol-absen">
                <button type="submit" name="status" value="hadir" class="btn-hadir" <?= $sudah_absen ? 'disabled' : '' ?>>Hadir</button>
                <button type="submit" name="status" value="izin"  class="btn-izin"  <?= $sudah_absen ? 'disabled' : '' ?>>Izin</button>
                <button type="submit" name="status" value="sakit" class="btn-sakit" <?= $sudah_absen ? 'disabled' : '' ?>>Sakit</button>
            </div>
        </form>

        <?php if ($bisa_undo) { ?>
            <form method="POST" action="absen.php" class="form-undo">
                <input type="hidden" name="aksi" value="undo">
                <button type="submit" class="btn-undo">Salah pencet? Batalkan absen</button>
                <small>Tersedia kurang lebih <?= (int)$sisa_menit ?> menit lagi.</small>
            </form>
        <?php } ?>
    </div>
</body>
</html>