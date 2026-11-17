<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Tambah Murid Manual';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $jk = sanitize($_POST['jk'] ?? '');
    $nik = sanitize($_POST['nik'] ?? '');
    $nisn = sanitize($_POST['nisn'] ?? '');
    $tempatLahir = sanitize($_POST['tempat_lahir'] ?? '');
    $tanggalLahir = sanitize($_POST['tanggal_lahir'] ?? '');
    $agama = sanitize($_POST['agama'] ?? 'Islam');
    $alamat = sanitize($_POST['alamat'] ?? '');
    $kelurahan = sanitize($_POST['kelurahan'] ?? '');
    $kecamatan = sanitize($_POST['kecamatan'] ?? 'Kec. Mataraman');
    $hp = sanitize($_POST['hp'] ?? '');
    $paket = sanitize($_POST['paket'] ?? 'Paket B');
    $rombel = sanitize($_POST['rombel_saat_ini'] ?? '');

    $namaAyah = sanitize($_POST['nama_ayah'] ?? '');
    $namaIbu = sanitize($_POST['nama_ibu'] ?? '');

    if (empty($nama) || empty($nik)) {
        setFlash('danger', 'Nama Lengkap dan NIK wajib diisi.');
    } else {
        // Cek duplikasi NIK
        $stmtEx = $pdo->prepare("SELECT id FROM students WHERE nik = ?");
        $stmtEx->execute([$nik]);
        if ($stmtEx->fetchColumn()) {
            setFlash('danger', "NIK {$nik} sudah terdaftar di database.");
        } else {
            $noUrut = (int)$pdo->query("SELECT COALESCE(MAX(no_urut), 0) + 1 FROM students")->fetchColumn();

            $stmtIns = $pdo->prepare("INSERT INTO students (
                no_urut, nama, jk, nisn, nik, tempat_lahir, tanggal_lahir, agama,
                alamat, kelurahan, kecamatan, hp, nama_ayah, nama_ibu,
                rombel_saat_ini, paket, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");

            $stmtIns->execute([
                $noUrut, $nama, $jk, $nisn, $nik, $tempatLahir, $tanggalLahir ?: null, $agama,
                $alamat, $kelurahan, $kecamatan, $hp, $namaAyah, $namaIbu,
                $rombel ?: $paket, $paket
            ]);

            setFlash('success', "Murid {$nama} berhasil ditambahkan ke database.");
            redirect('modules/admin/siswa.php');
        }
    }
}

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Tambah Murid Baru (Manual)</h1>
            <p class="text-xs text-slate-500">Input langsung data warga belajar baru ke pangkalan data</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/admin/siswa.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
            Kembali
        </a>
    </div>

    <form method="POST" action="" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                <input type="text" name="nama" required placeholder="Nama lengkap siswa" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Kelamin *</label>
                <select name="jk" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    <option value="L">Laki-Laki (L)</option>
                    <option value="P">Perempuan (P)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NIK (16 Digit) *</label>
                <input type="text" name="nik" required maxlength="16" placeholder="NIK 16 digit (digunakan untuk login siswa)" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Program Kesetaraan *</label>
                <select name="paket" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-700">
                    <option value="Paket A">Paket A (Setara SD)</option>
                    <option value="Paket B" selected>Paket B (Setara SMP)</option>
                    <option value="Paket C">Paket C (Setara SMA)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rombel / Kelas</label>
                <input type="text" name="rombel_saat_ini" placeholder="Contoh: Paket B Kelas 7 (1)" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" placeholder="Tempat lahir" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                <input type="tel" name="hp" placeholder="Contoh: 083162151175" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NISN</label>
                <input type="text" name="nisn" maxlength="10" placeholder="10 digit NISN" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Lengkap</label>
                <input type="text" name="alamat" placeholder="Nama Jalan, Desa, dsb" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Ayah</label>
                <input type="text" name="nama_ayah" placeholder="Nama ayah" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Ibu</label>
                <input type="text" name="nama_ibu" placeholder="Nama ibu" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <i class="bi bi-save"></i> Simpan Murid ke Database
            </button>
        </div>
    </form>
</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

