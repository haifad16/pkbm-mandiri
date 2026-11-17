<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/app.php';
}
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolFullName = getSetting('nama_lengkap_sekolah', APP_FULL_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$schoolAddress = getSetting('alamat', APP_ADDRESS);
$waNumber = getSetting('whatsapp', APP_WHATSAPP);
$phone = getSetting('telepon', APP_PHONE);
$email = getSetting('email', APP_EMAIL);
?>
    </main>

    <!-- Footer Section -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-8 border-t border-slate-800 mt-20 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                <!-- Col 1: Brand & Info -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xl">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <div class="font-bold text-lg text-white"><?= htmlspecialchars($schoolName) ?></div>
                            <div class="text-xs text-slate-400">Pendidikan Kesetaraan Nonformal Terakreditasi</div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-md">
                        <?= htmlspecialchars(getSetting('sambutan_kepala', 'Lembaga Pendidikan Nonformal yang melayani program kesetaraan Paket A (setara SD), Paket B (setara SMP), dan Paket C (setara SMA) di bawah naungan Yayasan Pendidikan Maju Mandiri Mataraman.')) ?>
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-900/60 text-blue-300 border border-blue-700/50">
                            <i class="bi bi-patch-check"></i> NPSN: <?= htmlspecialchars($schoolNpsn) ?>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">
                            <i class="bi bi-building-check"></i> Izin Operasional Kemendikbud
                        </span>
                    </div>
                </div>

                <!-- Col 2: Program Layanan -->
                <div>
                    <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4 border-l-2 border-blue-500 pl-3">
                        Program Pendidikan
                    </h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2 text-emerald-400"></i>
                            <a href="<?= BASE_URL ?>/index.php#program" class="hover:text-white transition">Paket A (Setara SD)</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2 text-emerald-400"></i>
                            <a href="<?= BASE_URL ?>/index.php#program" class="hover:text-white transition">Paket B (Setara SMP)</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2 text-emerald-400"></i>
                            <a href="<?= BASE_URL ?>/index.php#program" class="hover:text-white transition">Paket C (Setara SMA)</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2 text-emerald-400"></i>
                            <span>Keterampilan & Vokasi</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="bi bi-check2 text-emerald-400"></i>
                            <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="text-emerald-400 hover:underline">Pendaftaran Online</a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Kontak & Alamat -->
                <div>
                    <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4 border-l-2 border-blue-500 pl-3">
                        Kontak & Alamat
                    </h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-start gap-2.5">
                            <i class="bi bi-geo-alt-fill text-rose-400 mt-0.5"></i>
                            <span class="leading-relaxed"><?= htmlspecialchars($schoolAddress) ?></span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="bi bi-whatsapp text-emerald-400"></i>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waNumber) ?>" target="_blank" class="hover:text-white transition">
                                <?= htmlspecialchars($waNumber) ?>
                            </a>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="bi bi-envelope-fill text-sky-400"></i>
                            <span><?= htmlspecialchars($email) ?></span>
                        </li>
                        <li class="pt-2">
                            <a href="<?= BASE_URL ?>/modules/auth/login.php" class="inline-flex items-center gap-2 text-xs text-slate-400 hover:text-white border border-slate-700 rounded-lg px-3 py-1.5 transition">
                                <i class="bi bi-shield-lock-fill text-amber-400"></i>
                                <span>Portal Guru & Admin</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-800 text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    &copy; <?= date('Y') ?> <strong><?= htmlspecialchars($schoolFullName) ?></strong>. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="flex items-center gap-4">
                    <span>Sistem Informasi & CBT Online</span>
                    <span>•</span>
                    <span>Tahun Ajaran <?= htmlspecialchars(getSetting('tahun_ajaran', APP_YEAR)) ?></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

