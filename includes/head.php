<?php
/** @var string $pageTitle */
/** @var string $activeNav */
/** @var bool $isLanding */
$pageTitle = $pageTitle ?? APP_NAME;
$activeNav = $activeNav ?? '';
$isLanding = $isLanding ?? false;
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
    <?php renderFaviconTags(); ?>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: '#0f5238',
                        'primary-container': '#2d6a4f',
                        'primary-fixed-dim': '#95d4b3',
                        'leaf-green-light': '#D8F3DC',
                        'text-main': '#1B4332',
                        'text-muted': '#52796F',
                        'success-green': '#40916C',
                        'error-red': '#BC4749',
                        'earth-tan': '#EFEBE9',
                        'secondary-container': '#fed0c1',
                        'on-secondary-container': '#79574c',
                        'tertiary-container': '#c9a900',
                        'on-tertiary-container': '#4c3e00',
                        'tertiary-fixed': '#ffe170',
                        'on-tertiary-fixed': '#221b00',
                        'tertiary-fixed-dim': '#e9c400',
                        'tertiary': '#705d00',
                        'on-tertiary': '#ffffff',
                        'sun-tint': '#FFF9C4',
                        surface: '#f9f9f8',
                        background: '#f9f9f8',
                        'on-background': '#191c1c',
                        'surface-container': '#edeeed',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f3f4f3',
                        'surface-container-high': '#e7e8e7',
                        'outline-variant': '#bfc9c1',
                        outline: '#707973',
                        'on-surface': '#191c1c',
                        'on-surface-variant': '#404943',
                        'on-primary': '#ffffff',
                        'on-primary-container': '#a8e7c5',
                        secondary: '#79564b',
                    },
                    spacing: {
                        'margin-mobile': '16px',
                        'margin-desktop': '48px',
                        'container-max': '1280px',
                        gutter: '24px',
                    },
                    maxWidth: { 'container-max': '1280px' },
                    fontSize: {
                        'headline-xl': ['40px', { lineHeight: '48px', letterSpacing: '-0.02em', fontWeight: '700' }],
                        'headline-lg': ['32px', { lineHeight: '40px', letterSpacing: '-0.01em', fontWeight: '700' }],
                        'headline-lg-mobile': ['24px', { lineHeight: '32px', fontWeight: '700' }],
                        'headline-md': ['24px', { lineHeight: '32px', fontWeight: '600' }],
                        'body-lg': ['18px', { lineHeight: '28px', fontWeight: '400' }],
                        'body-md': ['16px', { lineHeight: '24px', fontWeight: '400' }],
                        'label-md': ['14px', { lineHeight: '20px', fontWeight: '600' }],
                        'label-sm': ['12px', { lineHeight: '16px', fontWeight: '500' }],
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9f9f8; min-height: max(884px, 100dvh); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(2, 250px);
            gap: 24px;
        }
        @media (max-width: 768px) {
            .bento-grid { grid-template-columns: 1fr; grid-template-rows: auto; }
        }
        .tonal-shadow { box-shadow: 0 4px 20px -2px rgba(45, 106, 79, 0.08); }
        .icon-btn { width:2.5rem;height:2.5rem;display:flex;align-items:center;justify-content:center;border-radius:9999px;color:#0f5238;transition:all .2s; }
        .icon-btn:hover { background:#D8F3DC; }
        .icon-btn:active { transform:scale(.95); }
        .btn-primary { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem 1.5rem;background:#0f5238;color:#fff;border-radius:.75rem;font-weight:600;transition:all .2s;box-shadow:0 1px 3px rgba(15,82,56,.15); }
        .btn-primary:hover { background:#2d6a4f; }
        .btn-primary-sm { padding:.5rem 1rem;background:#0f5238;color:#fff;border-radius:.5rem;font-size:.875rem;font-weight:600; }
        .btn-primary-sm:hover { background:#2d6a4f; }
        .btn-outline { display:block;padding:.75rem 1.5rem;border:2px solid #0f5238;color:#0f5238;border-radius:.75rem;font-weight:600;text-align:center; }
        .btn-outline:hover { background:#D8F3DC; }
        .input-field,
        select.input-field,
        textarea.input-field,
        input.input-field {
            width:100%;background:#fff;border:1px solid #bfc9c1;padding:.875rem 1.25rem;font-size:.875rem;outline:none;transition:all .2s;
            border-radius:9999px !important;
        }
        textarea.input-field,
        select.input-field,
        input.input-field[type="file"] {
            border-radius:1.25rem !important;
        }
        .input-field:focus,
        select.input-field:focus,
        textarea.input-field:focus {
            border-color:#0f5238;box-shadow:0 0 0 3px rgba(15,82,56,.15);
        }
        select.input-field {
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23707973' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat;
            background-position:right 1rem center;
            padding-right:2.75rem;
        }
        input[type="file"].input-field { padding:.625rem 1rem; }
        input[type="file"].input-field::file-selector-button {
            margin-right:.75rem;border:0;border-radius:9999px;background:#0f5238;color:#fff;padding:.5rem 1rem;font-size:.8125rem;font-weight:600;cursor:pointer;
        }
        .chip { padding:.5rem 1rem;border-radius:9999px;font-size:.875rem;font-weight:600;white-space:nowrap;transition:all .2s; }
        .chip-active { background:#2d6a4f;color:#fff; }
        .chip-inactive { background:#D8F3DC;color:#0f5238; }
        .chip-inactive:hover { background:#95d4b3; }
        .page-container { max-width:1280px;margin-left:auto;margin-right:auto;padding-left:16px;padding-right:16px; }
        @media(min-width:768px){ .page-container { padding-left:48px;padding-right:48px; } }
        .page-main { padding-bottom:7rem; }
        @media(min-width:768px){ .page-main { padding-bottom:3rem; } }
        .safe-bottom { padding-bottom:env(safe-area-inset-bottom,0); }
        <?= passwordToggleCss() ?>
        .hero-slide { position:absolute; inset:0; opacity:0; transition:opacity 1s ease; pointer-events:none; }
        .hero-slide.is-active { opacity:1; pointer-events:auto; }
        .hero-slide-img { transform:scale(1.08); transition:transform 7s ease-out; }
        .hero-slide.is-active .hero-slide-img { transform:scale(1); }
        .hero-text { position:absolute; inset:0; opacity:0; transform:translateY(18px); transition:opacity .65s ease, transform .65s ease; pointer-events:none; }
        .hero-text.is-active { opacity:1; transform:translateY(0); pointer-events:auto; }
        .hero-dot { width:2.25rem; height:.35rem; border-radius:9999px; background:rgba(255,255,255,.35); transition:all .3s ease; }
        .hero-dot.is-active { width:2.75rem; background:#D8F3DC; }
        #hero-progress { transition:transform .15s linear; }
        @media (prefers-reduced-motion: reduce) {
            .hero-slide, .hero-slide-img, .hero-text, #hero-progress { transition:none !important; }
            .hero-slide-img { transform:none !important; }
        }
    </style>
</head>
<body class="<?= $isLanding ? 'text-on-background overflow-x-hidden' : 'bg-background text-on-surface' ?>">
