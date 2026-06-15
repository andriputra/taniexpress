<?php
/** @var string $activeNav */
/** @var string|null $pageHeading */
/** @var string|null $backUrl */
/** @var bool $minimalHeader */
/** @var string $headerExtra */

require_once __DIR__ . '/ui.php';

$activeNav = $activeNav ?? '';
$pageHeading = $pageHeading ?? null;
$backUrl = $backUrl ?? null;
$minimalHeader = $minimalHeader ?? false;
$headerExtra = $headerExtra ?? '';
$cartBadge = cartCount();
$user = currentUser();
$navItems = uiNavItems();
$accountUrl = $user ? 'account.php' : 'login.php';
?>
<header id="app-header" class="sticky top-0 z-50 bg-surface/90 backdrop-blur-md border-b border-outline-variant/30 transition-shadow duration-300">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
        <!-- Bar utama -->
        <div class="flex items-center justify-between h-16 gap-4">
            <!-- Kiri: menu + logo -->
            <div class="flex items-center gap-3 min-w-0">
                <?php if (!$minimalHeader): ?>
                    <button type="button" id="menu-btn" class="icon-btn md:hidden" aria-label="Menu">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                <?php endif; ?>
                <a href="index.php" class="flex items-center gap-2 shrink-0 group">
                    <span class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white shadow-sm group-hover:bg-primary-container transition-colors">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">eco</span>
                    </span>
                    <span class="hidden sm:block text-lg font-bold text-primary tracking-tight"><?= e(APP_NAME) ?></span>
                </a>
            </div>

            <?php if (!$minimalHeader): ?>
            <!-- Tengah: navigasi desktop -->
            <nav class="hidden lg:flex items-center gap-8">
                <a href="index.php" class="text-sm <?= uiNavClass('beranda', $activeNav) ?>">Beranda</a>
                <a href="home.php" class="text-sm <?= uiNavClass('belanja', $activeNav) ?>">Belanja</a>
                <a href="orders.php" class="text-sm <?= uiNavClass('orders', $activeNav) ?>">Pesanan</a>
            </nav>

            <!-- Kanan: aksi -->
            <div class="flex items-center gap-1 sm:gap-2">
                <a href="home.php" class="icon-btn hidden md:flex" title="Cari produk">
                    <span class="material-symbols-outlined">search</span>
                </a>
                <a href="cart.php" class="icon-btn relative" title="Keranjang">
                    <span class="material-symbols-outlined">shopping_basket</span>
                    <?php if ($cartBadge > 0): ?>
                        <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-error-red text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?= $cartBadge > 9 ? '9+' : $cartBadge ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($user): ?>
                    <a href="account.php" class="hidden md:flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full hover:bg-leaf-green-light transition-colors">
                        <span class="w-8 h-8 rounded-full bg-primary-container text-white text-sm font-bold flex items-center justify-center"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
                        <span class="text-sm font-medium text-text-main max-w-[100px] truncate"><?= e(explode(' ', $user['nama'])[0]) ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="hidden md:inline-flex btn-primary-sm ml-1">Masuk</a>
                <?php endif; ?>
                <?= $headerExtra ?>
            </div>
            <?php else: ?>
            <a href="index.php" class="text-sm text-text-muted hover:text-primary">← Kembali</a>
            <?php endif; ?>
        </div>

        <?php if ($pageHeading && !$minimalHeader): ?>
        <!-- Sub-header halaman -->
        <div class="flex items-center gap-3 pb-4 border-t border-outline-variant/20 pt-3 lg:hidden">
            <?php if ($backUrl): ?>
                <a href="<?= e($backUrl) ?>" class="icon-btn shrink-0"><span class="material-symbols-outlined">arrow_back</span></a>
            <?php endif; ?>
            <h1 class="text-base font-bold text-text-main truncate flex-1"><?= e($pageHeading) ?></h1>
        </div>
        <?php endif; ?>
    </div>
</header>

<?php if (!$minimalHeader): ?>
<!-- Mobile drawer -->
<div id="mobile-menu" class="hidden fixed inset-0 z-[60] md:hidden">
    <div class="absolute inset-0 bg-black/40" id="menu-overlay"></div>
    <aside class="absolute left-0 top-0 h-full w-72 max-w-[85vw] bg-white shadow-2xl flex flex-col">
        <div class="p-5 border-b border-outline-variant/30 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">eco</span>
                </span>
                <div>
                    <p class="font-bold text-primary"><?= e(APP_NAME) ?></p>
                    <p class="text-xs text-text-muted">Sayur segar dari petani</p>
                </div>
            </div>
            <button type="button" id="menu-close" class="icon-btn"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php if ($user): ?>
            <div class="px-5 py-4 bg-leaf-green-light/50 flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-primary-container text-white font-bold flex items-center justify-center"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
                <div>
                    <p class="font-semibold text-sm"><?= e($user['nama']) ?></p>
                    <p class="text-xs text-text-muted"><?= e($user['email']) ?></p>
                </div>
            </div>
        <?php endif; ?>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <?php foreach ($navItems as $key => $item):
                $url = ($key === 'account') ? $accountUrl : $item['url'];
            ?>
                <a href="<?= $url ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors <?= $activeNav === $key ? 'bg-leaf-green-light text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' ?>">
                    <span class="material-symbols-outlined text-[22px]" style="<?= $activeNav === $key ? "font-variation-settings:'FILL' 1" : '' ?>"><?= $item['icon'] ?></span>
                    <?= $item['label'] ?>
                    <?php if ($key === 'cart' && $cartBadge > 0): ?>
                        <span class="ml-auto bg-error-red text-white text-xs px-2 py-0.5 rounded-full"><?= $cartBadge ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <?php if (!$user): ?>
                <div class="pt-4 mt-4 border-t border-outline-variant/30 space-y-2">
                    <a href="login.php" class="btn-primary w-full text-center block">Masuk</a>
                    <a href="register.php" class="btn-outline w-full text-center block">Daftar</a>
                </div>
            <?php else: ?>
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-error-red hover:bg-red-50 mt-4">
                    <span class="material-symbols-outlined text-[22px]">logout</span> Keluar
                </a>
            <?php endif; ?>
        </nav>
    </aside>
</div>
<?php endif; ?>

<?php renderFlashMessages(); ?>
