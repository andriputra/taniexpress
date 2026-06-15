<?php
require_once __DIR__ . '/includes/bootstrap.php';

$search = trim($_GET['q'] ?? '');
$kategori = $_GET['kategori'] ?? 'all';
$petaniId = !empty($_GET['petani']) ? (int) $_GET['petani'] : null;

$products = getProducts($search ?: null, $kategori, $petaniId);
$categories = getCategories();
$petaniList = db()->query('SELECT id, nama FROM petani ORDER BY nama')->fetchAll();

$pageTitle = 'Belanja';
$activeNav = 'belanja';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <div class="page-container pt-6">
        <!-- Search -->
        <form method="GET" class="relative mb-6">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input name="q" value="<?= e($search) ?>" type="text" placeholder="Cari produk atau petani..." class="input-field !py-4 !pl-12 shadow-sm"/>
            <?php if ($kategori !== 'all'): ?><input type="hidden" name="kategori" value="<?= e($kategori) ?>"/><?php endif; ?>
            <?php if ($petaniId): ?><input type="hidden" name="petani" value="<?= $petaniId ?>"/><?php endif; ?>
        </form>

        <!-- Kategori chips -->
        <div class="overflow-x-auto hide-scrollbar mb-5">
            <div class="flex gap-2 pb-1">
                <a href="home.php<?= $search ? '?q=' . urlencode($search) : '' ?>" class="chip <?= $kategori === 'all' ? 'chip-active' : 'chip-inactive' ?>">Semua</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="home.php?kategori=<?= urlencode($cat) ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $petaniId ? '&petani=' . $petaniId : '' ?>"
                       class="chip <?= $kategori === $cat ? 'chip-active' : 'chip-inactive' ?>"><?= e($cat) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filter petani -->
        <?php if ($petaniList): ?>
        <form method="GET" class="flex items-center gap-3 mb-6 p-4 bg-white rounded-xl tonal-shadow">
            <span class="material-symbols-outlined text-primary">agriculture</span>
            <select name="petani" onchange="this.form.submit()" class="input-field !py-2 flex-1">
                <option value="">Semua Petani</option>
                <?php foreach ($petaniList as $pt): ?>
                    <option value="<?= $pt['id'] ?>" <?= $petaniId == $pt['id'] ? 'selected' : '' ?>><?= e($pt['nama']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"/><?php endif; ?>
            <?php if ($kategori !== 'all'): ?><input type="hidden" name="kategori" value="<?= e($kategori) ?>"/><?php endif; ?>
        </form>
        <?php endif; ?>

        <!-- Hasil -->
        <div class="flex items-center justify-between mb-5">
            <p class="text-sm text-text-muted"><span class="font-semibold text-text-main"><?= count($products) ?></span> produk ditemukan</p>
        </div>

        <?php if (empty($products)): ?>
            <?php renderEmptyState('search_off', 'Produk Tidak Ditemukan', 'Coba kata kunci lain atau ubah filter kategori.', 'Lihat Semua Produk', 'home.php'); ?>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <?php foreach ($products as $p): renderProductCard($p); endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
