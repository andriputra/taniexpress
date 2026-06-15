<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

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
$activeMenu = 'account';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="max-w-2xl">
    <div class="bg-white rounded-2xl p-6 tonal-shadow mb-6">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-outline-variant/30">
            <div class="w-16 h-16 rounded-2xl bg-primary-container text-white flex items-center justify-center text-2xl font-bold">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div>
                <p class="font-bold text-lg text-text-main"><?= e($user['nama']) ?></p>
                <p class="text-sm text-text-muted"><?= e($user['email']) ?></p>
            </div>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="profile"/>
            <div>
                <label class="block text-sm font-medium text-text-muted mb-1.5">Nama</label>
                <input type="text" name="nama" required value="<?= e($user['nama']) ?>" class="input-field"/>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-muted mb-1.5">Telepon</label>
                <input type="tel" name="telepon" required value="<?= e($user['telepon']) ?>" class="input-field"/>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-muted mb-1.5">Alamat</label>
                <textarea name="alamat" required rows="3" class="input-field"><?= e($user['alamat']) ?></textarea>
            </div>
            <button type="submit" class="w-full py-3 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl p-6 tonal-shadow">
        <h3 class="font-bold text-text-main mb-1">Ubah Password</h3>
        <p class="text-sm text-text-muted mb-5">Gunakan password minimal 6 karakter.</p>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="password"/>
            <?php renderPasswordField('Password Lama', 'current_password', ['required' => true, 'autocomplete' => 'current-password']); ?>
            <?php renderPasswordField('Password Baru', 'new_password', ['required' => true, 'minlength' => 6, 'autocomplete' => 'new-password']); ?>
            <?php renderPasswordField('Konfirmasi Password Baru', 'confirm_password', ['required' => true, 'minlength' => 6, 'autocomplete' => 'new-password']); ?>
            <button type="submit" class="w-full py-3 border border-primary text-primary rounded-full text-sm font-semibold hover:bg-leaf-green-light transition-colors">
                Perbarui Password
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
