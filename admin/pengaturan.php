<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$qrisImage = getQrisImage();
$merchantName = getAppSetting('qris_merchant_name', APP_NAME) ?? APP_NAME;
$qrisNotes = getAppSetting('qris_notes', '') ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'upload';

    if ($action === 'remove') {
        removeUploadedFile($qrisImage);
        setAppSetting('qris_image', null);
        flash('success', 'Gambar QRIS dihapus.');
        redirect('pengaturan.php');
    }

    $merchantName = trim($_POST['qris_merchant_name'] ?? APP_NAME);
    $qrisNotes = trim($_POST['qris_notes'] ?? '');
    setAppSetting('qris_merchant_name', $merchantName !== '' ? $merchantName : APP_NAME);
    setAppSetting('qris_notes', $qrisNotes !== '' ? $qrisNotes : null);

    if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['qris_image']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Gagal mengunggah file. Coba lagi dengan format JPG atau PNG.');
            redirect('pengaturan.php');
        }

        $uploaded = uploadFile($_FILES['qris_image'], 'qris');
        if (!$uploaded) {
            flash('error', 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            redirect('pengaturan.php');
        }

        removeUploadedFile($qrisImage);
        setAppSetting('qris_image', $uploaded);
        flash('success', 'QRIS pembayaran berhasil diperbarui.');
    } else {
        flash('success', 'Pengaturan QRIS disimpan.');
    }

    redirect('pengaturan.php');
}

$qrisImage = getQrisImage();
$merchantName = getAppSetting('qris_merchant_name', APP_NAME) ?? APP_NAME;
$qrisNotes = getAppSetting('qris_notes', '') ?? '';
$isConfigured = isQrisConfigured();

$pageTitle = 'Pengaturan QRIS';
$activeMenu = 'pengaturan';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="mb-6">
    <p class="text-sm text-text-muted max-w-2xl">
        Unggah gambar QRIS statis dari merchant/e-wallet Anda. Pelanggan akan memindai kode ini saat checkout
        dan membayar sesuai nominal pesanan.
    </p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">
    <div class="xl:col-span-2 space-y-4 md:space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant/30 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-leaf-green-light text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">qr_code_2</span>
                    </span>
                    <div>
                        <h3 class="font-semibold text-text-main">Gambar QRIS</h3>
                        <p class="text-xs text-text-muted">PNG atau JPG, disarankan minimal 400×400 px</p>
                    </div>
                </div>
                <span class="text-xs px-3 py-1 rounded-full font-medium <?= $isConfigured ? 'bg-leaf-green-light text-primary' : 'bg-tertiary-container/20 text-tertiary-container' ?>">
                    <?= $isConfigured ? 'Aktif' : 'Belum diatur' ?>
                </span>
            </div>

            <form method="POST" enctype="multipart/form-data" class="p-5 space-y-5" id="qris-form">
                <input type="hidden" name="action" value="upload"/>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-1.5">Nama Merchant / Rekening</label>
                    <input type="text" name="qris_merchant_name" value="<?= e($merchantName) ?>"
                           placeholder="Contoh: TaniExpress - BCA"
                           class="input-field"/>
                    <p class="text-xs text-text-muted mt-1.5">Ditampilkan di halaman pembayaran pelanggan.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-1.5">Catatan untuk Pelanggan (opsional)</label>
                    <textarea name="qris_notes" rows="2" class="input-field"
                              placeholder="Contoh: Pastikan nominal transfer sesuai total pesanan."><?= e($qrisNotes) ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Upload Gambar QRIS</label>
                    <label for="qris_image" id="qris-dropzone"
                           class="group relative flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-outline-variant/60 bg-surface-container-low/40 px-6 py-10 cursor-pointer transition hover:border-primary/50 hover:bg-leaf-green-light/20">
                        <span class="material-symbols-outlined text-4xl text-primary/70 group-hover:text-primary">cloud_upload</span>
                        <div class="text-center">
                            <p class="font-medium text-text-main">Klik atau seret gambar QRIS ke sini</p>
                            <p class="text-xs text-text-muted mt-1">JPG, PNG, WEBP — maks. 5 MB</p>
                        </div>
                        <input type="file" name="qris_image" id="qris_image" accept="image/jpeg,image/png,image/webp,image/gif"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"/>
                    </label>
                    <p id="qris-file-name" class="text-sm text-primary mt-2 hidden"></p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="py-3 px-4 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Simpan Pengaturan
                    </button>
                    <?php if ($isConfigured): ?>
                        <button type="submit" name="action" value="remove" formnovalidate
                                onclick="return confirm('Hapus gambar QRIS? Pelanggan tidak bisa membayar sampai diunggah ulang.');"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-error-red/30 text-error-red hover:bg-error-red/5 text-sm font-medium transition">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            Hapus QRIS
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden sticky top-4">
            <div class="px-5 py-4 border-b border-outline-variant/30">
                <h3 class="font-semibold">Pratinjau Pelanggan</h3>
                <p class="text-xs text-text-muted mt-0.5">Tampilan di halaman pembayaran</p>
            </div>
            <div class="p-5 text-center">
                <?php if ($isConfigured): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-leaf-green-light text-primary rounded-full text-xs font-semibold mb-4">
                        <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span> Scan QRIS
                    </span>
                    <p class="text-xs text-text-muted mb-1"><?= e($merchantName) ?></p>
                    <p class="text-2xl font-bold text-primary mb-4">Rp 125.000</p>
                    <div class="inline-block p-3 bg-white rounded-xl border border-outline-variant shadow-sm mb-4">
                        <img src="<?= e(mediaSrc($qrisImage, true)) ?>" alt="Pratinjau QRIS"
                             class="w-40 h-40 object-contain mx-auto" id="qris-preview-current"/>
                    </div>
                    <?php if ($qrisNotes !== ''): ?>
                        <p class="text-xs text-on-surface-variant bg-surface-container-low rounded-lg p-3 text-left"><?= e($qrisNotes) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="py-8 px-4 rounded-xl bg-surface-container-low border border-dashed border-outline-variant/50">
                        <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">qr_code_2</span>
                        <p class="font-medium text-text-main text-sm">Belum ada QRIS</p>
                        <p class="text-xs text-text-muted mt-2">Unggah gambar QRIS merchant Anda agar pelanggan bisa membayar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-leaf-green-light/50 rounded-xl p-4 border border-primary/10">
            <h4 class="font-semibold text-sm text-primary mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">tips_and_updates</span> Tips
            </h4>
            <ul class="text-xs text-on-surface-variant space-y-1.5 list-disc list-inside">
                <li>Gunakan QRIS statis dari aplikasi bank atau e-wallet.</li>
                <li>Pelanggan membayar manual sesuai total pesanan.</li>
                <li>Setelah bayar, pelanggan upload bukti dan Anda verifikasi di menu Pesanan.</li>
            </ul>
        </div>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById('qris_image');
    const fileName = document.getElementById('qris-file-name');
    const dropzone = document.getElementById('qris-dropzone');
    const preview = document.getElementById('qris-preview-current');

    if (!input) return;

    function showSelected(file) {
        if (!file) return;
        fileName.textContent = 'File dipilih: ' + file.name;
        fileName.classList.remove('hidden');
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview) preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () {
        if (input.files && input.files[0]) showSelected(input.files[0]);
    });

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(function (ev) {
            dropzone.addEventListener(ev, function (e) {
                e.preventDefault();
                dropzone.classList.add('border-primary', 'bg-leaf-green-light/30');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            dropzone.addEventListener(ev, function (e) {
                e.preventDefault();
                dropzone.classList.remove('border-primary', 'bg-leaf-green-light/30');
            });
        });
        dropzone.addEventListener('drop', function (e) {
            const files = e.dataTransfer.files;
            if (files.length) {
                input.files = files;
                showSelected(files[0]);
            }
        });
    }
})();
</script>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
