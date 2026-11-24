<?php
// ============================================================
// admin/hapus_guru.php  -  hapus guru (D = Delete dari CRUD)
// Cukup menghapus baris di tabel guru. Baris absensi milik guru itu
// ikut terhapus OTOMATIS karena di database.sql kita pakai ON DELETE CASCADE.
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('admin');

// Hanya boleh lewat form POST (bukan dengan mengetik URL)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: data_guru.php");
    exit;
}

$id_guru = (int)($_POST['id_guru'] ?? 0);

// Hapus langsung dari tabel guru.
// affected_rows = 1 artinya ada baris yang terhapus, 0 artinya id tidak ditemukan.
$stmt = jalankan($koneksi, "DELETE FROM guru WHERE id_guru = ?", "i", [$id_guru]);

if (mysqli_stmt_affected_rows($stmt) == 1) {
    set_pesan("Data guru berhasil dihapus.");
} else {
    set_pesan("Data guru tidak ditemukan!");
}

header("Location: data_guru.php");
exit;
