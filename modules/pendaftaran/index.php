<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

$pageTitle = 'Formulir Pendaftaran Murid Baru (PPDB Online)';
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolYear = getSetting('tahun_ajaran', APP_YEAR);
$ppdbStatus = getSetting('ppdb_status', 'buka');

require_once BASE_PATH . '/includes/header.php';
?>

<div class="py-10 bg-gradient-to-b from-blue-50/70 via-slate-50 to-slate-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="text-center mb-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wider mb-3">
                <i class="bi bi-mortarboard-fill"></i> PPDB Online Tahun Ajaran <?= htmlspecialchars($schoolYear) ?>
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Pendaftaran Murid Baru <?= htmlspecialchars($schoolName) ?>
            </h1>
            <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl mx-auto">
                Layanan pendidikan kesetaraan resmi Paket A (Setara SD), Paket B (Setara SMP), dan Paket C (Setara SMA). Silakan isi formulir di bawah ini dengan data yang benar dan sah.
            </p>
        </div>

        <?php if ($ppdbStatus !== 'buka'): ?>
            <div class="bg-amber-50 border border-amber-300 rounded-3xl p-8 text-center text-amber-900">
                <div class="w-16 h-16 rounded-full bg-amber-200 text-amber-700 flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3 class="text-xl font-bold">Pendaftaran Sementara Ditutup</h3>
                <p class="text-sm mt-2 text-amber-800">
                    Mohon maaf, penerimaan murid baru saat ini sedang ditutup atau dalam proses seleksi berkas. Hubungi admin via WhatsApp untuk informasi pembukaan gelombang berikutnya.
                </p>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('whatsapp', APP_WHATSAPP)) ?>" class="mt-6 inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition">
                    <i class="bi bi-whatsapp"></i> Hubungi Admin via WhatsApp
                </a>
            </div>
        <?php else: ?>

            <form id="ppdb-form" action="<?= BASE_URL ?>/modules/pendaftaran/simpan.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
                
                <!-- Step 1: Pilihan Program Belajar -->
                <div class="p-6 sm:p-8 border-b border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-lg">1</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Pilihan Program Pendidikan Kesetaraan</h2>
                            <p class="text-xs text-slate-500">Pilih jenjang kesetaraan yang ingin Anda ikuti</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-5 bg-slate-50 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-blue-500 transition program-card has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                            <input type="radio" name="paket_pilihan" value="Paket A (Setara SD)" required class="sr-only" onchange="handleProgramChange(this.value)">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-blue-900 text-lg">Paket A</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full">Setara SD</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-2">Untuk warga belajar yang belum menyelesaikan jenjang Sekolah Dasar.</p>
                        </label>

                        <label class="relative flex flex-col p-5 bg-slate-50 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-blue-500 transition program-card has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                            <input type="radio" name="paket_pilihan" value="Paket B (Setara SMP)" required class="sr-only" onchange="handleProgramChange(this.value)">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-blue-900 text-lg">Paket B</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-sky-100 text-sky-800 rounded-full">Setara SMP</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-2">Untuk lulusan SD/MI/Paket A yang ingin melanjutkan ke jenjang SMP.</p>
                        </label>

                        <label class="relative flex flex-col p-5 bg-slate-50 rounded-2xl border-2 border-slate-200 cursor-pointer hover:border-blue-500 transition program-card has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                            <input type="radio" name="paket_pilihan" value="Paket C (Setara SMA)" required class="sr-only" onchange="handleProgramChange(this.value)">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-blue-900 text-lg">Paket C</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Setara SMA</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-2">Untuk lulusan SMP/MTs/Paket B yang ingin meraih ijazah setara SMA.</p>
                        </label>
                    </div>

                    <!-- Notice Khusus Paket A yang dinamis sesuai permintaan user -->
                    <div id="paket-a-notice" class="hidden mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-3">
                        <div class="font-bold flex items-center gap-2 text-sm text-amber-800">
                            <i class="bi bi-info-circle-fill text-amber-600"></i> Ketentuan Khusus Pendaftaran Paket A (Setara SD):
                        </div>
                        <p class="leading-relaxed">
                            Berdasarkan aturan kesetaraan:
                            <br>• Jika umur pendaftar masih dalam batas usia sekolah SD, wajib menyertakan Ijazah TK/PAUD (jika siswa baru) atau Surat Pindah (jika pindahan dari SD lain).
                            <br>• Jika umur pendaftar <strong>sudah tidak masuk dalam usia sekolah SD (usia dewasa)</strong>, maka <strong>tidak perlu menyertakan ijazah TK/Paud atau surat apapun</strong>.
                        </p>
                        <div class="pt-2 border-t border-amber-200/60 flex flex-wrap items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800">
                                <input type="checkbox" id="chk-lewat-usia" name="is_lewat_usia_sd" value="1" onchange="togglePaketARequirements()" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                <span>Pendaftar sudah <strong>melewati batas usia SD (Warga Belajar Dewasa)</strong></span>
                            </label>
                            <div id="kategori-anak-sd" class="flex items-center gap-3">
                                <span class="text-slate-500">Status Siswa:</span>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="kategori_sd" value="siswa_baru" checked onchange="togglePaketARequirements()"> Siswa Baru (Ada Ijazah TK)
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="kategori_sd" value="pindahan" onchange="togglePaketARequirements()"> Pindahan SD Lain
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Data Diri Murid -->
                <div class="p-6 sm:p-8 border-b border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-lg">2</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Data Diri Calon Murid</h2>
                            <p class="text-xs text-slate-500">Isi data identitas diri sesuai Kartu Keluarga (KK) / KTP</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        
                        <!-- Nama Lengkap -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Lengkap Murid <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" required placeholder="Contoh: AHMAD MAULANA"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jenis Kelamin (JK) <span class="text-rose-500">*</span>
                            </label>
                            <select name="jk" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-Laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>

                        <!-- NIK Murid -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nik" required maxlength="16" placeholder="16 digit NIK Murid"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tempat Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="tempat_lahir" required placeholder="Contoh: Mataraman, Martapura"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_lahir" id="inp-tgl-lahir" required onchange="calculateAge(this.value)"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <div id="usia-display" class="text-[11px] text-blue-600 font-bold mt-1"></div>
                        </div>

                        <!-- NISN -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                NISN <span class="text-slate-400 font-normal">(Jika Ada)</span>
                            </label>
                            <input type="text" name="nisn" maxlength="10" placeholder="10 digit NISN jika ada"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Agama -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Agama <span class="text-rose-500">*</span>
                            </label>
                            <select name="agama" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="Islam" selected>Islam</option>
                                <option value="Kristen Protestan">Kristen Protestan</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>

                        <!-- No HP Murid -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                No HP / WhatsApp (Murid / Ortu) <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" name="hp" required placeholder="Contoh: 083162151175"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Email Murid -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                E-Mail (Murid)
                            </label>
                            <input type="email" name="email" placeholder="Contoh: murid@gmail.com"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Asal Sekolah -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Asal Sekolah Sebelumnya
                            </label>
                            <input type="text" name="asal_sekolah" placeholder="Nama sekolah sebelumnya / MI / MTs / SMP"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- No Kartu Keluarga -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Kartu Keluarga (KK) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="no_kk" required maxlength="16" placeholder="16 digit Nomor KK"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Jalan / Rumah <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="alamat" required rows="2" placeholder="Nama Jalan, Gang, Nomor Rumah"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"></textarea>
                        </div>

                        <!-- RT & RW -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">RT</label>
                                <input type="text" name="rt" placeholder="01" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">RW</label>
                                <input type="text" name="rw" placeholder="01" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            </div>
                        </div>

                        <!-- Dusun -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Dusun / Kampung</label>
                            <input type="text" name="dusun" placeholder="Nama dusun / kampung jika ada"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <!-- Kelurahan / Desa -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kelurahan / Desa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="kelurahan" required placeholder="Contoh: Mataraman, Simpang Tiga"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <!-- Kecamatan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kecamatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="kecamatan" required value="Kec. Mataraman"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                    </div>
                </div>

                <!-- Step 3: Data Orang Tua (Ayah & Ibu) -->
                <div class="p-6 sm:p-8 border-b border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-lg">3</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Data Orang Tua / Wali</h2>
                            <p class="text-xs text-slate-500">Data ayah dan ibu kandung atau wali murid</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- DATA AYAH -->
                        <div class="space-y-4 p-5 bg-slate-50/70 rounded-2xl border border-slate-200">
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="bi bi-person-fill text-blue-600"></i> DATA AYAH
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Ayah</label>
                                <input type="text" name="nama_ayah" placeholder="Nama lengkap ayah" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Lahir Ayah</label>
                                <input type="number" name="tahun_lahir_ayah" placeholder="Contoh: 1980" min="1930" max="2010" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Jenjang Pendidikan Ayah</label>
                                <select name="pendidikan_ayah" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                                    <option value="">-- Pilih Pendidikan --</option>
                                    <option value="SD / sederajat">SD / sederajat</option>
                                    <option value="SMP / sederajat">SMP / sederajat</option>
                                    <option value="SMA / sederajat">SMA / sederajat</option>
                                    <option value="D1 / D2 / D3">D1 / D2 / D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2 / S3">S2 / S3</option>
                                    <option value="Tidak sekolah">Tidak sekolah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">NIK Ayah</label>
                                <input type="text" name="nik_ayah" maxlength="16" placeholder="16 digit NIK Ayah" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                            </div>
                        </div>

                        <!-- DATA IBU -->
                        <div class="space-y-4 p-5 bg-slate-50/70 rounded-2xl border border-slate-200">
                            <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="bi bi-person-fill text-rose-500"></i> DATA IBU
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Ibu</label>
                                <input type="text" name="nama_ibu" placeholder="Nama lengkap ibu kandung" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Lahir Ibu</label>
                                <input type="number" name="tahun_lahir_ibu" placeholder="Contoh: 1985" min="1930" max="2010" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Jenjang Pendidikan Ibu</label>
                                <select name="pendidikan_ibu" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                                    <option value="">-- Pilih Pendidikan --</option>
                                    <option value="SD / sederajat">SD / sederajat</option>
                                    <option value="SMP / sederajat">SMP / sederajat</option>
                                    <option value="SMA / sederajat">SMA / sederajat</option>
                                    <option value="D1 / D2 / D3">D1 / D2 / D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2 / S3">S2 / S3</option>
                                    <option value="Tidak sekolah">Tidak sekolah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">NIK Ibu</label>
                                <input type="text" name="nik_ibu" maxlength="16" placeholder="16 digit NIK Ibu" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Step 4: Upload Berkas Persyaratan -->
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-lg">4</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Upload Berkas Persyaratan</h2>
                            <p class="text-xs text-slate-500">Format file: JPG, PNG, atau PDF (Maks. 5 MB per file)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        
                        <!-- 1. Ijazah Terakhir -->
                        <div id="wrapper-ijazah" class="p-4 rounded-2xl border-2 border-dashed border-slate-300 hover:border-blue-400 bg-slate-50 transition">
                            <label class="block cursor-pointer">
                                <span class="font-bold text-xs uppercase text-slate-800 flex items-center justify-between">
                                    <span>1. Ijazah Terakhir <span id="star-ijazah" class="text-rose-500">*</span></span>
                                    <i class="bi bi-file-earmark-arrow-up text-blue-600 text-lg"></i>
                                </span>
                                <span class="block text-[11px] text-slate-500 mt-1 mb-2">Ijazah SD/SMP/TK atau Surat Keterangan Lulus</span>
                                <input type="file" name="file_ijazah" id="file_ijazah" accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            </label>
                        </div>

                        <!-- 2. Akta Kelahiran -->
                        <div class="p-4 rounded-2xl border-2 border-dashed border-slate-300 hover:border-blue-400 bg-slate-50 transition">
                            <label class="block cursor-pointer">
                                <span class="font-bold text-xs uppercase text-slate-800 flex items-center justify-between">
                                    <span>2. Akta Kelahiran <span class="text-rose-500">*</span></span>
                                    <i class="bi bi-file-earmark-person text-blue-600 text-lg"></i>
                                </span>
                                <span class="block text-[11px] text-slate-500 mt-1 mb-2">Scan / Foto Akta Kelahiran Resmi Murid</span>
                                <input type="file" name="file_akta" required accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            </label>
                        </div>

                        <!-- 3. Kartu Keluarga -->
                        <div class="p-4 rounded-2xl border-2 border-dashed border-slate-300 hover:border-blue-400 bg-slate-50 transition">
                            <label class="block cursor-pointer">
                                <span class="font-bold text-xs uppercase text-slate-800 flex items-center justify-between">
                                    <span>3. Kartu Keluarga (KK) <span class="text-rose-500">*</span></span>
                                    <i class="bi bi-people text-blue-600 text-lg"></i>
                                </span>
                                <span class="block text-[11px] text-slate-500 mt-1 mb-2">Scan / Foto Kartu Keluarga Terbaru</span>
                                <input type="file" name="file_kk" required accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            </label>
                        </div>

                        <!-- 4. Surat Pindah -->
                        <div id="wrapper-surat-pindah" class="p-4 rounded-2xl border-2 border-dashed border-slate-300 hover:border-blue-400 bg-slate-50 transition">
                            <label class="block cursor-pointer">
                                <span class="font-bold text-xs uppercase text-slate-800 flex items-center justify-between">
                                    <span>4. Surat Pindah <span id="star-pindah" class="text-slate-400 font-normal">(Jika Pindahan)</span></span>
                                    <i class="bi bi-signpost-split text-blue-600 text-lg"></i>
                                </span>
                                <span class="block text-[11px] text-slate-500 mt-1 mb-2">Surat Keterangan Pindah & Rapor Terakhir</span>
                                <input type="file" name="file_surat_pindah" id="file_surat_pindah" accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            </label>
                        </div>

                    </div>

                    <!-- Persetujuan & Tombol Submit -->
                    <div class="mt-8 pt-6 border-t border-slate-200 space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" required class="mt-1 w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <span class="text-xs text-slate-600 leading-relaxed">
                                Saya menyatakan bahwa data yang diisikan dalam formulir pendaftaran ini adalah benar, sah, dan dapat dipertanggungjawabkan sesuai ketentuan administrasi PKBM Mandiri.
                            </span>
                        </label>

                        <button type="submit" id="btn-submit-ppdb" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-base rounded-2xl shadow-lg hover:shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                            <i class="bi bi-send-check-fill text-xl"></i>
                            <span>Kirim Pendaftaran Murid Baru</span>
                        </button>
                    </div>

                </div>

            </form>

        <?php endif; ?>

    </div>
</div>

<script>
let currentSelectedProgram = '';

function handleProgramChange(program) {
    currentSelectedProgram = program;
    const notice = document.getElementById('paket-a-notice');
    if (program.includes('Paket A')) {
        notice.classList.remove('hidden');
    } else {
        notice.classList.add('hidden');
    }
    togglePaketARequirements();
}

function calculateAge(birthDateStr) {
    if (!birthDateStr) return;
    const birthDate = new Date(birthDateStr);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    const display = document.getElementById('usia-display');
    if (display) {
        display.innerText = `Usia Calon Murid: ${age} Tahun`;
    }

    // Auto check jika umur > 14 tahun untuk Paket A
    const chkLewat = document.getElementById('chk-lewat-usia');
    if (chkLewat && currentSelectedProgram.includes('Paket A')) {
        if (age >= 13) {
            chkLewat.checked = true;
        } else {
            chkLewat.checked = false;
        }
        togglePaketARequirements();
    }
}

function togglePaketARequirements() {
    const isPaketA = currentSelectedProgram.includes('Paket A');
    const chkLewat = document.getElementById('chk-lewat-usia')?.checked;
    const katSD = document.querySelector('input[name="kategori_sd"]:checked')?.value;
    const kategoriGroup = document.getElementById('kategori-anak-sd');
    const starIjazah = document.getElementById('star-ijazah');
    const inpIjazah = document.getElementById('file_ijazah');
    const starPindah = document.getElementById('star-pindah');
    const inpPindah = document.getElementById('file_surat_pindah');

    if (isPaketA) {
        if (chkLewat) {
            // Warga belajar dewasa lewat usia SD -> Berkas ijazah TK dan surat pindah TIDAK WAJIB!
            if (kategoriGroup) kategoriGroup.classList.add('hidden');
            if (starIjazah) starIjazah.classList.add('hidden');
            if (inpIjazah) inpIjazah.required = false;
            if (starPindah) {
                starPindah.innerText = '(Bebas syarat untuk usia dewasa)';
                starPindah.classList.remove('text-rose-500');
            }
            if (inpPindah) inpPindah.required = false;
        } else {
            // Masih usia sekolah SD
            if (kategoriGroup) kategoriGroup.classList.remove('hidden');
            if (katSD === 'pindahan') {
                if (starIjazah) starIjazah.classList.add('hidden');
                if (inpIjazah) inpIjazah.required = false;
                if (starPindah) {
                    starPindah.innerText = '* Wajib untuk Pindahan SD';
                    starPindah.classList.add('text-rose-500');
                }
                if (inpPindah) inpPindah.required = true;
            } else {
                // Siswa baru usia SD -> Wajib sertakan ijazah TK/PAUD
                if (starIjazah) {
                    starIjazah.classList.remove('hidden');
                    starIjazah.innerText = '* Wajib (Ijazah TK/PAUD)';
                }
                if (inpIjazah) inpIjazah.required = true;
                if (starPindah) {
                    starPindah.innerText = '(Jika ada)';
                    starPindah.classList.remove('text-rose-500');
                }
                if (inpPindah) inpPindah.required = false;
            }
        }
    } else {
        // Paket B atau C -> Wajib Ijazah Terakhir (SD untuk B, SMP untuk C)
        if (starIjazah) {
            starIjazah.classList.remove('hidden');
            starIjazah.innerText = '*';
        }
        if (inpIjazah) inpIjazah.required = true;
        if (starPindah) {
            starPindah.innerText = '(Jika Pindahan)';
            starPindah.classList.remove('text-rose-500');
        }
        if (inpPindah) inpPindah.required = false;
    }
}
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

