<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Kelola Galeri Foto Kegiatan';
$pdo = getDB();

// Handle Hapus Foto
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmtG = $pdo->prepare("SELECT file_gambar FROM gallery WHERE id = ?");
    $stmtG->execute([$delId]);
    $filename = $stmtG->fetchColumn();
    if ($filename && file_exists(UPLOAD_FOTO_DIR . '/' . $filename)) {
        @unlink(UPLOAD_FOTO_DIR . '/' . $filename);
    }
    $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$delId]);
    setFlash('success', 'Foto kegiatan berhasil dihapus dari galeri.');
    redirect('modules/admin/cms_galeri.php');
}

// Handle Upload Foto Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_foto'])) {
    $judul = sanitize($_POST['judul'] ?? '');
    $kategori = sanitize($_POST['kategori'] ?? 'Kegiatan Belajar');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $tglKegiatan = sanitize($_POST['tanggal_kegiatan'] ?? date('Y-m-d'));

    if (empty($judul) || empty($_FILES['file_gambar']['name'])) {
        setFlash('danger', 'Judul kegiatan dan file gambar wajib diisi.');
    } else {
        $up = uploadFile($_FILES['file_gambar'], UPLOAD_FOTO_DIR, ['jpg', 'jpeg', 'png', 'webp'], 5);
        if ($up['success']) {
            $stmtIns = $pdo->prepare("INSERT INTO gallery (judul, kategori, deskripsi, file_gambar, tanggal_kegiatan) VALUES (?, ?, ?, ?, ?)");
            $stmtIns->execute([$judul, $kategori, $deskripsi, $up['filename'], $tglKegiatan]);
            setFlash('success', 'Foto kegiatan baru berhasil ditambahkan ke galeri!');
            redirect('modules/admin/cms_galeri.php');
        } else {
            setFlash('danger', $up['message']);
        }
    }
}

// Ambil seluruh foto
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY id DESC");
$galleries = $stmt->fetchAll();

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 mb-1">
                <i class="bi bi-images"></i> Galeri & Dokumentasi
            </div>
            <h1 class="text-xl font-extrabold text-slate-900">Kelola Galeri Foto Kegiatan</h1>
            <p class="text-xs text-slate-500">Unggah foto dokumentasi kegiatan belajar, vokasi, dan ujian yang tampil di website publik</p>
        </div>

        <div>
            <button onclick="document.getElementById('modal-upload-foto').classList.remove('hidden')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="bi bi-cloud-arrow-up-fill text-base"></i>
                <span>Unggah Foto Baru</span>
            </button>
        </div>
    </div>

    <!-- Gallery Grid -->
    <?php if (empty($galleries)): ?>
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm text-slate-400 text-xs">
            Belum ada foto dalam galeri. Klik tombol "Unggah Foto Baru" di atas untuk menambahkan dokumentasi kegiatan.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($galleries as $g): ?>
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between group">
                    <div>
                        <!-- Image Container with Aspect Ratio -->
                        <div class="h-48 bg-slate-100 overflow-hidden relative">
                            <?php if (file_exists(UPLOAD_FOTO_DIR . '/' . $g['file_gambar'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/foto/<?= htmlspecialchars($g['file_gambar']) ?>" alt="<?= htmlspecialchars($g['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <?php else: ?>
                                <!-- Fallback Placeholder Visual -->
                                <div class="w-full h-full bg-gradient-to-tr from-blue-700 to-indigo-600 flex flex-col items-center justify-center text-white p-4 text-center">
                                    <i class="bi bi-image text-4xl mb-1 text-blue-200"></i>
                                    <span class="text-xs font-bold leading-tight"><?= htmlspecialchars($g['judul']) ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900/80 text-white backdrop-blur-md">
                                <?= htmlspecialchars($g['kategori']) ?>
                            </span>
                        </div>

                        <!-- Card Info -->
                        <div class="p-5 space-y-2">
                            <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-600 transition">
                                <?= htmlspecialchars($g['judul']) ?>
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($g['deskripsi'] ?: 'Dokumentasi kegiatan resmi PKBM Mandiri.') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-400">
                        <span><?= formatTanggalIndo($g['tanggal_kegiatan']) ?></span>
                        <a href="?delete=<?= $g['id'] ?>" onclick="return confirm('Hapus foto ini dari galeri?')" class="text-rose-500 hover:text-rose-700 font-bold transition flex items-center gap-1">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- MODAL POPUP UPLOAD FOTO -->
    <div id="modal-upload-foto" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-5 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-lg font-black text-slate-900">Unggah Foto Kegiatan Baru</h3>
                <button type="button" onclick="document.getElementById('modal-upload-foto').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="tambah_foto" value="1">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Kegiatan *</label>
                    <input type="text" name="judul" required placeholder="Contoh: Ujian Kesetaraan Paket B & C" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
                        <select name="kategori" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="Kegiatan Belajar">Kegiatan Belajar</option>
                            <option value="Ujian">Ujian Kesetaraan</option>
                            <option value="Keterampilan">Keterampilan / Vokasi</option>
                            <option value="Ekstrakurikuler">Ekstrakurikuler</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Kegiatan</label>
                        <input type="date" name="tanggal_kegiatan" value="<?= date('Y-m-d') ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" rows="2" placeholder="Keterangan singkat tentang foto ini..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih File Gambar (Maks. 5 MB) *</label>
                    <input type="file" name="file_gambar" required accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white cursor-pointer">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-upload-foto').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Unggah Foto
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

