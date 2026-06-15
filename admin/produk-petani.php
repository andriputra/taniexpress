<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$openModal = isset($_GET['add']) || !empty($_GET['edit']);
$editId = (int) ($_GET['edit'] ?? 0);
$listQuery = adminQueryString(['page' => $page > 1 ? $page : null, 'add' => null, 'edit' => null]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $postListQuery = adminQueryString([
        'q' => trim($_POST['_q'] ?? '') ?: null,
        'page' => ((int) ($_POST['_page'] ?? 1)) > 1 ? (int) $_POST['_page'] : null,
        'add' => null,
        'edit' => null,
    ]);
    $errorRedirect = $action === 'update'
        ? adminQueryString([
            'edit' => (int) ($_POST['id'] ?? 0),
            'q' => trim($_POST['_q'] ?? '') ?: null,
            'page' => ((int) ($_POST['_page'] ?? 1)) > 1 ? (int) $_POST['_page'] : null,
        ])
        : adminQueryString([
            'add' => 1,
            'q' => trim($_POST['_q'] ?? '') ?: null,
            'page' => ((int) ($_POST['_page'] ?? 1)) > 1 ? (int) $_POST['_page'] : null,
        ]);

    try {
        if ($action === 'create' || $action === 'update') {
            $productId = (int) ($_POST['product_id'] ?? 0);
            $petaniId = (int) ($_POST['petani_id'] ?? 0);
            $stok = max(0, (int) ($_POST['stok'] ?? 0));
            $harga = (float) ($_POST['harga'] ?? 0);
            $excludeId = $action === 'update' ? (int) ($_POST['id'] ?? 0) : null;

            if (!$productId || !$petaniId) {
                flash('error', 'Pilih produk dan petani terlebih dahulu.');
                redirect('produk-petani.php' . $errorRedirect);
            }
            if ($harga <= 0) {
                flash('error', 'Harga harus lebih dari 0.');
                redirect('produk-petani.php' . $errorRedirect);
            }
            if (produkPetaniExists($productId, $petaniId, $excludeId)) {
                flash('error', 'Produk ini sudah dijual oleh petani yang dipilih. Edit listing yang ada atau pilih kombinasi lain.');
                redirect('produk-petani.php' . $errorRedirect);
            }

            if ($action === 'create') {
                db()->prepare('INSERT INTO produk_petani (product_id, petani_id, stok, harga) VALUES (?,?,?,?)')
                    ->execute([$productId, $petaniId, $stok, $harga]);
                refreshProductStockTotal($productId);
                flash('success', 'Listing produk-petani berhasil ditambahkan.');
                redirect('produk-petani.php' . $postListQuery);
            }

            db()->prepare('UPDATE produk_petani SET product_id=?, petani_id=?, stok=?, harga=? WHERE id=?')
                ->execute([$productId, $petaniId, $stok, $harga, $excludeId]);
            refreshProductStockTotal($productId);
            flash('success', 'Listing produk-petani berhasil diperbarui.');
            redirect('produk-petani.php' . $postListQuery);
        }

        if ($action === 'delete') {
            $row = db()->prepare('SELECT product_id FROM produk_petani WHERE id=?');
            $row->execute([(int) $_POST['id']]);
            $productId = (int) ($row->fetchColumn() ?: 0);
            db()->prepare('DELETE FROM produk_petani WHERE id=?')->execute([(int) $_POST['id']]);
            if ($productId) {
                refreshProductStockTotal($productId);
            }
            flash('success', 'Listing produk-petani berhasil dihapus.');
        }
    } catch (PDOException $e) {
        flash('error', pdoErrorMessage($e));
        redirect('produk-petani.php' . ($action === 'create' || $action === 'update' ? $errorRedirect : $postListQuery));
    }

    redirect('produk-petani.php' . $postListQuery);
}

$result = getAdminProdukPetani($search ?: null, $page);
$items = $result['items'];
$total = $result['total'];
$totalPages = $result['totalPages'];

$products = db()->query('SELECT id, nama FROM products ORDER BY nama')->fetchAll();
$farmers = db()->query('SELECT id, nama FROM petani ORDER BY nama')->fetchAll();

$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM produk_petani WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$modalMode = $edit ? 'edit' : 'create';
$modalPayload = $edit ? [
    'id' => (int) $edit['id'],
    'product_id' => (int) $edit['product_id'],
    'petani_id' => (int) $edit['petani_id'],
    'stok' => (int) $edit['stok'],
    'harga' => (float) $edit['harga'],
] : null;

$pageTitle = 'Manajemen Produk-Petani';
$activeMenu = 'produk-petani';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="min-w-0">
    <!-- Toolbar -->
    <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-outline-variant/30 mb-4 md:mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3 md:gap-4">
            <form method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 min-w-0">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari produk, petani, SKU, atau kategori..." class="input-field text-sm !pl-11 w-full"/>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container shrink-0">Cari</button>
                <?php if ($search): ?>
                    <a href="produk-petani.php" class="px-5 py-2.5 border border-outline-variant rounded-full text-sm font-semibold text-text-muted hover:text-primary text-center shrink-0">Reset</a>
                <?php endif; ?>
            </form>
            <button type="button" class="listing-modal-open inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container shadow-sm shrink-0" data-mode="create">
                <span class="material-symbols-outlined text-[18px]">add_link</span> Tambah Listing
            </button>
        </div>
    </div>

    <!-- Daftar -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="px-4 md:px-5 py-4 border-b border-outline-variant/20 flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="font-semibold text-text-main">Daftar Listing</p>
                <p class="text-xs text-text-muted mt-0.5">
                    Menampilkan <?= count($items) ?> dari <?= $total ?> listing
                    <?php if ($search): ?> · pencarian "<strong><?= e($search) ?></strong>"<?php endif; ?>
                </p>
            </div>
            <?php if ($totalPages > 1): ?>
                <p class="text-xs text-text-muted">Halaman <?= $page ?> / <?= $totalPages ?></p>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
            <div class="p-10 md:p-16 text-center">
                <span class="material-symbols-outlined text-5xl text-outline mb-3">link_off</span>
                <p class="font-semibold text-text-main"><?= $search ? 'Listing tidak ditemukan' : 'Belum ada listing' ?></p>
                <p class="text-sm text-text-muted mt-1 mb-5"><?= $search ? 'Coba kata kunci lain.' : 'Hubungkan produk dengan petani untuk mulai menjual di toko.' ?></p>
                <?php if ($search): ?>
                    <a href="produk-petani.php" class="inline-flex px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold">Lihat Semua</a>
                <?php else: ?>
                    <button type="button" class="listing-modal-open inline-flex px-5 py-2.5 bg-primary text-white rounded-full text-sm font-semibold" data-mode="create">Tambah Listing Pertama</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Mobile -->
            <div class="md:hidden divide-y divide-outline-variant/15">
                <?php foreach ($items as $item): ?>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <p class="font-semibold text-text-main leading-snug"><?= e($item['produk_nama']) ?></p>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-leaf-green-light text-primary font-semibold shrink-0"><?= e($item['kategori']) ?></span>
                        </div>
                        <p class="text-xs text-text-muted">Petani: <strong class="text-text-main"><?= e($item['petani_nama']) ?></strong></p>
                        <p class="text-[11px] font-mono text-outline mt-0.5"><?= e($item['sku']) ?></p>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-outline-variant/20">
                            <p class="text-sm text-text-muted">Stok <strong class="text-text-main"><?= $item['stok'] ?> <?= e($item['satuan']) ?></strong></p>
                            <p class="font-bold text-primary"><?= formatRupiah($item['harga']) ?></p>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="button" class="listing-modal-open flex-1 py-2 text-center text-sm font-semibold text-primary bg-leaf-green-light/60 rounded-full"
                                    data-mode="edit"
                                    data-id="<?= $item['id'] ?>"
                                    data-product-id="<?= $item['product_id'] ?>"
                                    data-petani-id="<?= $item['petani_id'] ?>"
                                    data-stok="<?= $item['stok'] ?>"
                                    data-harga="<?= $item['harga'] ?>">Edit</button>
                            <form method="POST" class="flex-1" data-confirm="Hapus listing <?= e($item['produk_nama']) ?> dari <?= e($item['petani_nama']) ?>?" data-confirm-title="Hapus Listing">
                                <input type="hidden" name="action" value="delete"/>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>"/>
                                <input type="hidden" name="_q" value="<?= e($search) ?>"/>
                                <input type="hidden" name="_page" value="<?= $page ?>"/>
                                <button type="submit" class="w-full py-2 text-sm font-semibold text-error-red border border-error-red/20 rounded-full">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Desktop -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low/80">
                        <tr class="text-left text-xs uppercase tracking-wide text-text-muted">
                            <th class="px-5 py-3 font-semibold">Produk</th>
                            <th class="px-4 py-3 font-semibold">Petani</th>
                            <th class="px-4 py-3 font-semibold text-right">Stok</th>
                            <th class="px-4 py-3 font-semibold text-right">Harga</th>
                            <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/15">
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-leaf-green-light/15 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-text-main"><?= e($item['produk_nama']) ?></p>
                                    <p class="text-[11px] font-mono text-outline mt-0.5"><?= e($item['sku']) ?></p>
                                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full bg-leaf-green-light text-primary text-[10px] font-semibold"><?= e($item['kategori']) ?></span>
                                </td>
                                <td class="px-4 py-3 text-on-surface-variant"><?= e($item['petani_nama']) ?></td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex items-center justify-center min-w-[40px] h-7 px-2 rounded-full text-xs font-bold <?= $item['stok'] < 10 ? 'bg-sun-tint text-on-tertiary-fixed' : 'bg-leaf-green-light text-primary' ?>">
                                        <?= $item['stok'] ?> <?= e($item['satuan']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-primary whitespace-nowrap"><?= formatRupiah($item['harga']) ?></td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" class="listing-modal-open w-9 h-9 rounded-full flex items-center justify-center text-primary hover:bg-leaf-green-light transition-colors" title="Edit"
                                                data-mode="edit"
                                                data-id="<?= $item['id'] ?>"
                                                data-product-id="<?= $item['product_id'] ?>"
                                                data-petani-id="<?= $item['petani_id'] ?>"
                                                data-stok="<?= $item['stok'] ?>"
                                                data-harga="<?= $item['harga'] ?>">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <form method="POST" data-confirm="Hapus listing ini?" data-confirm-title="Hapus Listing">
                                            <input type="hidden" name="action" value="delete"/>
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>"/>
                                            <input type="hidden" name="_q" value="<?= e($search) ?>"/>
                                            <input type="hidden" name="_page" value="<?= $page ?>"/>
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
            <div class="px-4 md:px-5 py-4 border-t border-outline-variant/20 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-text-muted">
                    <?= (($page - 1) * $result['perPage']) + 1 ?>–<?= min($page * $result['perPage'], $total) ?> dari <?= $total ?>
                </p>
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                        <a href="produk-petani.php<?= adminQueryString(['page' => $page - 1, 'add' => null, 'edit' => null]) ?>" class="w-9 h-9 rounded-full flex items-center justify-center border border-outline-variant/40 hover:bg-leaf-green-light text-primary">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2), $end = min($totalPages, $page + 2); $i <= $end; $i++): ?>
                        <a href="produk-petani.php<?= adminQueryString(['page' => $i === 1 ? null : $i, 'add' => null, 'edit' => null]) ?>"
                           class="min-w-[36px] h-9 px-2 rounded-full flex items-center justify-center text-sm font-semibold transition-colors <?= $i === $page ? 'bg-primary text-white' : 'hover:bg-surface-container-low text-on-surface-variant' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="produk-petani.php<?= adminQueryString(['page' => $page + 1, 'add' => null, 'edit' => null]) ?>" class="w-9 h-9 rounded-full flex items-center justify-center border border-outline-variant/40 hover:bg-leaf-green-light text-primary">
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal form listing -->
<div id="listing-modal" class="fixed inset-0 z-[220] hidden items-center justify-center p-4" aria-hidden="true" role="dialog">
    <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]" data-listing-modal-dismiss></div>
    <div id="listing-modal-dialog" class="relative bg-white rounded-3xl tonal-shadow max-w-md w-full max-h-[min(90vh,640px)] overflow-y-auto transform scale-95 opacity-0 transition-all duration-200">
        <div class="sticky top-0 bg-white/95 backdrop-blur border-b border-outline-variant/20 px-5 py-4 flex items-center justify-between gap-3 rounded-t-3xl z-10">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 rounded-xl bg-leaf-green-light flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined" id="listing-modal-icon">link</span>
                </span>
                <div class="min-w-0">
                    <h3 id="listing-modal-title" class="font-bold text-text-main truncate">Tambah Listing</h3>
                    <p id="listing-modal-subtitle" class="text-xs text-text-muted truncate">Hubungkan produk ke petani</p>
                </div>
            </div>
            <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-outline hover:bg-surface-container-low shrink-0" data-listing-modal-dismiss aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" id="listing-form" class="p-5 space-y-4">
            <input type="hidden" name="action" id="listing-form-action" value="create"/>
            <input type="hidden" name="id" id="listing-form-id" value="" disabled/>
            <input type="hidden" name="_q" value="<?= e($search) ?>"/>
            <input type="hidden" name="_page" value="<?= $page ?>"/>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5" for="listing-product-id">Produk</label>
                <select name="product_id" id="listing-product-id" required class="input-field text-sm">
                    <option value="">Pilih produk</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5" for="listing-petani-id">Petani</label>
                <select name="petani_id" id="listing-petani-id" required class="input-field text-sm">
                    <option value="">Pilih petani</option>
                    <?php foreach ($farmers as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= e($f['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5" for="listing-stok">Stok</label>
                    <input type="number" name="stok" id="listing-stok" required min="0" value="0" class="input-field text-sm"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5" for="listing-harga">Harga (Rp)</label>
                    <input type="number" name="harga" id="listing-harga" required min="1" step="1" class="input-field text-sm" placeholder="15000"/>
                </div>
            </div>
            <p class="text-[11px] text-text-muted">Satu produk hanya boleh punya satu listing per petani.</p>
            <div class="flex gap-3 pt-1">
                <button type="button" class="flex-1 py-3 border border-outline-variant rounded-full text-sm font-semibold text-text-muted hover:bg-surface-container-low" data-listing-modal-dismiss>Batal</button>
                <button type="submit" id="listing-form-submit" class="flex-1 py-3 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors">Simpan Listing</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('listing-modal');
    const dialog = document.getElementById('listing-modal-dialog');
    const form = document.getElementById('listing-form');
    const idField = document.getElementById('listing-form-id');
    const actionField = document.getElementById('listing-form-action');
    const titleEl = document.getElementById('listing-modal-title');
    const subtitleEl = document.getElementById('listing-modal-subtitle');
    const iconEl = document.getElementById('listing-modal-icon');
    const submitEl = document.getElementById('listing-form-submit');

    const fields = {
        product: document.getElementById('listing-product-id'),
        petani: document.getElementById('listing-petani-id'),
        stok: document.getElementById('listing-stok'),
        harga: document.getElementById('listing-harga'),
    };

    function fillForm(data) {
        fields.product.value = data.product_id || '';
        fields.petani.value = data.petani_id || '';
        fields.stok.value = data.stok ?? 0;
        fields.harga.value = data.harga ?? '';
    }

    function openModal(mode, data) {
        if (!modal || !dialog) return;
        const isEdit = mode === 'edit';
        actionField.value = isEdit ? 'update' : 'create';
        idField.disabled = !isEdit;
        idField.value = isEdit ? String(data.id || '') : '';
        titleEl.textContent = isEdit ? 'Edit Listing' : 'Tambah Listing';
        subtitleEl.textContent = isEdit ? 'Perbarui stok & harga' : 'Hubungkan produk ke petani';
        iconEl.textContent = isEdit ? 'edit' : 'link';
        submitEl.textContent = isEdit ? 'Simpan Perubahan' : 'Simpan Listing';
        if (isEdit) fillForm(data);
        else { form.reset(); fields.stok.value = '0'; }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(() => dialog.classList.remove('scale-95', 'opacity-0'));
        fields.product.focus();
    }

    function closeModal() {
        if (!modal || !dialog) return;
        dialog.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            if (window.location.search.includes('add=1') || window.location.search.includes('edit=')) {
                const url = new URL(window.location.href);
                url.searchParams.delete('add');
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url.pathname + url.search);
            }
        }, 180);
    }

    document.querySelectorAll('.listing-modal-open').forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.getAttribute('data-mode') || 'create';
            if (mode === 'edit') {
                openModal('edit', {
                    id: btn.getAttribute('data-id'),
                    product_id: btn.getAttribute('data-product-id'),
                    petani_id: btn.getAttribute('data-petani-id'),
                    stok: btn.getAttribute('data-stok'),
                    harga: btn.getAttribute('data-harga'),
                });
            } else {
                openModal('create', {});
            }
        });
    });

    modal?.querySelectorAll('[data-listing-modal-dismiss]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
    });

    <?php if ($openModal): ?>
    openModal(<?= json_encode($modalMode) ?>, <?= json_encode($modalPayload ?? []) ?>);
    <?php endif; ?>
})();
</script>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
