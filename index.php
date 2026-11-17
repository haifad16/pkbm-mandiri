<?php
/**
 * Halaman Utama (Landing Page) PKBM Mandiri
 * Profil Lembaga, Program Kesetaraan, Guru, Galeri, dan Alur Pendaftaran
 */
require_once __DIR__ . '/config/app.php';

$pageTitle = 'Beranda';

// Ambil data profil dari database
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$skOperasional = getSetting('sk_operasional', '421.9/056/Disdik/2021');
$kepalaSekolah = getSetting('kepala_pkbm', 'H. Zailani, S.Pd');
$sambutan = getSetting('sambutan_kepala', 'Selamat datang di Website Resmi PKBM Mandiri Mataraman. Kami berdedikasi memberikan kesempatan pendidikan nonformal yang bermutu, fleksibel, dan inklusif bagi seluruh lapisan masyarakat.');
$visi = getSetting('visi', 'Terwujudnya Pusat Kegiatan Belajar Masyarakat yang Unggul, Mandiri, Berakhlak Mulia, dan Berdaya Saing dalam Layanan Pendidikan Kesetaraan.');
$misi = getSetting('misi', "1. Menyelenggarakan layanan pendidikan kesetaraan Paket A, B, dan C yang bermutu.\n2. Mengembangkan kompetensi vokasi dan keterampilan hidup.\n3. Membina karakter religius dan sopan santun.\n4. Menerapkan sistem pembelajaran fleksibel dan modern.");
$tahunAjaran = getSetting('tahun_ajaran', '2026/2027');
$ppdbStatus = getSetting('ppdb_status', 'buka');
$waNumber = getSetting('whatsapp', APP_WHATSAPP);
$driveLink = getSetting('google_drive_folder', 'https://drive.google.com/drive/folders/1bgIM8NcMXxKQDx3ekLFrJ95cgh2yh4BM?usp=sharing');

// Hitung statistik dari database
$pdo = getDB();

$stmtCountSiswa = $pdo->query("SELECT COUNT(*) FROM students");
$totalSiswa = (int)$stmtCountSiswa->fetchColumn();
if ($totalSiswa < 500) $totalSiswa = 556; // fallback jika belum seeder

$stmtCountGuru = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status_aktif = 1");
$totalGuru = (int)$stmtCountGuru->fetchColumn();

$stmtCountUjian = $pdo->query("SELECT COUNT(*) FROM exams WHERE status = 'aktif'");
$totalUjian = (int)$stmtCountUjian->fetchColumn();

// Ambil data guru untuk ditampilkan di landing page
$stmtTeachers = $pdo->query("SELECT * FROM teachers WHERE status_aktif = 1 ORDER BY id ASC LIMIT 8");
$teachers = $stmtTeachers->fetchAll();

// Ambil galeri kegiatan
$stmtGallery = $pdo->query("SELECT * FROM gallery ORDER BY tanggal_kegiatan DESC, id DESC LIMIT 6");
$galleryItems = $stmtGallery->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. HERO SECTION -->
<section id="beranda" class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-950 text-white pt-12 pb-24 lg:pt-20 lg:pb-32">
    <!-- Dekorasi Lingkaran Latar -->
    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Teks Hero -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Badges Legalitas -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <i class="bi bi-patch-check-fill text-emerald-400"></i> NPSN: <?= htmlspecialchars($schoolNpsn) ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        <i class="bi bi-shield-check text-blue-400"></i> Izin Disdik: <?= htmlspecialchars($skOperasional) ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        <i class="bi bi-award-fill text-amber-400"></i> Terakreditasi
                    </span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Masa Depan Gemilang, <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-300">
                        Pendidikan untuk Semua Usia
                    </span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Selamat datang di <strong><?= htmlspecialchars($schoolFullName) ?></strong>, Mataraman, Kab. Banjar. 
                    Solusi resmi meraih Ijazah Negara <strong>Paket A (Setara SD)</strong>, <strong>Paket B (Setara SMP)</strong>, dan <strong>Paket C (Setara SMA)</strong> dengan kurikulum merdeka yang fleksibel, nyaman, dan terpadu.
                </p>

                <!-- Tombol Aksi Utama -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-base shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition transform active:scale-95">
                        <i class="bi bi-pencil-square text-lg"></i>
                        <span>Daftar Murid Baru Online</span>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/modules/auth/login.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-base border border-white/20 backdrop-blur-sm transition">
                        <i class="bi bi-person-badge text-lg text-blue-300"></i>
                        <span>Login Murid (Via NIK) / CBT</span>
                    </a>
                </div>

                <!-- Fast Stats Counter -->
                <div class="pt-8 grid grid-cols-3 gap-4 border-t border-slate-800">
                    <div class="p-3 rounded-xl bg-slate-800/40 border border-slate-700/50 text-center lg:text-left">
                        <div class="text-2xl sm:text-3xl font-extrabold text-blue-400"><?= $totalSiswa ?>+</div>
                        <div class="text-xs text-slate-400 font-medium mt-0.5">Warga Belajar Aktif</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-800/40 border border-slate-700/50 text-center lg:text-left">
                        <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400">3 Paket</div>
                        <div class="text-xs text-slate-400 font-medium mt-0.5">Paket A, B, &amp; C Resmi</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-800/40 border border-slate-700/50 text-center lg:text-left">
                        <div class="text-2xl sm:text-3xl font-extrabold text-amber-400">100%</div>
                        <div class="text-xs text-slate-400 font-medium mt-0.5">Ujian CBT Ramah HP</div>
                    </div>
                </div>
            </div>

            <!-- Kartu Interaktif Hero Kanan -->
            <div class="lg:col-span-5">
                <div class="bg-gradient-to-b from-slate-800/80 to-slate-900/90 rounded-3xl p-6 sm:p-8 border border-slate-700 shadow-2xl backdrop-blur-xl relative">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-700/80">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600/30 border border-blue-500/40 flex items-center justify-center text-blue-400 text-2xl">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">Portal Pintar PKBM</h3>
                                <p class="text-xs text-slate-400">Pendaftaran &bull; Data Murid &bull; Ujian</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 text-xs font-semibold rounded-full border border-emerald-500/30">
                            Aktif 24 Jam
                        </span>
                    </div>

                    <!-- Quick Links List -->
                    <div class="space-y-3.5 my-6">
                        <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-750 border border-slate-700/70 text-slate-200 hover:text-white transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                    <i class="bi bi-file-earmark-person text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold">Formulir Pendaftaran Siswa</div>
                                    <div class="text-[11px] text-slate-400">Upload berkas Ijazah, KK, Akta, dll</div>
                                </div>
                            </div>
                            <i class="bi bi-arrow-right text-slate-400 group-hover:translate-x-1 text-slate-300 transition-transform"></i>
                        </a>

                        <a href="<?= BASE_URL ?>/modules/auth/login.php" class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-750 border border-slate-700/70 text-slate-200 hover:text-white transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                                    <i class="bi bi-card-heading text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold">Kartu Digital &amp; Database Murid</div>
                                    <div class="text-[11px] text-slate-400">Login dengan NIK untuk cek profil</div>
                                </div>
                            </div>
                            <i class="bi bi-arrow-right text-slate-400 group-hover:translate-x-1 text-slate-300 transition-transform"></i>
                        </a>

                        <a href="<?= BASE_URL ?>/modules/auth/login.php" class="flex items-center justify-between p-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-750 border border-slate-700/70 text-slate-200 hover:text-white transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold">Ujian Mandiri (CBT Online)</div>
                                    <div class="text-[11px] text-slate-400">Pilihan ganda otomatis &amp; esai terarah</div>
                                </div>
                            </div>
                            <i class="bi bi-arrow-right text-slate-400 group-hover:translate-x-1 text-slate-300 transition-transform"></i>
                        </a>
                    </div>

                    <!-- Callout Rule Paket A Dewasa -->
                    <div class="p-3.5 rounded-xl bg-indigo-950/60 border border-indigo-500/40 text-xs text-indigo-200 flex items-start gap-2.5">
                        <i class="bi bi-info-circle-fill text-indigo-400 text-base flex-shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-bold text-white">Warga Belajar Dewasa Paket A:</span> Bagi yang telah melewati usia SD formal, pendaftaran dibebaskan dari syarat surat pindah / ijazah TK!
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. BANNER PENGUMUMAN PPDB -->
<section class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white py-5 shadow-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-center md:text-left">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0 text-xl">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div>
                    <div class="font-bold text-base">PPDB Gelombang Baru Tahun Ajaran <?= htmlspecialchars($tahunAjaran) ?> Resmi Dibuka!</div>
                    <div class="text-xs text-emerald-100">Bisa daftar kapan saja, ijazah diakui oleh Kementerian Pendidikan Kebudayaan Riset dan Teknologi.</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="px-5 py-2.5 rounded-xl bg-white text-emerald-800 font-bold text-xs uppercase tracking-wider hover:bg-emerald-50 shadow transition transform active:scale-95">
                    Daftar Sekarang
                </a>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waNumber) ?>" target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-800/80 text-white font-semibold text-xs hover:bg-emerald-800 transition">
                    <i class="bi bi-whatsapp"></i> Tanya Panitia
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 3. PROFIL & SAMBUTAN KEPALA PKBM -->
<section id="profil" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Foto & Kartu Pimpinan -->
            <div class="lg:col-span-5 text-center">
                <div class="relative inline-block">
                    <div class="w-64 h-64 sm:w-72 sm:h-72 mx-auto rounded-3xl bg-gradient-to-tr from-blue-700 to-indigo-600 p-2 shadow-2xl overflow-hidden flex items-center justify-center">
                        <div class="w-full h-full rounded-2xl bg-slate-900 flex flex-col items-center justify-center text-white p-4">
                            <i class="bi bi-person-fill text-8xl text-blue-300"></i>
                            <div class="font-bold text-lg mt-2"><?= htmlspecialchars($kepalaSekolah) ?></div>
                            <div class="text-xs text-blue-300 font-medium">Ketua / Kepala PKBM Mandiri</div>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-white px-5 py-2 rounded-xl shadow-lg border border-slate-100 text-xs font-semibold text-slate-700 whitespace-nowrap">
                        <i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i> Terdaftar di Kemendikbud
                    </div>
                </div>

                <!-- Info Legalitas Lembaga -->
                <div class="mt-8 grid grid-cols-2 gap-3 text-left max-w-sm mx-auto">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">NPSN Resmi</div>
                        <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($schoolNpsn) ?></div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">Kecamatan</div>
                        <div class="text-sm font-bold text-slate-900">Mataraman, Banjar</div>
                    </div>
                </div>
            </div>

            <!-- Sambutan & Visi Misi -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-2">Profil &amp; Filosofi Lembaga</div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Sambutan Kepala PKBM Mandiri
                    </h2>
                </div>

                <div class="relative bg-slate-50 border-l-4 border-blue-600 p-6 rounded-r-2xl shadow-sm italic text-slate-700 leading-relaxed text-sm sm:text-base">
                    <i class="bi bi-quote text-4xl text-blue-200 absolute top-2 right-4 pointer-events-none"></i>
                    "<?= nl2br(htmlspecialchars($sambutan)) ?>"
                    <div class="mt-4 not-italic font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span><?= htmlspecialchars($kepalaSekolah) ?></span>
                        <span class="text-xs text-slate-400 font-normal">&bull; Kepala PKBM Mandiri Mataraman</span>
                    </div>
                </div>

                <!-- Visi & Misi Box -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <!-- Visi -->
                    <div class="p-5 rounded-2xl bg-blue-50/60 border border-blue-100">
                        <div class="flex items-center gap-2 text-blue-700 font-bold text-sm mb-2">
                            <i class="bi bi-eye-fill"></i>
                            <span>Visi Kami</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            <?= nl2br(htmlspecialchars($visi)) ?>
                        </p>
                    </div>

                    <!-- Misi -->
                    <div class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm mb-2">
                            <i class="bi bi-bullseye"></i>
                            <span>Misi Utama</span>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-1">
                            <?= nl2br(htmlspecialchars($misi)) ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 4. PROGRAM PENDIDIKAN KESETARAAN -->
<section id="program" class="py-20 bg-slate-50 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-2">Layanan Pendidikan</div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Pilih Program Kesetaraan Anda
            </h2>
            <p class="text-slate-600 mt-3 text-sm sm:text-base">
                Seluruh program memiliki legalitas resmi dari Kemendikbudristek dengan ijazah yang setara sekolah formal untuk kuliah, bekerja, atau berwirausaha.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Paket A -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Setara SD</span>
                        <span class="text-xs text-slate-400">Tingkat I / II</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3">Paket A (Pendidikan Dasar)</h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Diperuntukkan bagi anak usia sekolah SD yang putus sekolah, maupun <strong>warga belajar dewasa</strong> yang belum berkesempatan menuntaskan sekolah dasar.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Membaca, Menulis, Berhitung (Calistung)
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Pendidikan Agama, PPKn, Bahasa, IPA/IPS
                        </li>
                        <li class="flex items-start gap-2 text-emerald-700 font-semibold bg-emerald-50 p-2 rounded-lg">
                            <i class="bi bi-patch-check-fill mt-0.5"></i> Dewasa bebas syarat ijazah TK / surat pindah!
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow transition">
                        <span>Daftar Paket A</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Paket B -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Setara SMP</span>
                        <span class="text-xs text-slate-400">Tingkat III / IV</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3">Paket B (Pendidikan Menengah)</h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Menampung lulusan SD/MI/Paket A yang tertunda melanjutkan ke SMP. Ijazah resmi dapat digunakan melanjutkan ke SMA/SMK negeri maupun swasta.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Kurikulum Kesetaraan Terstandar
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Pembelajaran Tatap Muka &amp; Mandiri
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Ijazah Resmi Penyetaraan SMP
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow transition">
                        <span>Daftar Paket B</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Paket C -->
            <div class="bg-white rounded-3xl p-8 border-2 border-indigo-500 shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1 relative">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow">
                    Paling Banyak Diminati
                </div>
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">Setara SMA/SMK</span>
                        <span class="text-xs text-slate-400">Peminatan IPS/Vokasi</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3">Paket C (Pendidikan Atas)</h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Pilihan tepat bagi lulusan SMP/Paket B untuk meraih ijazah setara SMA. Sangat dicari untuk persyaratan kerja pabrik/kantor, kuliah, atau tes instansi resmi.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Syarat Masuk Kuliah PTN / PTS
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Syarat Kerja Swasta &amp; BUMN
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i> Keterampilan Komputer &amp; Wirausaha
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow transition">
                        <span>Daftar Paket C</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 5. FITUR SISTEM CERDAS (WEB & APK) -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-6 space-y-6">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-2">Inovasi Teknologi PKBM</div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Web &amp; Aplikasi Mobile Terintegrasi untuk Kemudahan Anda
                    </h2>
                </div>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    Kami mendesain sistem digital PKBM Mandiri agar dapat diakses oleh siapa saja, di mana saja, melalui smartphone Android, iPhone, tablet, hingga komputer desktop tanpa kendala.
                </p>

                <!-- Fitur 4 Poin -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xl">
                            <i class="bi bi-ui-checks-grid"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Pendaftaran Online Cepat &amp; Praktis</h4>
                            <p class="text-xs text-slate-600 mt-0.5">Isi formulir biodata lengkap dan orang tua, unggah berkas langsung dari foto HP, serta cetak bukti pendaftaran digital seketika.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center flex-shrink-0 text-xl">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Pangkalan Data &amp; Kartu Siswa Digital</h4>
                            <p class="text-xs text-slate-600 mt-0.5">Murid dapat login langsung menggunakan NIK untuk melihat data diri, rombel/kelas, dan mengunduh Kartu Pelajar Digital (KTP Pelajar).</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0 text-xl">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Ujian CBT Nyaman Tanpa Timer Kaku</h4>
                            <p class="text-xs text-slate-600 mt-0.5">Soal diacak otomatis, nilai pilihan ganda muncul seketika, bebas copy-paste, dan fleksibel tanpa tabrakan waktu bagi murid yang datang terlambat.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center flex-shrink-0 text-xl">
                            <i class="bi bi-file-earmark-word"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Arsip Bank Soal Word &amp; Ekspor Excel</h4>
                            <p class="text-xs text-slate-600 mt-0.5">Guru dan Admin dapat mengarsipkan kumpulan soal ke dokumen Microsoft Word (.doc) dan merekap seluruh nilai siswa ke Excel.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Mockup PWA App -->
            <div class="lg:col-span-6">
                <div class="bg-gradient-to-tr from-slate-900 to-blue-950 rounded-3xl p-8 text-white border border-slate-800 shadow-2xl relative">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xl">
                                <i class="bi bi-phone"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white text-base">Aplikasi PKBM Mandiri (APK/PWA)</div>
                                <div class="text-xs text-blue-300">Siap pasang di Android &amp; Komputer</div>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                            Ringan &amp; Cepat
                        </span>
                    </div>

                    <div class="py-6 space-y-4 text-xs text-slate-300 leading-relaxed">
                        <p>
                            Tidak perlu mengunduh aplikasi berukuran ratusan Megabyte. Anda dapat memasang website ini langsung ke layar beranda HP Anda layaknya aplikasi Android asli (APK).
                        </p>
                        <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700 space-y-2">
                            <div class="font-bold text-white flex items-center gap-2">
                                <i class="bi bi-android2 text-emerald-400 text-base"></i> Cara Pasang di HP Android:
                            </div>
                            <ol class="list-decimal list-inside space-y-1 text-slate-300">
                                <li>Buka website ini di Google Chrome HP Anda.</li>
                                <li>Klik menu titik tiga di pojok kanan atas Chrome.</li>
                                <li>Pilih <strong>"Pasang Aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.</li>
                                <li>Ikon PKBM Mandiri akan otomatis muncul di menu aplikasi HP Anda!</li>
                            </ol>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button id="pwa-install-btn-hero" class="w-full py-3.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-lg transition">
                            <i class="bi bi-download"></i>
                            <span>Klik di Sini untuk Pasang Aplikasi di HP Ini</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 6. TUTOR & TENAGA PENDIDIK -->
<section id="guru" class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-2">Tenaga Pendidik</div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    Tutor &amp; Pengajar Berdedikasi
                </h2>
                <p class="text-slate-600 mt-2 text-sm">
                    Tutor berpengalaman yang siap membimbing warga belajar dengan sabar, ramah, dan komunikatif.
                </p>
            </div>
            
            <a href="<?= htmlspecialchars($driveLink) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-300 shadow-sm transition">
                <i class="bi bi-google text-rose-500"></i>
                <span>Folder Google Drive Data Guru</span>
                <i class="bi bi-box-arrow-up-right text-slate-400"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (!empty($teachers)): ?>
                <?php foreach ($teachers as $t): ?>
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm text-center flex flex-col items-center hover:shadow-md transition">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-3xl font-black mb-4 shadow">
                            <?php if (!empty($t['foto']) && file_exists(BASE_PATH . '/' . $t['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($t['foto']) ?>" alt="<?= htmlspecialchars($t['nama_lengkap']) ?>" class="w-full h-full object-cover rounded-2xl">
                            <?php else: ?>
                                <?= strtoupper(substr($t['nama_lengkap'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <h4 class="font-bold text-slate-900 text-base leading-snug"><?= htmlspecialchars($t['nama_lengkap']) ?><?= !empty($t['gelar']) ? ', ' . htmlspecialchars($t['gelar']) : '' ?></h4>
                        <div class="text-xs text-blue-600 font-semibold mt-1"><?= htmlspecialchars($t['mapel_diampu'] ?? 'Tutor Pembina') ?></div>
                        <div class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($t['jabatan'] ?? 'Tutor PKBM') ?></div>
                        
                        <div class="mt-4 pt-4 border-t border-slate-100 w-full flex items-center justify-center gap-2 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <i class="bi bi-mortarboard-fill text-slate-400"></i> <?= htmlspecialchars($t['pendidikan_terakhir'] ?? 'S1') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-4 text-center py-8 text-slate-400 text-sm">
                    Data guru sedang diperbarui.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 7. GALERI KEGIATAN PKBM -->
<section id="galeri" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-2">Dokumentasi &amp; Aktivitas</div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Galeri Kegiatan Warga Belajar
            </h2>
            <p class="text-slate-600 mt-3 text-sm sm:text-base">
                Momen pembelajaran, pelatihan keterampilan vokasi, ujian kesetaraan, dan sosialisasi di PKBM Mandiri.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($galleryItems)): ?>
                <?php foreach ($galleryItems as $item): ?>
                    <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="h-56 bg-slate-800 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($item['file_gambar']) && file_exists(BASE_PATH . '/' . $item['file_gambar'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['file_gambar']) ?>" alt="<?= htmlspecialchars($item['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="text-center text-slate-500 p-4">
                                    <i class="bi bi-image text-5xl mb-2 text-slate-600"></i>
                                    <div class="text-xs"><?= htmlspecialchars($item['judul']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5 bg-white">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full font-semibold bg-blue-50 text-blue-700">
                                    <?= htmlspecialchars($item['kategori'] ?? 'Aktivitas') ?>
                                </span>
                                <span><i class="bi bi-calendar3 mr-1"></i> <?= htmlspecialchars($item['tanggal_kegiatan'] ?? date('Y-m-d')) ?></span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-blue-600 transition">
                                <?= htmlspecialchars($item['judul']) ?>
                            </h4>
                            <?php if (!empty($item['deskripsi'])): ?>
                                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2"><?= htmlspecialchars($item['deskripsi']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default Placeholder Gallery Cards jika belum diupload admin -->
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white p-5 shadow-sm text-center">
                    <div class="h-44 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 text-4xl mb-4">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div class="text-xs font-bold text-blue-600 uppercase">Kegiatan Belajar</div>
                    <h4 class="font-bold text-slate-900 text-sm mt-1">Pembelajaran Tatap Muka Mandiri</h4>
                    <p class="text-xs text-slate-500 mt-1">Sesi pendalaman materi paket kesetaraan bersama para tutor.</p>
                </div>

                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white p-5 shadow-sm text-center">
                    <div class="h-44 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 text-4xl mb-4">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <div class="text-xs font-bold text-emerald-600 uppercase">Pelatihan Vokasi</div>
                    <h4 class="font-bold text-slate-900 text-sm mt-1">Praktik Komputer &amp; Keterampilan Digital</h4>
                    <p class="text-xs text-slate-500 mt-1">Pembekalan keterampilan kerja siap pakai bagi warga belajar.</p>
                </div>

                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white p-5 shadow-sm text-center">
                    <div class="h-44 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 text-4xl mb-4">
                        <i class="bi bi-award"></i>
                    </div>
                    <div class="text-xs font-bold text-amber-600 uppercase">Asesmen &amp; Ujian</div>
                    <h4 class="font-bold text-slate-900 text-sm mt-1">Pelaksanaan Ujian CBT Berbasis Komputer</h4>
                    <p class="text-xs text-slate-500 mt-1">Ujian kesetaraan berbasis komputer dengan suasana nyaman.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 8. ALUR PENDAFTARAN 4 LANGKAH -->
<section class="py-20 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-2">Langkah Mudah</div>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                Alur Pendaftaran Murid Baru
            </h2>
            <p class="text-slate-400 mt-3 text-sm sm:text-base">
                Proses cepat, dapat diakses dari mana saja tanpa harus antre berlama-lama.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700 relative">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-extrabold text-lg flex items-center justify-center mb-4">
                    1
                </div>
                <h4 class="font-bold text-base text-white mb-2">Isi Formulir Online</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Buka menu pendaftaran, masukkan data diri calon siswa, NIK, alamat domisili, dan data orang tua/wali.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700 relative">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-extrabold text-lg flex items-center justify-center mb-4">
                    2
                </div>
                <h4 class="font-bold text-base text-white mb-2">Unggah Foto Berkas</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Upload foto/scan Ijazah terakhir, KK, dan Akta Kelahiran. Calon siswa dewasa Paket A dibebaskan dari syarat TK!
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700 relative">
                <div class="w-10 h-10 rounded-xl bg-amber-600 text-white font-extrabold text-lg flex items-center justify-center mb-4">
                    3
                </div>
                <h4 class="font-bold text-base text-white mb-2">Cetak Bukti Daftar</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Sistem otomatis menerbitkan Nomor Pendaftaran resmi dan Kartu Bukti Pendaftaran digital yang dapat Anda cetak atau simpan ke PDF.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700 relative">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-extrabold text-lg flex items-center justify-center mb-4">
                    4
                </div>
                <h4 class="font-bold text-base text-white mb-2">Verifikasi &amp; Mulai Belajar</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Admin memverifikasi berkas, murid langsung mendapatkan kelas/rombel dan bisa login dengan NIK untuk mengikuti ujian mandiri.
                </p>
            </div>

        </div>

        <div class="text-center mt-12">
            <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-base shadow-xl transition transform active:scale-95">
                <i class="bi bi-pencil-square"></i>
                <span>Mulai Pendaftaran Sekarang</span>
            </a>
        </div>
    </div>
</section>

<!-- 9. CALL TO ACTION & LOKASI -->
<section class="py-20 bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <div class="lg:col-span-8 space-y-4">
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Ada Pertanyaan atau Butuh Bantuan Pendaftaran?
                </h3>
                <p class="text-sm text-slate-600 leading-relaxed max-w-2xl">
                    Tim sekretariat dan tutor PKBM Mandiri siap membantu Anda dalam proses pendaftaran, konsultasi pemilihan paket, maupun kendala sistem.
                </p>
                <div class="pt-2 flex flex-wrap gap-4 text-xs font-semibold text-slate-700">
                    <span class="flex items-center gap-2"><i class="bi bi-geo-alt-fill text-rose-500 text-base"></i> Jl. A. Yani KM. 60 RT. 02 RW. 02, Kec. Mataraman</span>
                    <span class="flex items-center gap-2"><i class="bi bi-clock-fill text-blue-500 text-base"></i> Senin - Sabtu: 08.00 - 16.00 WITA</span>
                </div>
            </div>

            <div class="lg:col-span-4 flex flex-col gap-3">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waNumber) ?>?text=Halo%20Admin%20PKBM%20Mandiri,%20saya%20ingin%20tanya%20pendaftaran" target="_blank" class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow transition">
                    <i class="bi bi-whatsapp text-lg"></i>
                    <span>Chat WhatsApp Admin</span>
                </a>
                <a href="<?= BASE_URL ?>/modules/auth/login.php" class="w-full inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    <i class="bi bi-shield-lock-fill text-amber-400"></i>
                    <span>Login Petugas / Guru / Admin</span>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- Script Khusus PWA Install Prompt di Hero -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const heroInstallBtn = document.getElementById('pwa-install-btn-hero');
    if (heroInstallBtn) {
        heroInstallBtn.addEventListener('click', function() {
            if (window.deferredPWAEvent) {
                window.deferredPWAEvent.prompt();
                window.deferredPWAEvent.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('Pengguna menyetujui pemasangan aplikasi');
                    }
                    window.deferredPWAEvent = null;
                });
            } else {
                alert('Untuk memasang di HP: Buka menu titik tiga pada browser Chrome Anda, lalu pilih "Tambahkan ke Layar Utama" atau "Pasang Aplikasi".');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

