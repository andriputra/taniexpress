<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        db()->prepare('INSERT INTO produk_petani (product_id, petani_id, stok, harga) VALUES (?,?,?,?)')
            ->execute([$_POST['product_id'], $_POST['petani_id'], $_POST['stok'], $_POST['harga']]);
        flash('success', 'Produk-petani ditambahkan.');
    } elseif ($action === 'update') {
        db()->prepare('UPDATE produk_petani SET product_id=?, petani_id=?, stok=?, harga=? WHERE id=?')
            ->execute([$_POST['product_id'], $_POST['petani_id'], $_POST['stok'], $_POST['harga'], $_POST['id']]);
        flash('success', 'Produk-petani diperbarui.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM produk_petani WHERE id=?')->execute([$_POST['id']]);
        flash('success', 'Data dihapus.');
    }
    redirect('produk-petani.php');
}

$items = db()->query("
    SELECT pp.*, p.nama AS produk_nama, p.satuan, pt.nama AS petani_nama
    FROM produk_petani pp
    JOIN products p ON p.id = pp.product_id
    JOIN petani pt ON pt.id = pp.petani_id
    ORDER BY p.nama, pt.nama
")->fetchAll();

$products = db()->query('SELECT id, nama FROM products ORDER BY nama')->fetchAll();
$farmers = db()->query('SELECT id, nama FROM petani ORDER BY nama')->fetchAll();

$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM produk_petani WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$pageTitle = 'Manajemen Produk-Petani';
$activeMenu = 'produk-petani';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 h-fit">
        <h3 class="font-semibold mb-4"><?= $edit ? 'Edit Stok & Harga' : 'Setup Produk Petani' ?></h3>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"/><?php endif; ?>
            <div>
                <label class="text-xs font-medium">Produk</label>
                <select name="product_id" required class="input-field text-sm mt-1">
                    <option value="">Pilih Produk</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($edit['product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium">Petani</label>
                <select name="petani_id" required class="input-field text-sm mt-1">
                    <option value="">Pilih Petani</option>
                    <?php foreach ($farmers as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($edit['petani_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= e($f['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="text-xs font-medium">Stok</label><input type="number" name="stok" required min="0" value="<?= e($edit['stok'] ?? '0') ?>" class="input-field text-sm mt-1"/></div>
                <div><label class="text-xs font-medium">Harga (Rp)</label><input type="number" name="harga" required min="0" value="<?= e($edit['harga'] ?? '') ?>" class="input-field text-sm mt-1"/></div>
            </div>
            <button type="submit" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-semibold"><?= $edit ? 'Update' : 'Simpan' ?></button>
            <?php if ($edit): ?><a href="produk-petani.php" class="block text-center text-sm text-text-muted mt-2">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low">
                <tr>
                    <th class="text-left px-4 py-3">Produk</th>
                    <th class="text-left px-4 py-3">Petani</th>
                    <th class="text-right px-4 py-3">Stok</th>
                    <th class="text-right px-4 py-3">Harga</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                <?php foreach ($items as $item): ?>
                    <tr class="hover:bg-leaf-green-light/20">
                        <td class="px-4 py-3 font-medium"><?= e($item['produk_nama']) ?></td>
                        <td class="px-4 py-3"><?= e($item['petani_nama']) ?></td>
                        <td class="px-4 py-3 text-right"><?= $item['stok'] ?> <?= e($item['satuan']) ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-primary"><?= formatRupiah($item['harga']) ?></td>
                        <td class="px-4 py-3 text-right">
                            <a href="?edit=<?= $item['id'] ?>" class="text-primary hover:underline mr-2">Edit</a>
                            <form method="POST" class="inline" data-confirm="Hapus data produk-petani ini?" data-confirm-title="Hapus Data">
                                <input type="hidden" name="action" value="delete"/><input type="hidden" name="id" value="<?= $item['id'] ?>"/>
                                <button type="submit" class="text-error-red hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
