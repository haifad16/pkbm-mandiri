<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

$pageTitle = 'Login / Masuk';

// Jika sudah login, langsung arahkan ke dashboard masing-masing
if (isLoggedIn()) {
    $role = $_SESSION['user_role'] ?? 'siswa';
    redirect("modules/{$role}/index.php");
}

$error = '';
$activeTab = $_GET['tab'] ?? 'siswa'; // 'siswa' atau 'staf'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'] ?? 'siswa';
    $pdo = getDB();

    if ($loginType === 'siswa') {
        // Login Murid via NIK
        $nik = trim($_POST['nik'] ?? '');
        $tglLahir = trim($_POST['tanggal_lahir'] ?? '');

        if (empty($nik)) {
            $error = 'Nomor Induk Kependudukan (NIK) wajib diisi.';
            $activeTab = 'siswa';
        } else {
            // Cari data siswa berdasarkan NIK
            $stmt = $pdo->prepare("SELECT * FROM students WHERE nik = ?");
            $stmt->execute([$nik]);
            $student = $stmt->fetch();

            if ($student) {
                // Opsional: cek tanggal lahir jika diinputkan
                if (!empty($tglLahir) && !empty($student['tanggal_lahir']) && $student['tanggal_lahir'] !== $tglLahir) {
                    $error = 'Tanggal lahir tidak sesuai dengan data terdaftar.';
                    $activeTab = 'siswa';
                } else {
                    // Berhasil login sebagai Siswa
                    $_SESSION['user_id'] = 'siswa_' . $student['id'];
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['user_role'] = 'siswa';
                    $_SESSION['user'] = [
                        'id' => $student['id'],
                        'full_name' => $student['nama'],
                        'nik' => $student['nik'],
                        'nisn' => $student['nisn'],
                        'rombel' => $student['rombel_saat_ini'],
                        'paket' => $student['paket'],
                        'role' => 'siswa'
                    ];

                    setFlash('success', "Selamat datang, {$student['nama']}! Anda berhasil masuk ke Portal Murid.");
                    redirect('modules/siswa/index.php');
                }
            } else {
                $error = 'NIK tidak terdaftar dalam pangkalan data siswa PKBM Mandiri. Pastikan nomor NIK Anda benar atau silakan mendaftar terlebih dahulu.';
                $activeTab = 'siswa';
            }
        }
    } else {
        // Login Staf (Guru / Admin)
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Username dan Password wajib diisi.';
            $activeTab = 'staf';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user'] = $user;

                // Jika guru, cari data teacher id
                if ($user['role'] === 'guru') {
                    $stmtTeacher = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ? OR nama_lengkap = ?");
                    $stmtTeacher->execute([$user['id'], $user['full_name']]);
                    $teacherId = $stmtTeacher->fetchColumn();
                    $_SESSION['teacher_id'] = $teacherId ?: 1;
                }

                setFlash('success', "Selamat datang kembali, {$user['full_name']}!");
                redirect("modules/{$user['role']}/index.php");
            } else {
                $error = 'Username atau Password salah. Silakan periksa kembali.';
                $activeTab = 'staf';
            }
        }
    }
}

require_once BASE_PATH . '/includes/header.php';
?>

<div class="py-12 sm:py-16 bg-gradient-to-b from-blue-50/50 via-slate-50 to-slate-100 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        
        <!-- Header Card -->
        <div class="bg-gradient-to-tr from-blue-800 to-indigo-700 p-8 text-white text-center relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-3xl mx-auto mb-4 border border-white/20 shadow-inner">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight">Portal PKBM Mandiri</h2>
            <p class="text-xs text-blue-100 mt-1">Pilih jenis akun untuk masuk ke sistem</p>
        </div>

        <!-- Tab Navigasi Login -->
        <div class="flex border-b border-slate-200 bg-slate-50/70 p-1.5 m-4 rounded-2xl">
            <button type="button" onclick="switchLoginTab('siswa')" id="tab-btn-siswa" 
                class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all <?= $activeTab === 'siswa' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">
                <i class="bi bi-person-fill text-base"></i>
                <span>Masuk Siswa (NIK)</span>
            </button>
            <button type="button" onclick="switchLoginTab('staf')" id="tab-btn-staf" 
                class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all <?= $activeTab === 'staf' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">
                <i class="bi bi-shield-check text-base"></i>
                <span>Guru & Admin</span>
            </button>
        </div>

        <div class="p-6 sm:p-8 pt-2">
            
            <?php if ($error): ?>
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-medium flex items-start gap-2.5">
                    <i class="bi bi-exclamation-circle-fill text-base text-rose-500 mt-0.5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- FORM 1: LOGIN SISWA (NIK) -->
            <form id="form-siswa" method="POST" action="" class="<?= $activeTab === 'siswa' ? '' : 'hidden' ?> space-y-4">
                <input type="hidden" name="login_type" value="siswa">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-credit-card-2-front absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="text" name="nik" required placeholder="Contoh: 6303120801110002" maxlength="16"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Masukkan 16 digit NIK yang tertera pada KTP atau Kartu Keluarga Anda.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Lahir <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-calendar-event absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="date" name="tanggal_lahir" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="bi bi-box-arrow-in-right text-lg"></i>
                    <span>Masuk ke Portal Siswa</span>
                </button>

                <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3 text-xs text-blue-800">
                    <div class="font-bold flex items-center gap-1.5 mb-0.5">
                        <i class="bi bi-lightbulb-fill text-amber-500"></i> Informasi Siswa:
                    </div>
                    Siswa terdaftar cukup memasukkan NIK untuk melihat biodata lengkap, kartu pelajar, rombel, dan mengerjakan ujian kesetaraan.
                </div>
            </form>

            <!-- FORM 2: LOGIN GURU & ADMIN -->
            <form id="form-staf" method="POST" action="" class="<?= $activeTab === 'staf' ? '' : 'hidden' ?> space-y-4">
                <input type="hidden" name="login_type" value="staf">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Username <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-person absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="text" name="username" required placeholder="Username admin atau guru"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-lock absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="bi bi-shield-lock text-lg"></i>
                    <span>Masuk sebagai Pendidik / Admin</span>
                </button>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-600">
                    <span class="font-bold text-slate-800">Akun Default Sistem:</span><br>
                    • Admin: <code class="text-blue-600 font-bold">admin</code> / <code class="text-blue-600 font-bold">admin123</code><br>
                    • Tutor: <code class="text-emerald-600 font-bold">guru</code> / <code class="text-emerald-600 font-bold">guru123</code>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
                Belum terdaftar sebagai murid? 
                <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="text-emerald-600 font-bold hover:underline">
                    Daftar Murid Baru Disini
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function switchLoginTab(tab) {
    const formSiswa = document.getElementById('form-siswa');
    const formStaf = document.getElementById('form-staf');
    const btnSiswa = document.getElementById('tab-btn-siswa');
    const btnStaf = document.getElementById('tab-btn-staf');

    if (tab === 'siswa') {
        formSiswa.classList.remove('hidden');
        formStaf.classList.add('hidden');
        btnSiswa.classList.add('bg-white', 'text-blue-700', 'shadow-sm');
        btnSiswa.classList.remove('text-slate-500');
        btnStaf.classList.remove('bg-white', 'text-blue-700', 'shadow-sm');
        btnStaf.classList.add('text-slate-500');
    } else {
        formSiswa.classList.add('hidden');
        formStaf.classList.remove('hidden');
        btnStaf.classList.add('bg-white', 'text-blue-700', 'shadow-sm');
        btnStaf.classList.remove('text-slate-500');
        btnSiswa.classList.remove('bg-white', 'text-blue-700', 'shadow-sm');
        btnSiswa.classList.add('text-slate-500');
    }
}
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

