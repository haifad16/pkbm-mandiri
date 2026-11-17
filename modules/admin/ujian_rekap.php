<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pdo = getDB();
$selectedPaket = sanitize($_GET['paket'] ?? 'Paket B');
$selectedRombel = sanitize($_GET['rombel'] ?? '');

// Ambil semua mata pelajaran yang ada ujiannya
$stmtMapel = $pdo->query("SELECT DISTINCT mapel FROM exams WHERE status = 'aktif' ORDER BY mapel ASC");
$mapelList = $stmtMapel->fetchAll(PDO::FETCH_COLUMN);

// Filter siswa
$whereSiswa = "WHERE status = 'aktif'";
$paramsSiswa = [];
if (!empty($selectedPaket)) {
    $whereSiswa .= " AND paket = ?";
    $paramsSiswa[] = $selectedPaket;
}
if (!empty($selectedRombel)) {
    $whereSiswa .= " AND rombel_saat_ini LIKE ?";
    $paramsSiswa[] = "%$selectedRombel%";
}

$stmtSiswa = $pdo->prepare("SELECT id, nama, nik, nisn, rombel_saat_ini FROM students $whereSiswa ORDER BY nama ASC LIMIT 100");
$stmtSiswa->execute($paramsSiswa);
$students = $stmtSiswa->fetchAll();

// Ambil nilai ujian seluruh siswa
$allGrades = [];
$stmtGrades = $pdo->query("SELECT a.student_id, e.mapel, a.nilai_total 
    FROM exam_attempts a 
    JOIN exams e ON a.exam_id = e.id 
    WHERE a.status = 'selesai'");
while ($row = $stmtGrades->fetch()) {
    $allGrades[$row['student_id']][$row['mapel']] = (float)$row['nilai_total'];
}

// Export CSV / Excel
if (isset($_GET['export']) && in_array($_GET['export'], ['csv', 'excel'])) {
    $filename = "Leger_Rekap_Nilai_PKBM_Mandiri_{$selectedPaket}_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, ['LEGER REKAPITULASI NILAI SEMUA MAPEL - PKBM MANDIRI']);
    fputcsv($out, ['Jenjang', $selectedPaket]);
    fputcsv($out, ['Tahun Ajaran', getSetting('tahun_ajaran', APP_YEAR)]);
    fputcsv($out, []);

    $headerRow = ['No', 'NIK', 'Nama Murid', 'Rombel'];
    foreach ($mapelList as $m) {
        $headerRow[] = $m;
    }
    $headerRow[] = 'Rata-Rata';
    $headerRow[] = 'Predikat';
    $headerRow[] = 'Status';
    fputcsv($out, $headerRow);

    $no = 1;
    foreach ($students as $s) {
        $row = [$no++, "'" . $s['nik'], $s['nama'], $s['rombel_saat_ini']];
        $totalVal = 0;
        $countVal = 0;
        foreach ($mapelList as $m) {
            $val = $allGrades[$s['id']][$m] ?? null;
            if ($val !== null) {
                $row[] = number_format($val, 1);
                $totalVal += $val;
                $countVal++;
            } else {
                $row[] = '-';
            }
        }
        $avg = ($countVal > 0) ? round($totalVal / $countVal, 1) : 0;
        $predikat = ($avg >= 85) ? 'A' : (($avg >= 75) ? 'B' : (($avg >= 65) ? 'C' : 'D'));
        $status = ($avg >= 65) ? 'LULUS' : ($countVal > 0 ? 'REMEDIAL' : 'BELUM UJIAN');

        $row[] = $avg;
        $row[] = $predikat;
        $row[] = $status;
        fputcsv($out, $row);
    }
    fclose($out);
    exit();
}

$pageTitle = 'Rekap Nilai Semua Mapel (Leger)';
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$kepalaPkbm = getSetting('kepala_pkbm', 'H. Zailani, S.Pd');

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Action Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Rekapitulasi Nilai Semua Mapel (Leger Nilai)</h1>
            <p class="text-xs text-slate-500">Laporan komprehensif nilai akhir seluruh warga belajar dari berbagai mata pelajaran</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Jenjang -->
            <form method="GET" action="" class="flex items-center gap-2">
                <select name="paket" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                    <option value="Paket A" <?= $selectedPaket === 'Paket A' ? 'selected' : '' ?>>Paket A (Setara SD)</option>
                    <option value="Paket B" <?= $selectedPaket === 'Paket B' ? 'selected' : '' ?>>Paket B (Setara SMP)</option>
                    <option value="Paket C" <?= $selectedPaket === 'Paket C' ? 'selected' : '' ?>>Paket C (Setara SMA)</option>
                </select>
            </form>

            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-file-earmark-excel-fill text-base"></i>
                <span>Download Leger Excel</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-printer-fill text-base"></i>
                <span>Cetak Leger Nilai</span>
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
                LEGER NILAI HASIL BELAJAR PENDIDIKAN KESETARAAN
            </h3>
            <div class="text-xs text-slate-600 font-semibold mt-1">
                Program: <span class="text-blue-800 font-bold"><?= htmlspecialchars($selectedPaket) ?></span> 
                • Tahun Ajaran <?= htmlspecialchars(getSetting('tahun_ajaran', APP_YEAR)) ?>
            </div>
        </div>

        <!-- Matrix Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white text-left font-bold">
                        <th class="py-3 px-2 text-center w-8">No</th>
                        <th class="py-3 px-3">Nama Warga Belajar</th>
                        <th class="py-3 px-3">NIK</th>
                        <th class="py-3 px-3">Rombel</th>
                        <?php foreach ($mapelList as $m): ?>
                            <th class="py-3 px-2 text-center whitespace-nowrap"><?= htmlspecialchars($m) ?></th>
                        <?php endforeach; ?>
                        <th class="py-3 px-2 text-center bg-blue-950">Rata-Rata</th>
                        <th class="py-3 px-2 text-center">Predikat</th>
                        <th class="py-3 px-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 border-b border-slate-200">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="<?= 7 + count($mapelList) ?>" class="py-8 text-center text-slate-400">
                                Tidak ada data siswa pada jenjang ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($students as $s): ?>
                            <?php
                            $totalVal = 0;
                            $countVal = 0;
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-2 text-center font-bold text-slate-400"><?= $no++ ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900 whitespace-nowrap"><?= htmlspecialchars($s['nama']) ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-600 text-[11px]"><?= htmlspecialchars($s['nik']) ?></td>
                                <td class="py-2.5 px-3 text-slate-700 whitespace-nowrap text-[11px]"><?= htmlspecialchars($s['rombel_saat_ini']) ?></td>
                                
                                <?php foreach ($mapelList as $m): ?>
                                    <?php 
                                    $val = $allGrades[$s['id']][$m] ?? null;
                                    if ($val !== null) {
                                        $totalVal += $val;
                                        $countVal++;
                                    }
                                    ?>
                                    <td class="py-2.5 px-2 text-center font-mono font-semibold <?= $val !== null ? 'text-slate-800' : 'text-slate-300' ?>">
                                        <?= $val !== null ? number_format($val, 1) : '-' ?>
                                    </td>
                                <?php endforeach; ?>

                                <?php
                                $avg = ($countVal > 0) ? round($totalVal / $countVal, 1) : 0;
                                $predikat = ($avg >= 85) ? 'A' : (($avg >= 75) ? 'B' : (($avg >= 65) ? 'C' : 'D'));
                                $status = ($avg >= 65) ? 'LULUS' : ($countVal > 0 ? 'REMEDIAL' : '-');
                                $badgeColor = ($avg >= 65) ? 'text-emerald-700 bg-emerald-50' : 'text-slate-500 bg-slate-100';
                                ?>

                                <td class="py-2.5 px-2 text-center font-mono font-black text-blue-900 text-sm bg-blue-50/40">
                                    <?= $countVal > 0 ? number_format($avg, 1) : '-' ?>
                                </td>
                                <td class="py-2.5 px-2 text-center font-extrabold text-[11px]">
                                    <?= $countVal > 0 ? $predikat : '-' ?>
                                </td>
                                <td class="py-2.5 px-2 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black <?= $badgeColor ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Pengesahan -->
        <div class="mt-12 pt-6 border-t border-slate-200 flex justify-between items-end text-xs text-slate-600">
            <div>
                <div>Dokumen Sah Leger Nilai PKBM Mandiri</div>
                <div class="text-[10px] text-slate-400 mt-0.5"><?= date('d F Y, H:i') ?> WITA</div>
            </div>
            <div class="text-center w-52">
                <div>Mataraman, <?= date('d F Y') ?></div>
                <div class="font-bold text-slate-800 mt-1">Kepala PKBM Mandiri,</div>
                <div class="h-16"></div>
                <div class="font-bold border-b border-slate-900 pb-0.5"><?= htmlspecialchars($kepalaPkbm) ?></div>
                <div class="text-[10px] text-slate-500">NIP. -</div>
            </div>
        </div>

    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

