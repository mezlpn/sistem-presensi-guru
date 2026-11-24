<?php
// partials/navbar.php
// Menu atas, di-include di halaman guru & admin.
// Link ditulis relatif karena file yang meng-include ada di folder guru/ atau admin/.
// (Untuk Nabhan: boleh ubah HTML/class di sini, tapi jangan ubah blok PHP-nya)
?>
<nav class="navbar">
    <span class="sapaan">Halo, <?= e($_SESSION['nama']) ?> (<?= e($_SESSION['role']) ?>)</span>
    <div class="menu">
        <?php if ($_SESSION['role'] == 'admin') { ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="data_guru.php">Data Guru</a>
            <a href="rekap.php">Rekap Absensi</a>
        <?php } else { ?>
            <a href="dashboard.php">Absensi</a>
            <a href="profil.php">Profil</a>
        <?php } ?>
        <a href="../logout.php">Logout</a>
    </div>
</nav>
