<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $nama = trim($_POST['nama'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $foto = $action === 'update' ? ($_POST['foto_lama'] ?? null) : null;

        if (empty($nama) || empty($telepon) || empty($alamat)) {
            flash('error', 'Nama, telepon, dan alamat wajib diisi.');
            redirect('farmers.php' . ($action === 'update' ? '?edit=' . (int) ($_POST['id'] ?? 0) : ''));
        }

        if (!empty($_FILES['foto']['name'])) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                flash('error', 'Gagal mengunggah foto. Coba lagi.');
                redirect('farmers.php' . ($action === 'update' ? '?edit=' . (int) ($_POST['id'] ?? 0) : ''));
            }
            $uploaded = uploadFile($_FILES['foto'], 'petani');
            if (!$uploaded) {
                flash('error', 'Format foto tidak valid. Gunakan JPG, PNG, atau WebP.');
                redirect('farmers.php' . ($action === 'update' ? '?edit=' . (int) ($_POST['id'] ?? 0) : ''));
            }
            $foto = $uploaded;
        }

        if ($action === 'create') {
            db()->prepare('INSERT INTO petani (nama, telepon, alamat, foto) VALUES (?,?,?,?)')
                ->execute([$nama, $telepon, $alamat, $foto]);
            flash('success', 'Data petani berhasil ditambahkan.');
        } else {
            db()->prepare('UPDATE petani SET nama=?, telepon=?, alamat=?, foto=? WHERE id=?')
                ->execute([$nama, $telepon, $alamat, $foto, (int) $_POST['id']]);
            flash('success', 'Data petani berhasil diperbarui.');
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM petani WHERE id=?')->execute([(int) $_POST['id']]);
        flash('success', 'Petani berhasil dihapus.');
    }

    redirect('farmers.php');
}

$farmers = db()->query('SELECT p.*, COUNT(pp.id) AS jumlah_produk FROM petani p LEFT JOIN produk_petani pp ON pp.petani_id = p.id GROUP BY p.id ORDER BY p.nama')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM petani WHERE id=?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}

$editPhotoSrc = $edit ? mediaSrc($edit['foto'] ?? null, true) : null;

$pageTitle = 'Manajemen Petani';
$activeMenu = 'farmers';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
    <!-- Daftar petani (utama di mobile) -->
    <div class="lg:col-span-2 order-1 lg:order-2">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-text-muted"><strong class="text-text-main"><?= count($farmers) ?></strong> petani terdaftar</p>
        </div>

        <?php if (empty($farmers)): ?>
            <div class="bg-white rounded-2xl p-8 md:p-12 text-center border border-outline-variant/30">
                <span class="material-symbols-outlined text-5xl text-outline mb-3">agriculture</span>
                <p class="font-semibold text-text-main">Belum ada petani</p>
                <p class="text-sm text-text-muted mt-1">Tambahkan petani pertama menggunakan form di bawah.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                <?php foreach ($farmers as $f):
                    $photoSrc = mediaSrc($f['foto'] ?? null, true);
                ?>
                    <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-outline-variant/30 hover:border-primary/20 transition-all">
                        <div class="flex gap-4">
                            <?php if ($photoSrc): ?>
                                <img src="<?= e($photoSrc) ?>" alt="<?= e($f['nama']) ?>" class="w-14 h-14 rounded-full object-cover ring-2 ring-leaf-green-light shrink-0"/>
                            <?php else: ?>
                                <div class="w-14 h-14 rounded-full bg-leaf-green-light flex items-center justify-center text-primary font-bold text-lg shrink-0">
                                    <?= strtoupper(substr($f['nama'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-text-main truncate"><?= e($f['nama']) ?></p>
                                <p class="text-xs text-text-muted flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-outlined text-[14px]">call</span><?= e($f['telepon']) ?>
                                </p>
                                <p class="text-xs text-text-muted flex items-center gap-1 mt-0.5 truncate">
                                    <span class="material-symbols-outlined text-[14px] shrink-0">location_on</span><?= e($f['alamat']) ?>
                                </p>
                                <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-0.5 rounded-full bg-leaf-green-light text-primary text-[11px] font-semibold">
                                    <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                                    <?= $f['jumlah_produk'] ?> produk
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4 pt-4 border-t border-outline-variant/20">
                            <a href="?edit=<?= $f['id'] ?>" class="flex-1 py-2 text-center text-sm font-semibold text-primary bg-leaf-green-light/60 hover:bg-leaf-green-light rounded-full transition-colors">Edit</a>
                            <form method="POST" class="flex-1" data-confirm="Hapus data petani <?= e($f['nama']) ?>? Produk terkait mungkin terpengaruh." data-confirm-title="Hapus Petani">
                                <input type="hidden" name="action" value="delete"/>
                                <input type="hidden" name="id" value="<?= $f['id'] ?>"/>
                                <button type="submit" class="w-full py-2 text-sm font-semibold text-error-red border border-error-red/20 hover:bg-red-50 rounded-full transition-colors">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-2xl p-4 md:p-6 shadow-sm border border-outline-variant/30 h-fit order-2 lg:order-1 lg:sticky lg:top-24">
        <div class="flex items-center gap-3 mb-6">
            <span class="w-10 h-10 rounded-xl bg-leaf-green-light flex items-center justify-center text-primary">
                <span class="material-symbols-outlined"><?= $edit ? 'edit' : 'person_add' ?></span>
            </span>
            <div>
                <h3 class="font-bold text-text-main"><?= $edit ? 'Edit Petani' : 'Tambah Petani' ?></h3>
                <p class="text-xs text-text-muted"><?= $edit ? 'Perbarui data petani' : 'Daftarkan petani baru' ?></p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"/>
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>"/>
                <input type="hidden" name="foto_lama" value="<?= e($edit['foto'] ?? '') ?>"/>
            <?php endif; ?>

            <!-- Upload foto -->
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-2">Foto Petani</label>
                <label for="foto-input" class="group relative block cursor-pointer rounded-2xl border-2 border-dashed border-outline-variant/50 hover:border-primary/40 bg-surface-container-low/50 hover:bg-leaf-green-light/30 transition-all overflow-hidden">
                    <input type="file" name="foto" id="foto-input" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only"/>
                    <div id="photo-preview" class="flex flex-col items-center justify-center py-8 px-4 min-h-[160px]">
                        <?php if ($editPhotoSrc): ?>
                            <img src="<?= e($editPhotoSrc) ?>" alt="" class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-md mb-3" id="preview-img"/>
                            <p class="text-sm font-medium text-text-main">Klik untuk ganti foto</p>
                            <p class="text-xs text-text-muted mt-1">JPG, PNG, WebP — maks. 2MB</p>
                        <?php else: ?>
                            <span class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-primary mb-3 shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-3xl">add_a_photo</span>
                            </span>
                            <p class="text-sm font-medium text-text-main">Upload foto petani</p>
                            <p class="text-xs text-text-muted mt-1">Klik atau seret file ke sini</p>
                            <p class="text-[11px] text-outline mt-2">JPG, PNG, WebP — opsional</p>
                        <?php endif; ?>
                    </div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Nama Lengkap</label>
                <input name="nama" required value="<?= e($edit['nama'] ?? '') ?>" placeholder="Contoh: Pak Tono" class="input-field text-sm"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">No. Telepon</label>
                <input name="telepon" required type="tel" value="<?= e($edit['telepon'] ?? '') ?>" placeholder="0812xxxxxxx" class="input-field text-sm"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1.5">Alamat</label>
                <textarea name="alamat" required rows="2" placeholder="Kecamatan, Kota" class="input-field text-sm"><?= e($edit['alamat'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors shadow-sm">
                <?= $edit ? 'Simpan Perubahan' : 'Simpan Petani' ?>
            </button>
            <?php if ($edit): ?>
                <a href="farmers.php" class="block text-center text-sm text-text-muted hover:text-primary py-1">Batal edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById('foto-input');
    const preview = document.getElementById('photo-preview');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            window.TaniUI?.showToast('error', 'Ukuran foto maksimal 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="" class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-md mb-3" id="preview-img"/>' +
                '<p class="text-sm font-medium text-text-main">Foto siap diunggah</p>' +
                '<p class="text-xs text-text-muted mt-1">' + file.name + '</p>';
        };
        reader.readAsDataURL(file);
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
