<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (currentUser()) {
    redirect(currentUser()['role'] === 'admin' ? 'admin/index.php' : 'home.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        $user = currentUser();
        flash('success', 'Selamat datang, ' . $user['nama'] . '!');
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'home.php');
    }
    $error = 'Email atau password salah.';
}

$pageTitle = 'Masuk';
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
            <h1 class="text-2xl font-bold text-text-main">Selamat Datang Kembali</h1>
            <p class="text-text-muted mt-2">Masuk untuk melanjutkan belanja</p>
        </div>
        <div class="bg-white rounded-2xl p-8 tonal-shadow">
            <form method="POST" class="space-y-4">
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="input-field"/></div>
                <?php renderPasswordField('Password', 'password', ['required' => true, 'autocomplete' => 'current-password']); ?>
                <button type="submit" class="btn-primary w-full">Masuk</button>
            </form>
            <p class="text-center text-sm text-text-muted mt-6">Belum punya akun? <a href="register.php" class="text-primary font-semibold hover:underline">Daftar</a></p>
            <p class="text-center text-xs text-outline mt-3">Admin? <a href="admin/login.php" class="text-primary">Login Admin</a></p>
        </div>
    </div>
</main>

<?php $hideBottomNav = true; include __DIR__ . '/includes/app-footer.php'; ?>
