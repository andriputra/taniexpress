<?php
require_once __DIR__ . '/includes/bootstrap.php';

$popularProducts = getPopularProducts(8);
$heroSlides = getHeroSlides();

$categories = [
    ['nama' => 'Sayuran Hijau', 'desc' => 'Kaya nutrisi, dipetik setiap subuh', 'kategori' => 'Sayuran Hijau', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCPTGFjQjiaPhoPYKiXH5crblDIQ0HNEHZ3yneW9DRyFT1KFM-FjEvs0XA7JQC-pjEnCU6JwOe3LFh1JjE6ceP71Rfd9Un4wz_wDbICTqc_v_A1nnwba2_2DRr2y_PSMvpLfj3hAfXQUR4rEPHKrBcLLRmp55d3dbLWYp8GIxlNqRWiw9BIFeI7kEWhIbReeB7XmO7FVopIjrAAGwKailfQIasWG4DOlpesNFHNZh_Ru4Q0785DUyJsMbIxOn_J8lFGZ5XLzdOBhWKX', 'class' => 'md:col-span-2 md:row-span-2', 'gradient' => 'from-primary/80'],
    ['nama' => 'Umbi & Akar', 'desc' => 'Kualitas tanah pegunungan terbaik', 'kategori' => 'Umbi & Akar', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuByrmnW0m3b70g_r3ihu4n2RejxRvjWqdBS4Dg27n2FYgqTjRIGXhV2d-8AW7CnviMU7KaK_xqRWYSCmXpsebyXTwJH3cspatmBqmXhoxjJZ6jRBOKI6jHQaXSnTQ4BTh3yVVE-Ux0J4qkEegw7Wb9LrZZyfOukfWD4o0nmRY0SNIayQJBaA4HFMFyVheCliiYt_331b4TD7Ij-qTnwUss_zqL9RmG1dUw5BZYuahH1pTpOTTNOPrTFRxcPrBGhpomC630pMqRLdkMy', 'class' => 'md:col-span-2 h-[250px] md:h-auto bg-secondary-container', 'gradient' => 'from-secondary/80'],
    ['nama' => 'Buah Musiman', 'desc' => '', 'kategori' => 'Buah', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBeqgqXXD3hRJARzHP87MpxIREPY3ereUgsyPRY5KbIMO70G5wHcjOm9RXfeaV6iM46PucABxzCozAMwnc8dFnz1qlxqlKZCcMBqu1MQMLk-YMJt5Sdx0gf_00BK59Tp4I-bxF5tE27MwlrhtVh_-dtYaONeQXg_Bx0BvrTKrKoAlCb1v1xICErOgEXklPZZYMpyNVY4Q46VSjgGR_VFut_2RDr0U59GHeIMvG15jvvtpU0vbMWJsltDOGvtLIEC5GU-orWH8SmLy-g', 'class' => 'bg-tertiary-container', 'gradient' => 'from-tertiary/80', 'small' => true],
    ['nama' => 'Jamur Organik', 'desc' => '', 'kategori' => 'Jamur', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCwvjcP_bzXqkSk17Ksr4OogTz15lBuXNmW65QASLlpiYL0knoLWv-RhcT0CBaY1wXzyI2XrYxkKuc5F1khUVmbC4ljOtLEa3pwAEGSKH1JgucvkVQMYsyDj4y3LKGqP6mTgMLa1a2W7Rm0ByrF9MrHhQF0YPdc35HosdlLqNE6zD9na_QljLJOfyFfkD0oYwva4CsUjUB-NNlblYml3wSibPDU1c6hf_-XolsEbY_sUdQ1vpMk0q2kwJ864Jc9oqyRo6Ywys8R_smA', 'class' => 'bg-primary-container', 'gradient' => 'from-primary/80', 'small' => true],
];

$whyPillars = [
    [
        'icon' => 'diversity_3',
        'title' => 'Pemberdayaan Lokal',
        'desc' => 'Memanfaatkan inovasi teknologi untuk memberdayakan komunitas petani, memperpendek rantai pasok, dan menghidupkan ekosistem ekonomi daerah.',
    ],
    [
        'icon' => 'eco',
        'title' => 'Masa Depan Berkelanjutan',
        'desc' => 'Membangun sistem pertanian yang adil agar generasi petani mendapat apresiasi dan harga layak atas kerja keras mereka.',
    ],
    [
        'icon' => 'volunteer_activism',
        'title' => 'Dampak Nyata Bersama',
        'desc' => 'Setiap transaksi bukan sekadar jual beli, melainkan dukungan nyata dan investasi sosial bagi kesejahteraan petani lokal.',
    ],
];

$pageTitle = 'Sayuran Segar Langsung dari Petani';
$activeNav = 'beranda';
$isLanding = true;
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="min-h-screen">
    <section id="hero-slider" class="relative w-full h-[min(680px,88vh)] md:h-[min(751px,85vh)] flex items-center overflow-hidden group">
        <!-- Background slides -->
        <div class="absolute inset-0 z-0">
            <?php foreach ($heroSlides as $i => $slide): ?>
                <div class="hero-slide <?= $i === 0 ? 'is-active' : '' ?>" data-hero-slide="<?= $i ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
                    <img class="hero-slide-img w-full h-full object-cover brightness-[0.72]" src="<?= e($slide['img']) ?>" alt=""/>
                    <div class="absolute inset-0 bg-gradient-to-r <?= e($slide['gradient']) ?> via-primary/25 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-black/10"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Content -->
        <div class="page-container relative z-10 w-full py-10 md:py-12">
            <?php if (empty($heroSlides)): ?>
                <div class="max-w-2xl text-white">
                    <p class="text-lg">Belum ada slide hero. Admin dapat menambahkannya di panel Hero Beranda.</p>
                    <a href="home.php" class="btn-primary inline-flex mt-6">Mulai Belanja</a>
                </div>
            <?php else: ?>
            <div class="max-w-2xl">
                <div class="relative min-h-[280px] sm:min-h-[320px] md:min-h-[340px]">
                    <?php foreach ($heroSlides as $i => $slide): ?>
                        <div class="hero-text <?= $i === 0 ? 'is-active' : '' ?>" data-hero-text="<?= $i ?>">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-leaf-green-light text-primary text-label-md font-semibold mb-4 md:mb-6 uppercase tracking-wider shadow-sm"><?= e($slide['badge']) ?></span>
                            <h1 class="text-[1.75rem] leading-tight sm:text-headline-lg-mobile md:text-headline-xl text-white mb-4 md:mb-6 font-bold"><?= e($slide['title']) ?></h1>
                            <p class="text-base md:text-body-lg text-white/90 mb-8 md:mb-10 max-w-lg leading-relaxed"><?= e($slide['desc']) ?></p>
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                                <a href="<?= e($slide['btn_utama_url'] ?? 'home.php') ?>" class="btn-primary shadow-lg justify-center">
                                    <?= e($slide['btn_utama_label'] ?? 'Mulai Belanja') ?>
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </a>
                                <?php if (!empty($slide['btn_sekunder_label'])): ?>
                                    <a href="<?= e($slide['btn_sekunder_url'] ?? 'register.php') ?>" class="px-8 py-3 bg-white/10 backdrop-blur border border-white/25 text-white rounded-xl font-semibold hover:bg-white/20 text-center transition-all">
                                        <?= e($slide['btn_sekunder_label']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dots -->
                <div class="flex items-center gap-2 mt-8 md:mt-10" role="tablist" aria-label="Slide beranda">
                    <?php foreach ($heroSlides as $i => $slide): ?>
                        <button type="button" class="hero-dot <?= $i === 0 ? 'is-active' : '' ?>" data-hero-go="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>: <?= e($slide['badge']) ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Arrows -->
        <button type="button" id="hero-prev" class="absolute left-3 md:left-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-11 md:h-11 rounded-full bg-white/15 backdrop-blur border border-white/25 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 md:opacity-100 hover:bg-white/25 transition-all" aria-label="Slide sebelumnya">
            <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <button type="button" id="hero-next" class="absolute right-3 md:right-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 md:w-11 md:h-11 rounded-full bg-white/15 backdrop-blur border border-white/25 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 md:opacity-100 hover:bg-white/25 transition-all" aria-label="Slide berikutnya">
            <span class="material-symbols-outlined">chevron_right</span>
        </button>

        <!-- Progress bar -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/10 z-20">
            <div id="hero-progress" class="h-full bg-leaf-green-light/90 origin-left scale-x-0"></div>
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

    <section class="relative py-16 md:py-24 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-leaf-green-light/40 via-surface to-surface pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-primary/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-24 w-full h-72 rounded-full bg-secondary-container/30 blur-3xl pointer-events-none" aria-hidden="true"></div>
   
        <div class="page-container relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start mb-12 md:mb-16">
                <div class="lg:col-span-5 lg:sticky lg:top-28">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-leaf-green-light text-primary text-label-md font-semibold uppercase tracking-wider mb-5">
                        <span class="material-symbols-outlined text-[18px]">spa</span> Cerita Kami
                    </span>
                    <h2 class="text-headline-lg-mobile md:text-headline-lg text-text-main font-bold leading-tight mb-4">
                        Mengapa <?= e(APP_NAME) ?>?
                    </h2>
                    <p class="text-body-md text-text-muted leading-relaxed mb-6">
                        Inisiatif kewirausahaan sosial berbasis teknologi yang menghubungkan petani lokal dengan konsumen secara adil dan transparan.
                    </p>

                    <div class="rounded-2xl border border-outline-variant/30 bg-white/80 backdrop-blur-sm p-5 tonal-shadow">
                        <p class="text-xs font-bold uppercase tracking-wider text-primary mb-3">Didirikan oleh e-Muda</p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-leaf-green-light/80 text-primary text-xs font-semibold">Nakeisha Ghinavia</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-leaf-green-light/80 text-primary text-xs font-semibold">Auliya Urrasyidin</span>
                        </div>
                        <p class="text-sm text-text-muted leading-relaxed">
                            SMA Al-Karim Bandar Lampung — berangkat dari kepedulian terhadap petani di Pinang Jaya dan sekitarnya.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-5">
                    <div class="rounded-2xl bg-white border border-outline-variant/30 p-6 md:p-8 tonal-shadow">
                        <div class="flex gap-4">
                            <span class="w-1 shrink-0 rounded-full bg-primary self-stretch min-h-[3rem]"></span>
                            <p class="text-body-lg text-text-main font-medium leading-relaxed">
                                <?= e(APP_NAME) ?> lahir dari kepedulian generasi muda yang melihat langsung kerja keras petani yang sering kali tidak sebanding dengan nilai pasar yang diterima.
                            </p>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low/80 border border-outline-variant/25 p-6 md:p-8">
                        <p class="text-body-md text-on-surface-variant leading-relaxed">
                            Sebagai generasi yang dekat dengan sektor pertanian, kami percaya teknologi dapat memperpendek rantai distribusi, memberi peluang lebih adil, dan membuka akses pasar yang lebih luas bagi petani lokal.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-12 md:mb-14">
                <?php foreach ($whyPillars as $i => $pillar): ?>
                    <article class="group relative bg-white rounded-2xl md:rounded-3xl p-6 md:p-7 border border-outline-variant/30 tonal-shadow transition-all duration-300 hover:-translate-y-1 hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5">
                        <span class="absolute top-5 right-6 text-4xl font-bold text-primary/10 select-none"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <div class="w-12 h-12 rounded-2xl bg-leaf-green-light flex items-center justify-center text-primary mb-5 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                            <span class="material-symbols-outlined text-[26px]"><?= e($pillar['icon']) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-text-main mb-2.5 pr-8"><?= e($pillar['title']) ?></h3>
                        <p class="text-sm text-text-muted leading-relaxed"><?= e($pillar['desc']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <blockquote class="relative max-w-3xl mx-auto rounded-2xl md:rounded-3xl overflow-hidden border border-primary/15 bg-white tonal-shadow">
                <div class="absolute inset-0 bg-gradient-to-br from-leaf-green-light/50 via-white to-white pointer-events-none" aria-hidden="true"></div>
                <div class="relative px-6 py-8 md:px-10 md:py-10 text-center">
                    <span class="material-symbols-outlined text-primary/30 text-4xl mb-4 block">format_quote</span>
                    <p class="text-body-lg md:text-xl text-text-main font-medium leading-relaxed italic">
                        "<?= e(APP_NAME) ?> bukan hanya tentang hasil panen yang sampai ke tangan Anda, tetapi tentang harapan, kesempatan, dan dampak sosial yang tumbuh bersama di setiap transaksi."
                    </p>
                    <footer class="mt-5 flex items-center justify-center gap-2 text-sm text-text-muted">
                        <span class="w-8 h-px bg-outline-variant"></span>
                        <span>Visi <?= e(APP_NAME) ?></span>
                        <span class="w-8 h-px bg-outline-variant"></span>
                    </footer>
                </div>
            </blockquote>
        </div>
    </section>

    <section class="py-16 md:py-24 page-container">
        <div class="flex items-end justify-between gap-4 mb-10">
            <div>
                <h2 class="text-headline-lg text-text-main">Produk Terpopuler</h2>
                <p class="text-body-md text-text-muted mt-1">Berdasarkan jumlah penjualan</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button type="button" id="scroll-prev" class="icon-btn border border-outline-variant disabled:opacity-40 disabled:pointer-events-none" aria-label="Produk sebelumnya"><span class="material-symbols-outlined">arrow_back</span></button>
                <button type="button" id="scroll-next" class="icon-btn border border-outline-variant disabled:opacity-40 disabled:pointer-events-none" aria-label="Produk berikutnya"><span class="material-symbols-outlined">arrow_forward</span></button>
            </div>
        </div>
        <div class="overflow-hidden -mx-1 px-1">
            <div id="product-carousel" class="flex gap-4 w-full overflow-x-auto hide-scrollbar scroll-smooth snap-x snap-mandatory pb-1">
                <?php foreach ($popularProducts as $p): ?>
                    <div class="product-carousel-item flex-none w-[260px] sm:w-[280px] md:w-[300px] snap-start">
                        <?php renderProductCard($p, 'index.php'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    
</main>

<?php $showSiteFooter = true; include __DIR__ . '/includes/app-footer.php'; ?>
