<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('siswa');

$pageTitle = 'Kartu Pelajar Digital';
$studentId = $_SESSION['student_id'] ?? 0;
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    redirect('modules/siswa/index.php');
}

$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$schoolAddress = getSetting('alamat', APP_ADDRESS);
$kepalaPkbm = getSetting('kepala_pkbm', 'H. Zailani, S.Pd');

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Top Action Bar (No Print) -->
    <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Kartu Pelajar Digital</h1>
            <p class="text-xs text-slate-500">Kartu tanda warga belajar resmi PKBM Mandiri Tahun Ajaran <?= htmlspecialchars(getSetting('tahun_ajaran', APP_YEAR)) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <i class="bi bi-printer-fill"></i> Cetak Kartu Pelajar
            </button>
            <a href="<?= BASE_URL ?>/modules/siswa/index.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Container Kartu (Centered) -->
    <div class="flex justify-center items-center py-6">
        
        <!-- CARD WRAPPER: Ukuran Standar Kartu ID / KTP (Sekitar 85.6mm x 53.98mm aspect ratio) -->
        <div class="w-full max-w-lg bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-950 text-white rounded-3xl p-6 sm:p-7 shadow-2xl border-2 border-blue-500/30 relative overflow-hidden print:border-black print:text-black print:bg-white print:max-w-none print:shadow-none">
            
            <!-- Background Decorative Elements -->
            <div class="absolute -right-16 -top-16 w-52 h-52 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-16 -bottom-16 w-52 h-52 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Header Kartu -->
            <div class="flex items-center justify-between border-b border-blue-800/60 pb-3 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-blue-300 tracking-widest uppercase">KARTU TANDA PELAJAR</div>
                        <div class="text-sm sm:text-base font-black tracking-tight text-white uppercase leading-none mt-0.5">
                            <?= htmlspecialchars($schoolFullName) ?>
                        </div>
                        <div class="text-[9px] text-slate-300 mt-0.5">
                            NPSN: <?= htmlspecialchars($schoolNpsn) ?> • Kalimantan Selatan
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2.5 py-1 bg-emerald-500/90 text-white rounded-full text-[10px] font-black tracking-wider uppercase">
                        <?= htmlspecialchars($student['paket']) ?>
                    </span>
                </div>
            </div>

            <!-- Body Kartu -->
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                
                <!-- Foto Siswa Placeholder / Avatar -->
                <div class="flex flex-col items-center flex-shrink-0">
                    <div class="w-24 h-32 rounded-2xl bg-gradient-to-b from-blue-100 to-slate-200 border-2 border-white/40 shadow-md flex items-center justify-center text-slate-400 overflow-hidden relative">
                        <i class="bi bi-person-fill text-6xl text-slate-500"></i>
                        <span class="absolute bottom-1.5 px-2 py-0.5 bg-slate-900/80 text-white text-[8px] font-bold rounded-full">
                            <?= $student['jk'] === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' ?>
                        </span>
                    </div>
                    <div class="text-[9px] font-mono font-bold text-blue-300 mt-2">
                        NO: <?= str_pad($student['id'], 5, '0', STR_PAD_LEFT) ?>
                    </div>
                </div>

                <!-- Info Biodata -->
                <div class="flex-1 space-y-1.5 text-xs">
                    <div>
                        <div class="text-[9px] font-semibold uppercase text-blue-300">Nama Lengkap</div>
                        <div class="font-extrabold text-sm sm:text-base text-white tracking-wide uppercase leading-tight">
                            <?= htmlspecialchars($student['nama']) ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <div class="text-[9px] font-semibold uppercase text-blue-300">NIK</div>
                            <div class="font-mono font-bold text-slate-200"><?= htmlspecialchars($student['nik']) ?></div>
                        </div>
                        <div>
                            <div class="text-[9px] font-semibold uppercase text-blue-300">NISN</div>
                            <div class="font-mono font-bold text-slate-200"><?= htmlspecialchars($student['nisn'] ?: '-') ?></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <div class="text-[9px] font-semibold uppercase text-blue-300">TTL</div>
                            <div class="font-medium text-slate-200"><?= htmlspecialchars($student['tempat_lahir'] ?: '-') ?>, <?= date('d/m/Y', strtotime($student['tanggal_lahir'])) ?></div>
                        </div>
                        <div>
                            <div class="text-[9px] font-semibold uppercase text-blue-300">Rombel</div>
                            <div class="font-bold text-emerald-400"><?= htmlspecialchars($student['rombel_saat_ini'] ?: $student['paket']) ?></div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="text-[9px] font-semibold uppercase text-blue-300">Alamat</div>
                        <div class="text-[11px] text-slate-300 leading-snug line-clamp-2">
                            <?= htmlspecialchars($student['alamat'] ?: '-') ?>, Kec. <?= htmlspecialchars($student['kecamatan'] ?: 'Mataraman') ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Kartu: QR Code & Pengesahan -->
            <div class="mt-5 pt-3 border-t border-blue-800/60 flex items-end justify-between">
                
                <!-- QR Code Simulasi -->
                <div class="flex items-center gap-2">
                    <div class="w-12 h-12 bg-white p-1 rounded-lg flex items-center justify-center">
                        <i class="bi bi-qr-code text-slate-900 text-3xl"></i>
                    </div>
                    <div class="text-[9px] text-slate-400 leading-tight">
                        Validitas Terdaftar<br>
                        <strong class="text-blue-300">Kemendikdasmen RI</strong>
                    </div>
                </div>

                <!-- Tanda Tangan Kepala PKBM -->
                <div class="text-center text-[9px]">
                    <div class="text-slate-400">Mataraman, <?= date('d M Y') ?></div>
                    <div class="font-bold text-slate-300">Kepala PKBM Mandiri,</div>
                    <div class="h-6 flex items-center justify-center italic text-blue-400 font-serif text-[11px] opacity-80">
                        ( Ditandatangani )
                    </div>
                    <div class="font-bold text-white border-b border-blue-400/40 pb-0.5">
                        <?= htmlspecialchars($kepalaPkbm) ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

