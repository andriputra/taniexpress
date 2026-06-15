<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        db()->prepare('INSERT INTO products (sku, nama, kategori, satuan, berat, deskripsi, gambar) VALUES (?,?,?,?,?,?,?)')
            ->execute([$_POST['sku'], $_POST['nama'], $_POST['kategori'], $_POST['satuan'], $_POST['berat'], $_POST['deskripsi'], $_POST['gambar']]);
        flash('success', 'Produk berhasil ditambahkan.');
    } elseif ($action === 'update') {
        db()->prepare('UPDATE products SET sku=?, nama=?, kategori=?, satuan=?, berat=?, deskripsi=?, gambar=? WHERE id=?')
            ->execute([$_POST['sku'], $_POST['nama'], $_POST['kategori'], $_POST['satuan'], $_POST['berat'], $_POST['deskripsi'], $_POST['gambar'], $_POST['id']]);
        flash('success', 'Produk berhasil diperbarui.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM products WHERE id=?')->execute([$_POST['id']]);
        flash('success', 'Produk berhasil dihapus.');
    }
    redirect('products.php');
}

$products = db()->query('SELECT * FROM products ORDER BY nama')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$pageTitle = 'Manajemen Produk';
$activeMenu = 'products';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 h-fit">
        <h3 class="font-semibold mb-4"><?= $edit ? 'Edit Produk' : 'Tambah Produk' ?></h3>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"/><?php endif; ?>
            <div>
                <label class="text-xs font-medium">SKU</label>
                <input name="sku" required value="<?= e($edit['sku'] ?? '') ?>" class="input-field text-sm mt-1"/>
            </div>
            <div>
                <label class="text-xs font-medium">Nama Produk</label>
                <input name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="input-field text-sm mt-1"/>
            </div>
            <div>
                <label class="text-xs font-medium">Kategori</label>
                <input name="kategori" required value="<?= e($edit['kategori'] ?? '') ?>" placeholder="Sayuran Hijau" class="input-field text-sm mt-1"/>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-medium">Satuan</label>
                    <input name="satuan" required value="<?= e($edit['satuan'] ?? 'pack') ?>" class="input-field text-sm mt-1"/>
                </div>
                <div>
                    <label class="text-xs font-medium">Berat</label>
                    <input name="berat" value="<?= e($edit['berat'] ?? '') ?>" placeholder="500g" class="input-field text-sm mt-1"/>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium">URL Gambar</label>
                <input name="gambar" value="<?= e($edit['gambar'] ?? '') ?>" class="input-field text-sm mt-1"/>
            </div>
            <div>
                <label class="text-xs font-medium">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="input-field text-sm mt-1"><?= e($edit['deskripsi'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-semibold"><?= $edit ? 'Update' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="products.php" class="block text-center text-sm text-text-muted mt-2">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold">SKU</th>
                        <th class="text-left px-4 py-3 font-semibold">Nama</th>
                        <th class="text-left px-4 py-3 font-semibold">Kategori</th>
                        <th class="text-left px-4 py-3 font-semibold">Satuan</th>
                        <th class="text-right px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-leaf-green-light/20">
                            <td class="px-4 py-3 font-mono text-xs"><?= e($p['sku']) ?></td>
                            <td class="px-4 py-3 font-medium"><?= e($p['nama']) ?></td>
                            <td class="px-4 py-3"><?= e($p['kategori']) ?></td>
                            <td class="px-4 py-3"><?= e($p['satuan']) ?></td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="?edit=<?= $p['id'] ?>" class="text-primary hover:underline">Edit</a>
                                <form method="POST" class="inline" data-confirm="Hapus produk ini? Tindakan tidak dapat dibatalkan." data-confirm-title="Hapus Produk">
                                    <input type="hidden" name="action" value="delete"/>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                                    <button type="submit" class="text-error-red hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
