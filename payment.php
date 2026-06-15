<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireCustomer();

$id = (int) ($_GET['id'] ?? 0);
$user = currentUser();

$stmt = db()->prepare('SELECT * FROM pesanan WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$order = $stmt->fetch();

if (!$order) { flash('error', 'Pesanan tidak ditemukan.'); redirect('orders.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($order['status'] !== 'menunggu_pembayaran') {
        flash('error', 'Pesanan sudah dibayar.');
        redirect('order.php?id=' . $id);
    }
    if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Upload bukti pembayaran (screenshot).');
    } else {
        $bukti = uploadFile($_FILES['bukti'], 'bukti');
        if ($bukti) {
            db()->prepare('UPDATE pesanan SET bukti_bayar = ?, status = "menunggu_verifikasi" WHERE id = ?')->execute([$bukti, $id]);
            flash('success', 'Bukti pembayaran terkirim. Menunggu verifikasi admin.');
            redirect('order.php?id=' . $id);
        }
        flash('error', 'Gagal upload file. Gunakan format JPG/PNG.');
    }
}

$pageTitle = 'Pembayaran QRIS';
$activeNav = 'orders';
$pageHeading = 'Pembayaran';
$backUrl = 'orders.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <?php renderPageHero('Pembayaran QRIS', 'Kode: ' . $order['kode_pesanan'], 'orders.php'); ?>

    <div class="page-container max-w-lg mx-auto">
        <div class="bg-white rounded-2xl p-6 tonal-shadow border-2 border-primary/10 text-center mb-6">
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-leaf-green-light text-primary rounded-full text-sm font-semibold mb-4">
                <span class="material-symbols-outlined text-[18px]">qr_code_2</span> Scan QRIS
            </span>
            <p class="text-sm text-text-muted mb-1">Total Pembayaran</p>
            <p class="text-3xl font-bold text-primary mb-6"><?= formatRupiah($order['total']) ?></p>
            <div class="inline-block p-4 bg-white rounded-xl border border-outline-variant mb-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=TaniExpress-<?= urlencode($order['kode_pesanan']) ?>-<?= $order['total'] ?>" alt="QRIS" class="w-44 h-44 mx-auto"/>
            </div>
            <ol class="text-left text-sm text-on-surface-variant space-y-1.5 bg-surface-container-low rounded-xl p-4">
                <li>1. Buka e-wallet / mobile banking</li>
                <li>2. Pilih bayar via QRIS</li>
                <li>3. Scan kode QR di atas</li>
                <li>4. Bayar <strong><?= formatRupiah($order['total']) ?></strong></li>
                <li>5. Upload screenshot bukti di bawah</li>
            </ol>
        </div>

        <?php if ($order['status'] === 'menunggu_pembayaran'): ?>
            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl p-6 tonal-shadow">
                <h2 class="font-semibold text-text-main mb-4">Upload Bukti Pembayaran</h2>
                <p class="text-sm text-text-muted mb-4">Scan QRIS, bayar sesuai total, lalu upload screenshot bukti transfer.</p>
                <input type="file" name="bukti" accept="image/*" required class="input-field mb-4 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:text-sm"/>
                <button type="submit" class="btn-primary w-full"><span class="material-symbols-outlined">upload</span> Konfirmasi Pembayaran</button>
            </form>
        <?php elseif ($order['status'] === 'menunggu_verifikasi'): ?>
            <div class="text-center p-8 bg-sun-tint/40 rounded-2xl tonal-shadow border border-tertiary-container/20">
                <span class="material-symbols-outlined text-tertiary-container text-5xl mb-3">pending</span>
                <p class="font-semibold text-text-main text-lg">Menunggu Verifikasi Admin</p>
                <p class="text-sm text-text-muted mt-2">Bukti pembayaran sudah terkirim. Admin akan memverifikasi dalam waktu singkat.</p>
                <a href="order.php?id=<?= $id ?>" class="btn-primary mt-5 inline-flex">Lihat Status Pesanan</a>
            </div>
        <?php else: ?>
            <div class="text-center p-8 bg-leaf-green-light rounded-2xl tonal-shadow">
                <span class="material-symbols-outlined text-success-green text-5xl mb-3">check_circle</span>
                <p class="font-semibold text-primary text-lg">Pembayaran Dikonfirmasi</p>
                <a href="order.php?id=<?= $id ?>" class="btn-primary mt-5 inline-flex">Lihat Status Pesanan</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
