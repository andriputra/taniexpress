<?php
/** @var string $pageTitle */
/** @var string $activeMenu */
$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? 'dashboard';
$user = currentUser();

$menus = [
    'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'url' => 'index.php'],
    'products' => ['icon' => 'inventory_2', 'label' => 'Produk', 'url' => 'products.php'],
    'farmers' => ['icon' => 'agriculture', 'label' => 'Petani', 'url' => 'farmers.php'],
    'produk-petani' => ['icon' => 'link', 'label' => 'Produk-Petani', 'url' => 'produk-petani.php'],
    'kurir' => ['icon' => 'local_shipping', 'label' => 'Kurir', 'url' => 'kurir.php'],
    'pesanan' => ['icon' => 'shopping_cart', 'label' => 'Pesanan', 'url' => 'pesanan.php'],
    'laporan' => ['icon' => 'description', 'label' => 'Laporan', 'url' => 'laporan.php'],
    'account' => ['icon' => 'person', 'label' => 'Akun', 'url' => 'account.php'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= e($pageTitle) ?> - Admin <?= e(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0f5238', 'primary-container': '#2d6a4f',
                        'leaf-green-light': '#D8F3DC', 'text-main': '#1B4332',
                        'text-muted': '#52796F', 'success-green': '#40916C',
                        'error-red': '#BC4749', surface: '#f9f9f8',
                        'surface-container-low': '#f3f4f3',
                        'surface-container-lowest': '#ffffff',
                        'outline-variant': '#bfc9c1', outline: '#707973',
                        'on-surface': '#191c1c', 'on-primary': '#ffffff',
                        'on-primary-container': '#a8e7c5', 'tertiary-container': '#c9a900',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .tonal-shadow { box-shadow: 0 4px 20px -2px rgba(45, 106, 79, 0.08); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .input-field,
        select.input-field,
        textarea.input-field,
        input.input-field {
            width:100%;background:#fff;border:1px solid #bfc9c1;padding:.75rem 1.125rem;font-size:.875rem;outline:none;transition:all .2s;
            border-radius:9999px !important;
        }
        textarea.input-field,
        select.input-field,
        input.input-field[type="file"] { border-radius:1.25rem !important; }
        .input-field:focus, select.input-field:focus, textarea.input-field:focus {
            border-color:#0f5238;box-shadow:0 0 0 3px rgba(15,82,56,.15);
        }
        select.input-field {
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23707973' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:right 1rem center;padding-right:2.75rem;
        }
        .admin-chip-scroll {
            display:flex;gap:.5rem;overflow-x:auto;-webkit-overflow-scrolling:touch;
            padding-bottom:.25rem;margin-left:-1rem;margin-right:-1rem;padding-left:1rem;padding-right:1rem;
        }
        @media (min-width: 768px) {
            .admin-chip-scroll { margin-left:0;margin-right:0;padding-left:0;padding-right:0;flex-wrap:wrap;overflow:visible; }
        }
        #admin-sidebar { transform: translateX(-100%); }
        @media (min-width: 768px) {
            #admin-sidebar { transform: translateX(0) !important; }
        }
        @media print { aside, .no-print, #admin-drawer-overlay { display: none !important; } main { margin-left: 0 !important; } }
        <?= passwordToggleCss() ?>
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex">
<div id="admin-drawer-overlay" class="fixed inset-0 bg-black/45 z-[60] hidden md:hidden no-print" aria-hidden="true"></div>

<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-[70] flex flex-col w-[min(88vw,17rem)] md:w-64 h-screen border-r border-outline-variant bg-surface-container-low transition-transform duration-300 ease-out no-print">
    <div class="px-5 py-5 md:px-6 md:py-6 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-lg md:text-xl font-bold text-primary truncate"><?= e(APP_NAME) ?></h1>
            <p class="text-xs text-text-muted mt-0.5">Panel Admin</p>
        </div>
        <button type="button" id="admin-menu-close" class="md:hidden w-9 h-9 rounded-full flex items-center justify-center text-outline hover:bg-leaf-green-light hover:text-primary shrink-0" aria-label="Tutup menu">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <div class="px-5 md:px-6 mb-4 md:mb-6">
        <a href="account.php" class="flex items-center gap-3 group min-w-0">
            <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-white font-bold shrink-0 group-hover:ring-2 group-hover:ring-primary/30 transition-all">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate group-hover:text-primary transition-colors"><?= e($user['nama']) ?></p>
                <p class="text-xs text-text-muted">Administrator</p>
            </div>
        </a>
    </div>
    <nav class="flex-1 px-2 md:px-3 space-y-0.5 overflow-y-auto">
        <?php foreach ($menus as $key => $menu): ?>
            <a href="<?= $menu['url'] ?>"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                      <?= $activeMenu === $key ? 'bg-primary text-white' : 'text-on-surface hover:bg-leaf-green-light hover:text-primary' ?>">
                <span class="material-symbols-outlined text-[20px]"><?= $menu['icon'] ?></span>
                <?= $menu['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-outline-variant">
        <a href="../home.php" class="flex items-center gap-2 text-sm text-text-muted hover:text-primary px-2 py-2">
            <span class="material-symbols-outlined text-[18px]">storefront</span> Lihat Toko
        </a>
        <a href="../logout.php" class="flex items-center gap-2 text-sm text-error-red px-2 py-2">
            <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
        </a>
    </div>
</aside>

<main class="flex-1 md:ml-64 min-h-screen min-w-0 w-full">
    <header class="sticky top-0 z-40 bg-surface/95 backdrop-blur border-b border-outline-variant px-4 md:px-6 py-3 md:py-4 flex items-center gap-3 no-print">
        <button type="button" id="admin-menu-btn" class="md:hidden w-10 h-10 rounded-full flex items-center justify-center text-primary hover:bg-leaf-green-light shrink-0" aria-label="Buka menu">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h2 class="text-base sm:text-lg md:text-xl font-bold text-text-main truncate flex-1 min-w-0"><?= e($pageTitle) ?></h2>
    </header>

    <div class="p-4 md:p-6 pb-8">
        <?php renderFlashMessages(); ?>
