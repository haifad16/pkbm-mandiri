<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Data Semua Guru & Tutor PKBM';
$pdo = getDB();

// Handle Hapus Guru
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM teachers WHERE id = ?")->execute([$delId]);
    setFlash('success', 'Data guru/tutor berhasil dihapus.');
    redirect('modules/admin/guru.php');
}

// Handle Tambah Guru Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_guru'])) {
    $nama = sanitize($_POST['nama_lengkap'] ?? '');
    $gelar = sanitize($_POST['gelar'] ?? '');
    $nip = sanitize($_POST['nip_nuptk'] ?? '');
    $jk = sanitize($_POST['jenis_kelamin'] ?? 'L');
    $pendidikan = sanitize($_POST['pendidikan_terakhir'] ?? '');
    $mapel = sanitize($_POST['mapel_diampu'] ?? '');
    $jabatan = sanitize($_POST['jabatan'] ?? 'Tutor Pengajar');
    $noHp = sanitize($_POST['no_hp'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    // Upload Foto jika ada
    $fotoName = '';
    if (!empty($_FILES['foto_guru']['name'])) {
        $up = uploadFile($_FILES['foto_guru'], UPLOAD_FOTO_DIR, ['jpg', 'jpeg', 'png'], 3);
        if ($up['success']) {
            $fotoName = $up['filename'];
        }
    }

    if (empty($nama)) {
        setFlash('danger', 'Nama lengkap guru wajib diisi.');
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO teachers (
            nip_nuptk, nama_lengkap, gelar, jenis_kelamin, pendidikan_terakhir, mapel_diampu, jabatan, no_hp, email, foto, status_aktif
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmtIns->execute([
            $nip, $nama, $gelar, $jk, $pendidikan, $mapel, $jabatan, $noHp, $email, $fotoName
        ]);
        $teacherId = $pdo->lastInsertId();

        // Buat akun login jika dicentang
        if (!empty($_POST['buat_akun_login'])) {
            $username = sanitize($_POST['akun_username'] ?? '');
            $password = $_POST['akun_password'] ?? 'guru123';
            if (!empty($username)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name, email, phone) VALUES (?, ?, 'guru', ?, ?, ?)")
                    ->execute([$username, $hash, $nama . ($gelar ? ', ' . $gelar : ''), $email, $noHp]);
                $userId = $pdo->lastInsertId();
                $pdo->prepare("UPDATE teachers SET user_id = ? WHERE id = ?")->execute([$userId, $teacherId]);
            }
        }

        setFlash('success', "Data guru {$nama} berhasil ditambahkan!");
        redirect('modules/admin/guru.php');
    }
}

// Ambil semua guru
$stmt = $pdo->query("SELECT * FROM teachers ORDER BY id ASC");
$teachers = $stmt->fetchAll();

$driveFolder = getSetting('google_drive_folder', 'https://drive.google.com/drive/folders/1bgIM8NcMXxKQDx3ekLFrJ95cgh2yh4BM?usp=sharing');

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 mb-1">
                <i class="bi bi-person-workspace"></i> Dewan Pendidik PKBM Mandiri
            </div>
            <h1 class="text-xl font-extrabold text-slate-900">Data Semua Guru & Tutor</h1>
            <p class="text-xs text-slate-500">Pangkalan data profil pengajar, tutor mata pelajaran, dan akun akses sistem</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Link Google Drive Guru yang diminta user -->
            <a href="<?= htmlspecialchars($driveFolder) ?>" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-google text-amber-400"></i>
                <span>Buka Google Drive Guru</span>
            </a>
            <button onclick="document.getElementById('modal-tambah-guru').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Tambah Guru Baru</span>
            </button>
        </div>
    </div>

    <!-- Grid Card Guru -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($teachers as $t): ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between relative overflow-hidden group">
                <div class="space-y-4">
                    <!-- Foto & Avatar -->
                    <div class="flex items-center justify-between">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-100 to-indigo-100 border-2 border-blue-200 flex items-center justify-center text-slate-500 overflow-hidden shadow-inner">
                            <?php if (!empty($t['foto']) && file_exists(UPLOAD_FOTO_DIR . '/' . $t['foto'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/foto/<?= htmlspecialchars($t['foto']) ?>" alt="<?= htmlspecialchars($t['nama_lengkap']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="bi bi-person-fill text-3xl text-blue-600"></i>
                            <?php endif; ?>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Aktif
                        </span>
                    </div>

                    <!-- Info Guru -->
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 leading-snug group-hover:text-blue-600 transition">
                            <?= htmlspecialchars($t['nama_lengkap']) ?><?= !empty($t['gelar']) ? ', ' . htmlspecialchars($t['gelar']) : '' ?>
                        </h3>
                        <div class="text-xs font-semibold text-blue-700 mt-1">
                            <?= htmlspecialchars($t['jabatan']) ?>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Mapel: <strong><?= htmlspecialchars($t['mapel_diampu'] ?: '-') ?></strong>
                        </div>
                    </div>

                    <!-- Detail Spesifikasi -->
                    <div class="pt-3 border-t border-slate-100 text-xs text-slate-600 space-y-1">
                        <div><span class="text-slate-400">Pendidikan:</span> <?= htmlspecialchars($t['pendidikan_terakhir'] ?: '-') ?></div>
                        <div><span class="text-slate-400">NIP/NUPTK:</span> <?= htmlspecialchars($t['nip_nuptk'] ?: '-') ?></div>
                        <div><span class="text-slate-400">No. HP:</span> <strong class="text-slate-800"><?= htmlspecialchars($t['no_hp'] ?: '-') ?></strong></div>
                    </div>
                </div>

                <!-- Footer Card -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $t['no_hp']) ?>" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                        <i class="bi bi-whatsapp"></i> Chat WA
                    </a>
                    <a href="?delete=<?= $t['id'] ?>" onclick="return confirm('Hapus data guru <?= addslashes($t['nama_lengkap']) ?>?')" class="text-rose-500 hover:text-rose-700 font-bold transition">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MODAL POPUP TAMBAH GURU -->
    <div id="modal-tambah-guru" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-5 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-lg font-black text-slate-900">Tambah Guru / Tutor Baru</h3>
                <button type="button" onclick="document.getElementById('modal-tambah-guru').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="tambah_guru" value="1">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" required placeholder="Contoh: Siti Rahmah" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gelar Akademik</label>
                        <input type="text" name="gelar" placeholder="Contoh: S.Pd, M.Pd" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                            <option value="L">Laki-Laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NIP / NUPTK</label>
                        <input type="text" name="nip_nuptk" placeholder="Jika ada" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="D3">D3</option>
                            <option value="SMA / Sederajat">SMA / Sederajat</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mata Pelajaran yang Diampu</label>
                        <input type="text" name="mapel_diampu" placeholder="Contoh: Matematika, Bahasa Inggris" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jabatan di PKBM</label>
                        <input type="text" name="jabatan" value="Tutor / Guru Pengajar" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                        <input type="tel" name="no_hp" placeholder="Contoh: 08123456789" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                        <input type="email" name="email" placeholder="guru@gmail.com" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Foto Guru (Opsional)</label>
                        <input type="file" name="foto_guru" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white cursor-pointer">
                    </div>
                </div>

                <!-- Opsi Buatkan Akun Login -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-xs text-slate-800">
                        <input type="checkbox" name="buat_akun_login" value="1" id="chk-buat-akun" onchange="document.getElementById('akun-fields').classList.toggle('hidden', !this.checked)" class="w-4 h-4 text-blue-600 rounded">
                        <span>Buatkan Akun Login Guru (Akses CBT & Penilaian)</span>
                    </label>

                    <div id="akun-fields" class="hidden grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Username Login</label>
                            <input type="text" name="akun_username" placeholder="Contoh: tutor.rahmah" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Password</label>
                            <input type="text" name="akun_password" value="guru123" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-tambah-guru').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Simpan Data Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

