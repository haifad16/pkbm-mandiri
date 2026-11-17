<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('siswa');

$pageTitle = 'Portal Murid - Biodata & Informasi';
$studentId = $_SESSION['student_id'] ?? 0;
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('danger', 'Data profil siswa tidak ditemukan.');
    redirect('modules/auth/logout.php');
}

// Ambil riwayat ujian siswa
$stmtAttempts = $pdo->prepare("SELECT a.*, e.judul, e.mapel, e.paket, e.tingkat_kelas 
    FROM exam_attempts a 
    JOIN exams e ON a.exam_id = e.id 
    WHERE a.student_id = ? 
    ORDER BY a.created_at DESC");
$stmtAttempts->execute([$studentId]);
$attempts = $stmtAttempts->fetchAll();

// Hitung ujian aktif yang tersedia untuk paket siswa
$paketSiswa = $student['paket'] ?? 'Paket B';
$stmtExams = $pdo->prepare("SELECT COUNT(*) FROM exams 
    WHERE status = 'aktif' AND (paket = ? OR paket = 'Semua') 
    AND id NOT IN (SELECT exam_id FROM exam_attempts WHERE student_id = ?)");
$stmtExams->execute([$paketSiswa, $studentId]);
$availableExamsCount = (int)$stmtExams->fetchColumn();

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Welcome Banner Card -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-blue-100">
                        <i class="bi bi-person-check-fill mr-1"></i> Warga Belajar Aktif
                    </span>
                    <span class="px-3 py-1 bg-emerald-500/80 rounded-full text-xs font-bold text-white">
                        <?= htmlspecialchars($student['paket']) ?>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, <?= htmlspecialchars($student['nama']) ?>!
                </h1>
                <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-xl">
                    Selamat datang di Portal Murid PKBM Mandiri. Di sini Anda dapat memantau data induk diri, mencetak kartu pelajar, mengikuti ujian kesetaraan CBT, dan melihat hasil rapor nilai.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= BASE_URL ?>/modules/siswa/ujian.php" class="px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm shadow-md hover:shadow-lg transition flex items-center gap-2 transform active:scale-95">
                    <i class="bi bi-pencil-square text-lg"></i>
                    <span>Ujian Online (<?= $availableExamsCount ?> Tersedia)</span>
                </a>
                <a href="<?= BASE_URL ?>/modules/siswa/kartu.php" class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm backdrop-blur-md transition flex items-center gap-2">
                    <i class="bi bi-card-heading text-lg"></i>
                    <span>Kartu Pelajar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Rombel / Kelas</div>
            <div class="text-base sm:text-lg font-black text-slate-900 mt-1 truncate">
                <?= htmlspecialchars($student['rombel_saat_ini'] ?: $student['paket']) ?>
            </div>
            <div class="text-[11px] text-blue-600 font-medium mt-1">Kelas Aktif 2026/2027</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Nomor NIK</div>
            <div class="text-base sm:text-lg font-mono font-bold text-slate-900 mt-1">
                <?= htmlspecialchars($student['nik']) ?>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Akun Login Siswa</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">NISN</div>
            <div class="text-base sm:text-lg font-mono font-bold text-slate-900 mt-1">
                <?= htmlspecialchars($student['nisn'] ?: '-') ?>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Dapodik Nonformal</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Ujian Selesai</div>
            <div class="text-base sm:text-lg font-black text-emerald-600 mt-1">
                <?= count($attempts) ?> Mapel
            </div>
            <div class="text-[11px] text-emerald-600 font-medium mt-1">
                <a href="<?= BASE_URL ?>/modules/siswa/nilai.php" class="hover:underline">Lihat Rapor Nilai &rarr;</a>
            </div>
        </div>
    </div>

    <!-- 2 Column Details: Biodata & Recent Exams -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Col 1 & 2: Biodata Lengkap Siswa -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Biodata Lengkap Murid</h3>
                        <p class="text-xs text-slate-500">Data terdaftar di Database Pusat PKBM Mandiri</p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/modules/siswa/kartu.php" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                    <i class="bi bi-printer"></i> Cetak Kartu
                </a>
            </div>

            <!-- Identitas Pribadi -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Identitas Murid</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block">Nama Lengkap</span>
                        <span class="font-bold text-slate-900"><?= htmlspecialchars($student['nama']) ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Jenis Kelamin</span>
                        <span class="font-medium text-slate-800"><?= $student['jk'] === 'L' ? 'Laki-Laki (L)' : 'Perempuan (P)' ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Tempat, Tanggal Lahir</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($student['tempat_lahir'] ?: '-') ?>, <?= formatTanggalIndo($student['tanggal_lahir']) ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Agama</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($student['agama'] ?: 'Islam') ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Nomor HP / WhatsApp</span>
                        <span class="font-bold text-blue-700"><?= htmlspecialchars($student['hp'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Asal Sekolah Sebelumnya</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($student['asal_sekolah'] ?: '-') ?></span>
                    </div>
                </div>
            </div>

            <!-- Alamat -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Alamat Tempat Tinggal</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                    <div class="sm:col-span-2">
                        <span class="text-xs text-slate-400 block">Alamat / Jalan</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($student['alamat'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">RT / RW / Dusun</span>
                        <span class="font-medium text-slate-800">RT <?= htmlspecialchars($student['rt'] ?: '-') ?> / RW <?= htmlspecialchars($student['rw'] ?: '-') ?> (<?= htmlspecialchars($student['dusun'] ?: '-') ?>)</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Desa/Kelurahan & Kecamatan</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($student['kelurahan'] ?: '-') ?>, <?= htmlspecialchars($student['kecamatan'] ?: 'Kec. Mataraman') ?></span>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Data Orang Tua</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-blue-800 mb-2 flex items-center gap-1.5">
                            <i class="bi bi-person-fill"></i> Data Ayah
                        </div>
                        <div class="space-y-1">
                            <div><span class="text-slate-400">Nama:</span> <strong class="text-slate-800"><?= htmlspecialchars($student['nama_ayah'] ?: '-') ?></strong></div>
                            <div><span class="text-slate-400">Tahun Lahir:</span> <?= htmlspecialchars($student['tahun_lahir_ayah'] ?: '-') ?></div>
                            <div><span class="text-slate-400">Pendidikan:</span> <?= htmlspecialchars($student['pendidikan_ayah'] ?: '-') ?></div>
                            <div><span class="text-slate-400">NIK:</span> <?= htmlspecialchars($student['nik_ayah'] ?: '-') ?></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-rose-700 mb-2 flex items-center gap-1.5">
                            <i class="bi bi-person-fill"></i> Data Ibu
                        </div>
                        <div class="space-y-1">
                            <div><span class="text-slate-400">Nama:</span> <strong class="text-slate-800"><?= htmlspecialchars($student['nama_ibu'] ?: '-') ?></strong></div>
                            <div><span class="text-slate-400">Tahun Lahir:</span> <?= htmlspecialchars($student['tahun_lahir_ibu'] ?: '-') ?></div>
                            <div><span class="text-slate-400">Pendidikan:</span> <?= htmlspecialchars($student['pendidikan_ibu'] ?: '-') ?></div>
                            <div><span class="text-slate-400">NIK:</span> <?= htmlspecialchars($student['nik_ibu'] ?: '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Col 3: Riwayat Ujian & Pemberitahuan -->
        <div class="space-y-6">
            
            <!-- Ujian Card -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="bi bi-journal-bookmark-fill text-blue-600"></i> Ujian Terakhir
                    </h3>
                    <a href="<?= BASE_URL ?>/modules/siswa/nilai.php" class="text-xs font-bold text-blue-600 hover:underline">Semua &rarr;</a>
                </div>

                <?php if (empty($attempts)): ?>
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <i class="bi bi-pencil-square text-3xl mb-2 block text-slate-300"></i>
                        Belum ada riwayat ujian yang dikerjakan.
                        <div class="mt-3">
                            <a href="<?= BASE_URL ?>/modules/siswa/ujian.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700 transition">
                                Mulai Kerjakan Ujian
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($attempts, 0, 4) as $at): ?>
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-xs text-slate-900 leading-tight"><?= htmlspecialchars($at['mapel']) ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5"><?= formatTanggalIndo($at['created_at']) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-base font-black <?= $at['nilai_total'] >= 75 ? 'text-emerald-600' : 'text-amber-600' ?>">
                                        <?= number_format($at['nilai_total'], 0) ?>
                                    </div>
                                    <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-bold <?= $at['status_koreksi'] === 'sudah' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= $at['status_koreksi'] === 'sudah' ? 'Tervalidasi' : 'Koreksi Esai' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Petunjuk Belajar -->
            <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-blue-100 rounded-3xl p-6 text-xs text-slate-600 space-y-2.5">
                <div class="font-bold text-sm text-blue-900 flex items-center gap-2">
                    <i class="bi bi-info-circle-fill text-blue-600"></i> Informasi Akademik:
                </div>
                <p>
                    • Setiap warga belajar dapat mengerjakan ujian kesetaraan online secara mandiri melalui menu <strong>Ujian Online (CBT)</strong>.
                </p>
                <p>
                    • Soal pilihan ganda akan langsung dinilai oleh sistem secara otomatis, sedangkan soal esai akan diperiksa oleh guru pengampu mapel terkait.
                </p>
                <p>
                    • Jika ada kesalahan data identitas atau pergantian nomor WhatsApp, silakan lapor kepada admin atau sekretariat PKBM Mandiri.
                </p>
            </div>

        </div>

    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

