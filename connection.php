<?php
// ============================================================
// connection.php
// Fungsi : menghubungkan PHP ke database MySQL (XAMPP) + berisi
//          beberapa fungsi bantu (helper) yang dipakai semua halaman.
// File ini di-include dengan "require" di hampir setiap halaman.
// ============================================================

// PHP 8.1+ secara bawaan "melempar error keras" kalau query gagal.
// Kita matikan supaya error bisa kita tangani sendiri (lebih mudah dipahami).
mysqli_report(MYSQLI_REPORT_OFF);

// Atur zona waktu PHP ke WIB supaya fungsi date() sesuai jam Indonesia.
date_default_timezone_set('Asia/Jakarta');

// ---------- Pengaturan koneksi (default XAMPP) ----------
$host = "localhost";
$user = "root";      // user default XAMPP
$pass = "";          // password default XAMPP kosong
$db   = "sdm_guru";  // nama database (sesuai file database.sql)

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Kalau gagal konek, hentikan program dan tampilkan pesan.
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// ============================================================
// FUNGSI BANTU
// ============================================================

// jalankan() : menjalankan query dengan PREPARED STATEMENT.
// Kenapa prepared statement? Supaya aman dari SQL Injection.
// Data dari user TIDAK digabung langsung ke SQL, tapi dititipkan lewat tanda "?".
//
// Parameter:
//   $sql    = query, tempat data diganti tanda ?
//   $tipe   = jenis tiap data: "s" = string, "i" = integer
//             contoh "si" artinya data pertama string, data kedua integer
//   $params = array data yang menggantikan tanda ? (urutannya harus sama)
//
// Contoh: jalankan($koneksi, "SELECT * FROM guru WHERE id_guru = ?", "i", [5]);
function jalankan($koneksi, $sql, $tipe = "", $params = []) {
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        die("Query salah: " . mysqli_error($koneksi));
    }
    if ($tipe != "") {
        // tanda ... (spread) mengubah array $params menjadi argumen satu-satu
        mysqli_stmt_bind_param($stmt, $tipe, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        die("Query gagal: " . mysqli_stmt_error($stmt));
    }
    return $stmt;
}

// ambil_satu() : untuk SELECT yang hasilnya 1 baris saja.
// Hasil berupa array (misal $data['nama']) atau null kalau tidak ada.
function ambil_satu($koneksi, $sql, $tipe = "", $params = []) {
    $stmt  = jalankan($koneksi, $sql, $tipe, $params);
    $hasil = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($hasil);
}

// ambil_semua() : untuk SELECT yang hasilnya banyak baris.
// Hasil berupa array berisi array (dipakai dengan foreach).
function ambil_semua($koneksi, $sql, $tipe = "", $params = []) {
    $stmt  = jalankan($koneksi, $sql, $tipe, $params);
    $hasil = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($hasil, MYSQLI_ASSOC);
}

// e() : singkatan "escape". Dipakai setiap kali menampilkan data ke HTML.
// Mengubah karakter berbahaya seperti < > menjadi aman (mencegah XSS).
function e($teks) {
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}
