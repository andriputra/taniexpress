<?php
/** @var string $activeNav */
/** @var bool $showSiteFooter */
/** @var bool $hideBottomNav */

$activeNav = $activeNav ?? '';
$showSiteFooter = $showSiteFooter ?? false;
$hideBottomNav = $hideBottomNav ?? false;
$accountUrl = currentUser() ? 'account.php' : 'login.php';
$cartBadge = cartCount();
$bottomNav = [
    'beranda' => ['url' => 'index.php', 'icon' => 'spa', 'label' => 'Beranda'],
    'belanja' => ['url' => 'home.php', 'icon' => 'storefront', 'label' => 'Belanja'],
    'cart'    => ['url' => 'cart.php', 'icon' => 'shopping_basket', 'label' => 'Keranjang'],
    'orders'  => ['url' => 'orders.php', 'icon' => 'receipt_long', 'label' => 'Pesanan'],
    'account' => ['url' => $accountUrl, 'icon' => 'person', 'label' => 'Akun'],
];
?>

<?php if ($showSiteFooter): ?>
<footer class="bg-surface-container-high border-t border-outline-variant mt-12">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">eco</span>
                </span>
                <span class="font-bold text-primary text-lg"><?= e(APP_NAME) ?></span>
            </div>
            <p class="text-sm text-on-surface-variant leading-relaxed">Menghubungkan dapur Anda dengan ladang petani lokal terbaik di Indonesia.</p>
        </div>
        <div>
            <h4 class="text-label-md font-bold uppercase tracking-wider mb-4">Belanja</h4>
            <ul class="space-y-2 text-sm text-on-surface-variant">
                <li><a href="home.php" class="hover:text-primary transition-colors">Semua Produk</a></li>
                <li><a href="cart.php" class="hover:text-primary transition-colors">Keranjang</a></li>
                <li><a href="orders.php" class="hover:text-primary transition-colors">Pesanan Saya</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-label-md font-bold uppercase tracking-wider mb-4">Bantuan</h4>
            <ul class="space-y-2 text-sm text-on-surface-variant">
                <li><a href="#" class="hover:text-primary transition-colors">Cara Belanja</a></li>
                <li><a href="#" class="hover:text-primary transition-colors">Pengiriman</a></li>
                <li><a href="#" class="hover:text-primary transition-colors">Kontak</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-label-md font-bold uppercase tracking-wider mb-4">Kontak</h4>
            <ul class="space-y-3 text-sm text-on-surface-variant">
                <li class="flex items-start gap-2"><span class="material-symbols-outlined text-primary text-[18px]">location_on</span> Jl. Pertanian No. 88, Lembang</li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">call</span> +62 812-3456-7890</li>
            </ul>
        </div>
    </div>
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-6 border-t border-outline-variant/50 text-center text-xs text-outline">
        © <?= date('Y') ?> <?= e(APP_NAME) ?>. All Rights Reserved.
    </div>
</footer>
<?php endif; ?>

<?php if (!$hideBottomNav): ?>
<nav class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white/95 backdrop-blur-md border-t border-outline-variant/40 shadow-[0_-4px_20px_rgba(15,82,56,0.06)] safe-bottom">
    <div class="flex justify-around items-center h-[68px] px-1 max-w-xl mx-auto">
        <?php foreach ($bottomNav as $key => $item): ?>
            <a href="<?= $item['url'] ?>"
               class="flex flex-col items-center justify-center min-w-[56px] transition-all active:scale-95 <?= uiNavClass($key, $activeNav, 'mobile-bottom') ?>">
                <span class="material-symbols-outlined text-[22px] relative" style="<?= $activeNav === $key ? "font-variation-settings:'FILL' 1" : '' ?>">
                    <?= $item['icon'] ?>
                    <?php if ($key === 'cart' && $cartBadge > 0): ?>
                        <span class="absolute -top-1 -right-2 min-w-[14px] h-[14px] bg-error-red text-white text-[9px] font-bold rounded-full flex items-center justify-center"><?= $cartBadge > 9 ? '9+' : $cartBadge ?></span>
                    <?php endif; ?>
                </span>
                <span class="text-[10px] font-semibold mt-0.5"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>

<?php renderUiShell(); ?>

<script>
(function () {
    const header = document.getElementById('app-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('shadow-md', window.scrollY > 8);
        }, { passive: true });
    }

    const menu = document.getElementById('mobile-menu');
    const menuBtn = document.getElementById('menu-btn');
    const menuClose = document.getElementById('menu-close');
    const menuOverlay = document.getElementById('menu-overlay');
    const openMenu = () => menu?.classList.remove('hidden');
    const closeMenu = () => menu?.classList.add('hidden');
    menuBtn?.addEventListener('click', openMenu);
    menuClose?.addEventListener('click', closeMenu);
    menuOverlay?.addEventListener('click', closeMenu);

    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', () => card.querySelector('.add-btn')?.classList.add('scale-110'));
        card.addEventListener('mouseleave', () => card.querySelector('.add-btn')?.classList.remove('scale-110'));
    });

    const grid = document.getElementById('product-grid');
    document.getElementById('scroll-prev')?.addEventListener('click', () => grid?.scrollBy({ left: -320, behavior: 'smooth' }));
    document.getElementById('scroll-next')?.addEventListener('click', () => grid?.scrollBy({ left: 320, behavior: 'smooth' }));
})();
</script>
<?php renderUiScripts(); ?>
</body>
</html>
