<?php
// logout.php : menghapus sesi lalu kembali ke halaman pilihan (index.php).
session_start();     // buka sesi yang sedang berjalan
session_unset();     // kosongkan semua isi $_SESSION
session_destroy();   // hancurkan sesinya
header("Location: index.php");
exit;
