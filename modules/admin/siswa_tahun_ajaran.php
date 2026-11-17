<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Manajemen Tahun Ajaran & Kenaikan Kelas';
$pdo = getDB();

// 1. Handle Update Tahun Ajaran
if (isset($_POST['update_tahun_ajaran'])) {
    $taBaru = sanitize($_POST['tahun_ajaran'] ?? '');
    if (!empty($taBaru)) {
        updateSetting('tahun_ajaran', $taBaru);
        setFlash('success', "Tahun ajaran berhasil diperbarui menjadi: {$taBaru}");
        redirect('modules/admin/siswa_tahun_ajaran.php');
    }
}

// 2. Handle Promosi / Kenaikan Kelas Otomatis
if (isset($_POST['proses_kenaikan_kelas'])) {
    $jenjang = sanitize($_POST['jenjang'] ?? '');

    $countPromoted = 0;
    $countAlumni = 0;

    if ($jenjang === 'Paket B') {
        // Kelas 9 -> Alumni
        $stmt9 = $pdo->prepare("UPDATE students SET status = 'alumni', rombel_saat_ini = 'Alumni Paket B' 
            WHERE paket = 'Paket B' AND (rombel_saat_ini LIKE '%Kelas 9%' OR tingkat_kelas = 'Kelas 9') AND status = 'aktif'");
        $stmt9->execute();
        $countAlumni += $stmt9->rowCount();

        // Kelas 8 -> Kelas 9
        $stmt8 = $pdo->prepare("UPDATE students SET rombel_saat_ini = REPLACE(rombel_saat_ini, 'Kelas 8', 'Kelas 9'), tingkat_kelas = 'Kelas 9' 
            WHERE paket = 'Paket B' AND (rombel_saat_ini LIKE '%Kelas 8%' OR tingkat_kelas = 'Kelas 8') AND status = 'aktif'");
        $stmt8->execute();
        $countPromoted += $stmt8->rowCount();

        // Kelas 7 -> Kelas 8
        $stmt7 = $pdo->prepare("UPDATE students SET rombel_saat_ini = REPLACE(rombel_saat_ini, 'Kelas 7', 'Kelas 8'), tingkat_kelas = 'Kelas 8' 
            WHERE paket = 'Paket B' AND (rombel_saat_ini LIKE '%Kelas 7%' OR tingkat_kelas = 'Kelas 7') AND status = 'aktif'");
        $stmt7->execute();
        $countPromoted += $stmt7->rowCount();

    } elseif ($jenjang === 'Paket C') {
        // Kelas 12 -> Alumni
        $stmt12 = $pdo->prepare("UPDATE students SET status = 'alumni', rombel_saat_ini = 'Alumni Paket C' 
            WHERE paket = 'Paket C' AND (rombel_saat_ini LIKE '%Kelas 12%' OR tingkat_kelas = 'Kelas 12') AND status = 'aktif'");
        $stmt12->execute();
        $countAlumni += $stmt12->rowCount();

        // Kelas 11 -> Kelas 12
        $stmt11 = $pdo->prepare("UPDATE students SET rombel_saat_ini = REPLACE(rombel_saat_ini, 'Kelas 11', 'Kelas 12'), tingkat_kelas = 'Kelas 12' 
            WHERE paket = 'Paket C' AND (rombel_saat_ini LIKE '%Kelas 11%' OR tingkat_kelas = 'Kelas 11') AND status = 'aktif'");
        $stmt11->execute();
        $countPromoted += $stmt11->rowCount();

        // Kelas 10 -> Kelas 11
        $stmt10 = $pdo->prepare("UPDATE students SET rombel_saat_ini = REPLACE(rombel_saat_ini, 'Kelas 10', 'Kelas 11'), tingkat_kelas = 'Kelas 11' 
            WHERE paket = 'Paket C' AND (rombel_saat_ini LIKE '%Kelas 10%' OR tingkat_kelas = 'Kelas 10') AND status = 'aktif'");
        $stmt10->execute();
        $countPromoted += $stmt10->rowCount();
    }

    setFlash('success', "Proses kenaikan kelas {$jenjang} selesai! {$countPromoted} murid naik ke kelas berikutnya, dan {$countAlumni} murid tingkat akhir dipindahkan ke status Alumni.");
    redirect('modules/admin/siswa_tahun_ajaran.php');
}

// 3. Handle Bersihkan / Hapus Seluruh Siswa (Reset Tahun Ajaran Baru)
if (isset($_POST['reset_semua_siswa'])) {
    $passwordKonfirmasi = $_POST['admin_password'] ?? '';
    $user = currentUser();

    $stmtAdmin = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmtAdmin->execute([$user['id']]);
    $hash = $stmtAdmin->fetchColumn();

    if (password_verify($passwordKonfirmasi, $hash)) {
        // Hapus semua siswa atau bersihkan
        $pdo->exec("DELETE FROM exam_answers");
        $pdo->exec("DELETE FROM exam_attempts");
        $pdo->exec("DELETE FROM students");
        setFlash('warning', 'Pangkalan data murid dan riwayat ujian telah dibersihkan untuk tahun ajaran baru.');
        redirect('modules/admin/siswa.php');
    } else {
        setFlash('danger', 'Kata sandi konfirmasi administrator salah! Aksi reset dibatalkan.');
        redirect('modules/admin/siswa_tahun_ajaran.php');
    }
}

// Statistik
$totalAktif = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'aktif'")->fetchColumn();
$totalAlumni = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'alumni'")->fetchColumn();
$taAktif = getSetting('tahun_ajaran', APP_YEAR);

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 mb-1">
                <i class="bi bi-calendar-range"></i> Pengelolaan Tahun Ajaran Baru
            </div>
            <h1 class="text-xl font-extrabold text-slate-900">Kenaikan Kelas & Manajemen Rombel</h1>
            <p class="text-xs text-slate-500">Alat bantu admin untuk menaikkan kelas siswa secara massal atau mereset data tahun ajaran baru</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/modules/admin/siswa.php?export=excel" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                <i class="bi bi-cloud-arrow-down-fill"></i> Download Cadangan (Backup)
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- CARD 1: Kenaikan Kelas Otomatis (Promosi Massal) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900">Promosi Kenaikan Kelas Otomatis</h3>
                    <p class="text-xs text-slate-500">Menaikkan rombel siswa ke tingkat kelas berikutnya</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Fitur ini akan secara otomatis memperbarui rombel siswa yang berstatus aktif:
                <br>• <strong>Paket B</strong>: Kelas 7 &rarr; Kelas 8, Kelas 8 &rarr; Kelas 9, Kelas 9 &rarr; Alumni (Lulus).
                <br>• <strong>Paket C</strong>: Kelas 10 &rarr; Kelas 11, Kelas 11 &rarr; Kelas 12, Kelas 12 &rarr; Alumni (Lulus).
            </p>

            <!-- Form Eksekusi Paket B -->
            <form method="POST" action="" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <input type="hidden" name="jenjang" value="Paket B">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-slate-900">Kenaikan Kelas Paket B (SMP)</div>
                        <div class="text-[11px] text-slate-500">Terapkan pada seluruh murid Paket B aktif</div>
                    </div>
                    <button type="submit" name="proses_kenaikan_kelas" value="1" onclick="return confirm('Apakah Anda yakin ingin menaikkan seluruh siswa Paket B ke kelas berikutnya?')"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition">
                        Naikkan Paket B
                    </button>
                </div>
            </form>

            <!-- Form Eksekusi Paket C -->
            <form method="POST" action="" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <input type="hidden" name="jenjang" value="Paket C">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-slate-900">Kenaikan Kelas Paket C (SMA)</div>
                        <div class="text-[11px] text-slate-500">Terapkan pada seluruh murid Paket C aktif</div>
                    </div>
                    <button type="submit" name="proses_kenaikan_kelas" value="1" onclick="return confirm('Apakah Anda yakin ingin menaikkan seluruh siswa Paket C ke kelas berikutnya?')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
                        Naikkan Paket C
                    </button>
                </div>
            </form>
        </div>

        <!-- CARD 2: Tahun Ajaran & Reset Data -->
        <div class="space-y-6">
            
            <!-- Ganti Tahun Ajaran -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Tahun Ajaran Aktif</h3>
                        <p class="text-xs text-slate-500">Pengaturan periode akademik lembaga</p>
                    </div>
                </div>

                <form method="POST" action="" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Ajaran Berjalan</label>
                        <input type="text" name="tahun_ajaran" value="<?= htmlspecialchars($taAktif) ?>" required placeholder="Contoh: 2026/2027"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-900">
                    </div>
                    <button type="submit" name="update_tahun_ajaran" value="1" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                        Simpan Perubahan Tahun Ajaran
                    </button>
                </form>
            </div>

            <!-- Danger Zone: Reset Keseluruhan Murid -->
            <div class="bg-rose-50/70 border border-rose-200 rounded-3xl p-6 sm:p-8 space-y-3">
                <div class="font-bold text-rose-900 text-sm flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-rose-600"></i> Area Berbahaya: Reset Siswa Tahun Ajaran Baru
                </div>
                <p class="text-xs text-rose-800 leading-relaxed">
                    Jika lembaga memulai tahun ajaran baru dan ingin mengosongkan data murid lama (misalnya setelah di-backup ke Excel), masukkan password admin Anda untuk konfirmasi pembersihan pangkalan data.
                </p>

                <form method="POST" action="" class="space-y-3 pt-2">
                    <input type="password" name="admin_password" required placeholder="Masukkan kata sandi Administrator untuk konfirmasi..."
                        class="w-full px-3.5 py-2 bg-white border border-rose-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500">
                    <button type="submit" name="reset_semua_siswa" value="1" onclick="return confirm('PERINGATAN! Aksi ini akan menghapus seluruh data siswa dan riwayat ujian. Pastikan Anda telah mengunduh backup Excel terlebih dahulu! Lanjutkan?')"
                        class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow transition">
                        Reset Seluruh Data Siswa untuk Tahun Ajaran Baru
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

