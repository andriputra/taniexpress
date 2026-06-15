<?php
require_once __DIR__ . '/includes/bootstrap.php';

$items = getCartItems();
$total = cartTotal();

$pageTitle = 'Keranjang';
$activeNav = 'cart';
$pageHeading = 'Keranjang Belanja';
$backUrl = 'home.php';
$headerExtra = !empty($items) ? '<form action="actions/cart.php" method="POST" data-confirm="Kosongkan semua item di keranjang?" data-confirm-title="Kosongkan Keranjang" class="hidden sm:block"><input type="hidden" name="action" value="clear"/><button type="submit" class="text-sm text-error-red hover:underline">Kosongkan</button></form>' : '';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <?php renderPageHero('Keranjang Belanja', count($items) . ' item', 'home.php'); ?>

    <div class="page-container">
        <?php if (empty($items)): ?>
            <?php renderEmptyState('shopping_basket', 'Keranjang Kosong', 'Yuk, pilih sayuran segar favoritmu dan mulai belanja.', 'Mulai Belanja', 'home.php'); ?>
        <?php else: ?>
            <div class="flex flex-col gap-4 mb-6">
                <?php foreach ($items as $item): ?>
                    <div class="flex gap-4 p-4 bg-white rounded-2xl tonal-shadow">
                        <a href="product.php?id=<?= $item['id'] ?>" class="w-20 h-20 rounded-xl overflow-hidden shrink-0">
                            <img src="<?= e($item['gambar']) ?>" alt="" class="w-full h-full object-cover"/>
                        </a>
                        <div class="flex flex-col justify-between flex-1 min-w-0">
                            <div>
                                <a href="product.php?id=<?= $item['id'] ?>" class="font-semibold text-text-main hover:text-primary line-clamp-1"><?= e($item['produk_nama']) ?></a>
                                <p class="text-xs text-text-muted"><?= e($item['petani_nama']) ?></p>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-bold text-primary"><?= formatRupiah($item['harga']) ?></span>
                                <div class="flex items-center gap-2">
                                    <form action="actions/cart.php" method="POST" class="flex items-center bg-surface-container-high rounded-full px-1">
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>"/>
                                        <button type="submit" name="qty" value="<?= max(1, $item['qty'] - 1) ?>" class="icon-btn !w-8 !h-8"><span class="material-symbols-outlined text-[16px]">remove</span></button>
                                        <span class="w-8 text-center text-sm font-bold"><?= $item['qty'] ?></span>
                                        <button type="submit" name="qty" value="<?= min($item['stok'], $item['qty'] + 1) ?>" class="icon-btn !w-8 !h-8"><span class="material-symbols-outlined text-[16px]">add</span></button>
                                    </form>
                                    <form action="actions/cart.php" method="POST">
                                        <input type="hidden" name="action" value="remove"/>
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>"/>
                                        <button type="submit" class="icon-btn !w-8 !h-8 text-error-red hover:!bg-red-50"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-2xl p-6 tonal-shadow sticky bottom-24 md:bottom-6">
                <div class="flex justify-between mb-2"><span class="text-text-muted">Subtotal</span><span class="font-bold"><?= formatRupiah($total) ?></span></div>
                <p class="text-xs text-text-muted mb-5">Ongkir & biaya platform dihitung saat checkout</p>
                <a href="checkout.php" class="btn-primary w-full text-center block">Lanjut Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
