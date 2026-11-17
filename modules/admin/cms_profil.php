<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Kelola Profil Lembaga & CMS Web';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'nama_sekolah', 'nama_lengkap_sekolah', 'npsn', 'sk_operasional',
        'kepala_pkbm', 'sambutan_kepala', 'visi', 'misi',
        'alamat', 'kecamatan', 'kabupaten', 'provinsi', 'kode_pos',
        'telepon', 'whatsapp', 'email', 'google_drive_folder'
    ];

    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            updateSetting($f, trim($_POST[$f]));
        }
    }

    setFlash('success', 'Profil lembaga dan informasi halaman utama berhasil diperbarui!');
    redirect('modules/admin/cms_profil.php');
}

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Kelola Profil Lembaga (CMS Web)</h1>
            <p class="text-xs text-slate-500">Edit teks sambutan, visi misi, legalitas, dan informasi kontak yang tampil di web utama</p>
        </div>
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
            <i class="bi bi-box-arrow-up-right"></i> Lihat Tampilan Web
        </a>
    </div>

    <form method="POST" action="" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        
        <!-- Identitas Lembaga -->
        <div>
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                I. Identitas & Legalitas Satuan Pendidikan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Satuan (Singkat) *</label>
                    <input type="text" name="nama_sekolah" value="<?= htmlspecialchars(getSetting('nama_sekolah', APP_NAME)) ?>" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Pokok Sekolah Nasional (NPSN) *</label>
                    <input type="text" name="npsn" value="<?= htmlspecialchars(getSetting('npsn', APP_NPSN)) ?>" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-blue-700">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Satuan Pendidikan *</label>
                    <input type="text" name="nama_lengkap_sekolah" value="<?= htmlspecialchars(getSetting('nama_lengkap_sekolah', APP_FULL_NAME)) ?>" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SK Izin Operasional</label>
                    <input type="text" name="sk_operasional" value="<?= htmlspecialchars(getSetting('sk_operasional', APP_SK_OPERASIONAL)) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Kepala Satuan PKBM</label>
                    <input type="text" name="kepala_pkbm" value="<?= htmlspecialchars(getSetting('kepala_pkbm', 'H. Zailani, S.Pd')) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                </div>
            </div>
        </div>

        <!-- Teks Sambutan & Visi Misi -->
        <div class="pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                II. Teks Sambutan, Visi & Misi Lembaga
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sambutan Kepala PKBM (Tampil di Beranda)</label>
                    <textarea name="sambutan_kepala" rows="4" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm leading-relaxed"><?= htmlspecialchars(getSetting('sambutan_kepala')) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Visi Lembaga</label>
                    <textarea name="visi" rows="2" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm leading-relaxed font-medium"><?= htmlspecialchars(getSetting('visi')) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Misi Lembaga (Tuliskan per butir penomoran)</label>
                    <textarea name="misi" rows="4" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm leading-relaxed font-sans"><?= htmlspecialchars(getSetting('misi')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Alamat & Kontak -->
        <div class="pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                III. Alamat & Kontak Resmi
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Jalan Lengkap *</label>
                    <input type="text" name="alamat" value="<?= htmlspecialchars(getSetting('alamat', APP_ADDRESS)) ?>" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" value="<?= htmlspecialchars(getSetting('kecamatan', 'Kec. Mataraman')) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kabupaten / Kota</label>
                    <input type="text" name="kabupaten" value="<?= htmlspecialchars(getSetting('kabupaten', 'Kab. Banjar')) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">WhatsApp Pendaftaran *</label>
                    <input type="text" name="whatsapp" value="<?= htmlspecialchars(getSetting('whatsapp', APP_WHATSAPP)) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Resmi Satuan</label>
                    <input type="email" name="email" value="<?= htmlspecialchars(getSetting('email', APP_EMAIL)) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tautan Google Drive Admin</label>
                    <input type="url" name="google_drive_folder" value="<?= htmlspecialchars(getSetting('google_drive_folder', 'https://drive.google.com/drive/folders/1bgIM8NcMXxKQDx3ekLFrJ95cgh2yh4BM?usp=sharing')) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-blue-700">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <i class="bi bi-save"></i> Simpan Perubahan Profil Lembaga
            </button>
        </div>

    </form>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

