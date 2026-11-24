<?php
// ============================================================
// admin/approval_absen.php  -  menyetujui atau menolak izin/sakit guru
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: rekap.php");
    exit;
}

$id_absensi = (int)($_POST['id_absensi'] ?? 0);
$keputusan  = $_POST['keputusan'] ?? '';
$kembali    = $_POST['kembali'] ?? 'rekap.php';

// Batasi tujuan redirect hanya ke file di folder admin/
$boleh_kembali = ['rekap.php', 'detail_guru.php'];
$nama_file_kembali = basename(parse_url($kembali, PHP_URL_PATH) ?? '');
if (!in_array($nama_file_kembali, $boleh_kembali)) {
    $kembali = 'rekap.php';
}

if ($id_absensi <= 0 || !in_array($keputusan, ['disetujui', 'ditolak'])) {
    set_pesan("Permintaan persetujuan tidak valid!");
    header("Location: " . $kembali);
    exit;
}

$stmt = jalankan(
    $koneksi,
    "UPDATE absensi
     SET status_approval = ?
     WHERE id_absensi = ? AND status IN ('izin', 'sakit')",
    "si",
    [$keputusan, $id_absensi]
);

if (mysqli_stmt_affected_rows($stmt) == 1) {
    $label = ($keputusan == 'disetujui') ? 'disetujui' : 'ditolak';
    set_pesan("Pengajuan absen berhasil " . $label . ".");
} else {
    set_pesan("Data pengajuan tidak ditemukan atau bukan status izin/sakit.");
}

header("Location: " . $kembali);
exit;