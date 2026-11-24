<?php
// ============================================================
// guru/absen.php  -  proses simpan absen (+ bukti izin/sakit) & undo
// ============================================================
require '../connection.php';
require '../cek_login.php';
wajib_login('guru');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: dashboard.php");
    exit;
}

$id_guru = $_SESSION['id_guru'];
$aksi    = $_POST['aksi'] ?? '';

if ($aksi == 'absen') {
    $status     = $_POST['status'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!in_array($status, ['hadir', 'izin', 'sakit'])) {
        set_pesan("Status absen tidak valid!");
        header("Location: dashboard.php");
        exit;
    }

    // Cek apakah sudah absen hari ini sebelum memproses upload foto
    $sudah = ambil_satu(
        $koneksi,
        "SELECT id_absensi FROM absensi WHERE id_guru = ? AND tanggal = CURDATE()",
        "i",
        [$id_guru]
    );
    if ($sudah) {
        set_pesan("Kamu sudah absen hari ini!");
        header("Location: dashboard.php");
        exit;
    }

    $status_approval = ($status == 'hadir') ? 'disetujui' : 'pending';
    $nama_file_bukti = null;

    // Jika izin atau sakit, wajib menyertakan alasan teks ATAU foto bukti
    if ($status == 'izin' || $status == 'sakit') {
        $ada_foto = isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] != UPLOAD_ERR_NO_FILE;

        if ($keterangan == '' && !$ada_foto) {
            set_pesan("Untuk izin/sakit, isi alasan atau unggah foto bukti terlebih dahulu!");
            header("Location: dashboard.php");
            exit;
        }

        if ($ada_foto) {
            if ($_FILES['bukti_foto']['error'] !== UPLOAD_ERR_OK) {
                set_pesan("Gagal mengunggah foto bukti!");
                header("Location: dashboard.php");
                exit;
            }

            $ukuran_maks = 2 * 1024 * 1024; // 2 MB
            if ($_FILES['bukti_foto']['size'] > $ukuran_maks) {
                set_pesan("Ukuran foto bukti maksimal 2 MB!");
                header("Location: dashboard.php");
                exit;
            }

            $ext = strtolower(pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION));
            $boleh_ext = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $boleh_ext)) {
                set_pesan("Format foto harus JPG, JPEG, PNG, atau WEBP!");
                header("Location: dashboard.php");
                exit;
            }

            $folder_upload = __DIR__ . '/../uploads/bukti';
            if (!is_dir($folder_upload)) {
                mkdir($folder_upload, 0777, true);
            }

            $nama_file_bukti = 'bukti_' . $id_guru . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            $tujuan = $folder_upload . '/' . $nama_file_bukti;

            if (!move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $tujuan)) {
                set_pesan("Gagal menyimpan foto bukti ke server lokal!");
                header("Location: dashboard.php");
                exit;
            }
        }
    }

    $stmt = jalankan(
        $koneksi,
        "INSERT IGNORE INTO absensi (id_guru, tanggal, status, status_approval, keterangan, bukti_foto)
         VALUES (?, CURDATE(), ?, ?, ?, ?)",
        "issss",
        [$id_guru, $status, $status_approval, $keterangan !== '' ? $keterangan : null, $nama_file_bukti]
    );

    if (mysqli_stmt_affected_rows($stmt) == 1) {
        if ($status_approval == 'pending') {
            set_pesan("Pengajuan " . $status . " terkirim (status: PENDING). Menunggu persetujuan admin.");
        } else {
            set_pesan("Absen berhasil dicatat: " . $status);
        }
    } else {
        set_pesan("Kamu sudah absen hari ini!");
    }

} elseif ($aksi == 'undo') {
    $data_absen = ambil_satu(
        $koneksi,
        "SELECT bukti_foto FROM absensi
         WHERE id_guru = ? AND tanggal = CURDATE()
           AND waktu_absen >= NOW() - INTERVAL 5 MINUTE",
        "i",
        [$id_guru]
    );

    $stmt = jalankan(
        $koneksi,
        "DELETE FROM absensi
         WHERE id_guru = ? AND tanggal = CURDATE()
           AND waktu_absen >= NOW() - INTERVAL 5 MINUTE",
        "i",
        [$id_guru]
    );

    if (mysqli_stmt_affected_rows($stmt) == 1) {
        if ($data_absen && !empty($data_absen['bukti_foto'])) {
            $path_foto = __DIR__ . '/../uploads/bukti/' . basename($data_absen['bukti_foto']);
            if (file_exists($path_foto)) {
                @unlink($path_foto);
            }
        }
        set_pesan("Absen dibatalkan. Silakan absen ulang.");
    } else {
        set_pesan("Absen tidak bisa dibatalkan (sudah lewat 5 menit).");
    }
}

header("Location: dashboard.php");
exit;