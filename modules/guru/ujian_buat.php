<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$pageTitle = 'Buat Ujian Baru';
$pdo = getDB();
$teacherId = $_SESSION['teacher_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $paket = sanitize($_POST['paket'] ?? 'Paket B');
    $tingkatKelas = sanitize($_POST['tingkat_kelas'] ?? 'Semua Kelas');
    $mapel = sanitize($_POST['mapel'] ?? '');
    $durasi = (int)($_POST['durasi_menit'] ?? 0);
    $acakSoal = isset($_POST['acak_soal']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'aktif');
    $nilaiPublikasi = isset($_POST['nilai_dipublikasi']) ? 1 : 0;

    if (empty($judul) || empty($mapel)) {
        setFlash('danger', 'Judul Ujian dan Mata Pelajaran wajib diisi.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO exams (
            guru_id, judul, deskripsi, paket, tingkat_kelas, mapel, durasi_menit, acak_soal, status, nilai_dipublikasi
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $teacherId, $judul, $deskripsi, $paket, $tingkatKelas, $mapel, $durasi, $acakSoal, $status, $nilaiPublikasi
        ]);
        $newId = $pdo->lastInsertId();

        setFlash('success', 'Ujian berhasil dibuat! Sekarang silakan tambahkan butir soal Pilihan Ganda atau Esai.');
        redirect("modules/guru/soal_manage.php?id={$newId}");
    }
}

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Buat Ujian Baru (CBT)</h1>
            <p class="text-xs text-slate-500">Konfigurasi asesmen online untuk warga belajar Paket A, B, atau C</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/guru/ujian_list.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
            Kembali ke Daftar
        </a>
    </div>

    <form method="POST" action="" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
        
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Judul Ujian <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="judul" required placeholder="Contoh: Asesmen Sumatif Akhir Semester Bahasa Indonesia"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Mata Pelajaran <span class="text-rose-500">*</span>
                </label>
                <select name="mapel" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                    <option value="Matematika">Matematika</option>
                    <option value="Ilmu Pengetahuan Alam (IPA)">Ilmu Pengetahuan Alam (IPA)</option>
                    <option value="Ilmu Pengetahuan Sosial (IPS)">Ilmu Pengetahuan Sosial (IPS)</option>
                    <option value="Bahasa Inggris">Bahasa Inggris</option>
                    <option value="Pendidikan Kewarganegaraan (PKn)">Pendidikan Kewarganegaraan (PKn)</option>
                    <option value="Pendidikan Agama Islam (PAI)">Pendidikan Agama Islam (PAI)</option>
                    <option value="Keterampilan & Vokasi">Keterampilan & Vokasi</option>
                    <option value="Pemberdayaan & Wirausaha">Pemberdayaan & Wirausaha</option>
                    <option value="Seni Budaya">Seni Budaya</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Target Program Kesetaraan <span class="text-rose-500">*</span>
                </label>
                <select name="paket" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="Paket B">Paket B (Setara SMP)</option>
                    <option value="Paket C">Paket C (Setara SMA)</option>
                    <option value="Paket A">Paket A (Setara SD)</option>
                    <option value="Semua">Semua Program (Umum)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tingkat Kelas
                </label>
                <select name="tingkat_kelas" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="Semua Kelas">Semua Kelas</option>
                    <option value="Kelas 7">Kelas 7</option>
                    <option value="Kelas 8">Kelas 8</option>
                    <option value="Kelas 9">Kelas 9</option>
                    <option value="Kelas 10">Kelas 10</option>
                    <option value="Kelas 11">Kelas 11</option>
                    <option value="Kelas 12">Kelas 12</option>
                    <option value="Kelas 2">Kelas 2 (Paket A)</option>
                    <option value="Kelas 5">Kelas 5 (Paket A)</option>
                    <option value="Kelas 6">Kelas 6 (Paket A)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Durasi Pengerjaan
                </label>
                <select name="durasi_menit" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="0" selected>Waktu Fleksibel (Rekomendasi PKBM - Tidak ada batasan waktu kaku)</option>
                    <option value="60">60 Menit</option>
                    <option value="90">90 Menit</option>
                    <option value="120">120 Menit</option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Sesuai kebutuhan PKBM agar tidak error/terputus jika murid telat hadir.</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Petunjuk / Deskripsi Ujian
            </label>
            <textarea name="deskripsi" rows="3" placeholder="Contoh: Bacalah setiap butir soal dengan seksama. Kerjakan soal pilihan ganda dan jawab soal esai dengan argumen Anda sendiri."
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"></textarea>
        </div>

        <!-- Pengaturan Tambahan -->
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="acak_soal" value="1" checked class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                <span class="text-xs font-bold text-slate-800">Acak Urutan Soal (Tiap murid mendapat urutan soal yang berbeda)</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="nilai_dipublikasi" value="1" checked class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                <span class="text-xs font-bold text-slate-800">Publikasikan Nilai Otomatis ke Database & Portal Murid</span>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                <span>Lanjut ke Pembuatan Butir Soal</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>

    </form>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

