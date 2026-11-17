<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/pendaftaran/index.php');
}

$pdo = getDB();

// Tangkap input
$paketPilihan = sanitize($_POST['paket_pilihan'] ?? '');
$nama = sanitize($_POST['nama'] ?? '');
$jk = sanitize($_POST['jk'] ?? '');
$nisn = sanitize($_POST['nisn'] ?? '');
$tempatLahir = sanitize($_POST['tempat_lahir'] ?? '');
$tanggalLahir = sanitize($_POST['tanggal_lahir'] ?? '');
$nik = sanitize($_POST['nik'] ?? '');
$agama = sanitize($_POST['agama'] ?? 'Islam');
$hp = sanitize($_POST['hp'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$asalSekolah = sanitize($_POST['asal_sekolah'] ?? '');
$noKK = sanitize($_POST['no_kk'] ?? '');
$alamat = sanitize($_POST['alamat'] ?? '');
$rt = sanitize($_POST['rt'] ?? '');
$rw = sanitize($_POST['rw'] ?? '');
$dusun = sanitize($_POST['dusun'] ?? '');
$kelurahan = sanitize($_POST['kelurahan'] ?? '');
$kecamatan = sanitize($_POST['kecamatan'] ?? 'Kec. Mataraman');

// Data Orang Tua
$namaAyah = sanitize($_POST['nama_ayah'] ?? '');
$tahunLahirAyah = sanitize($_POST['tahun_lahir_ayah'] ?? '');
$pendidikanAyah = sanitize($_POST['pendidikan_ayah'] ?? '');
$nikAyah = sanitize($_POST['nik_ayah'] ?? '');

$namaIbu = sanitize($_POST['nama_ibu'] ?? '');
$tahunLahirIbu = sanitize($_POST['tahun_lahir_ibu'] ?? '');
$pendidikanIbu = sanitize($_POST['pendidikan_ibu'] ?? '');
$nikIbu = sanitize($_POST['nik_ibu'] ?? '');

// Logika Paket A
$isLewatUsia = !empty($_POST['is_lewat_usia_sd']) ? 'lewat_usia_sekolah' : 'usia_sekolah';
$kategoriSd = sanitize($_POST['kategori_sd'] ?? 'siswa_baru');
$usia = hitungUsia($tanggalLahir);

// Validasi Dasar
if (empty($nama) || empty($nik) || empty($tanggalLahir) || empty($paketPilihan) || empty($noKK) || empty($alamat)) {
    setFlash('danger', 'Mohon lengkapi semua kolom bertanda bintang (*) pada formulir pendaftaran.');
    redirect('modules/pendaftaran/index.php');
}

// Cek apakah NIK sudah pernah mendaftar
$stmtCheck = $pdo->prepare("SELECT no_pendaftaran FROM registrations WHERE nik = ?");
$stmtCheck->execute([$nik]);
$existingReg = $stmtCheck->fetchColumn();

if ($existingReg) {
    setFlash('warning', "NIK {$nik} sudah pernah melakukan pendaftaran dengan Nomor: {$existingReg}. Anda dapat mencetak ulang bukti pendaftaran.");
    redirect("modules/pendaftaran/sukses.php?no={$existingReg}");
}

// Upload Berkas
$targetDir = UPLOAD_BERKAS_DIR;
$fileIjazah = '';
$fileAkta = '';
$fileKK = '';
$fileSuratPindah = '';

// 1. Ijazah
if (!empty($_FILES['file_ijazah']['name'])) {
    $up = uploadFile($_FILES['file_ijazah'], $targetDir);
    if ($up['success']) {
        $fileIjazah = $up['filename'];
    }
}

// 2. Akta
if (!empty($_FILES['file_akta']['name'])) {
    $up = uploadFile($_FILES['file_akta'], $targetDir);
    if ($up['success']) {
        $fileAkta = $up['filename'];
    }
}

// 3. KK
if (!empty($_FILES['file_kk']['name'])) {
    $up = uploadFile($_FILES['file_kk'], $targetDir);
    if ($up['success']) {
        $fileKK = $up['filename'];
    }
}

// 4. Surat Pindah
if (!empty($_FILES['file_surat_pindah']['name'])) {
    $up = uploadFile($_FILES['file_surat_pindah'], $targetDir);
    if ($up['success']) {
        $fileSuratPindah = $up['filename'];
    }
}

// Generate Nomor Pendaftaran
$noPendaftaran = generateNoPendaftaran();

try {
    $stmt = $pdo->prepare("INSERT INTO registrations (
        no_pendaftaran, nama_lengkap, jk, nisn, tempat_lahir, tanggal_lahir, nik, agama,
        alamat, rt, rw, dusun, kelurahan, kecamatan, hp, email, asal_sekolah, no_kk,
        paket_pilihan, nama_ayah, tahun_lahir_ayah, pendidikan_ayah, nik_ayah,
        nama_ibu, tahun_lahir_ibu, pendidikan_ibu, nik_ibu,
        usia_tahun, status_usia_sd, kategori_pendaftaran,
        file_ijazah, file_akta, file_kk, file_surat_pindah, status_verifikasi
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?, 'menunggu'
    )");

    $stmt->execute([
        $noPendaftaran, $nama, $jk, $nisn, $tempatLahir, $tanggalLahir, $nik, $agama,
        $alamat, $rt, $rw, $dusun, $kelurahan, $kecamatan, $hp, $email, $asalSekolah, $noKK,
        $paketPilihan, $namaAyah, $tahunLahirAyah, $pendidikanAyah, $nikAyah,
        $namaIbu, $tahunLahirIbu, $pendidikanIbu, $nikIbu,
        $usia, $isLewatUsia, $kategoriSd,
        $fileIjazah, $fileAkta, $fileKK, $fileSuratPindah
    ]);

    setFlash('success', 'Pendaftaran Anda berhasil dikirim! Silakan simpan dan cetak Bukti Pendaftaran ini.');
    redirect("modules/pendaftaran/sukses.php?no={$noPendaftaran}");

} catch (Exception $e) {
    setFlash('danger', 'Terjadi kesalahan saat menyimpan pendaftaran: ' . $e->getMessage());
    redirect('modules/pendaftaran/index.php');
}

