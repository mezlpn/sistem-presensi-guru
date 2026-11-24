<?php
// ============================================================
// admin/rekap.php  -  persetujuan izin/sakit & rekap absensi bulanan
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

$nama_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Kalau filter belum dipilih, pakai bulan & tahun sekarang
$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

// Pengaman kalau user mengutak-atik URL
if ($bulan < 1 || $bulan > 12) { $bulan = (int)date('n'); }
if ($tahun < 2000 || $tahun > 2100) { $tahun = (int)date('Y'); }

// Daftar izin / sakit yang masih menunggu persetujuan admin
$pengajuan_pending = ambil_semua(
    $koneksi,
    "SELECT a.id_absensi, a.tanggal, a.status, a.keterangan, a.bukti_foto, a.waktu_absen,
            g.nip, g.nama
     FROM absensi a
     JOIN guru g ON g.id_guru = a.id_guru
     WHERE a.status IN ('izin', 'sakit') AND a.status_approval = 'pending'
     ORDER BY a.tanggal DESC, a.waktu_absen DESC"
);

// Rekap bulanan hanya menghitung absensi yang sudah berstatus 'disetujui'
$rekap = ambil_semua(
    $koneksi,
    "SELECT g.id_guru, g.nip, g.nama,
            COALESCE(SUM(a.status = 'hadir' AND a.status_approval = 'disetujui'), 0) AS hadir,
            COALESCE(SUM(a.status = 'izin'  AND a.status_approval = 'disetujui'), 0) AS izin,
            COALESCE(SUM(a.status = 'sakit' AND a.status_approval = 'disetujui'), 0) AS sakit,
            ROUND(
                SUM(a.status = 'hadir' AND a.status_approval = 'disetujui') /
                NULLIF(SUM(a.status_approval = 'disetujui'), 0) * 100
            ) AS persen_hadir
     FROM guru g
     LEFT JOIN absensi a ON a.id_guru = g.id_guru
          AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
     GROUP BY g.id_guru, g.nip, g.nama
     ORDER BY g.nama",
    "ii",
    [$bulan, $tahun]
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Absensi - SDM Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require '../partials/navbar.php'; ?>

    <div class="konten">
        <h2>Rekap Absensi Guru</h2>

        <?php tampilkan_pesan(); ?>

        <h3>Pengajuan Izin / Sakit Menunggu Persetujuan (<?= count($pengajuan_pending) ?>)</h3>
        <div class="tabel-wrap">
        <table class="tabel">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NIP</th>
                <th>Nama Guru</th>
                <th>Jenis</th>
                <th>Alasan</th>
                <th>Bukti Foto</th>
                <th>Aksi</th>
            </tr>

            <?php if (count($pengajuan_pending) == 0) { ?>
                <tr><td colspan="8">Tidak ada pengajuan izin/sakit yang menunggu persetujuan.</td></tr>
            <?php } ?>

            <?php $no = 1; foreach ($pengajuan_pending as $p) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e(date('d-m-Y', strtotime($p['tanggal']))) ?></td>
                <td><?= e($p['nip']) ?></td>
                <td><?= e($p['nama']) ?></td>
                <td class="status-<?= e($p['status']) ?>"><?= e(strtoupper($p['status'])) ?></td>
                <td><?= e($p['keterangan'] ?: '-') ?></td>
                <td>
                    <?php if (!empty($p['bukti_foto'])) { ?>
                        <a href="../uploads/bukti/<?= e(basename($p['bukti_foto'])) ?>" target="_blank">
                            <img src="../uploads/bukti/<?= e(basename($p['bukti_foto'])) ?>" alt="Bukti" class="bukti-img">
                        </a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td>
                    <form method="POST" action="approval_absen.php" class="form-inline">
                        <input type="hidden" name="id_absensi" value="<?= (int)$p['id_absensi'] ?>">
                        <input type="hidden" name="keputusan" value="disetujui">
                        <input type="hidden" name="kembali" value="rekap.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
                        <button type="submit" class="btn-setuju">Setujui</button>
                    </form>
                    <form method="POST" action="approval_absen.php" class="form-inline">
                        <input type="hidden" name="id_absensi" value="<?= (int)$p['id_absensi'] ?>">
                        <input type="hidden" name="keputusan" value="ditolak">
                        <input type="hidden" name="kembali" value="rekap.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
                        <button type="submit" class="btn-tolak">Tolak</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
        </div>

        <h3 style="margin-top: 24px;">Rekap Bulanan</h3>

        <!-- Filter bulan & tahun. method GET = nilainya muncul di URL -->
        <form method="GET" class="filter">
            <select name="bulan">
                <?php foreach ($nama_bulan as $angka => $nama) { ?>
                    <option value="<?= $angka ?>" <?= $bulan == $angka ? 'selected' : '' ?>><?= $nama ?></option>
                <?php } ?>
            </select>
            <input type="number" name="tahun" value="<?= $tahun ?>" min="2000" max="2100">
            <button type="submit">Tampilkan</button>
        </form>

        <p>Periode: <strong><?= $nama_bulan[$bulan] ?> <?= $tahun ?></strong></p>

        <div class="tabel-wrap">
        <table class="tabel">
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>% Hadir</th>
                <th>Detail</th>
            </tr>

            <?php $no = 1; foreach ($rekap as $r) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($r['nip']) ?></td>
                <td><?= e($r['nama']) ?></td>
                <td><?= (int)$r['hadir'] ?></td>
                <td><?= (int)$r['izin'] ?></td>
                <td><?= (int)$r['sakit'] ?></td>
                <td><?= $r['persen_hadir'] === null ? '-' : (int)$r['persen_hadir'] . '%' ?></td>
                <td><a href="detail_guru.php?id=<?= (int)$r['id_guru'] ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">Lihat</a></td>
            </tr>
            <?php } ?>
        </table>
        </div>
    </div>
</body>
</html>