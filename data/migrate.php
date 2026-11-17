<?php
/**
 * Database Migration & Seeding Script
 */

function runMigration(PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $autoInc = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

    // 1. Tabel users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $autoInc,
        username VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'siswa', -- 'admin', 'guru', 'siswa'
        full_name VARCHAR(200) NOT NULL,
        email VARCHAR(150),
        phone VARCHAR(30),
        avatar VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Tabel settings (CMS Profil PKBM)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        setting_group VARCHAR(50) DEFAULT 'general',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 3. Tabel teachers (Guru & Tutor)
    $pdo->exec("CREATE TABLE IF NOT EXISTS teachers (
        id $autoInc,
        user_id INT,
        nip_nuptk VARCHAR(50),
        nama_lengkap VARCHAR(200) NOT NULL,
        gelar VARCHAR(50),
        jenis_kelamin VARCHAR(10),
        pendidikan_terakhir VARCHAR(50),
        mapel_diampu VARCHAR(150),
        jabatan VARCHAR(100) DEFAULT 'Tutor / Guru Pengajar',
        no_hp VARCHAR(30),
        email VARCHAR(150),
        foto VARCHAR(255),
        status_aktif TINYINT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 4. Tabel students (Data Base 556 Murid PKBM)
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id $autoInc,
        user_id INT,
        no_urut INT,
        nama VARCHAR(200) NOT NULL,
        jk VARCHAR(5),
        nisn VARCHAR(30),
        tempat_lahir VARCHAR(100),
        tanggal_lahir DATE,
        nik VARCHAR(30) UNIQUE,
        agama VARCHAR(30) DEFAULT 'Islam',
        alamat TEXT,
        rt VARCHAR(10),
        rw VARCHAR(10),
        dusun VARCHAR(100),
        kelurahan VARCHAR(100),
        kecamatan VARCHAR(100),
        kab_kota VARCHAR(100) DEFAULT 'Kab. Banjar',
        provinsi VARCHAR(100) DEFAULT 'Kalimantan Selatan',
        hp VARCHAR(30),
        email VARCHAR(100),
        asal_sekolah VARCHAR(150),
        no_kk VARCHAR(30),
        nama_ayah VARCHAR(150),
        tahun_lahir_ayah VARCHAR(10),
        pendidikan_ayah VARCHAR(50),
        nik_ayah VARCHAR(30),
        pekerjaan_ayah VARCHAR(100),
        nama_ibu VARCHAR(150),
        tahun_lahir_ibu VARCHAR(10),
        pendidikan_ibu VARCHAR(50),
        nik_ibu VARCHAR(30),
        pekerjaan_ibu VARCHAR(100),
        rombel_saat_ini VARCHAR(100),
        paket VARCHAR(20), -- 'Paket A', 'Paket B', 'Paket C'
        tingkat_kelas VARCHAR(20),
        status VARCHAR(20) DEFAULT 'aktif', -- 'aktif', 'alumni', 'pendaftar_baru', 'mutasi', 'tidak_aktif'
        catatan TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 5. Tabel registrations (Pendaftaran Murid Baru)
    $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
        id $autoInc,
        no_pendaftaran VARCHAR(50) UNIQUE NOT NULL,
        nama_lengkap VARCHAR(200) NOT NULL,
        jk VARCHAR(5),
        nisn VARCHAR(30),
        tempat_lahir VARCHAR(100),
        tanggal_lahir DATE,
        nik VARCHAR(30) NOT NULL,
        agama VARCHAR(30) DEFAULT 'Islam',
        alamat TEXT,
        rt VARCHAR(10),
        rw VARCHAR(10),
        dusun VARCHAR(100),
        kelurahan VARCHAR(100),
        kecamatan VARCHAR(100),
        kab_kota VARCHAR(100) DEFAULT 'Kab. Banjar',
        provinsi VARCHAR(100) DEFAULT 'Kalimantan Selatan',
        hp VARCHAR(30),
        email VARCHAR(100),
        asal_sekolah VARCHAR(150),
        no_kk VARCHAR(30),
        paket_pilihan VARCHAR(30) NOT NULL, -- 'Paket A (Setara SD)', 'Paket B (Setara SMP)', 'Paket C (Setara SMA)'
        nama_ayah VARCHAR(150),
        tahun_lahir_ayah VARCHAR(10),
        pendidikan_ayah VARCHAR(50),
        nik_ayah VARCHAR(30),
        nama_ibu VARCHAR(150),
        tahun_lahir_ibu VARCHAR(10),
        pendidikan_ibu VARCHAR(50),
        nik_ibu VARCHAR(30),
        usia_tahun INT,
        status_usia_sd VARCHAR(50), -- 'usia_sekolah', 'lewat_usia_sekolah'
        kategori_pendaftaran VARCHAR(50), -- 'siswa_baru', 'pindahan'
        file_ijazah VARCHAR(255),
        file_akta VARCHAR(255),
        file_kk VARCHAR(255),
        file_surat_pindah VARCHAR(255),
        status_verifikasi VARCHAR(30) DEFAULT 'menunggu', -- 'menunggu', 'diverifikasi', 'diterima', 'ditolak'
        catatan_admin TEXT,
        synced_drive TINYINT DEFAULT 0,
        drive_file_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 6. Tabel exams (Ujian)
    $pdo->exec("CREATE TABLE IF NOT EXISTS exams (
        id $autoInc,
        guru_id INT,
        judul VARCHAR(200) NOT NULL,
        deskripsi TEXT,
        paket VARCHAR(30) NOT NULL, -- 'Paket A', 'Paket B', 'Paket C', 'Semua'
        tingkat_kelas VARCHAR(30),
        mapel VARCHAR(100) NOT NULL,
        durasi_menit INT DEFAULT 0, -- 0 = fleksibel tanpa timer kaku
        acak_soal TINYINT DEFAULT 1,
        status VARCHAR(20) DEFAULT 'aktif', -- 'draft', 'aktif', 'selesai'
        nilai_dipublikasi TINYINT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 7. Tabel exam_questions (Soal Ujian: PG & Esai)
    $pdo->exec("CREATE TABLE IF NOT EXISTS exam_questions (
        id $autoInc,
        exam_id INT NOT NULL,
        nomor_urut INT DEFAULT 1,
        jenis VARCHAR(10) NOT NULL DEFAULT 'pg', -- 'pg' atau 'esai'
        pertanyaan TEXT NOT NULL,
        gambar VARCHAR(255),
        opsi_a TEXT,
        opsi_b TEXT,
        opsi_c TEXT,
        opsi_d TEXT,
        opsi_e TEXT,
        kunci_jawaban VARCHAR(10), -- A, B, C, D, E (untuk PG) atau rubrik esai
        bobot FLOAT DEFAULT 1.0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 8. Tabel exam_attempts (Peserta Ujian)
    $pdo->exec("CREATE TABLE IF NOT EXISTS exam_attempts (
        id $autoInc,
        exam_id INT NOT NULL,
        student_id INT NOT NULL,
        waktu_mulai DATETIME DEFAULT CURRENT_TIMESTAMP,
        waktu_selesai DATETIME,
        status VARCHAR(30) DEFAULT 'sedang_mengerjakan', -- 'sedang_mengerjakan', 'selesai'
        nilai_pg FLOAT DEFAULT 0,
        nilai_esai FLOAT DEFAULT 0,
        nilai_total FLOAT DEFAULT 0,
        status_koreksi VARCHAR(20) DEFAULT 'belum', -- 'belum', 'sudah'
        catatan_guru TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 9. Tabel exam_answers (Jawaban Siswa per Soal)
    $pdo->exec("CREATE TABLE IF NOT EXISTS exam_answers (
        id $autoInc,
        attempt_id INT NOT NULL,
        question_id INT NOT NULL,
        jawaban_siswa TEXT,
        nilai_skor FLOAT DEFAULT 0,
        koreksi_catatan TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 10. Tabel gallery (Foto & Kegiatan PKBM)
    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
        id $autoInc,
        judul VARCHAR(200) NOT NULL,
        kategori VARCHAR(50) DEFAULT 'Kegiatan Belajar',
        deskripsi TEXT,
        file_gambar VARCHAR(255) NOT NULL,
        tanggal_kegiatan DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // ==========================================
    // SEEDING DATA AWAL
    // ==========================================

    // 1. Akun Admin Default
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name, email, phone) 
            VALUES ('admin', ?, 'admin', 'Administrator PKBM Mandiri', 'admin@pkbmmandiri.sch.id', '083162151175')")
            ->execute([$hash]);
    }

    // 2. Akun Guru Default
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'guru'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('guru123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name, email, phone) 
            VALUES ('guru', ?, 'guru', 'Tutor Kepala (Ujian & Penilaian)', 'guru@pkbmmandiri.sch.id', '085251315379')")
            ->execute([$hash]);
    }

    // 3. Settings Default (Profil PKBM Mandiri)
    $defaultSettings = [
        'nama_sekolah' => 'PKBM Mandiri',
        'nama_lengkap_sekolah' => 'Pusat Kegiatan Belajar Masyarakat (PKBM) Mandiri',
        'npsn' => 'P2962977',
        'sk_operasional' => '421.9/056/Disdik/2021',
        'alamat' => 'Jl. A. Yani KM. 60 RT. 02 RW. 02, Desa Mataraman',
        'kecamatan' => 'Kec. Mataraman',
        'kabupaten' => 'Kab. Banjar',
        'provinsi' => 'Kalimantan Selatan',
        'kode_pos' => '70672',
        'telepon' => '0812-5597-4191',
        'whatsapp' => '6281255974191',
        'email' => 'pkbmmandiri.mataraman@gmail.com',
        'kepala_pkbm' => 'H. Zailani, S.Pd',
        'sambutan_kepala' => 'Selamat datang di Website Resmi PKBM Mandiri Mataraman. Kami berdedikasi memberikan kesempatan pendidikan nonformal yang bermutu, fleksibel, dan inklusif bagi seluruh lapisan masyarakat untuk meraih ijazah resmi setara SD, SMP, dan SMA melalui Program Paket A, Paket B, dan Paket C.',
        'visi' => 'Terwujudnya Pusat Kegiatan Belajar Masyarakat yang Unggul, Mandiri, Berakhlak Mulia, dan Berdaya Saing dalam Layanan Pendidikan Kesetaraan.',
        'misi' => "1. Menyelenggarakan layanan pendidikan kesetaraan Paket A, B, dan C yang bermutu dan terjangkau bagi masyarakat luas.\n2. Mengembangkan kompetensi vokasi dan keterampilan hidup (life skills) peserta didik agar mandiri dan siap kerja.\n3. Membina karakter religius, sopan santun, dan cinta tanah air bagi seluruh warga belajar.\n4. Menerapkan sistem pembelajaran dan asesmen yang fleksibel, modern, dan adaptif.",
        'google_drive_folder' => 'https://drive.google.com/drive/folders/1bgIM8NcMXxKQDx3ekLFrJ95cgh2yh4BM?usp=sharing',
        'ppdb_status' => 'buka',
        'tahun_ajaran' => '2026/2027',
    ];

    $stmtSet = $pdo->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    if ($driver !== 'sqlite') {
        $stmtSet = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    }
    foreach ($defaultSettings as $k => $v) {
        $stmtSet->execute([$k, $v]);
    }

    // 4. Seed Data Guru & Tutor
    $stmtGuru = $pdo->prepare("SELECT COUNT(*) FROM teachers");
    $stmtGuru->execute();
    if ($stmtGuru->fetchColumn() == 0) {
        $teachersData = [
            ['H. Zailani, S.Pd', 'M.Pd', 'L', 'S2', 'Pendidikan Kewarganegaraan', 'Kepala PKBM / Tutor PKn', '083162151183'],
            ['Siti Asmah, S.Pd', 'S.Pd', 'P', 'S1', 'Bahasa Indonesia', 'Tutor Bahasa Indonesia', '085251315379'],
            ['Ahmad Fauzi, S.Si', 'S.Si', 'L', 'S1', 'Matematika & IPA', 'Tutor Matematika & Sains', '081256036873'],
            ['Maimunah, S.Pd', 'S.Pd', 'P', 'S1', 'Bahasa Inggris', 'Tutor Bahasa Inggris', '083150527017'],
            ['Muhammad Arsyad, S.Sos', 'S.Sos', 'L', 'S1', 'Ilmu Pengetahuan Sosial (IPS)', 'Tutor IPS & Sejarah', '085349953523'],
            ['Nor Halimah, S.Pd.I', 'S.Pd.I', 'P', 'S1', 'Pendidikan Agama Islam', 'Tutor PAI & Budi Pekerti', '081545421804'],
            ['Rahmat Hidayat, S.Kom', 'S.Kom', 'L', 'S1', 'Teknologi Informasi & Komputer', 'Tutor Keterampilan Komputer & Admin Ujian', '082151324829'],
            ['Dahlia, S.Pd', 'S.Pd', 'P', 'S1', 'Keterampilan / Tata Busana', 'Tutor Keterampilan & Wirausaha', '083162151472'],
        ];

        $insTeacher = $pdo->prepare("INSERT INTO teachers (nama_lengkap, gelar, jenis_kelamin, pendidikan_terakhir, mapel_diampu, jabatan, no_hp, status_aktif) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        foreach ($teachersData as $t) {
            $insTeacher->execute($t);
        }
    }

    // 5. Seed 556 Siswa dari data/students_seed.csv
    $stmtSiswa = $pdo->prepare("SELECT COUNT(*) FROM students");
    $stmtSiswa->execute();
    if ($stmtSiswa->fetchColumn() == 0) {
        $csvFile = BASE_PATH . '/data/students_seed.csv';
        if (file_exists($csvFile)) {
            $handle = fopen($csvFile, 'r');
            // Skip 2 header rows
            fgetcsv($handle);
            fgetcsv($handle);

            $insStudent = $pdo->prepare("INSERT INTO students (
                no_urut, nama, jk, nisn, tempat_lahir, tanggal_lahir, nik, agama,
                alamat, rt, rw, dusun, kelurahan, kecamatan, hp,
                nama_ayah, tahun_lahir_ayah, pendidikan_ayah, nik_ayah,
                nama_ibu, tahun_lahir_ibu, pendidikan_ibu, nik_ibu,
                rombel_saat_ini, paket, tingkat_kelas, status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, 'aktif'
            )");

            $pdo->beginTransaction();
            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row[0]) && empty($row[1])) continue;

                $no = (int)($row[0] ?? 0);
                $nama = trim($row[1] ?? '');
                $jk = trim($row[2] ?? '');
                $nisn = trim($row[3] ?? '');
                $tempatLahir = trim($row[4] ?? '');
                $tglLahir = trim($row[5] ?? '');
                $nik = trim($row[6] ?? '');
                $agama = trim($row[7] ?? 'Islam');
                $alamat = trim($row[8] ?? '');
                $rt = trim($row[9] ?? '');
                $rw = trim($row[10] ?? '');
                $dusun = trim($row[11] ?? '');
                $kelurahan = trim($row[12] ?? '');
                $kecamatan = trim($row[13] ?? 'Kec. Mataraman');
                $hp = trim($row[14] ?? '');

                // Data Ayah
                $namaAyah = trim($row[15] ?? '');
                $thnAyah = trim($row[16] ?? '');
                $pendAyah = trim($row[17] ?? '');
                $nikAyah = trim($row[18] ?? '');

                // Data Ibu
                $namaIbu = trim($row[19] ?? '');
                $thnIbu = trim($row[20] ?? '');
                $pendIbu = trim($row[21] ?? '');
                $nikIbu = trim($row[22] ?? '');

                // Rombel
                $rombel = trim($row[23] ?? '');

                // Deteksi Paket dan Kelas
                $paket = 'Paket C';
                if (stripos($rombel, 'Paket A') !== false) {
                    $paket = 'Paket A';
                } elseif (stripos($rombel, 'Paket B') !== false) {
                    $paket = 'Paket B';
                } elseif (stripos($rombel, 'Paket C') !== false) {
                    $paket = 'Paket C';
                }

                $tingkatKelas = '';
                if (preg_match('/Kelas\s*(\d+)/i', $rombel, $m)) {
                    $tingkatKelas = 'Kelas ' . $m[1];
                }

                try {
                    $insStudent->execute([
                        $no, $nama, $jk, $nisn, $tempatLahir, $tglLahir ?: null, $nik, $agama,
                        $alamat, $rt, $rw, $dusun, $kelurahan, $kecamatan, $hp,
                        $namaAyah, $thnAyah, $pendAyah, $nikAyah,
                        $namaIbu, $thnIbu, $pendIbu, $nikIbu,
                        $rombel, $paket, $tingkatKelas
                    ]);
                } catch (Exception $e) {
                    // Skip or ignore duplicate nik
                }
            }
            $pdo->commit();
            fclose($handle);
        }
    }

    // 6. Seed Contoh Ujian & Soal (PG dan Esai)
    $stmtEx = $pdo->prepare("SELECT COUNT(*) FROM exams");
    $stmtEx->execute();
    if ($stmtEx->fetchColumn() == 0) {
        // Ujian 1: Bahasa Indonesia Paket B
        $pdo->prepare("INSERT INTO exams (guru_id, judul, deskripsi, paket, tingkat_kelas, mapel, durasi_menit, acak_soal, status, nilai_dipublikasi)
            VALUES (1, 'Penilaian Sumatif Bahasa Indonesia Paket B', 'Ujian kesetaraan mata pelajaran Bahasa Indonesia untuk warga belajar Paket B (Setara SMP). Fleksibel tanpa batasan waktu kaku.', 'Paket B', 'Semua Kelas', 'Bahasa Indonesia', 0, 1, 'aktif', 1)")
            ->execute();
        $examId1 = $pdo->lastInsertId();

        // Soal Ujian 1 (PG & Esai)
        $q1 = [
            [
                'exam_id' => $examId1, 'nomor_urut' => 1, 'jenis' => 'pg',
                'pertanyaan' => 'Gagasan utama atau ide pokok dalam suatu paragraf biasanya terletak pada...',
                'opsi_a' => 'Kalimat penjelas', 'opsi_b' => 'Kalimat utama', 'opsi_c' => 'Kalimat pengembang', 'opsi_d' => 'Kalimat penghubung', 'opsi_e' => 'Kalimat pelengkap',
                'kunci_jawaban' => 'B', 'bobot' => 15
            ],
            [
                'exam_id' => $examId1, 'nomor_urut' => 2, 'jenis' => 'pg',
                'pertanyaan' => 'Berikut ini yang merupakan ciri-ciri teks laporan hasil observasi adalah...',
                'opsi_a' => 'Bersifat subjektif dan imajinatif', 'opsi_b' => 'Berdasarkan fakta dan data yang objektif', 'opsi_c' => 'Berisi opini penulis semata', 'opsi_d' => 'Menggunakan bahasa kiasan dan puisi', 'opsi_e' => 'Tidak perlu melalui pengamatan langsung',
                'kunci_jawaban' => 'B', 'bobot' => 15
            ],
            [
                'exam_id' => $examId1, 'nomor_urut' => 3, 'jenis' => 'pg',
                'pertanyaan' => 'Kata penghubung yang menyatakan hubungan sebab-akibat adalah...',
                'opsi_a' => 'Dan, serta', 'opsi_b' => 'Atau, melainkan', 'opsi_c' => 'Karena, sehingga', 'opsi_d' => 'Meskipun, kendatipun', 'opsi_e' => 'Kemudian, lalu',
                'kunci_jawaban' => 'C', 'bobot' => 15
            ],
            [
                'exam_id' => $examId1, 'nomor_urut' => 4, 'jenis' => 'pg',
                'pertanyaan' => 'Teks yang berisi petunjuk langkah-langkah untuk melakukan atau membuat sesuatu secara berurutan disebut teks...',
                'opsi_a' => 'Eksplanasi', 'opsi_b' => 'Deskripsi', 'opsi_c' => 'Narasi', 'opsi_d' => 'Prosedur', 'opsi_e' => 'Anekdot',
                'kunci_jawaban' => 'D', 'bobot' => 15
            ],
            [
                'exam_id' => $examId1, 'nomor_urut' => 5, 'jenis' => 'esai',
                'pertanyaan' => 'Jelaskan perbedaan mendasar antara fakta dan opini dalam sebuah teks bacaan, serta berikan masing-masing satu contohnya!',
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null, 'opsi_e' => null,
                'kunci_jawaban' => 'Fakta: peristiwa yang benar-benar terjadi dan dapat dibuktikan kebenarannya dengan data. Opini: pendapat, tanggapan, atau pemikiran seseorang yang belum tentu disepakati semua orang.',
                'bobot' => 20
            ],
            [
                'exam_id' => $examId1, 'nomor_urut' => 6, 'jenis' => 'esai',
                'pertanyaan' => 'Tuliskan sebuah paragraf singkat (minimal 4 kalimat) bertema "Semangat Belajar di PKBM Mandiri" dengan menggunakan kalimat efektif!',
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null, 'opsi_e' => null,
                'kunci_jawaban' => 'Rubrik penilaian: Kesesuaian tema (5 poin), Kohesi dan kepaduan antar kalimat (5 poin), Penggunaan ejaan dan tanda baca (5 poin), Kelengkapan struktur minimal 4 kalimat (5 poin).',
                'bobot' => 20
            ],
        ];

        $insQ = $pdo->prepare("INSERT INTO exam_questions (exam_id, nomor_urut, jenis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, kunci_jawaban, bobot) 
            VALUES (:exam_id, :nomor_urut, :jenis, :pertanyaan, :opsi_a, :opsi_b, :opsi_c, :opsi_d, :opsi_e, :kunci_jawaban, :bobot)");
        foreach ($q1 as $q) {
            $insQ->execute($q);
        }

        // Ujian 2: PPKn Paket C
        $pdo->prepare("INSERT INTO exams (guru_id, judul, deskripsi, paket, tingkat_kelas, mapel, durasi_menit, acak_soal, status, nilai_dipublikasi)
            VALUES (1, 'Asesmen Akhir Semester Pendidikan Kewarganegaraan Paket C', 'Ujian Kesetaraan Paket C Setara SMA bidang studi PPKn. Kerjakan dengan teliti dan jujur.', 'Paket C', 'Semua Kelas', 'Pendidikan Kewarganegaraan', 0, 1, 'aktif', 1)")
            ->execute();
        $examId2 = $pdo->lastInsertId();

        $q2 = [
            [
                'exam_id' => $examId2, 'nomor_urut' => 1, 'jenis' => 'pg',
                'pertanyaan' => 'Sila dalam Pancasila yang menjadi landasan utama musyawarah untuk mufakat dalam kehidupan berbangsa adalah sila...',
                'opsi_a' => 'Pertama', 'opsi_b' => 'Kedua', 'opsi_c' => 'Ketiga', 'opsi_d' => 'Keempat', 'opsi_e' => 'Kelima',
                'kunci_jawaban' => 'D', 'bobot' => 20
            ],
            [
                'exam_id' => $examId2, 'nomor_urut' => 2, 'jenis' => 'pg',
                'pertanyaan' => 'Lembaga negara di Indonesia yang berwenang mengubah dan menetapkan Undang-Undang Dasar adalah...',
                'opsi_a' => 'Presiden', 'opsi_b' => 'DPR', 'opsi_c' => 'MPR', 'opsi_d' => 'Mahkamah Konstitusi', 'opsi_e' => 'Mahkamah Agung',
                'kunci_jawaban' => 'C', 'bobot' => 20
            ],
            [
                'exam_id' => $examId2, 'nomor_urut' => 3, 'jenis' => 'pg',
                'pertanyaan' => 'Hak warga negara untuk mendapatkan pendidikan yang layak diatur secara tegas dalam UUD 1945 pasal...',
                'opsi_a' => 'Pasal 27 ayat 1', 'opsi_b' => 'Pasal 28', 'opsi_c' => 'Pasal 29 ayat 2', 'opsi_d' => 'Pasal 30 ayat 1', 'opsi_e' => 'Pasal 31 ayat 1',
                'kunci_jawaban' => 'E', 'bobot' => 20
            ],
            [
                'exam_id' => $examId2, 'nomor_urut' => 4, 'jenis' => 'esai',
                'pertanyaan' => 'Menurut pendapat Anda, mengapa pendidikan kesetaraan (Paket A, B, C) memiliki peranan yang sangat penting dalam memajukan kualitas sumber daya manusia di lingkungan masyarakat?',
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null, 'opsi_e' => null,
                'kunci_jawaban' => 'Pendidikan kesetaraan memberikan kesempatan kedua bagi warga belajar yang putus sekolah untuk meningkatkan literasi, ijazah resmi, keterampilan kerja, dan martabat sosial keluarga.',
                'bobot' => 40
            ],
        ];
        foreach ($q2 as $q) {
            $insQ->execute($q);
        }

        // Ujian 3: Tematik / IPA Paket A
        $pdo->prepare("INSERT INTO exams (guru_id, judul, deskripsi, paket, tingkat_kelas, mapel, durasi_menit, acak_soal, status, nilai_dipublikasi)
            VALUES (1, 'Penilaian Sumatif Ilmu Pengetahuan Alam Paket A', 'Ujian Kesetaraan Paket A Setara SD tentang Lingkungan Hidup dan Makhluk Hidup.', 'Paket A', 'Semua Kelas', 'Ilmu Pengetahuan Alam', 0, 1, 'aktif', 1)")
            ->execute();
        $examId3 = $pdo->lastInsertId();

        $q3 = [
            [
                'exam_id' => $examId3, 'nomor_urut' => 1, 'jenis' => 'pg',
                'pertanyaan' => 'Tumbuhan hijau membuat makanannya sendiri melalui proses yang disebut...',
                'opsi_a' => 'Respirasi', 'opsi_b' => 'Fotosintesis', 'opsi_c' => 'Metamorfosis', 'opsi_d' => 'Penyerbukan', 'opsi_e' => 'Penguapan',
                'kunci_jawaban' => 'B', 'bobot' => 25
            ],
            [
                'exam_id' => $examId3, 'nomor_urut' => 2, 'jenis' => 'pg',
                'pertanyaan' => 'Hewan yang berkembang biak dengan cara bertelur disebut...',
                'opsi_a' => 'Vivipar', 'opsi_b' => 'Ovipar', 'opsi_c' => 'Ovovivipar', 'opsi_d' => 'Karnivora', 'opsi_e' => 'Herbivora',
                'kunci_jawaban' => 'B', 'bobot' => 25
            ],
            [
                'exam_id' => $examId3, 'nomor_urut' => 3, 'jenis' => 'esai',
                'pertanyaan' => 'Sebutkan 3 cara menjaga kelestarian lingkungan di sekitar tempat tinggal kita!',
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null, 'opsi_e' => null,
                'kunci_jawaban' => '1. Membuang sampah pada tempatnya. 2. Melakukan penghijauan / menanam pohon. 3. Menghemat penggunaan air dan listrik.',
                'bobot' => 50
            ],
        ];
        foreach ($q3 as $q) {
            $insQ->execute($q);
        }

        // Berikan contoh attempt / riwayat nilai untuk 2 siswa agar hasil ujian langsung terlihat di dashboard murid!
        // Ambil murid 1 (A. RUDIANSYAH) dan murid 3 (ABDUL AZIZ)
        $st1 = $pdo->query("SELECT id FROM students WHERE nik = '6303120801110002'")->fetch();
        if ($st1) {
            $pdo->prepare("INSERT INTO exam_attempts (exam_id, student_id, waktu_selesai, status, nilai_pg, nilai_esai, nilai_total, status_koreksi, catatan_guru)
                VALUES (?, ?, datetime('now'), 'selesai', 60, 32, 92, 'sudah', 'Prestasi sangat baik, pemahaman materi teks bacaan dan esai sangat memuaskan.')")
                ->execute([$examId1, $st1['id']]);
        }

        $st2 = $pdo->query("SELECT id FROM students WHERE nik = '6303120806840002'")->fetch();
        if ($st2) {
            $pdo->prepare("INSERT INTO exam_attempts (exam_id, student_id, waktu_selesai, status, nilai_pg, nilai_esai, nilai_total, status_koreksi, catatan_guru)
                VALUES (?, ?, datetime('now'), 'selesai', 60, 28, 88, 'sudah', 'Jawaban esai terstruktur baik, pertahankan nilai dan semangat belajarnya.')")
                ->execute([$examId2, $st2['id']]);
        }
    }

    // 7. Seed Galeri Kegiatan PKBM
    $stmtGal = $pdo->prepare("SELECT COUNT(*) FROM gallery");
    $stmtGal->execute();
    if ($stmtGal->fetchColumn() == 0) {
        $galleryData = [
            ['Ujian Pendidikan Kesetaraan (UPK) Paket B & C', 'Ujian', 'Pelaksanaan Ujian Pendidikan Kesetaraan berbasis komputer dan tertulis di ruang kelas PKBM Mandiri.', 'kegiatan_1.jpg', '2026-05-15'],
            ['Pelatihan Keterampilan Komputer & Digital Marketing', 'Keterampilan', 'Program peningkatan life skill warga belajar dalam penguasaan komputer perkantoran dan bisnis online.', 'kegiatan_2.jpg', '2026-06-20'],
            ['Workshop Tata Busana dan Menjahit', 'Vokasi', 'Kegiatan vokasi menjahit bagi warga belajar Paket C untuk mencetak wirausaha mandiri di Banjar.', 'kegiatan_3.jpg', '2026-08-10'],
            ['Pembelajaran Tatap Muka & Bimbingan Tutor', 'Belajar', 'Suasana kegiatan belajar mengajar modul terpadu antara tutor dan warga belajar Paket B.', 'kegiatan_4.jpg', '2026-09-05'],
        ];
        $insGal = $pdo->prepare("INSERT INTO gallery (judul, kategori, deskripsi, file_gambar, tanggal_kegiatan) VALUES (?, ?, ?, ?, ?)");
        foreach ($galleryData as $g) {
            $insGal->execute($g);
        }
    }
}

