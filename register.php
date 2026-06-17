<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (currentUser()) redirect('home.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['nama' => trim($_POST['nama'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'password' => $_POST['password'] ?? '', 'telepon' => trim($_POST['telepon'] ?? ''), 'alamat' => trim($_POST['alamat'] ?? '')];
    if (strlen($data['password']) < 6) $error = 'Password minimal 6 karakter.';
    elseif (empty($data['nama']) || empty($data['email']) || empty($data['telepon']) || empty($data['alamat'])) $error = 'Semua field wajib diisi.';
    else {
        $result = register($data);
        if ($result === true) { flash('success', 'Registrasi berhasil! Selamat belanja.'); redirect('home.php'); }
        $error = $result;
    }
}

$pageTitle = 'Daftar';
$minimalHeader = true;
if ($error) pushToast('error', $error);
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="mb-4 flex justify-center">
                <?php renderBrandLogo('xl'); ?>
            </div>
            <h1 class="text-2xl font-bold text-text-main">Gabung TaniExpress</h1>
            <p class="text-text-muted mt-2">Daftar dan nikmati sayur segar dari petani</p>
        </div>
        <div class="bg-white rounded-2xl p-8 tonal-shadow">
            <form method="POST" class="space-y-4">
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Nama Lengkap</label><input type="text" name="nama" required value="<?= e($_POST['nama'] ?? '') ?>" class="input-field"/></div>
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="input-field"/></div>
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">No. Telepon</label><input type="tel" name="telepon" required value="<?= e($_POST['telepon'] ?? '') ?>" class="input-field"/></div>
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Alamat</label><textarea name="alamat" required rows="2" class="input-field"><?= e($_POST['alamat'] ?? '') ?></textarea></div>
                <?php renderPasswordField('Password', 'password', ['required' => true, 'minlength' => 6, 'autocomplete' => 'new-password']); ?>
                <button type="submit" class="btn-primary w-full">Daftar & Mulai Belanja</button>
            </form>
            <p class="text-center text-sm text-text-muted mt-6">Sudah punya akun? <a href="login.php" class="text-primary font-semibold hover:underline">Masuk</a></p>
        </div>
    </div>
</main>

<?php $hideBottomNav = true; include __DIR__ . '/includes/app-footer.php'; ?>
