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
        @media print { aside, .no-print { display: none !important; } main { margin-left: 0 !important; } }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex">
<aside class="hidden md:flex flex-col w-64 h-screen border-r border-outline-variant bg-surface-container-low fixed left-0 top-0 z-50">
    <div class="px-6 py-6">
        <h1 class="text-xl font-bold text-primary"><?= e(APP_NAME) ?></h1>
        <p class="text-xs text-text-muted mt-1">Panel Admin</p>
    </div>
    <div class="px-6 mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-white font-bold">
            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
        </div>
        <div>
            <p class="text-sm font-semibold"><?= e($user['nama']) ?></p>
            <p class="text-xs text-text-muted">Administrator</p>
        </div>
    </div>
    <nav class="flex-1 px-3 space-y-1">
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

<main class="flex-1 md:ml-64 min-h-screen">
    <header class="sticky top-0 z-40 bg-surface/90 backdrop-blur border-b border-outline-variant px-6 py-4 flex justify-between items-center no-print">
        <h2 class="text-xl font-bold text-text-main"><?= e($pageTitle) ?></h2>
        <div class="md:hidden">
            <select onchange="location.href=this.value" class="input-field text-sm !py-2">
                <?php foreach ($menus as $key => $menu): ?>
                    <option value="<?= $menu['url'] ?>" <?= $activeMenu === $key ? 'selected' : '' ?>><?= $menu['label'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </header>

    <div class="p-6">
        <?php renderFlashMessages(); ?>
