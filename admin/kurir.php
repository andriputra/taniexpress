<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        db()->prepare('INSERT INTO kurir (nama, telepon, status) VALUES (?,?,?)')
            ->execute([$_POST['nama'], $_POST['telepon'], $_POST['status']]);
        flash('success', 'Kurir ditambahkan.');
    } elseif ($action === 'update') {
        db()->prepare('UPDATE kurir SET nama=?, telepon=?, status=? WHERE id=?')
            ->execute([$_POST['nama'], $_POST['telepon'], $_POST['status'], $_POST['id']]);
        flash('success', 'Kurir diperbarui.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM kurir WHERE id=?')->execute([$_POST['id']]);
        flash('success', 'Kurir dihapus.');
    }
    redirect('kurir.php');
}

$kurirs = db()->query("
    SELECT k.*, COUNT(p.id) AS active_delivery
    FROM kurir k LEFT JOIN pesanan p ON p.kurir_id = k.id AND p.status = 'dikirim'
    GROUP BY k.id ORDER BY k.nama
")->fetchAll();

$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM kurir WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$pageTitle = 'Manajemen Kurir';
$activeMenu = 'kurir';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 h-fit">
        <h3 class="font-semibold mb-4"><?= $edit ? 'Edit Kurir' : 'Tambah Kurir' ?></h3>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"/><?php endif; ?>
            <div><label class="text-xs font-medium">Nama Kurir</label><input name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="input-field text-sm mt-1"/></div>
            <div><label class="text-xs font-medium">No. Telepon</label><input name="telepon" required value="<?= e($edit['telepon'] ?? '') ?>" class="input-field text-sm mt-1"/></div>
            <div>
                <label class="text-xs font-medium">Status</label>
                <select name="status" class="input-field text-sm mt-1">
                    <option value="tersedia" <?= ($edit['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                    <option value="sibuk" <?= ($edit['status'] ?? '') === 'sibuk' ? 'selected' : '' ?>>Sibuk</option>
                </select>
            </div>
            <button type="submit" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-semibold"><?= $edit ? 'Update' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="kurir.php" class="block text-center text-sm text-text-muted mt-2">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($kurirs as $k): ?>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-container text-white flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                        </div>
                        <div>
                            <p class="font-semibold"><?= e($k['nama']) ?></p>
                            <p class="text-xs text-text-muted"><?= e($k['telepon']) ?></p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full <?= $k['status'] === 'tersedia' ? 'bg-leaf-green-light text-primary' : 'bg-tertiary-container text-white' ?>">
                        <?= ucfirst($k['status']) ?>
                    </span>
                </div>
                <p class="text-xs text-text-muted mt-3"><?= $k['active_delivery'] ?> pengantaran aktif</p>
                <div class="flex gap-3 mt-3 text-sm">
                    <a href="?edit=<?= $k['id'] ?>" class="text-primary hover:underline">Edit</a>
                    <form method="POST" data-confirm="Hapus data kurir ini?" data-confirm-title="Hapus Kurir">
                        <input type="hidden" name="action" value="delete"/><input type="hidden" name="id" value="<?= $k['id'] ?>"/>
                        <button type="submit" class="text-error-red hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
