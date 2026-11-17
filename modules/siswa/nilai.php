<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('siswa');

$pageTitle = 'Hasil Ujian & Rapor Nilai Murid';
$studentId = $_SESSION['student_id'] ?? 0;
$pdo = getDB();

$stmtStudent = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmtStudent->execute([$studentId]);
$student = $stmtStudent->fetch();

if (!$student) {
    redirect('modules/siswa/index.php');
}

// Ambil seluruh attempt ujian murid yang sudah dipublikasikan
$stmtAttempts = $pdo->prepare("SELECT a.*, e.judul, e.mapel, e.paket, e.tingkat_kelas 
    FROM exam_attempts a 
    JOIN exams e ON a.exam_id = e.id 
    WHERE a.student_id = ? AND e.nilai_dipublikasi = 1
    ORDER BY a.created_at DESC");
$stmtAttempts->execute([$studentId]);
$attempts = $stmtAttempts->fetchAll();

$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$kepalaPkbm = getSetting('kepala_pkbm', 'H. Zailani, S.Pd');

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Action Bar (No Print) -->
    <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Rapor & Hasil Ujian Kesetaraan</h1>
            <p class="text-xs text-slate-500">Daftar rekapitulasi nilai ujian resmi yang telah divalidasi oleh dewan tutor</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <i class="bi bi-printer-fill"></i> Cetak Lembar Nilai
            </button>
            <a href="<?= BASE_URL ?>/modules/siswa/index.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Printable Report Card Paper -->
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-12">
        
        <!-- Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6 text-center">
            <div class="flex items-center justify-center gap-4 mb-2">
                <div class="w-14 h-14 rounded-2xl bg-blue-700 text-white flex items-center justify-center text-3xl font-extrabold shadow-md">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="text-left">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none uppercase">
                        <?= htmlspecialchars($schoolFullName) ?>
                    </h2>
                    <div class="text-xs text-slate-600 font-semibold mt-1">
                        NPSN: <?= htmlspecialchars($schoolNpsn) ?> • Dinas Pendidikan Kabupaten Banjar
                    </div>
                    <div class="text-[11px] text-slate-500">
                        <?= htmlspecialchars(getSetting('alamat', APP_ADDRESS)) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-8">
            <h3 class="text-lg font-extrabold text-slate-900 uppercase tracking-wider">
                LEMBAR REKAPITULASI HASIL BELAJAR & NILAI UJIAN
            </h3>
            <div class="text-xs text-slate-500 mt-1">
                Tahun Ajaran <?= htmlspecialchars(getSetting('tahun_ajaran', APP_YEAR)) ?>
            </div>
        </div>

        <!-- Student Identity Header -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-xs sm:text-sm bg-slate-50 p-4 rounded-2xl border border-slate-200 mb-8">
            <div>
                <span class="text-slate-500">Nama Murid :</span>
                <strong class="text-slate-900 uppercase ml-1"><?= htmlspecialchars($student['nama']) ?></strong>
            </div>
            <div>
                <span class="text-slate-500">Program Kesetaraan :</span>
                <strong class="text-blue-700 ml-1"><?= htmlspecialchars($student['paket']) ?></strong>
            </div>
            <div>
                <span class="text-slate-500">NIK Murid :</span>
                <strong class="font-mono text-slate-900 ml-1"><?= htmlspecialchars($student['nik']) ?></strong>
            </div>
            <div>
                <span class="text-slate-500">Rombongan Belajar :</span>
                <strong class="text-slate-900 ml-1"><?= htmlspecialchars($student['rombel_saat_ini'] ?: $student['paket']) ?></strong>
            </div>
            <div>
                <span class="text-slate-500">NISN :</span>
                <span class="font-mono text-slate-800 ml-1"><?= htmlspecialchars($student['nisn'] ?: '-') ?></span>
            </div>
            <div>
                <span class="text-slate-500">Status Warga Belajar :</span>
                <span class="text-emerald-700 font-bold ml-1 uppercase"><?= htmlspecialchars($student['status']) ?></span>
            </div>
        </div>

        <!-- Tabel Nilai -->
        <?php if (empty($attempts)): ?>
            <div class="p-8 text-center text-slate-400 text-sm">
                <i class="bi bi-journal-x text-4xl block mb-2 text-slate-300"></i>
                Belum ada data nilai ujian yang tercatat untuk akun Anda.
                <div class="mt-4 no-print">
                    <a href="<?= BASE_URL ?>/modules/siswa/ujian.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700 transition">
                        <i class="bi bi-pencil-fill"></i> Ikuti Ujian Online Sekarang
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-left">
                            <th class="py-3 px-3 rounded-tl-xl text-center w-12">No</th>
                            <th class="py-3 px-3">Mata Pelajaran</th>
                            <th class="py-3 px-3">Judul Ujian</th>
                            <th class="py-3 px-3 text-center">Tanggal</th>
                            <th class="py-3 px-3 text-center">Skor PG</th>
                            <th class="py-3 px-3 text-center">Skor Esai</th>
                            <th class="py-3 px-3 text-center">Nilai Akhir</th>
                            <th class="py-3 px-3 text-center">Predikat</th>
                            <th class="py-3 px-3 rounded-tr-xl text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 border-b border-slate-200">
                        <?php 
                        $no = 1;
                        $totalNilaiSum = 0;
                        ?>
                        <?php foreach ($attempts as $at): ?>
                            <?php
                            $totalNilaiSum += (float)$at['nilai_total'];
                            $skor = (float)$at['nilai_total'];
                            if ($skor >= 85) {
                                $predikat = 'A (Sangat Baik)';
                                $status = 'LULUS';
                                $badgeColor = 'text-emerald-700 bg-emerald-50';
                            } elseif ($skor >= 75) {
                                $predikat = 'B (Baik)';
                                $status = 'LULUS';
                                $badgeColor = 'text-blue-700 bg-blue-50';
                            } elseif ($skor >= 65) {
                                $predikat = 'C (Cukup)';
                                $status = 'LULUS';
                                $badgeColor = 'text-amber-700 bg-amber-50';
                            } else {
                                $predikat = 'D (Kurang)';
                                $status = 'REMEDIAL';
                                $badgeColor = 'text-rose-700 bg-rose-50';
                            }
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 text-center font-bold text-slate-500"><?= $no++ ?></td>
                                <td class="py-3 px-3 font-extrabold text-slate-900"><?= htmlspecialchars($at['mapel']) ?></td>
                                <td class="py-3 px-3 text-slate-600 text-xs"><?= htmlspecialchars($at['judul']) ?></td>
                                <td class="py-3 px-3 text-center text-slate-500 text-xs whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($at['created_at'])) ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-semibold text-slate-700">
                                    <?= number_format($at['nilai_pg'], 1) ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-semibold text-slate-700">
                                    <?= $at['status_koreksi'] === 'sudah' ? number_format($at['nilai_esai'], 1) : '<span class="text-amber-600 text-[10px] font-sans font-bold">Menunggu</span>' ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-black text-base text-blue-900">
                                    <?= number_format($at['nilai_total'], 1) ?>
                                </td>
                                <td class="py-3 px-3 text-center font-semibold text-xs"><?= $predikat ?></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold <?= $badgeColor ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-extrabold text-slate-900">
                            <td colspan="6" class="py-3 px-4 text-right uppercase text-xs">Rata-Rata Nilai Akhir:</td>
                            <td class="py-3 px-3 text-center font-mono text-base text-blue-800">
                                <?= count($attempts) > 0 ? number_format($totalNilaiSum / count($attempts), 1) : '0' ?>
                            </td>
                            <td colspan="2" class="py-3 px-3 text-center text-xs text-slate-500">
                                Skala 0 - 100
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- Footer Pengesahan -->
        <div class="mt-12 pt-6 border-t border-slate-200 flex justify-between items-end text-xs text-slate-600">
            <div>
                <div>Dokumen Sah Rapor Digital PKBM Mandiri</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Dicetak pada: <?= date('d F Y, H:i') ?> WITA</div>
            </div>
            <div class="text-center w-52">
                <div>Mataraman, <?= date('d F Y') ?></div>
                <div class="font-bold text-slate-800 mt-1">Kepala Satuan Pendidikan,</div>
                <div class="h-16"></div>
                <div class="font-bold border-b border-slate-900 pb-0.5"><?= htmlspecialchars($kepalaPkbm) ?></div>
                <div class="text-[10px] text-slate-500">PKBM Mandiri Mataraman</div>
            </div>
        </div>

    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

