<?php
require_once __DIR__ . '/includes/bootstrap.php';

$search = trim($_GET['q'] ?? '');
$kategori = $_GET['kategori'] ?? 'all';
$petaniId = !empty($_GET['petani']) ? (int) $_GET['petani'] : null;

$products = getProducts($search ?: null, $kategori, $petaniId);
$categories = getCategories();
$petaniList = db()->query('SELECT id, nama FROM petani ORDER BY nama')->fetchAll();
$petaniProfile = $petaniId ? getPetaniDetail($petaniId) : null;
$petaniPhotoSrc = $petaniProfile ? mediaSrc($petaniProfile['foto'] ?? null) : null;
$petaniProfilText = '';
if ($petaniProfile) {
    $petaniProfilText = trim($petaniProfile['profil_petani'] ?? '');
    if ($petaniProfilText === '') {
        $petaniProfilText = trim($petaniProfile['cerita_petani'] ?? '');
    }
}

$pageTitle = $petaniProfile ? 'Produk ' . $petaniProfile['nama'] : 'Belanja';
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

        <?php if ($petaniProfile): ?>
        <section class="mb-6 rounded-2xl overflow-hidden border border-outline-variant/25 tonal-shadow bg-white">
            <div class="bg-leaf-green-light/50 px-5 py-3 flex items-center gap-2 border-b border-outline-variant/20">
                <span class="material-symbols-outlined text-primary text-[20px]">agriculture</span>
                <h2 class="text-sm font-bold text-primary uppercase tracking-wide">Mengenal Petani Anda</h2>
            </div>
            <div class="p-5 md:p-6">
                <div class="flex flex-col sm:flex-row gap-5 md:gap-6">
                    <div class="shrink-0 flex sm:flex-col items-center sm:items-start gap-4 sm:gap-3">
                        <?php if ($petaniPhotoSrc): ?>
                            <img src="<?= e($petaniPhotoSrc) ?>" alt="<?= e($petaniProfile['nama']) ?>"
                                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-leaf-green-light shadow-sm"/>
                        <?php else: ?>
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-leaf-green-light flex items-center justify-center text-primary font-bold text-2xl ring-4 ring-leaf-green-light/60">
                                <?= strtoupper(substr($petaniProfile['nama'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="sm:hidden flex-1 min-w-0">
                            <h3 class="text-xl font-bold text-text-main"><?= e($petaniProfile['nama']) ?></h3>
                            <p class="text-sm text-text-muted flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[16px]">location_on</span>
                                <?= e($petaniProfile['alamat'] ?? 'Petani Lokal') ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="hidden sm:block mb-3">
                            <h3 class="text-xl md:text-2xl font-bold text-text-main"><?= e($petaniProfile['nama']) ?></h3>
                            <p class="text-sm text-text-muted flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[16px]">location_on</span>
                                <?= e($petaniProfile['alamat'] ?? 'Petani Lokal') ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-leaf-green-light text-primary text-xs font-semibold">
                                <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                                <?= (int) $petaniProfile['jumlah_produk'] ?> produk tersedia
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-low text-on-surface-variant text-xs font-medium">
                                <span class="material-symbols-outlined text-[16px] text-primary">eco</span>
                                Petani lokal terpercaya
                            </span>
                        </div>
                        <?php if ($petaniProfilText !== ''): ?>
                            <div class="text-on-surface-variant text-sm md:text-[15px] leading-relaxed space-y-3">
                                <?php foreach (preg_split("/\r\n|\r|\n/", $petaniProfilText) as $paragraph): ?>
                                    <?php if (trim($paragraph) !== ''): ?>
                                        <p><?= e(trim($paragraph)) ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-text-muted italic">Profil petani akan segera ditambahkan oleh admin.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
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
