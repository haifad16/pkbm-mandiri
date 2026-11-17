<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pdo = getDB();
$mapelFilter = sanitize($_GET['mapel'] ?? '');
$paketFilter = sanitize($_GET['paket'] ?? '');

$query = "SELECT q.*, e.judul as judul_ujian, e.mapel, e.paket, t.nama_lengkap as nama_guru
    FROM exam_questions q 
    JOIN exams e ON q.exam_id = e.id 
    LEFT JOIN teachers t ON e.guru_id = t.id 
    WHERE 1=1";
$params = [];

if (!empty($mapelFilter)) {
    $query .= " AND e.mapel = ?";
    $params[] = $mapelFilter;
}
if (!empty($paketFilter)) {
    $query .= " AND e.paket = ?";
    $params[] = $paketFilter;
}

$query .= " ORDER BY e.mapel ASC, q.nomor_urut ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$questions = $stmt->fetchAll();

// Handle Export ke Dokumen Word (.doc format yang kompatibel dengan Microsoft Word)
if (isset($_GET['export_word'])) {
    $cleanMapel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $mapelFilter ?: 'Semua_Mapel');
    $filename = "Arsip_Bank_Soal_PKBM_Mandiri_{$cleanMapel}_" . date('Ymd') . ".doc";

    header("Content-Type: application/vnd.ms-word; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
    echo "<head><meta charset='utf-8'><title>Bank Soal PKBM Mandiri</title>";
    echo "<style>body{font-family:Arial, sans-serif; font-size:11pt;} .header{text-align:center; border-bottom:2px solid black; padding-bottom:10px; margin-bottom:20px;} .q-box{margin-bottom:15px; page-break-inside:avoid;} .key{color:#15803d; font-weight:bold; font-size:10pt;}</style></head>";
    echo "<body>";
    echo "<div class='header'>";
    echo "<h2>" . strtoupper(getSetting('nama_lengkap_sekolah', APP_FULL_NAME)) . "</h2>";
    echo "<p>NPSN: " . getSetting('npsn', APP_NPSN) . " • " . getSetting('alamat', APP_ADDRESS) . "</p>";
    echo "<h3>ARSIP BANK SOAL & KUNCI JAWABAN UJIAN</h3>";
    echo "<p>Mata Pelajaran: " . ($mapelFilter ?: 'Semua Mapel') . " | Program: " . ($paketFilter ?: 'Semua Paket') . " | Tahun Ajaran: " . getSetting('tahun_ajaran', APP_YEAR) . "</p>";
    echo "</div>";

    $no = 1;
    foreach ($questions as $q) {
        echo "<div class='q-box'>";
        echo "<p><strong>" . $no++ . ". [" . strtoupper($q['jenis']) . "] (" . $q['mapel'] . " - " . $q['paket'] . ")</strong><br>";
        echo nl2br(htmlspecialchars($q['pertanyaan'])) . "</p>";

        if ($q['jenis'] === 'pg') {
            echo "<ol type='A' style='margin-top:0;'>";
            if (!empty($q['opsi_a'])) echo "<li>" . htmlspecialchars($q['opsi_a']) . "</li>";
            if (!empty($q['opsi_b'])) echo "<li>" . htmlspecialchars($q['opsi_b']) . "</li>";
            if (!empty($q['opsi_c'])) echo "<li>" . htmlspecialchars($q['opsi_c']) . "</li>";
            if (!empty($q['opsi_d'])) echo "<li>" . htmlspecialchars($q['opsi_d']) . "</li>";
            if (!empty($q['opsi_e'])) echo "<li>" . htmlspecialchars($q['opsi_e']) . "</li>";
            echo "</ol>";
            echo "<div class='key'>Kunci Jawaban: " . htmlspecialchars($q['kunci_jawaban']) . " (Bobot: " . $q['bobot'] . " poin)</div>";
        } else {
            echo "<div class='key'>Panduan Rubrik Esai: " . htmlspecialchars($q['kunci_jawaban']) . " (Bobot: " . $q['bobot'] . " poin)</div>";
        }
        echo "</div>";
    }

    echo "</body></html>";
    exit();
}

$pageTitle = 'Arsip Bank Soal & Kunci Jawaban';
$mapelList = $pdo->query("SELECT DISTINCT mapel FROM exams WHERE mapel IS NOT NULL ORDER BY mapel ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 mb-1">
                <i class="bi bi-archive-fill"></i> Bank Soal & Kunci Jawaban
            </div>
            <h1 class="text-xl font-extrabold text-slate-900">Arsip Seluruh Butir Soal & Mapel</h1>
            <p class="text-xs text-slate-500">Kumpulan soal ujian PG dan Esai dari seluruh guru lengkap dengan kunci jawaban, siap diekspor ke Word</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Export Word Button -->
            <a href="?<?= http_build_query(array_merge($_GET, ['export_word' => 1])) ?>" class="px-4 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-file-earmark-word-fill text-base"></i>
                <span>Download File Word (.doc)</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-printer-fill text-base"></i>
                <span>Cetak Arsip Soal</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm no-print">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <select name="mapel" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    <?php foreach ($mapelList as $m): ?>
                        <option value="<?= htmlspecialchars($m) ?>" <?= $mapelFilter === $m ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select name="paket" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white">
                    <option value="">-- Semua Jenjang --</option>
                    <option value="Paket A" <?= $paketFilter === 'Paket A' ? 'selected' : '' ?>>Paket A (Setara SD)</option>
                    <option value="Paket B" <?= $paketFilter === 'Paket B' ? 'selected' : '' ?>>Paket B (Setara SMP)</option>
                    <option value="Paket C" <?= $paketFilter === 'Paket C' ? 'selected' : '' ?>>Paket C (Setara SMA)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                    <i class="bi bi-funnel-fill mr-1"></i> Terapkan Filter
                </button>
                <a href="<?= BASE_URL ?>/modules/admin/bank_soal_arsip.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Question Cards List -->
    <div class="space-y-4">
        <?php if (empty($questions)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm text-slate-400 text-xs">
                Belum ada butir soal pada kategori ini.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php $no = 1; ?>
                <?php foreach ($questions as $q): ?>
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-3 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($q['mapel']) ?> (<?= htmlspecialchars($q['paket']) ?>)
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase <?= $q['jenis'] === 'pg' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' ?>">
                                    <?= $q['jenis'] === 'pg' ? 'Pilihan Ganda' : 'Esai' ?>
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-medium">
                                Ujian: <strong class="text-slate-800"><?= htmlspecialchars($q['judul_ujian']) ?></strong>
                            </div>
                            <div class="text-sm font-semibold text-slate-900 mt-2 leading-relaxed">
                                <span class="text-blue-600 font-bold"><?= $no++ ?>.</span> <?= nl2br(htmlspecialchars($q['pertanyaan'])) ?>
                            </div>

                            <?php if ($q['jenis'] === 'pg'): ?>
                                <div class="grid grid-cols-2 gap-1.5 text-xs text-slate-600 pt-2">
                                    <div class="<?= $q['kunci_jawaban'] === 'A' ? 'font-bold text-emerald-700' : '' ?>">A. <?= htmlspecialchars($q['opsi_a']) ?></div>
                                    <div class="<?= $q['kunci_jawaban'] === 'B' ? 'font-bold text-emerald-700' : '' ?>">B. <?= htmlspecialchars($q['opsi_b']) ?></div>
                                    <div class="<?= $q['kunci_jawaban'] === 'C' ? 'font-bold text-emerald-700' : '' ?>">C. <?= htmlspecialchars($q['opsi_c']) ?></div>
                                    <div class="<?= $q['kunci_jawaban'] === 'D' ? 'font-bold text-emerald-700' : '' ?>">D. <?= htmlspecialchars($q['opsi_d']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-700 flex items-center gap-1">
                                <i class="bi bi-key-fill"></i> Kunci: <?= htmlspecialchars($q['kunci_jawaban']) ?>
                            </span>
                            <span class="text-slate-400 font-semibold">
                                Bobot: <?= $q['bobot'] ?> Poin
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

