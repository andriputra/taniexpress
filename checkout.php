<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireCustomer();

$items = getCartItems();
if (empty($items)) {
    flash('error', 'Keranjang kosong.');
    redirect('cart.php');
}

$user = currentUser();
$subtotal = cartTotal();
$ongkir = ONGKIR_DEFAULT;
$platform = BIAYA_PLATFORM;
$total = $subtotal + $ongkir + $platform;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_penerima'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($nama) || empty($telepon) || empty($alamat)) {
        flash('error', 'Lengkapi data pengiriman.');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $kode = generateKodePesanan();
            $stmt = $pdo->prepare('
                INSERT INTO pesanan (user_id, kode_pesanan, nama_penerima, telepon, alamat, subtotal, ongkir, biaya_platform, total, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "menunggu_pembayaran")
            ');
            $stmt->execute([$user['id'], $kode, $nama, $telepon, $alamat, $subtotal, $ongkir, $platform, $total]);
            $pesananId = (int) $pdo->lastInsertId();

            $detailStmt = $pdo->prepare('INSERT INTO pesanan_detil (pesanan_id, produk_petani_id, qty, harga) VALUES (?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE produk_petani SET stok = stok - ? WHERE id = ? AND stok >= ?');

            foreach ($items as $item) {
                $detailStmt->execute([$pesananId, $item['id'], $item['qty'], $item['harga']]);
                $stockStmt->execute([$item['qty'], $item['id'], $item['qty']]);
                if ($stockStmt->rowCount() === 0) {
                    throw new Exception('Stok ' . $item['produk_nama'] . ' tidak mencukupi.');
                }
                refreshProductStockTotal((int) $item['product_id']);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            redirect('payment.php?id=' . $pesananId);
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', $e->getMessage());
        }
    }
}

$pageTitle = 'Checkout';
$activeNav = 'cart';
$pageHeading = 'Checkout';
$backUrl = 'cart.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <?php renderPageHero('Checkout', 'Lengkapi data pengiriman', 'cart.php'); ?>

    <div class="page-container">
        <form method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl p-6 tonal-shadow">
                <h2 class="text-lg font-bold text-text-main mb-5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">location_on</span> Alamat Pengiriman
                </h2>
                <div class="space-y-4">
                    <div><label class="block text-sm font-medium mb-1.5 text-text-muted">Nama Penerima</label>
                        <input type="text" name="nama_penerima" required value="<?= e($_POST['nama_penerima'] ?? $user['nama']) ?>" class="input-field"/></div>
                    <div><label class="block text-sm font-medium mb-1.5 text-text-muted">No. Telepon</label>
                        <input type="tel" name="telepon" required value="<?= e($_POST['telepon'] ?? $user['telepon']) ?>" class="input-field"/></div>
                    <div><label class="block text-sm font-medium mb-1.5 text-text-muted">Alamat Lengkap</label>
                        <textarea name="alamat" required rows="3" class="input-field"><?= e($_POST['alamat'] ?? $user['alamat']) ?></textarea></div>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-2xl p-6 tonal-shadow mb-4">
                    <h2 class="text-lg font-bold text-text-main mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-3 mb-4">
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-on-surface-variant"><?= e($item['produk_nama']) ?> ×<?= $item['qty'] ?></span>
                                <span class="font-medium"><?= formatRupiah($item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="border-outline-variant/40 mb-4"/>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-text-muted">Subtotal</span><span><?= formatRupiah($subtotal) ?></span></div>
                        <div class="flex justify-between"><span class="text-text-muted">Ongkos Kirim</span><span><?= formatRupiah($ongkir) ?></span></div>
                        <div class="flex justify-between"><span class="text-text-muted">Biaya Platform</span><span><?= formatRupiah($platform) ?></span></div>
                    </div>
                    <hr class="border-outline-variant/40 my-4"/>
                    <div class="flex justify-between text-lg font-bold text-primary">
                        <span>Total Bayar</span><span><?= formatRupiah($total) ?></span>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full"><span class="material-symbols-outlined">payments</span> Bayar Sekarang</button>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
