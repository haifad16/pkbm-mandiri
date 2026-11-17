<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

$no = sanitize($_GET['no'] ?? '');
if (empty($no)) {
    redirect('modules/pendaftaran/index.php');
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE no_pendaftaran = ?");
$stmt->execute([$no]);
$reg = $stmt->fetch();

if (!$reg) {
    setFlash('danger', 'Data pendaftaran dengan nomor tersebut tidak ditemukan.');
    redirect('modules/pendaftaran/index.php');
}

$pageTitle = 'Bukti Pendaftaran - ' . $reg['no_pendaftaran'];
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolAddress = getSetting('alamat', APP_ADDRESS);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$waNumber = getSetting('whatsapp', APP_WHATSAPP);

require_once BASE_PATH . '/includes/header.php';
?>

<div class="py-10 bg-slate-100 min-h-screen">
    <div class="max-w-3xl mx-auto px-4">
        
        <!-- Action Buttons (No Print) -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 no-print">
            <a href="<?= BASE_URL ?>/index.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm transition">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                    <i class="bi bi-printer-fill"></i> Cetak Bukti Pendaftaran
                </button>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waNumber) ?>?text=Halo%20Admin%20PKBM%20Mandiri,%20saya%20sudah%20mendaftar%20dengan%20Nomor%20Pendaftaran:%20<?= urlencode($reg['no_pendaftaran']) ?>%20atas%20nama%20<?= urlencode($reg['nama_lengkap']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                    <i class="bi bi-whatsapp"></i> Konfirmasi ke Admin
                </a>
            </div>
        </div>

        <!-- Printable Document Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-12 relative overflow-hidden">
            
            <!-- Watermark / Stamp -->
            <div class="absolute right-6 top-28 opacity-10 pointer-events-none rotate-12">
                <i class="bi bi-mortarboard-fill text-9xl text-blue-900"></i>
            </div>

            <!-- Kop Surat Resmi -->
            <div class="border-b-2 border-slate-900 pb-5 mb-6 text-center">
                <div class="flex items-center justify-center gap-4 mb-2">
                    <div class="w-14 h-14 rounded-2xl bg-blue-700 text-white flex items-center justify-center text-3xl font-extrabold shadow-md">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div class="text-left">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none uppercase">
                            <?= htmlspecialchars($schoolFullName) ?>
                        </h2>
                        <div class="text-xs text-slate-600 font-semibold mt-1">
                            NPSN: <?= htmlspecialchars($schoolNpsn) ?> • Izin Operasional Kemendikbud
                        </div>
                        <div class="text-[11px] text-slate-500">
                            <?= htmlspecialchars($schoolAddress) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Judul Bukti -->
            <div class="text-center mb-8">
                <h3 class="text-lg font-extrabold text-slate-900 uppercase tracking-wider">
                    TANDA BUKTI PENDAFTARAN MURID BARU (PPDB)
                </h3>
                <div class="text-xs text-slate-500 mt-1">
                    Tahun Ajaran <?= htmlspecialchars(getSetting('tahun_ajaran', APP_YEAR)) ?>
                </div>
                
                <!-- Nomor Pendaftaran Badge -->
                <div class="mt-4 inline-block bg-blue-50 border-2 border-blue-600/40 rounded-2xl px-6 py-2.5">
                    <span class="text-[11px] font-bold text-blue-700 uppercase tracking-widest block">Nomor Registrasi / Pendaftaran</span>
                    <span class="text-xl sm:text-2xl font-black text-blue-900 font-mono tracking-wider"><?= htmlspecialchars($reg['no_pendaftaran']) ?></span>
                </div>
            </div>

            <!-- Biodata Table -->
            <div class="space-y-6 text-sm">
                
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-700 border-b border-blue-100 pb-1 mb-3">
                        I. Program Pendidikan & Status Calon Murid
                    </h4>
                    <table class="w-full text-xs sm:text-sm">
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 w-1/3 text-slate-500 font-medium">Program Kesetaraan</td>
                            <td class="py-1.5 font-bold text-slate-900">: <?= htmlspecialchars($reg['paket_pilihan']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Status Usia</td>
                            <td class="py-1.5 font-semibold text-slate-800">
                                : <?= $reg['status_usia_sd'] === 'lewat_usia_sekolah' ? 'Warga Belajar Dewasa (Lewat Usia SD)' : 'Usia Sekolah Standar (' . $reg['usia_tahun'] . ' Tahun)' ?>
                            </td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Status Verifikasi</td>
                            <td class="py-1.5">
                                : <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $reg['status_verifikasi'] === 'diterima' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= strtoupper($reg['status_verifikasi']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-700 border-b border-blue-100 pb-1 mb-3">
                        II. Data Diri Calon Murid
                    </h4>
                    <table class="w-full text-xs sm:text-sm">
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 w-1/3 text-slate-500 font-medium">Nama Lengkap</td>
                            <td class="py-1.5 font-bold text-slate-900">: <?= htmlspecialchars($reg['nama_lengkap']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Jenis Kelamin</td>
                            <td class="py-1.5 font-medium text-slate-800">: <?= $reg['jk'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">NIK Murid</td>
                            <td class="py-1.5 font-mono font-semibold text-slate-900">: <?= htmlspecialchars($reg['nik']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">NISN</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['nisn'] ?: '-') ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Tempat, Tanggal Lahir</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['tempat_lahir']) ?>, <?= formatTanggalIndo($reg['tanggal_lahir']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Agama</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['agama']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Alamat Lengkap</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['alamat']) ?> RT.<?= htmlspecialchars($reg['rt'] ?: '0') ?>/RW.<?= htmlspecialchars($reg['rw'] ?: '0') ?>, Kel. <?= htmlspecialchars($reg['kelurahan']) ?>, <?= htmlspecialchars($reg['kecamatan']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">No. HP / WhatsApp</td>
                            <td class="py-1.5 text-slate-800 font-semibold">: <?= htmlspecialchars($reg['hp']) ?></td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Nomor Kartu Keluarga</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['no_kk']) ?></td>
                        </tr>
                    </table>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-700 border-b border-blue-100 pb-1 mb-3">
                        III. Data Orang Tua / Wali
                    </h4>
                    <table class="w-full text-xs sm:text-sm">
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 w-1/3 text-slate-500 font-medium">Nama Ayah</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['nama_ayah'] ?: '-') ?> (NIK: <?= htmlspecialchars($reg['nik_ayah'] ?: '-') ?>)</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 font-medium">Nama Ibu Kandung</td>
                            <td class="py-1.5 text-slate-800">: <?= htmlspecialchars($reg['nama_ibu'] ?: '-') ?> (NIK: <?= htmlspecialchars($reg['nik_ibu'] ?: '-') ?>)</td>
                        </tr>
                    </table>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-700 border-b border-blue-100 pb-1 mb-3">
                        IV. Status Dokumen Terunggah
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <i class="bi <?= $reg['file_ijazah'] ? 'bi-check-circle-fill text-emerald-500' : 'bi-dash-circle text-slate-400' ?> text-lg block mb-1"></i>
                            <span class="font-semibold block">Ijazah</span>
                            <span class="text-[10px] text-slate-400"><?= $reg['file_ijazah'] ? 'Terupload' : 'Tidak Ada' ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <i class="bi <?= $reg['file_akta'] ? 'bi-check-circle-fill text-emerald-500' : 'bi-dash-circle text-slate-400' ?> text-lg block mb-1"></i>
                            <span class="font-semibold block">Akta Kelahiran</span>
                            <span class="text-[10px] text-slate-400"><?= $reg['file_akta'] ? 'Terupload' : 'Tidak Ada' ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <i class="bi <?= $reg['file_kk'] ? 'bi-check-circle-fill text-emerald-500' : 'bi-dash-circle text-slate-400' ?> text-lg block mb-1"></i>
                            <span class="font-semibold block">Kartu Keluarga</span>
                            <span class="text-[10px] text-slate-400"><?= $reg['file_kk'] ? 'Terupload' : 'Tidak Ada' ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <i class="bi <?= $reg['file_surat_pindah'] ? 'bi-check-circle-fill text-emerald-500' : 'bi-dash-circle text-slate-400' ?> text-lg block mb-1"></i>
                            <span class="font-semibold block">Surat Pindah</span>
                            <span class="text-[10px] text-slate-400"><?= $reg['file_surat_pindah'] ? 'Terupload' : 'Tidak Ada' ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Tanda Tangan & Cap -->
            <div class="mt-12 pt-6 border-t border-slate-200 flex justify-between items-end text-xs text-slate-600">
                <div>
                    <div>Tanggal Pendaftaran: <?= formatTanggalIndo($reg['created_at'], true) ?></div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Dokumen ini diterbitkan resmi oleh Sistem PPDB Online PKBM Mandiri</div>
                </div>
                <div class="text-center w-48">
                    <div>Mataraman, <?= formatTanggalIndo($reg['created_at']) ?></div>
                    <div class="font-bold text-slate-800 mt-1">Petugas PPDB,</div>
                    <div class="h-16"></div>
                    <div class="font-bold border-b border-slate-400 pb-0.5">Panitia Penerimaan Murid</div>
                    <div class="text-[10px] text-slate-500">PKBM Mandiri Mataraman</div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

