<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$pageTitle = 'Kelola Ujian Online';
$pdo = getDB();

// Handle aksi delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM exams WHERE id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM exam_questions WHERE exam_id = ?")->execute([$delId]);
    setFlash('success', 'Ujian beserta butir soal terkait berhasil dihapus.');
    redirect('modules/guru/ujian_list.php');
}

// Handle toggle status
if (isset($_GET['toggle_status'])) {
    $toggleId = (int)$_GET['toggle_status'];
    $stmtCur = $pdo->prepare("SELECT status FROM exams WHERE id = ?");
    $stmtCur->execute([$toggleId]);
    $curStatus = $stmtCur->fetchColumn();
    $newStatus = ($curStatus === 'aktif') ? 'draft' : 'aktif';
    $pdo->prepare("UPDATE exams SET status = ? WHERE id = ?")->execute([$newStatus, $toggleId]);
    setFlash('success', "Status ujian berhasil diubah menjadi: {$newStatus}");
    redirect('modules/guru/ujian_list.php');
}

// Ambil semua ujian
$stmt = $pdo->query("SELECT e.*, 
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id) as total_soal,
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id AND q.jenis = 'pg') as total_pg,
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id AND q.jenis = 'esai') as total_esai,
    (SELECT COUNT(*) FROM exam_attempts a WHERE a.exam_id = e.id AND a.status = 'selesai') as total_peserta
    FROM exams e 
    ORDER BY e.created_at DESC");
$exams = $stmt->fetchAll();

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Kelola Ujian Online (CBT)</h1>
            <p class="text-xs text-slate-500">Daftar bank ujian dan instrumen asesmen kesetaraan Paket A, B, dan C</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/guru/ujian_buat.php" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Buat Ujian Baru</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-900 text-white text-left font-bold text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Mata Pelajaran & Ujian</th>
                        <th class="py-3 px-4">Program / Rombel</th>
                        <th class="py-3 px-4 text-center">Butir Soal</th>
                        <th class="py-3 px-4 text-center">Peserta</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Belum ada data ujian. Silakan buat ujian baru terlebih dahulu.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($exams as $ex): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-500"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900"><?= htmlspecialchars($ex['judul']) ?></div>
                                    <div class="text-[11px] text-blue-600 font-semibold mt-0.5">
                                        Mapel: <?= htmlspecialchars($ex['mapel']) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        <?= htmlspecialchars($ex['paket']) ?>
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1"><?= htmlspecialchars($ex['tingkat_kelas']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-black text-slate-800 text-sm"><?= $ex['total_soal'] ?></span>
                                    <span class="text-[10px] text-slate-400 block">(<?= $ex['total_pg'] ?> PG, <?= $ex['total_esai'] ?> Esai)</span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-emerald-600">
                                    <?= $ex['total_peserta'] ?> Murid
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="?toggle_status=<?= $ex['id'] ?>" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase transition <?= $ex['status'] === 'aktif' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>" title="Klik untuk mengubah status">
                                        <?= $ex['status'] ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= BASE_URL ?>/modules/guru/soal_manage.php?id=<?= $ex['id'] ?>" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition flex items-center gap-1 shadow-sm">
                                            <i class="bi bi-list-task"></i> Soal
                                        </a>
                                        <a href="<?= BASE_URL ?>/modules/guru/koreksi_esai.php?exam_id=<?= $ex['id'] ?>" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center gap-1 shadow-sm" title="Koreksi Soal Esai">
                                            <i class="bi bi-pencil-square"></i> Koreksi
                                        </a>
                                        <a href="<?= BASE_URL ?>/modules/guru/rekap_nilai.php?exam_id=<?= $ex['id'] ?>" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center gap-1 shadow-sm" title="Rekap Nilai Siswa">
                                            <i class="bi bi-printer"></i> Rekap
                                        </a>
                                        <a href="?delete=<?= $ex['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus ujian ini beserta seluruh butir soalnya?')" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Ujian">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

