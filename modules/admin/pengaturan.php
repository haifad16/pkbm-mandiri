<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Pengaturan & Integrasi Google Drive';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ppdbStatus = sanitize($_POST['ppdb_status'] ?? 'buka');
    $tahunAjaran = sanitize($_POST['tahun_ajaran'] ?? APP_YEAR);
    $driveLink = sanitize($_POST['google_drive_folder'] ?? '');
    $driveWebhook = sanitize($_POST['google_drive_webhook'] ?? '');

    updateSetting('ppdb_status', $ppdbStatus);
    updateSetting('tahun_ajaran', $tahunAjaran);
    updateSetting('google_drive_folder', $driveLink);
    updateSetting('google_drive_webhook', $driveWebhook);

    setFlash('success', 'Pengaturan sistem dan integrasi berhasil disimpan!');
    redirect('modules/admin/pengaturan.php');
}

$ppdbStatus = getSetting('ppdb_status', 'buka');
$tahunAjaran = getSetting('tahun_ajaran', APP_YEAR);
$driveFolder = getSetting('google_drive_folder', 'https://drive.google.com/drive/folders/1bgIM8NcMXxKQDx3ekLFrJ95cgh2yh4BM?usp=sharing');
$driveWebhook = getSetting('google_drive_webhook', '');

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 mb-1">
                <i class="bi bi-gear-fill"></i> Konfigurasi Sistem
            </div>
            <h1 class="text-xl font-extrabold text-slate-900">Pengaturan & Integrasi Google Drive</h1>
            <p class="text-xs text-slate-500">Kelola status pendaftaran, tahun ajaran, dan sinkronisasi file ke Google Drive admin</p>
        </div>
    </div>

    <!-- PANDUAN EDUKASI MENGAPA WEB AI LAMA DI NETLIFY GAGAL MASUK DRIVE -->
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-3xl p-6 sm:p-8 space-y-3">
        <div class="flex items-center gap-2.5 text-amber-900 font-extrabold text-base">
            <i class="bi bi-question-circle-fill text-amber-600 text-xl"></i>
            <span>Mengapa Web Pendaftaran Netlify Sebelumnya Tidak Mau Masuk ke Google Drive?</span>
        </div>
        <div class="text-xs sm:text-sm text-amber-900/90 leading-relaxed space-y-2">
            <p>
                Di web Anda sebelumnya (<code>https://ppdb-pkbmmandiri.netlify.app/</code>), sistemnya hanya menggunakan <strong>file statis (HTML/JS biasa)</strong> yang di-hosting di Netlify. Netlify tidak memiliki server backend (seperti PHP/Node.js) untuk menyimpan data ke database atau menjalankan otentikasi Google.
            </p>
            <p>
                Google Drive memiliki sistem keamanan ketat. Kode JavaScript di browser pengunjung <strong>dilarang langsung mengunggah file ke Google Drive pribadi orang lain</strong> tanpa proses login pemilik akun atau API token keamanan. Karena itulah di web lama file tidak pernah berhasil masuk ke drive.
            </p>
            <div class="p-3 bg-white/80 rounded-2xl border border-amber-300 font-semibold text-xs text-slate-800">
                <i class="bi bi-check-circle-fill text-emerald-600 mr-1"></i>
                <strong>Solusi yang Telah Kami Terapkan di Web PKBM Mandiri Ini:</strong>
                <br>1. Seluruh berkas upload pendaftar (Ijazah, Akta, KK, Surat Pindah) langsung tersimpan aman 100% di server lokal di folder <code>uploads/berkas/</code> dan tercatat di database.
                <br>2. Admin dapat melihat, memverifikasi, dan mengunduh berkas satu per satu atau bersamaan kapan saja.
                <br>3. Admin juga dapat menghubungkan Webhook Google Apps Script di bawah jika ingin salinan otomatis masuk ke folder Google Drive admin!
            </div>
        </div>
    </div>

    <!-- FORM PENGATURAN -->
    <form method="POST" action="" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        
        <!-- Status PPDB & Tahun Ajaran -->
        <div>
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                I. Pengaturan Pendaftaran & Periode Belajar
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status PPDB Online *</label>
                    <select name="ppdb_status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                        <option value="buka" <?= $ppdbStatus === 'buka' ? 'selected' : '' ?>>BUKA (Menerima Pendaftar Baru)</option>
                        <option value="tutup" <?= $ppdbStatus === 'tutup' ? 'selected' : '' ?>>TUTUP (Pendaftaran Sementara Ditutup)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Ajaran Berjalan *</label>
                    <input type="text" name="tahun_ajaran" value="<?= htmlspecialchars($tahunAjaran) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-900">
                </div>
            </div>
        </div>

        <!-- Google Drive Config -->
        <div class="pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider text-blue-800 border-b border-blue-100 pb-2 mb-4">
                II. Tautan & Sinkronisasi Google Drive Admin
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tautan Folder Google Drive Utama Admin</label>
                    <div class="relative">
                        <i class="bi bi-google absolute left-3.5 top-3 text-amber-500"></i>
                        <input type="url" name="google_drive_folder" value="<?= htmlspecialchars($driveFolder) ?>" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-blue-700 focus:bg-white">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Folder Google Drive yang Anda gunakan untuk mengarsipkan data guru dan berkas siswa.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Google Apps Script Webhook URL (Opsional)</label>
                    <input type="url" name="google_drive_webhook" value="<?= htmlspecialchars($driveWebhook) ?>" placeholder="https://script.google.com/macros/s/.../exec"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:bg-white">
                    <p class="text-[11px] text-slate-400 mt-1">Jika diisi, server akan otomatis mengirimkan sinyal backup ke Google Drive admin saat ada siswa baru mendaftar.</p>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <i class="bi bi-save"></i> Simpan Pengaturan Sistem
            </button>
        </div>

    </form>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

