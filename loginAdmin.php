<?php
// ============================================================
// loginAdmin.php  -  halaman login Admin
// Alur: user isi form -> cari email di tabel admin -> cocokkan password
//       dengan password_verify() -> simpan data ke SESI -> arahkan ke dashboard.
// (Halaman login Admin dan Guru dipisah, tapi alurnya sama persis.)
// ============================================================
require 'connection.php';
require 'cek_login.php';

// Kalau sudah login, tidak perlu lihat halaman login lagi
if (isset($_SESSION['role'])) {
    header("Location: " . $_SESSION['role'] . "/dashboard.php");
    exit;
}

$error = "";

// COOKIE (opsional): ambil email terakhir yang diingat browser.
// Operator ?? artinya "kalau tidak ada, pakai nilai kosong".
$email_terakhir = $_COOKIE['email_admin'] ?? "";

// Kode di dalam if ini hanya jalan saat form disubmit (method POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Cari akun berdasarkan email di tabel admin
    $akun = ambil_satu($koneksi, "SELECT id_admin, nama, password FROM admin WHERE email = ?", "s", [$email]);
    //Menggantikan stmt = mysqli_prepare($conn, "SELECT id_admin, nama, password FROM admin WHERE email = ?")
    //mysqli_stmt_bind_param  = ($stmt, "s", $email)
    //hasil = mysqli_stmt_get_result


    // 2. Kalau akun ada DAN password cocok dengan hash di database
    //    password_verify(password_yang_diketik, hash_di_database)
    if ($akun && password_verify($password, $akun['password'])) {

        // Ganti nomor sesi baru setelah login (mencegah session fixation)
        session_regenerate_id(true);

        // 3. Simpan data penting ke SESI
        $_SESSION['role'] = 'admin';
        $_SESSION['id_admin'] = $akun['id_admin'];
        $_SESSION['nama'] = $akun['nama'];

        // 4. COOKIE (opsional): kalau kotak "ingat email" dicentang,
        //    simpan email di browser selama 7 hari.
        //    Cookie hanya untuk kenyamanan, BUKAN untuk menentukan login.
        if (isset($_POST['ingat'])) {
            setcookie('email_admin', $email, time() + (60 * 60 * 24 * 7), "/");
        } else {
            setcookie('email_admin', '', time() - 3600, "/"); // hapus cookie
        }

        // 5. Arahkan ke dashboard
        header("Location: admin/dashboard.php");
        exit;
    } else {
        // Pesan sengaja dibuat sama untuk email salah / password salah
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - SDM Guru</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="halaman-login">
    <div class="kotak-login">
        <h2>Login Admin</h2>

        <?php if ($error != "") { ?><!-- Jika variabel error memiliki isi -->
            <div class="notif error"><?= e($error) ?></div>
        <?php } ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($email_terakhir) ?>" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label class="ingat">
                <input type="checkbox" name="ingat" <?= $email_terakhir != "" ? 'checked' : '' ?>>
                Ingat email saya
            </label>

            <button type="submit">Login</button>
        </form>

        <p><a href="index.php">&laquo; Kembali ke pilihan</a></p>
    </div>
</body>
</html>
