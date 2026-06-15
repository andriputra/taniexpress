<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$product = getProductDetail($id);
if (!$product) { flash('error', 'Produk tidak ditemukan.'); redirect('home.php'); }

$related = getRelatedProducts($id, $product['kategori']);
$stokMenipis = $product['stok'] < 10;

$pageTitle = $product['nama'];
$activeNav = 'belanja';
$hideBottomNav = true;
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<style>
    .product-hero-gradient { background: linear-gradient(to top, rgba(15,82,56,.45) 0%, transparent 50%); }
    .qty-btn:active { transform: scale(0.92); }
</style>

<main class="page-main !pb-0">
    <!-- Breadcrumb desktop -->
    <nav class="hidden md:block page-container pt-5 pb-2" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-sm text-text-muted">
            <li><a href="home.php" class="hover:text-primary transition-colors">Belanja</a></li>
            <li><span class="material-symbols-outlined text-[16px] text-outline">chevron_right</span></li>
            <li><a href="home.php?kategori=<?= urlencode($product['kategori']) ?>" class="hover:text-primary transition-colors"><?= e($product['kategori']) ?></a></li>
            <li><span class="material-symbols-outlined text-[16px] text-outline">chevron_right</span></li>
            <li class="text-text-main font-medium truncate max-w-xs"><?= e($product['nama']) ?></li>
        </ol>
    </nav>

    <div class="page-container pb-36">
        <div class="lg:grid lg:grid-cols-2 lg:gap-10 lg:items-start">

            <!-- Kolom gambar -->
            <div class="relative -mx-margin-mobile md:mx-0 lg:sticky lg:top-24">
                <div class="relative aspect-square sm:aspect-[4/3] lg:aspect-square max-h-[480px] lg:max-h-none overflow-hidden bg-surface-container-low lg:rounded-3xl">
                    <img src="<?= e($product['gambar']) ?>" alt="<?= e($product['nama']) ?>"
                         class="w-full h-full object-cover"/>
                    <div class="absolute inset-0 product-hero-gradient pointer-events-none lg:rounded-3xl"></div>

                    <!-- Tombol kembali floating -->
                    <a href="home.php" class="absolute top-4 left-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-primary shadow-md hover:bg-white active:scale-95 transition-all z-10" aria-label="Kembali">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>

                    <?php if ($stokMenipis): ?>
                        <span class="absolute top-4 right-4 px-3 py-1.5 bg-sun-tint text-on-tertiary-fixed text-xs font-bold rounded-full shadow-sm">Stok Terbatas</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom informasi -->
            <div class="relative z-10 -mt-8 lg:mt-0">
                <div class="bg-white rounded-t-3xl lg:rounded-3xl p-6 md:p-8 tonal-shadow lg:shadow-none lg:border lg:border-outline-variant/30">

                    <!-- Header produk -->
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-3 py-1 rounded-full bg-leaf-green-light text-primary text-xs font-bold uppercase tracking-wide mb-3"><?= e($product['kategori']) ?></span>
                            <h1 class="text-2xl md:text-3xl font-bold text-text-main leading-tight mb-3"><?= e($product['nama']) ?></h1>
                            <a href="home.php?petani=<?= (int)($product['petani_id'] ?? 0) ?>" class="inline-flex items-center gap-2.5 group">
                                <?php if ($product['petani_foto']): ?>
                                    <img src="<?= e($product['petani_foto']) ?>" class="w-9 h-9 rounded-full object-cover ring-2 ring-leaf-green-light group-hover:ring-primary/30 transition-all" alt=""/>
                                <?php else: ?>
                                    <span class="w-9 h-9 rounded-full bg-primary-container text-white text-sm font-bold flex items-center justify-center"><?= strtoupper(substr($product['petani_nama'], 0, 1)) ?></span>
                                <?php endif; ?>
                                <div>
                                    <p class="text-sm font-semibold text-text-main group-hover:text-primary transition-colors"><?= e($product['petani_nama']) ?></p>
                                    <p class="text-xs text-text-muted"><?= e($product['petani_alamat'] ?? 'Petani Lokal') ?></p>
                                </div>
                            </a>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-2xl md:text-3xl font-bold text-primary"><?= formatRupiah($product['harga']) ?></p>
                            <p class="text-sm text-outline">per <?= e($product['satuan']) ?></p>
                        </div>
                    </div>

                    <!-- Meta info pills -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-sm text-on-surface-variant">
                            <span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span>
                            Stok: <strong class="text-text-main"><?= $product['stok'] ?> <?= e($product['satuan']) ?></strong>
                        </span>
                        <?php if ($product['berat']): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-sm text-on-surface-variant">
                            <span class="material-symbols-outlined text-primary text-[18px]">scale</span>
                            <?= e($product['berat']) ?>
                        </span>
                        <?php endif; ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-leaf-green-light/60 text-sm text-primary font-medium">
                            <span class="material-symbols-outlined text-[18px]">eco</span> Organik
                        </span>
                    </div>

                    <!-- Fitur highlight - scroll horizontal di mobile -->
                    <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-2 mb-8 -mx-1 px-1">
                        <?php foreach ([['eco','Organik','Tanpa pestisida'],['schedule','Dipetik Pagi','Panen segar'],['local_shipping','Kirim Cepat','Same day'],['verified','Premium','Kualitas terjamin']] as [$ic,$title,$sub]): ?>
                        <div class="shrink-0 w-[130px] p-3.5 rounded-2xl bg-surface-container-low border border-outline-variant/20 text-center">
                            <span class="material-symbols-outlined text-primary text-2xl mb-1.5 block"><?= $ic ?></span>
                            <p class="text-xs font-bold text-text-main"><?= $title ?></p>
                            <p class="text-[10px] text-text-muted mt-0.5 leading-tight"><?= $sub ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Deskripsi -->
                    <section class="mb-8">
                        <h2 class="text-base font-bold text-text-main mb-3 flex items-center gap-2">
                            <span class="w-1 h-5 bg-primary rounded-full"></span> Deskripsi Produk
                        </h2>
                        <p class="text-on-surface-variant leading-relaxed text-[15px]"><?= e($product['deskripsi']) ?></p>
                    </section>

                    <!-- Petani card -->
                    <section class="rounded-2xl overflow-hidden border border-outline-variant/25">
                        <div class="bg-leaf-green-light/40 px-5 py-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">agriculture</span>
                            <h3 class="text-sm font-bold text-primary uppercase tracking-wide">Mengenal Petani Anda</h3>
                        </div>
                        <div class="p-5 bg-white">
                            <p class="text-on-surface-variant italic text-sm leading-relaxed mb-3">"Saya percaya sayur yang ditanam dengan kasih sayang akan memberikan kesehatan yang lebih baik bagi yang memakannya."</p>
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-primary text-sm">— <?= e($product['petani_nama']) ?></p>
                                <a href="home.php?kategori=<?= urlencode($product['kategori']) ?>" class="text-xs text-primary font-semibold hover:underline flex items-center gap-1">
                                    Lihat produk lain <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Produk terkait -->
        <?php if (!empty($related)): ?>
        <section class="mt-12 lg:mt-16">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-text-main">Produk Serupa</h2>
                <a href="home.php?kategori=<?= urlencode($product['kategori']) ?>" class="text-sm text-primary font-semibold hover:underline">Lihat semua</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($related as $rp): renderProductCard($rp); endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>

<!-- Sticky action bar -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-outline-variant/40 shadow-[0_-8px_30px_rgba(15,82,56,0.08)]">
    <form action="actions/cart.php" method="POST" class="page-container py-3 md:py-4">
        <input type="hidden" name="action" value="add"/>
        <input type="hidden" name="id" value="<?= $product['id'] ?>"/>
        <input type="hidden" name="from" value="product.php?id=<?= $product['id'] ?>"/>

        <div class="flex items-center gap-3 md:gap-4">
            <!-- Qty -->
            <div class="flex items-center bg-surface-container-high rounded-full border border-outline-variant/40 shrink-0">
                <button type="button" onclick="changeQty(-1)" class="qty-btn w-10 h-10 flex items-center justify-center text-primary rounded-l-full hover:bg-leaf-green-light transition-colors" aria-label="Kurangi">
                    <span class="material-symbols-outlined text-[20px]">remove</span>
                </button>
                <span id="qty-display" class="w-8 text-center font-bold text-text-main text-sm">1</span>
                <input type="hidden" name="qty" id="qty" value="1"/>
                <button type="button" onclick="changeQty(1)" class="qty-btn w-10 h-10 flex items-center justify-center text-primary rounded-r-full hover:bg-leaf-green-light transition-colors" aria-label="Tambah">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
            </div>

            <!-- Subtotal preview -->
            <div class="hidden sm:block shrink-0">
                <p class="text-[10px] text-text-muted uppercase tracking-wide">Subtotal</p>
                <p id="subtotal-preview" class="text-base font-bold text-primary"><?= formatRupiah($product['harga']) ?></p>
            </div>

            <!-- CTA -->
            <button type="submit" class="btn-primary flex-1 !py-3.5 !rounded-2xl shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">shopping_cart</span>
                <span class="sm:hidden">Keranjang</span>
                <span class="hidden sm:inline">Masukan ke Keranjang</span>
            </button>
        </div>
    </form>
</div>

<script>
const maxStock = <?= (int) $product['stok'] ?>;
const unitPrice = <?= (float) $product['harga'] ?>;

function formatRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function changeQty(delta) {
    const input = document.getElementById('qty');
    const display = document.getElementById('qty-display');
    const preview = document.getElementById('subtotal-preview');
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(maxStock, val));
    input.value = val;
    display.textContent = val;
    if (preview) preview.textContent = formatRp(val * unitPrice);
    display.classList.add('text-success-green', 'scale-110');
    setTimeout(() => display.classList.remove('text-success-green', 'scale-110'), 150);
}
</script>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
