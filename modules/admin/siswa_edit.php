<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$studentId = (int)($_GET['id'] ?? 0);
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('danger', 'Data murid tidak ditemukan.');
    redirect('modules/admin/siswa.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $jk = sanitize($_POST['jk'] ?? '');
    $nisn = sanitize($_POST['nisn'] ?? '');
    $nik = sanitize($_POST['nik'] ?? '');
    $tempatLahir = sanitize($_POST['tempat_lahir'] ?? '');
    $tanggalLahir = sanitize($_POST['tanggal_lahir'] ?? '');
    $agama = sanitize($_POST['agama'] ?? 'Islam');
    $alamat = sanitize($_POST['alamat'] ?? '');
    $rt = sanitize($_POST['rt'] ?? '');
    $rw = sanitize($_POST['rw'] ?? '');
    $dusun = sanitize($_POST['dusun'] ?? '');
    $kelurahan = sanitize($_POST['kelurahan'] ?? '');
    $kecamatan = sanitize($_POST['kecamatan'] ?? 'Kec. Mataraman');
    $hp = sanitize($_POST['hp'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $asalSekolah = sanitize($_POST['asal_sekolah'] ?? '');
    $noKk = sanitize($_POST['no_kk'] ?? '');

    $namaAyah = sanitize($_POST['nama_ayah'] ?? '');
    $tahunLahirAyah = sanitize($_POST['tahun_lahir_ayah'] ?? '');
    $pendidikanAyah = sanitize($_POST['pendidikan_ayah'] ?? '');
    $nikAyah = sanitize($_POST['nik_ayah'] ?? '');

    $namaIbu = sanitize($_POST['nama_ibu'] ?? '');
    $tahunLahirIbu = sanitize($_POST['tahun_lahir_ibu'] ?? '');
    $pendidikanIbu = sanitize($_POST['pendidikan_ibu'] ?? '');
    $nikIbu = sanitize($_POST['nik_ibu'] ?? '');

    $rombel = sanitize($_POST['rombel_saat_ini'] ?? '');
    $paket = sanitize($_POST['paket'] ?? 'Paket B');
    $status = sanitize($_POST['status'] ?? 'aktif');

    if (empty($nama) || empty($nik)) {
        setFlash('danger', 'Nama Lengkap dan NIK wajib diisi.');
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE students SET 
            nama = ?, jk = ?, nisn = ?, nik = ?, tempat_lahir = ?, tanggal_lahir = ?, agama = ?,
            alamat = ?, rt = ?, rw = ?, dusun = ?, kelurahan = ?, kecamatan = ?, hp = ?, email = ?, asal_sekolah = ?, no_kk = ?,
            nama_ayah = ?, tahun_lahir_ayah = ?, pendidikan_ayah = ?, nik_ayah = ?,
            nama_ibu = ?, tahun_lahir_ibu = ?, pendidikan_ibu = ?, nik_ibu = ?,
            rombel_saat_ini = ?, paket = ?, status = ?, updated_at = datetime('now')
            WHERE id = ?");

        $stmtUpdate->execute([
            $nama, $jk, $nisn, $nik, $tempatLahir, $tanggalLahir ?: null, $agama,
            $alamat, $rt, $rw, $dusun, $kelurahan, $kecamatan, $hp, $email, $asalSekolah, $noKk,
            $namaAyah, $tahunLahirAyah, $pendidikanAyah, $nikAyah,
            $namaIbu, $tahunLahirIbu, $pendidikanIbu, $nikIbu,
            $rombel, $paket, $status, $studentId
        ]);

        setFlash('success', "Data murid {$nama} berhasil diperbarui.");
        redirect('modules/admin/siswa.php');
    }
}

$pageTitle = 'Edit Data Murid - ' . $student['nama'];
require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Edit Biodata Murid</h1>
            <p class="text-xs text-slate-500">Perbarui informasi data pokok siswa di pangkalan data PKBM Mandiri</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/admin/siswa.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
            Batal & Kembali
        </a>
    </div>

    <form method="POST" action="" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        
        <!-- Identitas Utama -->
        <div>
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                I. Data Pokok Siswa
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($student['nama']) ?>" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jenis Kelamin *</label>
                    <select name="jk" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="L" <?= $student['jk'] === 'L' ? 'selected' : '' ?>>Laki-Laki (L)</option>
                        <option value="P" <?= $student['jk'] === 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">NIK (16 Digit) *</label>
                    <input type="text" name="nik" value="<?= htmlspecialchars($student['nik']) ?>" required maxlength="16" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">NISN</label>
                    <input type="text" name="nisn" value="<?= htmlspecialchars($student['nisn']) ?>" maxlength="10" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Agama</label>
                    <input type="text" name="agama" value="<?= htmlspecialchars($student['agama'] ?: 'Islam') ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= htmlspecialchars($student['tempat_lahir']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($student['tanggal_lahir']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nomor HP / WhatsApp</label>
                    <input type="tel" name="hp" value="<?= htmlspecialchars($student['hp']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nomor Kartu Keluarga (KK)</label>
                    <input type="text" name="no_kk" value="<?= htmlspecialchars($student['no_kk']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <!-- Rombel & Status Akademik -->
        <div class="pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                II. Rombel & Status Akademik
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Program Kesetaraan *</label>
                    <select name="paket" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                        <option value="Paket A" <?= $student['paket'] === 'Paket A' ? 'selected' : '' ?>>Paket A (Setara SD)</option>
                        <option value="Paket B" <?= $student['paket'] === 'Paket B' ? 'selected' : '' ?>>Paket B (Setara SMP)</option>
                        <option value="Paket C" <?= $student['paket'] === 'Paket C' ? 'selected' : '' ?>>Paket C (Setara SMA)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Rombel Saat Ini *</label>
                    <input type="text" name="rombel_saat_ini" value="<?= htmlspecialchars($student['rombel_saat_ini']) ?>" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Keaktifan *</label>
                    <select name="status" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                        <option value="aktif" <?= $student['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="alumni" <?= $student['status'] === 'alumni' ? 'selected' : '' ?>>Alumni (Lulus)</option>
                        <option value="mutasi" <?= $student['status'] === 'mutasi' ? 'selected' : '' ?>>Mutasi / Pindah</option>
                        <option value="tidak_aktif" <?= $student['status'] === 'tidak_aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Alamat -->
        <div class="pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                III. Alamat Tempat Tinggal
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alamat Jalan</label>
                    <input type="text" name="alamat" value="<?= htmlspecialchars($student['alamat']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kelurahan / Desa</label>
                    <input type="text" name="kelurahan" value="<?= htmlspecialchars($student['kelurahan']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" value="<?= htmlspecialchars($student['kecamatan']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <!-- Orang Tua -->
        <div class="pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                IV. Data Orang Tua
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Ayah -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <h4 class="font-bold text-xs text-blue-900">DATA AYAH</h4>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="<?= htmlspecialchars($student['nama_ayah']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Tahun Lahir</label>
                        <input type="text" name="tahun_lahir_ayah" value="<?= htmlspecialchars($student['tahun_lahir_ayah']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_ayah" value="<?= htmlspecialchars($student['pendidikan_ayah']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">NIK Ayah</label>
                        <input type="text" name="nik_ayah" value="<?= htmlspecialchars($student['nik_ayah']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-mono">
                    </div>
                </div>

                <!-- Ibu -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <h4 class="font-bold text-xs text-rose-900">DATA IBU</h4>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="<?= htmlspecialchars($student['nama_ibu']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Tahun Lahir</label>
                        <input type="text" name="tahun_lahir_ibu" value="<?= htmlspecialchars($student['tahun_lahir_ibu']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_ibu" value="<?= htmlspecialchars($student['pendidikan_ibu']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">NIK Ibu</label>
                        <input type="text" name="nik_ibu" value="<?= htmlspecialchars($student['nik_ibu']) ?>" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-mono">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <i class="bi bi-save"></i> Simpan Perubahan Biodata
            </button>
        </div>

    </form>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

