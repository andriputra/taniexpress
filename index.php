<?php
require_once __DIR__ . '/includes/bootstrap.php';

$products = getProducts();
$popularProducts = array_slice($products, 0, 4);

$categories = [
    ['nama' => 'Sayuran Hijau', 'desc' => 'Kaya nutrisi, dipetik setiap subuh', 'kategori' => 'Sayuran Hijau', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCPTGFjQjiaPhoPYKiXH5crblDIQ0HNEHZ3yneW9DRyFT1KFM-FjEvs0XA7JQC-pjEnCU6JwOe3LFh1JjE6ceP71Rfd9Un4wz_wDbICTqc_v_A1nnwba2_2DRr2y_PSMvpLfj3hAfXQUR4rEPHKrBcLLRmp55d3dbLWYp8GIxlNqRWiw9BIFeI7kEWhIbReeB7XmO7FVopIjrAAGwKailfQIasWG4DOlpesNFHNZh_Ru4Q0785DUyJsMbIxOn_J8lFGZ5XLzdOBhWKX', 'class' => 'md:col-span-2 md:row-span-2', 'gradient' => 'from-primary/80'],
    ['nama' => 'Umbi & Akar', 'desc' => 'Kualitas tanah pegunungan terbaik', 'kategori' => 'Umbi & Akar', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuByrmnW0m3b70g_r3ihu4n2RejxRvjWqdBS4Dg27n2FYgqTjRIGXhV2d-8AW7CnviMU7KaK_xqRWYSCmXpsebyXTwJH3cspatmBqmXhoxjJZ6jRBOKI6jHQaXSnTQ4BTh3yVVE-Ux0J4qkEegw7Wb9LrZZyfOukfWD4o0nmRY0SNIayQJBaA4HFMFyVheCliiYt_331b4TD7Ij-qTnwUss_zqL9RmG1dUw5BZYuahH1pTpOTTNOPrTFRxcPrBGhpomC630pMqRLdkMy', 'class' => 'md:col-span-2 h-[250px] md:h-auto bg-secondary-container', 'gradient' => 'from-secondary/80'],
    ['nama' => 'Buah Musiman', 'desc' => '', 'kategori' => 'Buah', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBeqgqXXD3hRJARzHP87MpxIREPY3ereUgsyPRY5KbIMO70G5wHcjOm9RXfeaV6iM46PucABxzCozAMwnc8dFnz1qlxqlKZCcMBqu1MQMLk-YMJt5Sdx0gf_00BK59Tp4I-bxF5tE27MwlrhtVh_-dtYaONeQXg_Bx0BvrTKrKoAlCb1v1xICErOgEXklPZZYMpyNVY4Q46VSjgGR_VFut_2RDr0U59GHeIMvG15jvvtpU0vbMWJsltDOGvtLIEC5GU-orWH8SmLy-g', 'class' => 'bg-tertiary-container', 'gradient' => 'from-tertiary/80', 'small' => true],
    ['nama' => 'Jamur Organik', 'desc' => '', 'kategori' => 'Jamur', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCwvjcP_bzXqkSk17Ksr4OogTz15lBuXNmW65QASLlpiYL0knoLWv-RhcT0CBaY1wXzyI2XrYxkKuc5F1khUVmbC4ljOtLEa3pwAEGSKH1JgucvkVQMYsyDj4y3LKGqP6mTgMLa1a2W7Rm0ByrF9MrHhQF0YPdc35HosdlLqNE6zD9na_QljLJOfyFfkD0oYwva4CsUjUB-NNlblYml3wSibPDU1c6hf_-XolsEbY_sUdQ1vpMk0q2kwJ864Jc9oqyRo6Ywys8R_smA', 'class' => 'bg-primary-container', 'gradient' => 'from-primary/80', 'small' => true],
];

$pageTitle = 'Sayuran Segar Langsung dari Petani';
$activeNav = 'beranda';
$isLanding = true;
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="min-h-screen">
    <section class="relative w-full h-[min(751px,85vh)] flex items-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img class="w-full h-full object-cover brightness-75" alt="Ladang sayur"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuDpNh1RVfev1fkjcwn1FSugvisPsudFtdeUoaPqY0bJTZBRz9_WXApOrLza1MECssg8hBImwzHtbrzz2p1v43SnCiVzoLIDv3mbXihRppNMl8K2L3HmZadG6Y8hwRoBWcx66WtYJl71oQk2cezjjLjIXclDyd4FR8GTlEY4IJb5_uWcfdyyfAo0-dWDi4Ih-TK66ABpFCsW-THx-E2VTeBvQOVA4Hn7IHFZ9sCB-LXmvRLRkL2E23qpfFC7bYjfHgCfp6BnyUtR1yPK"/>
            <div class="absolute inset-0 bg-gradient-to-r from-primary/60 to-transparent"></div>
        </div>
        <div class="page-container relative z-10 w-full py-12">
            <div class="max-w-2xl">
                <span class="inline-block px-4 py-1.5 rounded-full bg-leaf-green-light text-primary text-label-md font-semibold mb-6 uppercase tracking-wider">Paling Segar Hari Ini</span>
                <h1 class="text-headline-xl text-white mb-6 leading-tight">Sayuran Segar Langsung dari Petani</h1>
                <p class="text-body-lg text-white/90 mb-10 max-w-lg">Nikmati hasil panen terbaik yang dipetik hari ini dan dikirim langsung ke pintu rumah Anda.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="home.php" class="btn-primary shadow-lg">Mulai Belanja <span class="material-symbols-outlined">arrow_forward</span></a>
                    <a href="register.php" class="px-8 py-3 bg-white/10 backdrop-blur border border-white/20 text-white rounded-xl font-semibold hover:bg-white/20 text-center transition-all">Daftar Gratis</a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 md:py-24 page-container">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h2 class="text-headline-lg text-text-main mb-2">Kategori Pilihan</h2>
                <p class="text-body-md text-text-muted">Jelajahi kesegaran dari berbagai jenis hasil bumi</p>
            </div>
            <a href="home.php" class="text-primary text-label-md font-semibold flex items-center gap-1 hover:underline shrink-0">Lihat Semua <span class="material-symbols-outlined text-[18px]">chevron_right</span></a>
        </div>
        <div class="bento-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="home.php?kategori=<?= urlencode($cat['kategori']) ?>" class="<?= e($cat['class']) ?> rounded-3xl overflow-hidden relative group block min-h-[200px]">
                    <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 absolute inset-0" src="<?= e($cat['img']) ?>" alt="<?= e($cat['nama']) ?>"/>
                    <div class="absolute inset-0 bg-gradient-to-t <?= e($cat['gradient']) ?> via-transparent to-transparent p-6 md:p-8 flex flex-col justify-end">
                        <?php if (!empty($cat['small'])): ?>
                            <h3 class="text-label-md text-white uppercase font-bold"><?= e($cat['nama']) ?></h3>
                        <?php else: ?>
                            <h3 class="text-headline-md text-white font-semibold"><?= e($cat['nama']) ?></h3>
                            <?php if ($cat['desc']): ?><p class="text-white/80 text-body-md"><?= e($cat['desc']) ?></p><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="bg-earth-tan/30 py-16 md:py-24">
        <div class="page-container text-center">
            <h2 class="text-headline-lg text-text-main mb-12">Mengapa TaniExpress?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-leaf-green-light rounded-2xl flex items-center justify-center text-primary mb-5"><span class="material-symbols-outlined text-[40px]">eco</span></div>
                    <h3 class="text-headline-md mb-3">100% Organik</h3>
                    <p class="text-body-md text-on-surface-variant px-4">Bebas pestisida kimia, ditanam berkelanjutan.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-secondary-container rounded-2xl flex items-center justify-center text-secondary mb-5"><span class="material-symbols-outlined text-[40px]">local_shipping</span></div>
                    <h3 class="text-headline-md mb-3">Pengiriman Kilat</h3>
                    <p class="text-body-md text-on-surface-variant px-4">Pesanan hari ini, sampai besok pagi.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-tertiary-container rounded-2xl flex items-center justify-center text-on-tertiary-container mb-5"><span class="material-symbols-outlined text-[40px]">groups</span></div>
                    <h3 class="text-headline-md mb-3">Dukung Petani Lokal</h3>
                    <p class="text-body-md text-on-surface-variant px-4">Harga adil langsung ke petani.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 md:py-24 page-container">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-headline-lg text-text-main">Produk Terpopuler</h2>
            <div class="flex gap-2">
                <button type="button" id="scroll-prev" class="icon-btn border border-outline-variant"><span class="material-symbols-outlined">arrow_back</span></button>
                <button type="button" id="scroll-next" class="icon-btn border border-outline-variant"><span class="material-symbols-outlined">arrow_forward</span></button>
            </div>
        </div>
        <div id="product-grid" class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-gutter">
            <?php foreach ($popularProducts as $p): renderProductCard($p, 'index.php'); endforeach; ?>
        </div>
    </section>

    <section class="py-16 md:py-24 page-container">
        <div class="bg-primary rounded-[2rem] md:rounded-[40px] p-8 md:p-16 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mt-24"></div>
            <div class="relative z-10 max-w-xl text-center md:text-left">
                <h2 class="text-headline-lg text-white mb-4">Dapatkan Promo Menarik Setiap Minggu</h2>
                <p class="text-body-lg text-white/80">Berlangganan buletin kami untuk info stok terbaru.</p>
            </div>
            <form class="relative z-10 w-full max-w-md flex flex-col sm:flex-row gap-3" onsubmit="event.preventDefault();window.TaniUI?.showToast('info','Terima kasih! Newsletter segera hadir.');">
                <input class="input-field !bg-white/10 !border-white/20 !text-white placeholder:text-white/50 flex-1 !rounded-full" placeholder="Alamat Email" type="email"/>
                <button class="px-6 py-3 bg-tertiary-fixed text-on-tertiary-fixed rounded-xl font-bold whitespace-nowrap" type="submit">Daftar</button>
            </form>
        </div>
    </section>
</main>

<?php $showSiteFooter = true; include __DIR__ . '/includes/app-footer.php'; ?>
