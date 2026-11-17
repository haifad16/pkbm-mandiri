<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pdo = getDB();

// Handle Hapus Satuan
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$delId]);
    setFlash('success', 'Data murid berhasil dihapus dari database.');
    redirect('modules/admin/siswa.php');
}

// Handle Bulk Action (Hapus Massal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selectedIds = $_POST['selected_students'] ?? [];
    $action = sanitize($_POST['action_type'] ?? '');

    if (!empty($selectedIds) && is_array($selectedIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        
        if ($action === 'delete') {
            $stmtDel = $pdo->prepare("DELETE FROM students WHERE id IN ($placeholders)");
            $stmtDel->execute($selectedIds);
            setFlash('success', count($selectedIds) . ' data murid berhasil dihapus secara massal.');
        } elseif ($action === 'set_alumni') {
            $stmtAlumni = $pdo->prepare("UPDATE students SET status = 'alumni' WHERE id IN ($placeholders)");
            $stmtAlumni->execute($selectedIds);
            setFlash('success', count($selectedIds) . ' murid berhasil diubah statusnya menjadi Alumni/Lulus.');
        } elseif ($action === 'set_aktif') {
            $stmtAktif = $pdo->prepare("UPDATE students SET status = 'aktif' WHERE id IN ($placeholders)");
            $stmtAktif->execute($selectedIds);
            setFlash('success', count($selectedIds) . ' murid berhasil diaktifkan kembali.');
        }
    }
    redirect('modules/admin/siswa.php');
}

// Filter & Pencarian
$search = sanitize($_GET['q'] ?? '');
$paketFilter = sanitize($_GET['paket'] ?? '');
$rombelFilter = sanitize($_GET['rombel'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? 'aktif');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (nama LIKE ? OR nik LIKE ? OR nisn LIKE ? OR kelurahan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($paketFilter)) {
    $whereClause .= " AND paket = ?";
    $params[] = $paketFilter;
}

if (!empty($rombelFilter)) {
    $whereClause .= " AND rombel_saat_ini LIKE ?";
    $params[] = "%$rombelFilter%";
}

if (!empty($statusFilter) && $statusFilter !== 'semua') {
    $whereClause .= " AND status = ?";
    $params[] = $statusFilter;
}

// Export CSV / Excel
if (isset($_GET['export']) && in_array($_GET['export'], ['csv', 'excel'])) {
    $exportStmt = $pdo->prepare("SELECT * FROM students $whereClause ORDER BY no_urut ASC, id ASC");
    $exportStmt->execute($params);
    $studentsAll = $exportStmt->fetchAll();

    $filename = "Database_Murid_PKBM_Mandiri_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, [
        'No', 'Nama Lengkap', 'JK', 'NISN', 'Tempat Lahir', 'Tanggal Lahir', 'NIK', 'Agama',
        'Alamat', 'RT', 'RW', 'Dusun', 'Kelurahan', 'Kecamatan', 'HP',
        'Nama Ayah', 'Tahun Lahir Ayah', 'Pendidikan Ayah', 'NIK Ayah',
        'Nama Ibu', 'Tahun Lahir Ibu', 'Pendidikan Ibu', 'NIK Ibu',
        'Rombel Saat Ini', 'Paket', 'Status'
    ]);

    $no = 1;
    foreach ($studentsAll as $s) {
        fputcsv($out, [
            $no++,
            $s['nama'],
            $s['jk'],
            "'" . $s['nisn'],
            $s['tempat_lahir'],
            $s['tanggal_lahir'],
            "'" . $s['nik'],
            $s['agama'],
            $s['alamat'],
            $s['rt'],
            $s['rw'],
            $s['dusun'],
            $s['kelurahan'],
            $s['kecamatan'],
            $s['hp'],
            $s['nama_ayah'],
            $s['tahun_lahir_ayah'],
            $s['pendidikan_ayah'],
            "'" . $s['nik_ayah'],
            $s['nama_ibu'],
            $s['tahun_lahir_ibu'],
            $s['pendidikan_ibu'],
            "'" . $s['nik_ibu'],
            $s['rombel_saat_ini'],
            $s['paket'],
            $s['status']
        ]);
    }
    fclose($out);
    exit();
}

// Hitung total data
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM students $whereClause");
$stmtCount->execute($params);
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Query data paginasi
$stmtStudents = $pdo->prepare("SELECT * FROM students $whereClause ORDER BY no_urut ASC, id ASC LIMIT $limit OFFSET $offset");
$stmtStudents->execute($params);
$students = $stmtStudents->fetchAll();

// Daftar Rombel Unik untuk Filter
$rombelList = $pdo->query("SELECT DISTINCT rombel_saat_ini FROM students WHERE rombel_saat_ini IS NOT NULL AND rombel_saat_ini != '' ORDER BY rombel_saat_ini ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Database 556 Murid PKBM';
require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-800">
                    556 Murid Terdata
                </span>
                <span class="text-xs text-slate-500 font-semibold">• Dapodik Nonformal</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Pangkalan Data Murid PKBM Mandiri</h1>
            <p class="text-xs text-slate-500">Pencarian, edit data induk, filter rombel, promosi kelas, dan unduh data ke Excel</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-file-earmark-excel-fill text-base"></i>
                <span>Export Excel / CSV</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/siswa_tambah.php" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-person-plus-fill text-base"></i>
                <span>Tambah Murid Manual</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div class="sm:col-span-2">
                <div class="relative">
                    <i class="bi bi-search absolute left-3.5 top-2.5 text-slate-400"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama murid, NIK (16 digit), atau NISN..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>

            <div>
                <select name="paket" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white">
                    <option value="">-- Semua Jenjang --</option>
                    <option value="Paket A" <?= $paketFilter === 'Paket A' ? 'selected' : '' ?>>Paket A (Setara SD)</option>
                    <option value="Paket B" <?= $paketFilter === 'Paket B' ? 'selected' : '' ?>>Paket B (Setara SMP)</option>
                    <option value="Paket C" <?= $paketFilter === 'Paket C' ? 'selected' : '' ?>>Paket C (Setara SMA)</option>
                </select>
            </div>

            <div>
                <select name="rombel" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white">
                    <option value="">-- Semua Rombel --</option>
                    <?php foreach ($rombelList as $rb): ?>
                        <option value="<?= htmlspecialchars($rb) ?>" <?= $rombelFilter === $rb ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rb) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/modules/admin/siswa.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Container with Bulk Action Form -->
    <form method="POST" action="" id="bulk-form" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Bulk Action Header Bar -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="select-all" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <span>Pilih Semua Halaman Ini</span>
                </label>
                <span class="text-slate-400">|</span>
                <span class="text-slate-600">Menampilkan <strong><?= count($students) ?></strong> dari <strong><?= $totalRows ?></strong> murid</span>
            </div>

            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-600">Aksi Massal:</span>
                <select name="action_type" id="action-type-select" class="px-3 py-1 bg-white border border-slate-300 rounded-lg text-xs font-semibold">
                    <option value="">-- Pilih Aksi Terpilih --</option>
                    <option value="delete">Hapus Murid Terpilih</option>
                    <option value="set_alumni">Pindahkan ke Status Alumni (Lulus)</option>
                    <option value="set_aktif">Kembalikan ke Status Aktif</option>
                </select>
                <button type="submit" name="bulk_action" value="1" onclick="return confirmBulkAction()"
                    class="px-3.5 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs transition">
                    Terapkan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-900 text-white text-left font-bold text-xs uppercase tracking-wider">
                        <th class="py-3 px-3 w-8 text-center"></th>
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Lengkap & JK</th>
                        <th class="py-3 px-4">NIK (Login) & NISN</th>
                        <th class="py-3 px-4">Rombel Saat Ini</th>
                        <th class="py-3 px-4">Tempat, Tgl Lahir</th>
                        <th class="py-3 px-4">Kontak HP</th>
                        <th class="py-3 px-4">Nama Orang Tua</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada data siswa yang cocok dengan kata kunci pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $index => $s): ?>
                            <?php $rowNum = $offset + $index + 1; ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox" name="selected_students[]" value="<?= $s['id'] ?>" class="student-checkbox w-4 h-4 text-blue-600 rounded border-slate-300">
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-slate-400"><?= $rowNum ?></td>
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-slate-900 text-sm"><?= htmlspecialchars($s['nama']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">
                                        <?= $s['jk'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?> • Agama <?= htmlspecialchars($s['agama'] ?: 'Islam') ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-mono font-bold text-blue-700"><?= htmlspecialchars($s['nik']) ?></div>
                                    <div class="text-[10px] font-mono text-slate-400">NISN: <?= htmlspecialchars($s['nisn'] ?: '-') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 whitespace-nowrap">
                                        <?= htmlspecialchars($s['rombel_saat_ini'] ?: $s['paket']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700 text-xs">
                                    <div><?= htmlspecialchars($s['tempat_lahir'] ?: '-') ?></div>
                                    <div class="text-[11px] text-slate-400"><?= $s['tanggal_lahir'] ? date('d/m/Y', strtotime($s['tanggal_lahir'])) : '-' ?></div>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700 text-xs">
                                    <?= htmlspecialchars($s['hp'] ?: '-') ?>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600">
                                    <div>A: <strong class="text-slate-800"><?= htmlspecialchars($s['nama_ayah'] ?: '-') ?></strong></div>
                                    <div>I: <strong class="text-slate-800"><?= htmlspecialchars($s['nama_ibu'] ?: '-') ?></strong></div>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= BASE_URL ?>/modules/admin/siswa_edit.php?id=<?= $s['id'] ?>" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition" title="Edit Biodata Murid">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data murid <?= addslashes($s['nama']) ?>?')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus Murid">
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

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="p-5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                <span class="text-slate-500 font-medium">
                    Halaman <strong><?= $page ?></strong> dari <strong><?= $totalPages ?></strong>
                </span>

                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold transition">
                            &larr; Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold transition <?= $p === $page ? 'bg-blue-600 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold transition">
                            Selanjutnya &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.student-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach((cb) => cb.checked = selectAll.checked);
        });
    }
});

function confirmBulkAction() {
    const checked = document.querySelectorAll('.student-checkbox:checked');
    const act = document.getElementById('action-type-select').value;
    if (checked.length === 0) {
        alert('Silakan pilih minimal satu murid terlebih dahulu!');
        return false;
    }
    if (!act) {
        alert('Silakan pilih jenis aksi yang ingin diterapkan!');
        return false;
    }
    return confirm(`Apakah Anda yakin ingin menerapkan aksi "${act}" pada ${checked.length} murid terpilih?`);
}
</script>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

