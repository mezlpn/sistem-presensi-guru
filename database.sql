-- ============================================================
-- database.sql  -  Sistem Manajemen SDM Guru (versi rancangan Nabhan)
-- Cara pakai: buka phpMyAdmin -> tab "Import" -> pilih file ini -> Go.
-- PERHATIAN: file ini menghapus tabel lama (jika ada) lalu membuat ulang.
--
-- Satu database (sdm_guru) berisi 3 tabel:
--   admin   -> akun admin
--   guru    -> akun guru + data kepegawaiannya (jadi 1 tabel saja)
--   absensi -> catatan absen, 1 baris = 1 guru di 1 hari
-- ============================================================

CREATE DATABASE IF NOT EXISTS sdm_guru CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sdm_guru;

-- Hapus dari tabel "anak" dulu (absensi -> guru) karena ada foreign key
DROP TABLE IF EXISTS absensi;
DROP TABLE IF EXISTS guru;
DROP TABLE IF EXISTS admin;

-- 1. Tabel akun admin
CREATE TABLE admin (
  id_admin INT AUTO_INCREMENT PRIMARY KEY,
  email    VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,        -- hasil password_hash(), bukan teks asli
  nama     VARCHAR(100) NOT NULL
);

-- 2. Tabel guru: akun login (email + password) sekaligus data kepegawaian
CREATE TABLE guru (
  id_guru            INT AUTO_INCREMENT PRIMARY KEY,
  email              VARCHAR(100) NOT NULL UNIQUE,
  password           VARCHAR(255) NOT NULL,   -- hasil password_hash()
  nip                VARCHAR(20)  NOT NULL UNIQUE,
  nama               VARCHAR(100) NOT NULL,
  jabatan            VARCHAR(50)  NOT NULL,
  status_kepegawaian ENUM('PNS','PPPK','Honorer') NOT NULL,
  mapel              VARCHAR(50)  NOT NULL,
  tanggal_bergabung  DATE NOT NULL,
  no_hp              VARCHAR(15)
);

-- 3. Tabel absensi (1 baris = 1 guru di 1 hari)
CREATE TABLE absensi (
  id_absensi  INT AUTO_INCREMENT PRIMARY KEY,
  id_guru     INT NOT NULL,
  tanggal     DATE NOT NULL,
  status      ENUM('hadir','izin','sakit') NOT NULL,
  status_approval ENUM('disetujui','pending','ditolak') NOT NULL DEFAULT 'disetujui', -- hadir otomatis disetujui, izin/sakit menunggu admin
  keterangan  TEXT NULL,             -- alasan izin/sakit (boleh kosong kalau ada foto)
  bukti_foto  VARCHAR(255) NULL,     -- nama file foto bukti di folder uploads/bukti
  waktu_absen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_guru_tanggal (id_guru, tanggal),  -- cegah absen ganda di hari yang sama
  FOREIGN KEY (id_guru) REFERENCES guru(id_guru) ON DELETE CASCADE
);

-- ============================================================
-- DATA CONTOH
-- Password admin = admin123 , password semua guru = guru123
-- (hash dibuat dengan bcrypt, kompatibel dengan password_verify PHP)
-- ============================================================
INSERT INTO admin (email, password, nama) VALUES
('admin@sekolah.sch.id', '$2y$10$rqBEEn64MhOZH8PrtWFCe.OTxXQlVQfQnNUE7VvQWMNO60SpqxIje', 'Administrator');

INSERT INTO guru (email, password, nip, nama, jabatan, status_kepegawaian, mapel, tanggal_bergabung, no_hp) VALUES
('budi@sekolah.sch.id', '$2y$10$1hzV1VS1IAxurLqDleNk6.QHu2Cg2GV93oSt.Qwxnf16aGyzRTeqq', '198501012010011001', 'Budi Santoso', 'Wali Kelas', 'PNS',     'Matematika',       '2010-01-01', '081234567890'),
('siti@sekolah.sch.id', '$2y$10$1hzV1VS1IAxurLqDleNk6.QHu2Cg2GV93oSt.Qwxnf16aGyzRTeqq', '199002022015022002', 'Siti Aminah',  'Guru Mapel', 'PPPK',    'Bahasa Indonesia', '2015-07-15', '081298765432'),
('andi@sekolah.sch.id', '$2y$10$1hzV1VS1IAxurLqDleNk6.QHu2Cg2GV93oSt.Qwxnf16aGyzRTeqq', '199503032020031003', 'Andi Pratama', 'Guru Mapel', 'Honorer', 'Informatika',      '2020-08-01', NULL);

-- Absensi contoh beberapa hari terakhir (supaya rekap langsung ada isinya)
INSERT INTO absensi (id_guru, tanggal, status, status_approval, keterangan) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'hadir', 'disetujui', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'hadir', 'disetujui', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'izin',  'pending',   'Ada keperluan keluarga'),
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'hadir', 'disetujui', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'sakit', 'disetujui', 'Demam'),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'hadir', 'disetujui', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'hadir', 'disetujui', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'hadir', 'disetujui', NULL);
