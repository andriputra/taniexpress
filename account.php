<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireCustomer();

$user = currentUser();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'password') {
        $result = updateUserPassword(
            (int) $user['id'],
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
        flash($result['ok'] ? 'success' : 'error', $result['message']);
    } else {
        updateUserProfile(
            (int) $user['id'],
            $_POST['nama'] ?? '',
            $_POST['telepon'] ?? '',
            $_POST['alamat'] ?? ''
        );
        flash('success', 'Profil berhasil diperbarui.');
    }

    redirect('account.php');
}
$user = currentUser();

$pageTitle = 'Akun Saya';
$activeNav = 'account';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <div class="page-container pt-6 max-w-lg mx-auto">
        <h1 class="text-headline-lg-mobile font-bold text-text-main mb-6">Akun Saya</h1>

        <div class="bg-white rounded-2xl p-6 tonal-shadow mb-4">
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-outline-variant/30">
                <div class="w-16 h-16 rounded-2xl bg-primary-container text-white flex items-center justify-center text-2xl font-bold"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
                <div>
                    <p class="font-bold text-lg text-text-main"><?= e($user['nama']) ?></p>
                    <p class="text-sm text-text-muted"><?= e($user['email']) ?></p>
                </div>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="profile"/>
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Nama</label><input type="text" name="nama" required value="<?= e($user['nama']) ?>" class="input-field"/></div>
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Telepon</label><input type="tel" name="telepon" required value="<?= e($user['telepon']) ?>" class="input-field"/></div>
                <div><label class="block text-sm font-medium text-text-muted mb-1.5">Alamat</label><textarea name="alamat" required rows="3" class="input-field"><?= e($user['alamat']) ?></textarea></div>
                <button type="submit" class="btn-primary w-full">Simpan Perubahan</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 tonal-shadow mb-4">
            <h2 class="font-bold text-text-main mb-1">Ubah Password</h2>
            <p class="text-sm text-text-muted mb-5">Gunakan password minimal 6 karakter.</p>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="password"/>
                <?php renderPasswordField('Password Lama', 'current_password', ['required' => true, 'autocomplete' => 'current-password']); ?>
                <?php renderPasswordField('Password Baru', 'new_password', ['required' => true, 'minlength' => 6, 'autocomplete' => 'new-password']); ?>
                <?php renderPasswordField('Konfirmasi Password Baru', 'confirm_password', ['required' => true, 'minlength' => 6, 'autocomplete' => 'new-password']); ?>
                <button type="submit" class="w-full py-3 border border-primary text-primary rounded-full text-sm font-semibold hover:bg-leaf-green-light transition-colors">Perbarui Password</button>
            </form>
        </div>

        <div class="space-y-3">
            <a href="orders.php" class="flex items-center gap-4 p-4 bg-white rounded-2xl tonal-shadow hover:border-primary/20 border border-transparent transition-all">
                <span class="w-10 h-10 rounded-xl bg-leaf-green-light flex items-center justify-center"><span class="material-symbols-outlined text-primary">receipt_long</span></span>
                <span class="font-medium flex-1">Riwayat Pesanan</span>
                <span class="material-symbols-outlined text-outline">chevron_right</span>
            </a>
            <a href="cart.php" class="flex items-center gap-4 p-4 bg-white rounded-2xl tonal-shadow hover:border-primary/20 border border-transparent transition-all">
                <span class="w-10 h-10 rounded-xl bg-leaf-green-light flex items-center justify-center"><span class="material-symbols-outlined text-primary">shopping_basket</span></span>
                <span class="font-medium flex-1">Keranjang Belanja</span>
                <span class="material-symbols-outlined text-outline">chevron_right</span>
            </a>
            <a href="logout.php" class="flex items-center gap-4 p-4 bg-white rounded-2xl tonal-shadow hover:border-red-200 border border-transparent transition-all text-error-red">
                <span class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center"><span class="material-symbols-outlined">logout</span></span>
                <span class="font-medium flex-1">Keluar</span>
            </a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
