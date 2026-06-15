<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        db()->prepare('INSERT INTO petani (nama, telepon, alamat, foto) VALUES (?,?,?,?)')
            ->execute([$_POST['nama'], $_POST['telepon'], $_POST['alamat'], $_POST['foto']]);
        flash('success', 'Data petani ditambahkan.');
    } elseif ($action === 'update') {
        db()->prepare('UPDATE petani SET nama=?, telepon=?, alamat=?, foto=? WHERE id=?')
            ->execute([$_POST['nama'], $_POST['telepon'], $_POST['alamat'], $_POST['foto'], $_POST['id']]);
        flash('success', 'Data petani diperbarui.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM petani WHERE id=?')->execute([$_POST['id']]);
        flash('success', 'Petani dihapus.');
    }
    redirect('farmers.php');
}

$farmers = db()->query('SELECT p.*, COUNT(pp.id) AS jumlah_produk FROM petani p LEFT JOIN produk_petani pp ON pp.petani_id = p.id GROUP BY p.id ORDER BY p.nama')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM petani WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$pageTitle = 'Manajemen Petani';
$activeMenu = 'farmers';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 h-fit">
        <h3 class="font-semibold mb-4"><?= $edit ? 'Edit Petani' : 'Tambah Petani' ?></h3>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"/><?php endif; ?>
            <div><label class="text-xs font-medium">Nama</label><input name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="input-field text-sm mt-1"/></div>
            <div><label class="text-xs font-medium">Telepon</label><input name="telepon" required value="<?= e($edit['telepon'] ?? '') ?>" class="input-field text-sm mt-1"/></div>
            <div><label class="text-xs font-medium">Alamat</label><textarea name="alamat" rows="2" class="input-field text-sm mt-1"><?= e($edit['alamat'] ?? '') ?></textarea></div>
            <div><label class="text-xs font-medium">URL Foto</label><input name="foto" value="<?= e($edit['foto'] ?? '') ?>" class="input-field text-sm mt-1"/></div>
            <button type="submit" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-semibold"><?= $edit ? 'Update' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="farmers.php" class="block text-center text-sm text-text-muted mt-2">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($farmers as $f): ?>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30">
                <div class="flex gap-3">
                    <div class="w-12 h-12 rounded-full bg-leaf-green-light flex items-center justify-center text-primary font-bold flex-shrink-0">
                        <?= strtoupper(substr($f['nama'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold truncate"><?= e($f['nama']) ?></p>
                        <p class="text-xs text-text-muted"><?= e($f['telepon']) ?></p>
                        <p class="text-xs text-text-muted truncate"><?= e($f['alamat']) ?></p>
                        <p class="text-xs text-success-green mt-1"><?= $f['jumlah_produk'] ?> produk</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-4 text-sm">
                    <a href="?edit=<?= $f['id'] ?>" class="text-primary hover:underline">Edit</a>
                    <form method="POST" data-confirm="Hapus data petani ini?" data-confirm-title="Hapus Petani">
                        <input type="hidden" name="action" value="delete"/><input type="hidden" name="id" value="<?= $f['id'] ?>"/>
                        <button type="submit" class="text-error-red hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
