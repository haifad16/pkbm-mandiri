<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Verifikasi Pendaftar Baru (PPDB)';
$pdo = getDB();

$filterStatus = sanitize($_GET['status'] ?? 'semua');
$selectedDetailId = (int)($_GET['detail'] ?? 0);

// Handle Verifikasi Terima Siswa
if (isset($_POST['aksi_terima'])) {
    $regId = (int)$_POST['reg_id'];
    $rombelTujuan = sanitize($_POST['rombel_tujuan'] ?? '');

    $stmtReg = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
    $stmtReg->execute([$regId]);
    $reg = $stmtReg->fetch();

    if ($reg) {
        // Cek jika siswa dengan NIK ini belum ada di tabel students
        $stmtEx = $pdo->prepare("SELECT id FROM students WHERE nik = ?");
        $stmtEx->execute([$reg['nik']]);
        $existingStudentId = $stmtEx->fetchColumn();

        $paketClean = 'Paket B';
        if (stripos($reg['paket_pilihan'], 'Paket A') !== false) $paketClean = 'Paket A';
        if (stripos($reg['paket_pilihan'], 'Paket C') !== false) $paketClean = 'Paket C';

        if (!$existingStudentId) {
            // Ambil no urut terakhir
            $noUrut = (int)$pdo->query("SELECT COALESCE(MAX(no_urut), 0) + 1 FROM students")->fetchColumn();

            $insStd = $pdo->prepare("INSERT INTO students (
                no_urut, nama, jk, nisn, tempat_lahir, tanggal_lahir, nik, agama,
                alamat, rt, rw, dusun, kelurahan, kecamatan, hp, email, asal_sekolah, no_kk,
                nama_ayah, tahun_lahir_ayah, pendidikan_ayah, nik_ayah,
                nama_ibu, tahun_lahir_ibu, pendidikan_ibu, nik_ibu,
                rombel_saat_ini, paket, tingkat_kelas, status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, 'aktif'
            )");

            $insStd->execute([
                $noUrut, $reg['nama_lengkap'], $reg['jk'], $reg['nisn'], $reg['tempat_lahir'], $reg['tanggal_lahir'], $reg['nik'], $reg['agama'],
                $reg['alamat'], $reg['rt'], $reg['rw'], $reg['dusun'], $reg['kelurahan'], $reg['kecamatan'], $reg['hp'], $reg['email'], $reg['asal_sekolah'], $reg['no_kk'],
                $reg['nama_ayah'], $reg['tahun_lahir_ayah'], $reg['pendidikan_ayah'], $reg['nik_ayah'],
                $reg['nama_ibu'], $reg['tahun_lahir_ibu'], $reg['pendidikan_ibu'], $reg['nik_ibu'],
                $rombelTujuan ?: $reg['paket_pilihan'], $paketClean, 'Kelas Baru'
            ]);
        }

        // Update status pendaftaran
        $pdo->prepare("UPDATE registrations SET status_verifikasi = 'diterima', catatan_admin = 'Berkas terverifikasi lengkap. Diterima sebagai warga belajar aktif.' WHERE id = ?")
            ->execute([$regId]);

        setFlash('success', "Pendaftaran {$reg['nama_lengkap']} berhasil DITERIMA dan dimasukkan ke Database Murid Aktif! Siswa kini dapat login menggunakan NIK.");
        redirect('modules/admin/pendaftar.php');
    }
}

// Handle Tolak Pendaftaran
if (isset($_POST['aksi_tolak'])) {
    $regId = (int)$_POST['reg_id'];
    $alasan = sanitize($_POST['alasan_tolak'] ?? 'Berkas persyaratan belum lengkap.');

    $pdo->prepare("UPDATE registrations SET status_verifikasi = 'ditolak', catatan_admin = ? WHERE id = ?")
        ->execute([$alasan, $regId]);

    setFlash('warning', 'Pendaftaran telah ditandai ditolak.');
    redirect('modules/admin/pendaftar.php');
}

// Query data pendaftar
$q = "SELECT * FROM registrations WHERE 1=1";
$params = [];
if ($filterStatus !== 'semua') {
    $q .= " AND status_verifikasi = ?";
    $params[] = $filterStatus;
}
$q .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($q);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

// Detail jika dibuka
$detailReg = null;
if ($selectedDetailId > 0) {
    $stmtD = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
    $stmtD->execute([$selectedDetailId]);
    $detailReg = $stmtD->fetch();
}

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Verifikasi Pendaftar Baru (PPDB)</h1>
            <p class="text-xs text-slate-500">Periksa isian formulir, kelengkapan berkas, dan konfirmasi penerimaan murid ke database</p>
        </div>

        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl text-xs font-bold">
            <a href="?status=semua" class="px-3 py-1.5 rounded-xl transition <?= $filterStatus === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">
                Semua
            </a>
            <a href="?status=menunggu" class="px-3 py-1.5 rounded-xl transition <?= $filterStatus === 'menunggu' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">
                Menunggu
            </a>
            <a href="?status=diterima" class="px-3 py-1.5 rounded-xl transition <?= $filterStatus === 'diterima' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">
                Diterima
            </a>
            <a href="?status=ditolak" class="px-3 py-1.5 rounded-xl transition <?= $filterStatus === 'ditolak' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">
                Ditolak
            </a>
        </div>
    </div>

    <!-- DETAIL MODAL / DRAWER -->
    <?php if ($detailReg): ?>
        <div class="bg-white rounded-3xl border-2 border-blue-600 shadow-xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase">
                        Verifikasi Berkas Calon Siswa
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 mt-1 uppercase">
                        <?= htmlspecialchars($detailReg['nama_lengkap']) ?>
                    </h2>
                    <div class="text-xs text-slate-500 mt-0.5">
                        No. Registrasi: <strong class="text-blue-700"><?= htmlspecialchars($detailReg['no_pendaftaran']) ?></strong> 
                        • NIK: <strong class="font-mono"><?= htmlspecialchars($detailReg['nik']) ?></strong>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/modules/admin/pendaftar.php" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                    <i class="bi bi-x-lg text-lg"></i>
                </a>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
                <!-- Info Pendaftaran -->
                <div class="space-y-3 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400">Rincian Calon Murid</h3>
                    <div><span class="text-slate-400">Program Pilihan:</span> <strong class="text-blue-900"><?= htmlspecialchars($detailReg['paket_pilihan']) ?></strong></div>
                    <div><span class="text-slate-400">TTL:</span> <?= htmlspecialchars($detailReg['tempat_lahir']) ?>, <?= formatTanggalIndo($detailReg['tanggal_lahir']) ?> (Usia: <?= $detailReg['usia_tahun'] ?> Tahun)</div>
                    <div><span class="text-slate-400">Jenis Kelamin:</span> <?= $detailReg['jk'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></div>
                    <div><span class="text-slate-400">NISN:</span> <?= htmlspecialchars($detailReg['nisn'] ?: '-') ?></div>
                    <div><span class="text-slate-400">No. WhatsApp / HP:</span> <strong class="text-emerald-700"><?= htmlspecialchars($detailReg['hp']) ?></strong></div>
                    <div><span class="text-slate-400">Alamat:</span> <?= htmlspecialchars($detailReg['alamat']) ?> RT.<?= htmlspecialchars($detailReg['rt']) ?>/RW.<?= htmlspecialchars($detailReg['rw']) ?>, <?= htmlspecialchars($detailReg['kelurahan']) ?>, <?= htmlspecialchars($detailReg['kecamatan']) ?></div>
                    <div><span class="text-slate-400">Status Usia:</span> <?= $detailReg['status_usia_sd'] === 'lewat_usia_sekolah' ? 'Warga Belajar Dewasa' : 'Usia Sekolah Reguler' ?></div>
                </div>

                <!-- Info Orang Tua -->
                <div class="space-y-3 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400">Orang Tua & Asal Sekolah</h3>
                    <div><span class="text-slate-400">Asal Sekolah:</span> <?= htmlspecialchars($detailReg['asal_sekolah'] ?: '-') ?></div>
                    <div><span class="text-slate-400">No. Kartu Keluarga:</span> <?= htmlspecialchars($detailReg['no_kk']) ?></div>
                    <div><span class="text-slate-400">Nama Ayah:</span> <?= htmlspecialchars($detailReg['nama_ayah'] ?: '-') ?> (NIK: <?= htmlspecialchars($detailReg['nik_ayah'] ?: '-') ?>)</div>
                    <div><span class="text-slate-400">Nama Ibu:</span> <?= htmlspecialchars($detailReg['nama_ibu'] ?: '-') ?> (NIK: <?= htmlspecialchars($detailReg['nik_ibu'] ?: '-') ?>)</div>
                    <div><span class="text-slate-400">Tanggal Daftar:</span> <?= formatTanggalIndo($detailReg['created_at'], true) ?></div>
                </div>
            </div>

            <!-- Dokumen Berkas Terlampir -->
            <div>
                <h3 class="font-extrabold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-paperclip text-blue-600"></i> Dokumen Berkas yang Di-Upload
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    
                    <!-- Ijazah -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-center">
                        <span class="font-bold text-xs text-slate-800 block mb-2">Ijazah Terakhir</span>
                        <?php if (!empty($detailReg['file_ijazah'])): ?>
                            <a href="<?= BASE_URL ?>/uploads/berkas/<?= htmlspecialchars($detailReg['file_ijazah']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                                <i class="bi bi-eye"></i> Lihat Berkas
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Tidak Ada File</span>
                        <?php endif; ?>
                    </div>

                    <!-- Akta Kelahiran -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-center">
                        <span class="font-bold text-xs text-slate-800 block mb-2">Akta Kelahiran</span>
                        <?php if (!empty($detailReg['file_akta'])): ?>
                            <a href="<?= BASE_URL ?>/uploads/berkas/<?= htmlspecialchars($detailReg['file_akta']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                                <i class="bi bi-eye"></i> Lihat Berkas
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Tidak Ada File</span>
                        <?php endif; ?>
                    </div>

                    <!-- Kartu Keluarga -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-center">
                        <span class="font-bold text-xs text-slate-800 block mb-2">Kartu Keluarga (KK)</span>
                        <?php if (!empty($detailReg['file_kk'])): ?>
                            <a href="<?= BASE_URL ?>/uploads/berkas/<?= htmlspecialchars($detailReg['file_kk']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                                <i class="bi bi-eye"></i> Lihat Berkas
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Tidak Ada File</span>
                        <?php endif; ?>
                    </div>

                    <!-- Surat Pindah -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-center">
                        <span class="font-bold text-xs text-slate-800 block mb-2">Surat Pindah</span>
                        <?php if (!empty($detailReg['file_surat_pindah'])): ?>
                            <a href="<?= BASE_URL ?>/uploads/berkas/<?= htmlspecialchars($detailReg['file_surat_pindah']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                                <i class="bi bi-eye"></i> Lihat Berkas
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Tidak Ada File</span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- Form Aksi Terima / Tolak -->
            <?php if ($detailReg['status_verifikasi'] === 'menunggu'): ?>
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                    <h3 class="font-extrabold text-sm text-slate-900">Keputusan Verifikasi Pendaftaran</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Form Terima -->
                        <form method="POST" action="" class="p-4 bg-white rounded-xl border border-emerald-200 space-y-3">
                            <input type="hidden" name="reg_id" value="<?= $detailReg['id'] ?>">
                            <div class="font-bold text-emerald-800 text-sm flex items-center gap-1.5">
                                <i class="bi bi-check-circle-fill text-emerald-600"></i> Terima Sebagai Murid Resmi
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Tentukan Rombel Siswa:</label>
                                <input type="text" name="rombel_tujuan" value="<?= htmlspecialchars($detailReg['paket_pilihan']) ?> Kelas Baru" required
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
                            </div>
                            <button type="submit" name="aksi_terima" value="1" onclick="return confirm('Terima siswa ini dan masukkan ke database 556 murid?')"
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                                Setujui & Masukkan ke Database Siswa
                            </button>
                        </form>

                        <!-- Form Tolak -->
                        <form method="POST" action="" class="p-4 bg-white rounded-xl border border-rose-200 space-y-3">
                            <input type="hidden" name="reg_id" value="<?= $detailReg['id'] ?>">
                            <div class="font-bold text-rose-800 text-sm flex items-center gap-1.5">
                                <i class="bi bi-x-circle-fill text-rose-600"></i> Tolak Pendaftaran
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Alasan Penolakan:</label>
                                <input type="text" name="alasan_tolak" placeholder="Contoh: Berkas Akta tidak terbaca jelas" required
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            </div>
                            <button type="submit" name="aksi_tolak" value="1" onclick="return confirm('Tolak pendaftaran ini?')"
                                class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow transition">
                                Tolak Pendaftaran
                            </button>
                        </form>

                    </div>
                </div>
            <?php else: ?>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500">Status Saat Ini:</span> 
                        <strong class="uppercase text-slate-900 ml-1"><?= $detailReg['status_verifikasi'] ?></strong>
                        <span class="text-slate-400 ml-2">(<?= htmlspecialchars($detailReg['catatan_admin'] ?? '') ?>)</span>
                    </div>
                    <a href="<?= BASE_URL ?>/modules/pendaftaran/sukses.php?no=<?= $detailReg['no_pendaftaran'] ?>" target="_blank" class="text-blue-600 font-bold hover:underline">
                        Lihat Tanda Bukti Registrasi &rarr;
                    </a>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <!-- Tabel Data Pendaftar -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-900 text-white font-bold uppercase text-[11px] tracking-wider text-left">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">No. Pendaftaran</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">NIK</th>
                        <th class="py-3 px-4">Program Pilihan</th>
                        <th class="py-3 px-4">No. HP / WA</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                Tidak ada data pendaftar pada kategori ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($registrations as $r): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4 text-center text-slate-500 font-bold"><?= $no++ ?></td>
                                <td class="py-3.5 px-4 font-mono font-bold text-blue-700"><?= htmlspecialchars($r['no_pendaftaran']) ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                                <td class="py-3.5 px-4 font-mono text-slate-600"><?= htmlspecialchars($r['nik']) ?></td>
                                <td class="py-3.5 px-4 font-medium text-slate-800"><?= htmlspecialchars($r['paket_pilihan']) ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700"><?= htmlspecialchars($r['hp']) ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase <?= $r['status_verifikasi'] === 'diterima' ? 'bg-emerald-100 text-emerald-800' : ($r['status_verifikasi'] === 'ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                                        <?= $r['status_verifikasi'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="?detail=<?= $r['id'] ?>" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition inline-flex items-center gap-1 shadow-sm">
                                        <i class="bi bi-file-earmark-check"></i> Periksa
                                    </a>
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

