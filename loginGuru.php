<?php
// ============================================================
// loginGuru.php  -  halaman login Guru
// Alur: user isi form -> cari email di tabel guru -> cocokkan password
//       dengan password_verify() -> simpan data ke SESI -> arahkan ke dashboard.
// (Halaman login Admin dan Guru dipisah, tapi alurnya sama persis.)
// ============================================================
require 'connection.php';
require 'cek_login.php';

// Kalau sudah login, tidak perlu lihat halaman login lagi
if (isset($_SESSION['role'])) {
    header("Location: " . $_SESSION['role'] . "/dashboard.php"); // Diarahkan ke halaman dashboard sesuai dengan session role
    exit;
}

$error = "";

// COOKIE (opsional): ambil email terakhir yang diingat browser.
// Operator ?? artinya "kalau tidak ada, pakai nilai kosong".
//Kalau semisal cookie nya kosong, maka ganti isinya dengan nilai '';
$email_terakhir = $_COOKIE['email_guru'] ?? "";

// Kode di dalam if ini hanya jalan saat form disubmit (method POST)
//Menangkap inputan dari form method POST dan menaruhnya di variabel $email dan $password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email'] ?? '');//Variabel email akan diisi oleh $POST array asosiatif dengan key 'email' apabila ada isinya. Namun jika tidak ada diisi oleh ''
    $password = $_POST['password'] ?? '';

    // 1. Cari akun berdasarkan email di tabel guru
    $akun = ambil_satu($koneksi, "SELECT id_guru, nama, password FROM guru WHERE email = ?", "s", [$email]);//Ini seperti menyingkat mysqli_prepare, mysqli_stmt_bind_param dan mysqli_stmt_execute

    // 2. Kalau akun ada DAN password cocok dengan hash di database
    //    password_verify(password_yang_diketik, hash_di_database)
    if ($akun && password_verify($password, $akun['password'])) {// Jika ada akun & password inputan cocok dengan password hash yang disimpan dalam database

        // Ganti nomor sesi baru setelah login (mencegah session fixation)
        session_regenerate_id(true);

        // 3. Simpan data penting ke SESI
        //Membuat sesi yang memiliki role guru, id guru disesuaikan dengan id akun guru yang diambil dari database, dan nama guru yang diambil dari nama guru yang tersimpan dalam database
        $_SESSION['role'] = 'guru';
        $_SESSION['id_guru'] = $akun['id_guru'];
        $_SESSION['nama'] = $akun['nama'];

        // 4. COOKIE (opsional): kalau kotak "ingat email" dicentang,
        //    simpan email di browser selama 7 hari.
        //    Cookie hanya untuk kenyamanan, BUKAN untuk menentukan login.
        if (isset($_POST['ingat'])) {
            setcookie('email_guru', $email, time() + (60 * 60 * 24 * 7), "/");// Buat cookie dengan nama variabel cookie "email_guru", bernilai apa yang disimpan dalam variabel $email yang diinputkan dalam form, dan waktunya diset menjadi satu minggu
        } else {
            setcookie('email_guru', '', time() - 3600, "/"); // hapus cookie dengan cara mengatur waktunya secara mundur
        }

        // 5. Arahkan ke dashboard
        header("Location: guru/dashboard.php");
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
    <title>Login Guru - SDM Guru</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="halaman-login">
    <div class="kotak-login">
        <h2>Login Guru</h2>

        <?php if ($error != "") { ?><!-- Jika variabel errornya tidak kosong, maka munculkan pesan error-->
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
<!-- Jika variabel email terakhir tidak kosong/ada isinya, maka akan bernilai checked, false nya akan diisi dengan kosong-->
            <button type="submit">Login</button>
        </form>

        <p><a href="index.php">&laquo; Kembali ke pilihan</a></p>
    </div>
</body>
</html>
