<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$pageTitle = 'Bank Soal Guru';
$pdo = getDB();

$mapelFilter = sanitize($_GET['mapel'] ?? '');
$jenisFilter = sanitize($_GET['jenis'] ?? '');
$keyword = sanitize($_GET['q'] ?? '');

$query = "SELECT q.*, e.judul as judul_ujian, e.mapel, e.paket 
    FROM exam_questions q 
    JOIN exams e ON q.exam_id = e.id 
    WHERE 1=1";
$params = [];

if (!empty($mapelFilter)) {
    $query .= " AND e.mapel = ?";
    $params[] = $mapelFilter;
}
if (!empty($jenisFilter)) {
    $query .= " AND q.jenis = ?";
    $params[] = $jenisFilter;
}
if (!empty($keyword)) {
    $query .= " AND q.pertanyaan LIKE ?";
    $params[] = "%$keyword%";
}

$query .= " ORDER BY e.mapel ASC, q.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$questions = $stmt->fetchAll();

// Daftar mapel unik
$mapelList = $pdo->query("SELECT DISTINCT mapel FROM exams WHERE mapel IS NOT NULL AND mapel != '' ORDER BY mapel ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Bank Soal & Arsip Instrumen</h1>
            <p class="text-xs text-slate-500">Pusat arsip butir soal Pilihan Ganda & Esai yang telah disusun oleh para guru</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/guru/ujian_buat.php" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i> Buat Ujian Baru
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari teks soal..."
                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
            </div>
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
                <select name="jenis" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white">
                    <option value="">-- Semua Jenis Soal --</option>
                    <option value="pg" <?= $jenisFilter === 'pg' ? 'selected' : '' ?>>Pilihan Ganda (PG)</option>
                    <option value="esai" <?= $jenisFilter === 'esai' ? 'selected' : '' ?>>Esai / Uraian</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                    <i class="bi bi-funnel-fill mr-1"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/modules/guru/bank_soal.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Daftar Soal -->
    <div class="space-y-4">
        <?php if (empty($questions)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm text-slate-400 text-xs">
                Tidak ada butir soal yang sesuai dengan kriteria pencarian.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($questions as $q): ?>
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-3 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($q['mapel']) ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase <?= $q['jenis'] === 'pg' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' ?>">
                                    <?= $q['jenis'] === 'pg' ? 'Pilihan Ganda' : 'Esai' ?>
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-medium">
                                Ujian: <span class="text-slate-800 font-bold"><?= htmlspecialchars($q['judul_ujian']) ?></span>
                            </div>
                            <div class="text-sm font-semibold text-slate-900 mt-2 leading-relaxed">
                                <?= nl2br(htmlspecialchars($q['pertanyaan'])) ?>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-700">
                                Kunci / Rubrik: <?= htmlspecialchars($q['kunci_jawaban']) ?>
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

