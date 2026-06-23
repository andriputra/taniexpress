<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
ensureHeroSlidesTable();

$gradientOptions = heroGradientOptions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'move') {
        swapHeroSlideOrder((int) ($_POST['id'] ?? 0), $_POST['direction'] ?? 'up');
        flash('success', 'Urutan slide diperbarui.');
        redirect('hero.php');
    }

    if ($action === 'toggle') {
        $slide = getHeroSlide((int) ($_POST['id'] ?? 0));
        if ($slide) {
            $newStatus = $slide['aktif'] ? 0 : 1;
            db()->prepare('UPDATE hero_slides SET aktif = ? WHERE id = ?')->execute([$newStatus, $slide['id']]);
            flash('success', $newStatus ? 'Slide diaktifkan.' : 'Slide dinonaktifkan.');
        }
        redirect('hero.php');
    }

    if ($action === 'delete') {
        $slide = getHeroSlide((int) ($_POST['id'] ?? 0));
        if ($slide) {
            removeUploadedFile($slide['gambar']);
            db()->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$slide['id']]);
            flash('success', 'Slide hero dihapus.');
        }
        redirect('hero.php');
    }

    if ($action === 'create' || $action === 'update') {
        $badge = trim($_POST['badge'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $gradient = $_POST['gradient'] ?? 'from-primary/80';
        $btnUtamaLabel = trim($_POST['btn_utama_label'] ?? 'Mulai Belanja');
        $btnUtamaUrl = trim($_POST['btn_utama_url'] ?? 'home.php');
        $btnSekunderLabel = trim($_POST['btn_sekunder_label'] ?? '');
        $btnSekunderUrl = trim($_POST['btn_sekunder_url'] ?? 'register.php');
        $gambarUrl = trim($_POST['gambar_url'] ?? '');
        $aktif = isset($_POST['aktif']) ? 1 : 0;
        $editId = (int) ($_POST['id'] ?? 0);

        if (!array_key_exists($gradient, $gradientOptions)) {
            $gradient = 'from-primary/80';
        }

        $redirectEdit = $action === 'update' ? '?edit=' . $editId : '?add=1';

        if ($badge === '' || $judul === '' || $deskripsi === '') {
            flash('error', 'Badge, judul, dan deskripsi wajib diisi.');
            redirect('hero.php' . $redirectEdit);
        }

        $gambar = $action === 'update' ? ($_POST['gambar_lama'] ?? '') : '';
        if ($gambarUrl !== '') {
            $gambar = $gambarUrl;
        }

        if (!empty($_FILES['gambar']['name'])) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                flash('error', 'Gagal mengunggah gambar slide.');
                redirect('hero.php' . $redirectEdit);
            }
            $uploaded = uploadFile($_FILES['gambar'], 'hero');
            if (!$uploaded) {
                flash('error', 'Format gambar tidak valid. Gunakan JPG, PNG, atau WEBP.');
                redirect('hero.php' . $redirectEdit);
            }
            if ($action === 'update' && $gambar && !str_starts_with($gambar, 'http')) {
                removeUploadedFile($gambar);
            }
            $gambar = $uploaded;
        }

        if ($gambar === '') {
            flash('error', 'Gambar slide wajib diunggah atau diisi URL-nya.');
            redirect('hero.php' . $redirectEdit);
        }

        if ($action === 'create') {
            $urutan = getNextHeroSlideOrder();
            db()->prepare('
                INSERT INTO hero_slides
                (badge, judul, deskripsi, gambar, gradient, btn_utama_label, btn_utama_url, btn_sekunder_label, btn_sekunder_url, urutan, aktif)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ')->execute([
                $badge, $judul, $deskripsi, $gambar, $gradient,
                $btnUtamaLabel, $btnUtamaUrl,
                $btnSekunderLabel !== '' ? $btnSekunderLabel : null,
                $btnSekunderUrl !== '' ? $btnSekunderUrl : null,
                $urutan, $aktif,
            ]);
            flash('success', 'Slide hero berhasil ditambahkan.');
        } else {
            db()->prepare('
                UPDATE hero_slides SET
                    badge = ?, judul = ?, deskripsi = ?, gambar = ?, gradient = ?,
                    btn_utama_label = ?, btn_utama_url = ?, btn_sekunder_label = ?, btn_sekunder_url = ?, aktif = ?
                WHERE id = ?
            ')->execute([
                $badge, $judul, $deskripsi, $gambar, $gradient,
                $btnUtamaLabel, $btnUtamaUrl,
                $btnSekunderLabel !== '' ? $btnSekunderLabel : null,
                $btnSekunderUrl !== '' ? $btnSekunderUrl : null,
                $aktif, $editId,
            ]);
            flash('success', 'Slide hero berhasil diperbarui.');
        }

        redirect('hero.php');
    }
}

$slides = db()->query('SELECT * FROM hero_slides ORDER BY urutan ASC, id ASC')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$isAdd = isset($_GET['add']);
$edit = $editId ? getHeroSlide($editId) : null;
$showForm = $isAdd || $edit;
$formPhotoSrc = $edit ? mediaSrc($edit['gambar'] ?? null, true) : null;

$pageTitle = 'Hero Beranda';
$activeMenu = 'hero';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-text-muted max-w-2xl">
        Kelola slide banner di halaman beranda. Atur teks, gambar latar, tombol aksi, dan urutan tampilan.
    </p>
    <?php if (!$showForm): ?>
        <a href="hero.php?add=1" class="inline-flex items-center justify-center gap-2 shrink-0 py-2.5 px-5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Slide
        </a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 <?= $showForm ? 'xl:grid-cols-3' : '' ?> gap-4 md:gap-6">
    <div class="<?= $showForm ? 'xl:col-span-2 order-1 xl:order-2' : '' ?>">
        <?php if (empty($slides)): ?>
            <div class="bg-white rounded-2xl p-10 text-center border border-outline-variant/30">
                <span class="material-symbols-outlined text-5xl text-outline mb-3">view_carousel</span>
                <p class="font-semibold text-text-main">Belum ada slide</p>
                <a href="hero.php?add=1" class="text-sm text-primary font-semibold mt-2 inline-block hover:underline">Tambah slide pertama</a>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($slides as $index => $slide):
                    $thumb = mediaSrc($slide['gambar'], true) ?? $slide['gambar'];
                ?>
                    <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden <?= $editId === (int) $slide['id'] ? 'ring-2 ring-primary/30' : '' ?>">
                        <div class="flex flex-col sm:flex-row">
                            <div class="sm:w-44 h-32 sm:h-auto shrink-0 relative">
                                <img src="<?= e($thumb) ?>" alt="" class="w-full h-full object-cover"/>
                                <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/50 text-white">#<?= (int) $slide['urutan'] ?></span>
                            </div>
                            <div class="flex-1 p-4 min-w-0">
                                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0">
                                        <span class="text-[10px] uppercase tracking-wider font-bold text-primary"><?= e($slide['badge']) ?></span>
                                        <h3 class="font-bold text-text-main truncate"><?= e($slide['judul']) ?></h3>
                                        <p class="text-xs text-text-muted line-clamp-2 mt-1"><?= e($slide['deskripsi']) ?></p>
                                    </div>
                                    <span class="text-[10px] px-2 py-1 rounded-full font-semibold shrink-0 <?= $slide['aktif'] ? 'bg-leaf-green-light text-primary' : 'bg-surface-container-high text-outline' ?>">
                                        <?= $slide['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <a href="hero.php?edit=<?= $slide['id'] ?>" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-leaf-green-light/70 text-primary hover:bg-leaf-green-light">Edit</a>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle"/>
                                        <input type="hidden" name="id" value="<?= $slide['id'] ?>"/>
                                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-full border border-outline-variant/40 text-text-muted hover:text-primary">
                                            <?= $slide['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                    <?php if ($index > 0): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="move"/>
                                            <input type="hidden" name="id" value="<?= $slide['id'] ?>"/>
                                            <input type="hidden" name="direction" value="up"/>
                                            <button type="submit" class="w-8 h-8 rounded-full border border-outline-variant/40 flex items-center justify-center text-primary hover:bg-leaf-green-light" title="Naikkan">
                                                <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($index < count($slides) - 1): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="move"/>
                                            <input type="hidden" name="id" value="<?= $slide['id'] ?>"/>
                                            <input type="hidden" name="direction" value="down"/>
                                            <button type="submit" class="w-8 h-8 rounded-full border border-outline-variant/40 flex items-center justify-center text-primary hover:bg-leaf-green-light" title="Turunkan">
                                                <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" class="inline ml-auto" data-confirm="Hapus slide ini dari beranda?" data-confirm-title="Hapus Slide">
                                        <input type="hidden" name="action" value="delete"/>
                                        <input type="hidden" name="id" value="<?= $slide['id'] ?>"/>
                                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-full text-error-red border border-error-red/20 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($showForm): ?>
    <div class="bg-white rounded-2xl p-4 md:p-6 shadow-sm border border-outline-variant/30 h-fit order-2 xl:order-1 xl:sticky xl:top-24">
        <div class="flex items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 rounded-xl bg-leaf-green-light flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined"><?= $edit ? 'edit' : 'add_photo_alternate' ?></span>
                </span>
                <div class="min-w-0">
                    <h3 class="font-bold text-text-main"><?= $edit ? 'Edit Slide' : 'Tambah Slide' ?></h3>
                    <p class="text-xs text-text-muted">Konten hero beranda</p>
                </div>
            </div>
            <a href="hero.php" class="text-sm text-text-muted hover:text-primary shrink-0">Tutup</a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>"/>
                <input type="hidden" name="gambar_lama" value="<?= e($edit['gambar'] ?? '') ?>"/>
            <?php endif; ?>

            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Badge / Label</label>
                <input name="badge" required value="<?= e($edit['badge'] ?? '') ?>" placeholder="Smart Distribution" class="input-field text-sm"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Judul Utama</label>
                <input name="judul" required value="<?= e($edit['judul'] ?? '') ?>" placeholder="Empowering Farmers..." class="input-field text-sm"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" required rows="3" class="input-field text-sm" placeholder="Teks pendukung di bawah judul..."><?= e($edit['deskripsi'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text-muted mb-2">Gambar Latar</label>
                <?php if ($formPhotoSrc): ?>
                    <img src="<?= e($formPhotoSrc) ?>" alt="" class="w-full h-28 object-cover rounded-xl mb-2 border border-outline-variant/30" id="hero-preview-img"/>
                <?php endif; ?>
                <input type="file" name="gambar" id="hero-gambar-input" accept="image/jpeg,image/png,image/webp,image/gif" class="input-field text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-primary file:text-white file:text-xs"/>
                <input type="url" name="gambar_url" value="<?= e(($edit && str_starts_with($edit['gambar'] ?? '', 'http')) ? $edit['gambar'] : '') ?>" placeholder="Atau tempel URL gambar eksternal" class="input-field text-sm mt-2"/>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Gradasi Overlay</label>
                <select name="gradient" class="input-field text-sm">
                    <?php foreach ($gradientOptions as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($edit['gradient'] ?? 'from-primary/80') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-3 pt-1 border-t border-outline-variant/20">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wide pt-2">Tombol Utama</p>
                <input name="btn_utama_label" value="<?= e($edit['btn_utama_label'] ?? 'Mulai Belanja') ?>" placeholder="Label tombol" class="input-field text-sm"/>
                <input name="btn_utama_url" value="<?= e($edit['btn_utama_url'] ?? 'home.php') ?>" placeholder="home.php" class="input-field text-sm"/>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Tombol Sekunder (opsional)</p>
                <input name="btn_sekunder_label" value="<?= e($edit['btn_sekunder_label'] ?? 'Daftar Gratis') ?>" placeholder="Daftar Gratis" class="input-field text-sm"/>
                <input name="btn_sekunder_url" value="<?= e($edit['btn_sekunder_url'] ?? 'register.php') ?>" placeholder="register.php" class="input-field text-sm"/>
            </div>

            <label class="flex items-center gap-2 text-sm text-text-main cursor-pointer">
                <input type="checkbox" name="aktif" value="1" <?= ($edit['aktif'] ?? 1) ? 'checked' : '' ?> class="rounded border-outline-variant text-primary focus:ring-primary"/>
                Tampilkan slide di beranda
            </label>

            <button type="submit" class="w-full py-3 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors">
                <?= $edit ? 'Simpan Perubahan' : 'Simpan Slide' ?>
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const input = document.getElementById('hero-gambar-input');
    const preview = document.getElementById('hero-preview-img');
    if (!input) return;
    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview) preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
