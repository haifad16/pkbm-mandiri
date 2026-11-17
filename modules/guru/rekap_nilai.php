<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$pdo = getDB();
$selectedExamId = (int)($_GET['exam_id'] ?? 0);
$selectedRombel = sanitize($_GET['rombel'] ?? '');

// Ambil daftar semua ujian
$exams = $pdo->query("SELECT id, judul, mapel, paket FROM exams ORDER BY created_at DESC")->fetchAll();
if ($selectedExamId === 0 && !empty($exams)) {
    $selectedExamId = $exams[0]['id'];
}

// Ambil info ujian terpilih
$stmtExam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmtExam->execute([$selectedExamId]);
$exam = $stmtExam->fetch();

// Ambil data nilai peserta
$query = "SELECT a.*, s.nama, s.nik, s.nisn, s.jk, s.rombel_saat_ini, s.hp, e.mapel, e.judul as judul_ujian
    FROM exam_attempts a 
    JOIN students s ON a.student_id = s.id 
    JOIN exams e ON a.exam_id = e.id 
    WHERE a.status = 'selesai'";

$params = [];
if ($selectedExamId > 0) {
    $query .= " AND a.exam_id = ?";
    $params[] = $selectedExamId;
}
if (!empty($selectedRombel)) {
    $query .= " AND s.rombel_saat_ini LIKE ?";
    $params[] = "%$selectedRombel%";
}

$query .= " ORDER BY s.nama ASC";
$stmtResults = $pdo->prepare($query);
$stmtResults->execute($params);
$results = $stmtResults->fetchAll();

// Handle Export CSV / Excel
if (isset($_GET['export']) && in_array($_GET['export'], ['csv', 'excel'])) {
    $cleanMapel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $exam['mapel'] ?? 'Ujian');
    $filename = "Rekap_Nilai_PKBM_Mandiri_{$cleanMapel}_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    // BOM for Excel UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header
    fputcsv($output, ['REKAPITULASI NILAI UJIAN PKBM MANDIRI']);
    fputcsv($output, ['Mata Pelajaran', $exam['mapel'] ?? 'Semua']);
    fputcsv($output, ['Judul Ujian', $exam['judul'] ?? 'Semua']);
    fputcsv($output, ['Tanggal Unduh', date('d-m-Y H:i') . ' WITA']);
    fputcsv($output, []); // Baris kosong

    fputcsv($output, [
        'No', 'NIK', 'NISN', 'Nama Siswa', 'JK', 'Rombel Saat Ini', 
        'Nilai PG', 'Nilai Esai', 'Nilai Akhir', 'Predikat', 'Status Kelulusan'
    ]);

    $no = 1;
    foreach ($results as $r) {
        $skor = (float)$r['nilai_total'];
        $predikat = ($skor >= 85) ? 'A' : (($skor >= 75) ? 'B' : (($skor >= 65) ? 'C' : 'D'));
        $status = ($skor >= 65) ? 'LULUS' : 'REMEDIAL';

        fputcsv($output, [
            $no++,
            "'" . $r['nik'],
            "'" . $r['nisn'],
            $r['nama'],
            $r['jk'],
            $r['rombel_saat_ini'],
            $r['nilai_pg'],
            $r['nilai_esai'],
            $r['nilai_total'],
            $predikat,
            $status
        ]);
    }
    fclose($output);
    exit();
}

$pageTitle = 'Rekap Nilai Siswa - ' . ($exam['judul'] ?? 'Ujian');
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$kepalaPkbm = getSetting('kepala_pkbm', 'H. Zailani, S.Pd');

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Action Bar (No Print) -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm no-print">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Rekap Nilai & Laporan Hasil Ujian</h1>
            <p class="text-xs text-slate-500">Cetak lembar nilai atau unduh dalam format CSV / Excel</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Ujian -->
            <form method="GET" action="" class="flex items-center gap-2">
                <select name="exam_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                    <?php foreach ($exams as $ex): ?>
                        <option value="<?= $ex['id'] ?>" <?= $selectedExamId == $ex['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ex['mapel']) ?> - <?= htmlspecialchars($ex['judul']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Export Buttons -->
            <a href="?exam_id=<?= $selectedExamId ?>&export=excel" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-file-earmark-excel-fill text-base"></i>
                <span>Download Excel / CSV</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-printer-fill text-base"></i>
                <span>Cetak Lembar Nilai</span>
            </button>
        </div>
    </div>

    <!-- Printable Paper -->
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

        <div class="text-center mb-6">
            <h3 class="text-lg font-extrabold text-slate-900 uppercase tracking-wider">
                LEGER REKAPITULASI NILAI UJIAN KESETARAAN
            </h3>
            <div class="text-xs text-slate-600 font-semibold mt-1">
                Mata Pelajaran: <span class="text-blue-800"><?= htmlspecialchars($exam['mapel'] ?? '-') ?></span> 
                • Jenjang: <span class="text-blue-800"><?= htmlspecialchars($exam['paket'] ?? '-') ?></span>
                • Tahun Ajaran <?= htmlspecialchars(getSetting('tahun_ajaran', APP_YEAR)) ?>
            </div>
        </div>

        <!-- Table Nilai Semua Murid -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white text-left font-bold text-xs">
                        <th class="py-3 px-3 text-center w-10">No</th>
                        <th class="py-3 px-3">Nama Warga Belajar</th>
                        <th class="py-3 px-3">NIK</th>
                        <th class="py-3 px-3">Rombel Saat Ini</th>
                        <th class="py-3 px-3 text-center">Skor PG</th>
                        <th class="py-3 px-3 text-center">Skor Esai</th>
                        <th class="py-3 px-3 text-center">Nilai Akhir</th>
                        <th class="py-3 px-3 text-center">Predikat</th>
                        <th class="py-3 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 border-b border-slate-200">
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                Belum ada data nilai peserta untuk ujian ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        $sumNilai = 0;
                        ?>
                        <?php foreach ($results as $r): ?>
                            <?php
                            $skor = (float)$r['nilai_total'];
                            $sumNilai += $skor;
                            if ($skor >= 85) {
                                $predikat = 'A';
                                $status = 'LULUS';
                                $color = 'text-emerald-700 bg-emerald-50';
                            } elseif ($skor >= 75) {
                                $predikat = 'B';
                                $status = 'LULUS';
                                $color = 'text-blue-700 bg-blue-50';
                            } elseif ($skor >= 65) {
                                $predikat = 'C';
                                $status = 'LULUS';
                                $color = 'text-amber-700 bg-amber-50';
                            } else {
                                $predikat = 'D';
                                $status = 'REMEDIAL';
                                $color = 'text-rose-700 bg-rose-50';
                            }
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 text-center text-slate-500 font-bold"><?= $no++ ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900"><?= htmlspecialchars($r['nama']) ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-600 text-xs"><?= htmlspecialchars($r['nik']) ?></td>
                                <td class="py-2.5 px-3 text-slate-700 text-xs"><?= htmlspecialchars($r['rombel_saat_ini']) ?></td>
                                <td class="py-2.5 px-3 text-center font-mono font-semibold"><?= number_format($r['nilai_pg'], 1) ?></td>
                                <td class="py-2.5 px-3 text-center font-mono font-semibold"><?= number_format($r['nilai_esai'], 1) ?></td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-blue-900 text-base"><?= number_format($r['nilai_total'], 1) ?></td>
                                <td class="py-2.5 px-3 text-center font-extrabold"><?= $predikat ?></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black <?= $color ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($results)): ?>
                    <tfoot>
                        <tr class="bg-slate-100 font-extrabold text-slate-900">
                            <td colspan="6" class="py-3 px-4 text-right uppercase text-xs">Rata-Rata Nilai Warga Belajar:</td>
                            <td class="py-3 px-3 text-center font-mono text-base text-blue-900">
                                <?= number_format($sumNilai / count($results), 1) ?>
                            </td>
                            <td colspan="2" class="py-3 px-3 text-center text-xs text-slate-500">
                                Total <?= count($results) ?> Murid
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- Tanda Tangan Guru & Kepala Sekolah -->
        <div class="mt-12 pt-6 border-t border-slate-200 flex justify-between items-end text-xs text-slate-600">
            <div>
                <div>Dicetak dari Sistem CBT PKBM Mandiri</div>
                <div class="text-[10px] text-slate-400 mt-0.5"><?= date('d F Y, H:i') ?> WITA</div>
            </div>
            <div class="text-center w-52">
                <div>Mataraman, <?= date('d F Y') ?></div>
                <div class="font-bold text-slate-800 mt-1">Tutor Pengampu / Penguji,</div>
                <div class="h-16"></div>
                <div class="font-bold border-b border-slate-900 pb-0.5">Dewan Tutor PKBM</div>
                <div class="text-[10px] text-slate-500">NIP/NUPTK. -</div>
            </div>
        </div>

    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

