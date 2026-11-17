-- Database Dump for PKBM Mandiri (MySQL / MariaDB / phpMyAdmin)
-- Host: localhost
-- Database: db_pkbm_mandiri

CREATE DATABASE IF NOT EXISTS `db_pkbm_mandiri` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_pkbm_mandiri`;

-- 1. Table users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'siswa',
  `full_name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(150),
  `phone` VARCHAR(30),
  `avatar` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table settings
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table teachers
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `nip_nuptk` VARCHAR(50),
  `nama_lengkap` VARCHAR(200) NOT NULL,
  `gelar` VARCHAR(50),
  `jenis_kelamin` VARCHAR(10),
  `pendidikan_terakhir` VARCHAR(50),
  `mapel_diampu` VARCHAR(150),
  `jabatan` VARCHAR(100) DEFAULT 'Tutor / Guru Pengajar',
  `no_hp` VARCHAR(30),
  `email` VARCHAR(150),
  `foto` VARCHAR(255),
  `status_aktif` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table students
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `no_urut` INT,
  `nama` VARCHAR(200) NOT NULL,
  `jk` VARCHAR(5),
  `nisn` VARCHAR(30),
  `tempat_lahir` VARCHAR(100),
  `tanggal_lahir` DATE,
  `nik` VARCHAR(30) UNIQUE,
  `agama` VARCHAR(30) DEFAULT 'Islam',
  `alamat` TEXT,
  `rt` VARCHAR(10),
  `rw` VARCHAR(10),
  `dusun` VARCHAR(100),
  `kelurahan` VARCHAR(100),
  `kecamatan` VARCHAR(100),
  `kab_kota` VARCHAR(100) DEFAULT 'Kab. Banjar',
  `provinsi` VARCHAR(100) DEFAULT 'Kalimantan Selatan',
  `hp` VARCHAR(30),
  `email` VARCHAR(100),
  `asal_sekolah` VARCHAR(150),
  `no_kk` VARCHAR(30),
  `nama_ayah` VARCHAR(150),
  `tahun_lahir_ayah` VARCHAR(10),
  `pendidikan_ayah` VARCHAR(50),
  `nik_ayah` VARCHAR(30),
  `pekerjaan_ayah` VARCHAR(100),
  `nama_ibu` VARCHAR(150),
  `tahun_lahir_ibu` VARCHAR(10),
  `pendidikan_ibu` VARCHAR(50),
  `nik_ibu` VARCHAR(30),
  `pekerjaan_ibu` VARCHAR(100),
  `rombel_saat_ini` VARCHAR(100),
  `paket` VARCHAR(20),
  `tingkat_kelas` VARCHAR(20),
  `status` VARCHAR(20) DEFAULT 'aktif',
  `catatan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table registrations
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_pendaftaran` VARCHAR(50) UNIQUE NOT NULL,
  `nama_lengkap` VARCHAR(200) NOT NULL,
  `jk` VARCHAR(5),
  `nisn` VARCHAR(30),
  `tempat_lahir` VARCHAR(100),
  `tanggal_lahir` DATE,
  `nik` VARCHAR(30) NOT NULL,
  `agama` VARCHAR(30) DEFAULT 'Islam',
  `alamat` TEXT,
  `rt` VARCHAR(10),
  `rw` VARCHAR(10),
  `dusun` VARCHAR(100),
  `kelurahan` VARCHAR(100),
  `kecamatan` VARCHAR(100),
  `kab_kota` VARCHAR(100) DEFAULT 'Kab. Banjar',
  `provinsi` VARCHAR(100) DEFAULT 'Kalimantan Selatan',
  `hp` VARCHAR(30),
  `email` VARCHAR(100),
  `asal_sekolah` VARCHAR(150),
  `no_kk` VARCHAR(30),
  `paket_pilihan` VARCHAR(30) NOT NULL,
  `nama_ayah` VARCHAR(150),
  `tahun_lahir_ayah` VARCHAR(10),
  `pendidikan_ayah` VARCHAR(50),
  `nik_ayah` VARCHAR(30),
  `nama_ibu` VARCHAR(150),
  `tahun_lahir_ibu` VARCHAR(10),
  `pendidikan_ibu` VARCHAR(50),
  `nik_ibu` VARCHAR(30),
  `usia_tahun` INT,
  `status_usia_sd` VARCHAR(50),
  `kategori_pendaftaran` VARCHAR(50),
  `file_ijazah` VARCHAR(255),
  `file_akta` VARCHAR(255),
  `file_kk` VARCHAR(255),
  `file_surat_pindah` VARCHAR(255),
  `status_verifikasi` VARCHAR(30) DEFAULT 'menunggu',
  `catatan_admin` TEXT,
  `synced_drive` TINYINT DEFAULT 0,
  `drive_file_url` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table exams
CREATE TABLE IF NOT EXISTS `exams` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `guru_id` INT,
  `judul` VARCHAR(200) NOT NULL,
  `deskripsi` TEXT,
  `paket` VARCHAR(30) NOT NULL,
  `tingkat_kelas` VARCHAR(30),
  `mapel` VARCHAR(100) NOT NULL,
  `durasi_menit` INT DEFAULT 0,
  `acak_soal` TINYINT DEFAULT 1,
  `status` VARCHAR(20) DEFAULT 'aktif',
  `nilai_dipublikasi` TINYINT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table exam_questions
CREATE TABLE IF NOT EXISTS `exam_questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `exam_id` INT NOT NULL,
  `nomor_urut` INT DEFAULT 1,
  `jenis` VARCHAR(10) NOT NULL DEFAULT 'pg',
  `pertanyaan` TEXT NOT NULL,
  `gambar` VARCHAR(255),
  `opsi_a` TEXT,
  `opsi_b` TEXT,
  `opsi_c` TEXT,
  `opsi_d` TEXT,
  `opsi_e` TEXT,
  `kunci_jawaban` VARCHAR(10),
  `bobot` FLOAT DEFAULT 1.0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table exam_attempts
CREATE TABLE IF NOT EXISTS `exam_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `exam_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `waktu_mulai` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `waktu_selesai` DATETIME,
  `status` VARCHAR(30) DEFAULT 'sedang_mengerjakan',
  `nilai_pg` FLOAT DEFAULT 0,
  `nilai_esai` FLOAT DEFAULT 0,
  `nilai_total` FLOAT DEFAULT 0,
  `status_koreksi` VARCHAR(20) DEFAULT 'belum',
  `catatan_guru` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Table exam_answers
CREATE TABLE IF NOT EXISTS `exam_answers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT NOT NULL,
  `question_id` INT NOT NULL,
  `jawaban_siswa` TEXT,
  `nilai_skor` FLOAT DEFAULT 0,
  `koreksi_catatan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Table gallery
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(200) NOT NULL,
  `kategori` VARCHAR(50) DEFAULT 'Kegiatan Belajar',
  `deskripsi` TEXT,
  `file_gambar` VARCHAR(255) NOT NULL,
  `tanggal_kegiatan` DATE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

