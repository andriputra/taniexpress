<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$search = trim($_GET['q'] ?? '');
$filterKategori = trim($_GET['kategori'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$showAdd = isset($_GET['add']);
$editId = (int) ($_GET['edit'] ?? 0);
$listQuery = adminQueryString(['page' => $page > 1 ? $page : null, 'add' => null, 'edit' => null]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $sku = trim($_POST['sku'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $satuan = trim($_POST['satuan'] ?? '');
        $berat = trim($_POST['berat'] ?? '') ?: null;
        $deskripsi = trim($_POST['deskripsi'] ?? '') ?: null;
        $stok = max(0, (int) ($_POST['stok'] ?? 0));
        $gambar = $action === 'update' ? ($_POST['gambar_lama'] ?? null) : null;
        $editRedirect = $action === 'update'
            ? adminQueryString(['edit' => (int) ($_POST['id'] ?? 0), 'add' => null])
            : adminQueryString(['add' => 1]);

        if (empty($sku) || empty($nama) || empty($kategori) || empty($satuan)) {
            flash('error', 'SKU, nama, kategori, dan satuan wajib diisi.');
            redirect('products.php' . $editRedirect);
        }

        if (!empty($_FILES['gambar']['name'])) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                flash('error', 'Gagal mengunggah gambar. Coba lagi.');
                redirect('products.php' . $editRedirect);
            }
            $uploaded = uploadFile($_FILES['gambar'], 'produk');
            if (!$uploaded) {
                flash('error', 'Format gambar tidak valid. Gunakan JPG, PNG, atau WebP.');
                redirect('products.php' . $editRedirect);
            }
            $gambar = $uploaded;
        }

        try {
            if ($action === 'create') {
                db()->prepare('INSERT INTO products (sku, nama, kategori, satuan, berat, deskripsi, gambar, stok) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$sku, $nama, $kategori, $satuan, $berat, $deskripsi, $gambar, $stok]);
                $productId = (int) db()->lastInsertId();
                syncProdukPetaniStock($productId, $stok);
                flash('success', 'Produk berhasil ditambahkan.');
            } else {
                $productId = (int) $_POST['id'];
                db()->prepare('UPDATE products SET sku=?, nama=?, kategori=?, satuan=?, berat=?, deskripsi=?, gambar=?, stok=? WHERE id=?')
                    ->execute([$sku, $nama, $kategori, $satuan, $berat, $deskripsi, $gambar, $stok, $productId]);
                syncProdukPetaniStock($productId, $stok);
                flash('success', 'Produk berhasil diperbarui.');
            }
        } catch (PDOException $e) {
            flash('error', pdoErrorMessage($e));
            redirect('products.php' . $editRedirect);
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM products WHERE id=?')->execute([(int) $_POST['id']]);
        flash('success', 'Produk berhasil dihapus.');
    }

    redirect('products.php' . $listQuery);
}

$result = getAdminProducts($search ?: null, $filterKategori ?: null, $page);
$products = $result['items'];
$total = $result['total'];
$totalPages = $result['totalPages'];
$categories = getCategories();

$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}
$showForm = $showAdd || $edit;
$editImageSrc = $edit ? mediaSrc($edit['gambar'] ?? null, true) : null;

$pageTitle = 'Manajemen Produk';
$activeMenu = 'products';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<!-- Toolbar -->
<div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-outline-variant/30 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
        <form method="GET" class="flex-1 flex flex-col sm:flex-row gap-3" id="search-form">
            <?php if ($filterKategori): ?><input type="hidden" name="kategori" value="<?= e($filterKategori) ?>"/><?php endif; ?>
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input type="search" name="q" id="search-input" value="<?= e($search) ?>" placeholder="Cari SKU, nama, atau kategori..." class="input-field text-sm !pl-11 w-full"/>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container shrink-0">Cari</button>
            <?php if ($search): ?>
                <a href="products.php<?= $filterKategori ? '?kategori=' . urlencode($filterKategori) : '' ?>" class="px-5 py-2.5 border border-outline-variant rounded-full text-sm font-semibold text-text-muted hover:text-primary text-center shrink-0">Reset</a>
            <?php endif; ?>
        </form>
        <a href="products.php<?= adminQueryString(['add' => 1, 'edit' => null, 'page' => null]) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container shadow-sm shrink-0">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Produk
        </a>
    </div>

    <?php if ($categories): ?>
    <div class="admin-chip-scroll mt-4 pt-4 border-t border-outline-variant/20">
        <a href="products.php<?= $search ? '?q=' . urlencode($search) : '' ?>" class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors <?= !$filterKategori ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-leaf-green-light hover:text-primary' ?>">Semua</a>
        <?php foreach ($categories as $cat): ?>
            <a href="products.php?<?= http_build_query(array_filter(['q' => $search ?: null, 'kategori' => $cat])) ?>"
               class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors <?= $filterKategori === $cat ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-leaf-green-light hover:text-primary' ?>">
                <?= e($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($showForm): ?>
<!-- Form panel (tambah / edit) -->
<div class="bg-white rounded-2xl p-6 shadow-sm border border-primary/20 mb-6" id="product-form-panel">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-leaf-green-light flex items-center justify-center text-primary">
                <span class="material-symbols-outlined"><?= $edit ? 'edit' : 'add_box' ?></span>
            </span>
            <div>
                <h3 class="font-bold text-text-main"><?= $edit ? 'Edit Produk' : 'Tambah Produk Baru' ?></h3>
                <p class="text-xs text-text-muted"><?= $edit ? 'Perbarui data produk #' . e($edit['sku']) : 'Isi form untuk menambahkan produk' ?></p>
            </div>
        </div>
        <a href="products.php<?= $listQuery ?>" class="w-9 h-9 rounded-full flex items-center justify-center text-outline hover:bg-surface-container-low hover:text-text-main transition-colors" title="Tutup">
            <span class="material-symbols-outlined">close</span>
        </a>
    </div>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= $edit['id'] ?>"/>
            <input type="hidden" name="gambar_lama" value="<?= e($edit['gambar'] ?? '') ?>"/>
        <?php endif; ?>

        <div>
            <label class="block text-xs font-semibold text-text-muted mb-2">Foto Produk</label>
            <label for="gambar-input" class="group relative block cursor-pointer rounded-2xl border-2 border-dashed border-outline-variant/50 hover:border-primary/40 bg-surface-container-low/50 hover:bg-leaf-green-light/30 transition-all overflow-hidden">
                <input type="file" name="gambar" id="gambar-input" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only"/>
                <div id="image-preview" class="flex flex-col items-center justify-center py-6 px-4 min-h-[200px]">
                    <?php if ($editImageSrc): ?>
                        <img src="<?= e($editImageSrc) ?>" alt="" class="w-full max-w-[220px] h-36 rounded-xl object-cover shadow-md mb-3"/>
                        <p class="text-sm font-medium text-text-main">Klik untuk ganti gambar</p>
                    <?php else: ?>
                        <span class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-primary mb-3 shadow-sm">
                            <span class="material-symbols-outlined text-3xl">add_photo_alternate</span>
                        </span>
                        <p class="text-sm font-medium text-text-main">Upload foto produk</p>
                        <p class="text-xs text-text-muted mt-1">JPG, PNG, WebP — opsional</p>
                    <?php endif; ?>
                </div>
            </label>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">SKU</label>
                    <input name="sku" required value="<?= e($edit['sku'] ?? '') ?>" placeholder="VEG-009" class="input-field text-sm font-mono"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Nama Produk</label>
                    <input name="nama" required value="<?= e($edit['nama'] ?? '') ?>" placeholder="Bayam Hijau Segar" class="input-field text-sm"/>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Kategori</label>
                    <input name="kategori" required value="<?= e($edit['kategori'] ?? '') ?>" list="kategori-list" class="input-field text-sm"/>
                    <datalist id="kategori-list"><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>"/><?php endforeach; ?></datalist>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Satuan</label>
                    <input name="satuan" required value="<?= e($edit['satuan'] ?? 'pack') ?>" class="input-field text-sm"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Stok</label>
                    <input type="number" name="stok" required min="0" value="<?= (int) ($edit['stok'] ?? 0) ?>" class="input-field text-sm"/>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Berat</label>
                    <input name="berat" value="<?= e($edit['berat'] ?? '') ?>" placeholder="500g" class="input-field text-sm"/>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="2" class="input-field text-sm"><?= e($edit['deskripsi'] ?? '') ?></textarea>
            </div>
            <p class="text-[11px] text-text-muted mt-2">Stok ini disinkronkan ke listing petani di toko.</p>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container"><?= $edit ? 'Simpan Perubahan' : 'Simpan Produk' ?></button>
                <a href="products.php<?= $listQuery ?>" class="px-6 py-2.5 border border-outline-variant rounded-full text-sm font-semibold text-text-muted hover:text-primary">Batal</a>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Listing -->
<div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="px-5 py-4 border-b border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="font-semibold text-text-main">Daftar Produk</p>
            <p class="text-xs text-text-muted mt-0.5">
                Menampilkan <?= count($products) ?> dari <?= $total ?> produk
                <?php if ($search): ?> · pencarian "<strong><?= e($search) ?></strong>"<?php endif; ?>
                <?php if ($filterKategori): ?> · kategori <strong><?= e($filterKategori) ?></strong><?php endif; ?>
            </p>
        </div>
        <?php if ($totalPages > 1): ?>
            <p class="text-xs text-text-muted">Halaman <?= $page ?> / <?= $totalPages ?></p>
        <?php endif; ?>
    </div>

    <?php if (empty($products)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-5xl text-outline mb-3">inventory_2</span>
            <p class="font-semibold text-text-main"><?= $search || $filterKategori ? 'Produk tidak ditemukan' : 'Belum ada produk' ?></p>
            <p class="text-sm text-text-muted mt-1 mb-5"><?= $search || $filterKategori ? 'Coba kata kunci atau filter lain.' : 'Mulai dengan menambahkan produk pertama.' ?></p>
            <?php if ($search || $filterKategori): ?>
                <a href="products.php" class="inline-flex px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold">Lihat Semua Produk</a>
            <?php else: ?>
                <a href="products.php?add=1" class="inline-flex px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold">Tambah Produk</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-outline-variant/15">
            <?php foreach ($products as $p):
                $imageSrc = mediaSrc($p['gambar'] ?? null, true);
                $stokVal = (int) ($p['stok_toko'] ?? $p['stok'] ?? 0);
            ?>
                <div class="p-4 <?= $editId == $p['id'] ? 'bg-leaf-green-light/20' : '' ?>">
                    <div class="flex gap-3">
                        <?php if ($imageSrc): ?>
                            <img src="<?= e($imageSrc) ?>" alt="" class="w-14 h-14 rounded-xl object-cover ring-1 ring-outline-variant/30 shrink-0"/>
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-xl bg-surface-container-low flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-outline">image</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-text-main truncate"><?= e($p['nama']) ?></p>
                            <p class="text-[11px] font-mono text-outline"><?= e($p['sku']) ?></p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="px-2 py-0.5 rounded-full bg-leaf-green-light text-primary text-[10px] font-semibold"><?= e($p['kategori']) ?></span>
                                <span class="text-[11px] text-text-muted">Stok <?= $stokVal ?> · Listing <?= (int) $p['jumlah_listing'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <a href="products.php<?= adminQueryString(['edit' => $p['id'], 'add' => null]) ?>" class="flex-1 py-2 text-center text-sm font-semibold text-primary bg-leaf-green-light/60 rounded-full">Edit</a>
                        <form method="POST" class="flex-1" data-confirm="Hapus produk <?= e($p['nama']) ?>?" data-confirm-title="Hapus Produk">
                            <input type="hidden" name="action" value="delete"/>
                            <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                            <button type="submit" class="w-full py-2 text-sm font-semibold text-error-red border border-error-red/20 rounded-full">Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead class="bg-surface-container-low/80">
                    <tr class="text-left text-xs uppercase tracking-wide text-text-muted">
                        <th class="px-5 py-3 font-semibold w-16">Foto</th>
                        <th class="px-4 py-3 font-semibold">Produk</th>
                        <th class="px-4 py-3 font-semibold">Kategori</th>
                        <th class="px-4 py-3 font-semibold">Satuan</th>
                        <th class="px-4 py-3 font-semibold text-center">Stok</th>
                        <th class="px-4 py-3 font-semibold text-center">Listing</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/15">
                    <?php foreach ($products as $p):
                        $imageSrc = mediaSrc($p['gambar'] ?? null, true);
                    ?>
                        <tr class="hover:bg-leaf-green-light/15 transition-colors group <?= $editId == $p['id'] ? 'bg-leaf-green-light/25' : '' ?>">
                            <td class="px-5 py-3">
                                <?php if ($imageSrc): ?>
                                    <img src="<?= e($imageSrc) ?>" alt="" class="w-11 h-11 rounded-xl object-cover ring-1 ring-outline-variant/30"/>
                                <?php else: ?>
                                    <div class="w-11 h-11 rounded-xl bg-surface-container-low flex items-center justify-center">
                                        <span class="material-symbols-outlined text-outline text-[20px]">image</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-text-main group-hover:text-primary transition-colors"><?= e($p['nama']) ?></p>
                                <p class="text-[11px] font-mono text-outline mt-0.5"><?= e($p['sku']) ?></p>
                                <?php if ($p['berat']): ?>
                                    <p class="text-[11px] text-text-muted mt-0.5"><?= e($p['berat']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2.5 py-1 rounded-full bg-leaf-green-light text-primary text-[11px] font-semibold whitespace-nowrap"><?= e($p['kategori']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-on-surface-variant"><?= e($p['satuan']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $stokVal = (int) ($p['stok_toko'] ?? $p['stok'] ?? 0);
                                $stokClass = $stokVal === 0 ? 'bg-red-50 text-error-red' : ($stokVal < 10 ? 'bg-sun-tint text-on-tertiary-fixed' : 'bg-leaf-green-light text-primary');
                                ?>
                                <span class="inline-flex items-center justify-center min-w-[36px] h-7 px-2 rounded-full text-xs font-bold <?= $stokClass ?>">
                                    <?= $stokVal ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-full text-xs font-bold <?= $p['jumlah_listing'] > 0 ? 'bg-leaf-green-light text-primary' : 'bg-surface-container-high text-outline' ?>">
                                    <?= (int) $p['jumlah_listing'] ?>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="products.php<?= adminQueryString(['edit' => $p['id'], 'add' => null]) ?>" class="w-9 h-9 rounded-full flex items-center justify-center text-primary hover:bg-leaf-green-light transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form method="POST" data-confirm="Hapus produk <?= e($p['nama']) ?>? Tindakan tidak dapat dibatalkan." data-confirm-title="Hapus Produk">
                                        <input type="hidden" name="action" value="delete"/>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                                        <button type="submit" class="w-9 h-9 rounded-full flex items-center justify-center text-error-red hover:bg-red-50 transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="px-5 py-4 border-t border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-text-muted">
                <?= (($page - 1) * $result['perPage']) + 1 ?>–<?= min($page * $result['perPage'], $total) ?> dari <?= $total ?>
            </p>
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="products.php<?= adminQueryString(['page' => $page - 1, 'add' => null, 'edit' => null]) ?>" class="w-9 h-9 rounded-full flex items-center justify-center border border-outline-variant/40 hover:bg-leaf-green-light text-primary">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="products.php<?= adminQueryString(['page' => $i === 1 ? null : $i, 'add' => null, 'edit' => null]) ?>"
                       class="min-w-[36px] h-9 px-2 rounded-full flex items-center justify-center text-sm font-semibold transition-colors <?= $i === $page ? 'bg-primary text-white' : 'hover:bg-surface-container-low text-on-surface-variant' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="products.php<?= adminQueryString(['page' => $page + 1, 'add' => null, 'edit' => null]) ?>" class="w-9 h-9 rounded-full flex items-center justify-center border border-outline-variant/40 hover:bg-leaf-green-light text-primary">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function () {
    const input = document.getElementById('gambar-input');
    const preview = document.getElementById('image-preview');
    if (input && preview) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                window.TaniUI?.showToast('error', 'Ukuran gambar maksimal 2MB.');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-full max-w-[220px] h-36 rounded-xl object-cover shadow-md mb-3"/>' +
                    '<p class="text-sm font-medium text-text-main">Gambar siap diunggah</p>' +
                    '<p class="text-xs text-text-muted mt-1">' + file.name + '</p>';
            };
            reader.readAsDataURL(file);
        });
    }

    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');
    if (searchInput && searchForm) {
        let timer;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(() => searchForm.requestSubmit(), 400);
        });
    }

    <?php if ($showForm): ?>
    document.getElementById('product-form-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    <?php endif; ?>
})();
</script>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
