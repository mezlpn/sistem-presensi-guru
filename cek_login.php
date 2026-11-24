<?php
// ============================================================
// cek_login.php
// Fungsi : memulai SESI dan menyediakan fungsi penjaga halaman.
// Di-include SETELAH connection.php (karena memakai fungsi e()).
// ============================================================

// session_start() wajib dipanggil sebelum memakai $_SESSION.
// Sesi = data yang disimpan di SERVER untuk satu pengunjung.
// Browser hanya menyimpan "nomor sesi" (PHPSESSID), jadi user tidak bisa
// mengubah isi sesi (misal mengubah role dirinya jadi admin).
session_start();

// wajib_login() : penjaga halaman.
// Dipanggil di awal halaman yang butuh login.
// Contoh: wajib_login('admin'); -> hanya admin yang boleh masuk.
function wajib_login($role_diizinkan) {
    // Kalau belum login (tidak ada $_SESSION['role']) -> tendang ke login
    if (!isset($_SESSION['role'])) {
        header("Location: ../index.php");
        exit; // exit WAJIB setelah header Location, supaya kode di bawahnya tidak jalan
    }
    // Kalau sudah login tapi rolenya salah (guru buka halaman admin) -> tendang
    if ($_SESSION['role'] != $role_diizinkan) {
        header("Location: ../index.php");
        exit;
    }
}

// Pesan notifikasi sekali tampil ("flash message").
// set_pesan() menyimpan pesan di sesi, tampilkan_pesan() menampilkannya
// satu kali lalu menghapusnya. Dipakai setelah proses simpan/hapus/absen.
function set_pesan($teks) {
    $_SESSION['pesan'] = $teks;
}

function tampilkan_pesan() {
    if (isset($_SESSION['pesan'])) {
        echo "<div class='notif'>" . e($_SESSION['pesan']) . "</div>";
        unset($_SESSION['pesan']); // hapus supaya tidak muncul lagi saat reload
    }
}
