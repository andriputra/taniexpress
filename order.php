<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireCustomer();

$id = (int) ($_GET['id'] ?? 0);
$user = currentUser();

$stmt = db()->prepare('
    SELECT p.*, k.nama AS kurir_nama, k.telepon AS kurir_telepon
    FROM pesanan p
    LEFT JOIN kurir k ON k.id = p.kurir_id
    WHERE p.id = ? AND p.user_id = ?
');
$stmt->execute([$id, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect('orders.php');
}

$detailStmt = db()->prepare('
    SELECT pd.*, pr.nama AS produk_nama, pr.satuan, pr.gambar, pt.nama AS petani_nama
    FROM pesanan_detil pd
    JOIN produk_petani pp ON pp.id = pd.produk_petani_id
    JOIN products pr ON pr.id = pp.product_id
    JOIN petani pt ON pt.id = pp.petani_id
    WHERE pd.pesanan_id = ?
');
$detailStmt->execute([$id]);
$details = $detailStmt->fetchAll();

$steps = orderSteps();
$currentStepIndex = array_search($order['status'], $steps);
if ($currentStepIndex === false) {
    $currentStepIndex = $order['status'] === 'dibatalkan' ? -1 : 0;
}

// Konfirmasi terima pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_received']) && $order['status'] === 'sampai') {
    db()->prepare('UPDATE pesanan SET status = "selesai" WHERE id = ?')->execute([$id]);
    flash('success', 'Terima kasih! Pesanan selesai.');
    redirect('order.php?id=' . $id);
}

$pageTitle = 'Detail Pesanan';
$activeNav = 'orders';
$pageHeading = 'Detail Pesanan';
$backUrl = 'orders.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <?php renderPageHero('Detail Pesanan', $order['kode_pesanan'], 'orders.php'); ?>

    <div class="page-container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl mx-auto">
        <!-- Status Stepper -->
        <section class="bg-white rounded-2xl p-6 tonal-shadow">
            <h2 class="text-lg font-bold text-text-main mb-1">Lacak Pesanan</h2>
            <p class="text-sm text-text-muted mb-6"><?= e($order['kode_pesanan']) ?></p>

            <?php if ($order['status'] === 'dibatalkan'): ?>
                <div class="p-4 bg-red-50 text-error-red rounded-xl text-center font-semibold">Pesanan Dibatalkan</div>
            <?php else: ?>
                <div class="space-y-0">
                    <?php
                    $stepLabels = [
                        'menunggu_pembayaran' => ['Bayar', 'Lakukan pembayaran QRIS'],
                        'menunggu_verifikasi' => ['Verifikasi', 'Menunggu konfirmasi admin'],
                        'diproses' => ['Diproses', 'Pesanan sedang diproses'],
                        'dikemas' => ['Dikemas', 'Sedang dikemas'],
                        'dikirim' => ['Dikirim', 'Dalam perjalanan'],
                        'sampai' => ['Sampai', 'Pesanan telah sampai'],
                        'selesai' => ['Selesai', 'Pesanan selesai'],
                    ];
                    foreach ($steps as $i => $step):
                        $active = $i <= $currentStepIndex;
                        $current = $i === $currentStepIndex;
                        [$title, $desc] = $stepLabels[$step];
                    ?>
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $active ? 'bg-primary text-white' : 'bg-surface-container-high text-outline' ?> <?= $current ? 'ring-4 ring-leaf-green-light' : '' ?>">
                                    <span class="material-symbols-outlined text-[20px]"><?= $active ? 'check' : 'radio_button_unchecked' ?></span>
                                </div>
                                <?php if ($i < count($steps) - 1): ?>
                                    <div class="w-0.5 h-12 <?= $i < $currentStepIndex ? 'bg-primary' : 'bg-outline-variant' ?>"></div>
                                <?php endif; ?>
                            </div>
                            <div class="pb-8">
                                <p class="font-semibold <?= $active ? 'text-text-main' : 'text-outline' ?>"><?= $title ?></p>
                                <p class="text-sm text-text-muted"><?= $desc ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($order['kurir_nama']): ?>
                <div class="mt-4 p-4 bg-leaf-green-light rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">local_shipping</span>
                    <div>
                        <p class="text-sm font-semibold"><?= e($order['kurir_nama']) ?></p>
                        <p class="text-xs text-text-muted"><?= e($order['kurir_telepon']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($order['status'] === 'menunggu_pembayaran'): ?>
                <a href="payment.php?id=<?= $id ?>" class="mt-4 block w-full py-3 bg-primary text-white text-center rounded-xl font-semibold">Bayar Sekarang</a>
            <?php endif; ?>

            <?php if ($order['status'] === 'sampai'): ?>
                <form method="POST" class="mt-4">
                    <button type="submit" name="confirm_received" value="1" class="w-full py-3 bg-success-green text-white rounded-xl font-semibold">
                        Pesanan Sudah Diterima
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($order['status'] === 'selesai'): ?>
                <div class="mt-6 p-6 bg-earth-tan/50 rounded-2xl text-center">
                    <h3 class="text-xl font-bold text-primary mb-2">Terima Kasih!</h3>
                    <p class="text-on-surface-variant text-sm mb-4">Terima kasih telah mempercayakan belanja sayur Anda kepada TaniExpress.</p>
                    <div class="grid grid-cols-3 gap-2">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBz9t_wRXaRC9NVBqWqIJty9DlQI69MoJtuWs8p5_fb9f5ZFhqkwh9CO9erVDYAMcz9G7ZbL-W9__KGF7KygVhCQ0nLmTvt3aUgXvLZ_c_ZFZP4pqL_Gsyf-37rKyvj3BsiHD4Qv68YEjOpt8Ynn7h1zuNSSvC_Aq27nOtZefaxciP9j1Wvn24NAMurj4utqISV36sLyyZnd-D4oAGwwPKR1cSL6fAprXjbXiyQ9GQlE5iTimslbAiujf4Ktawac1s9XG1QsGNR19YY" class="rounded-lg h-20 object-cover w-full" alt=""/>
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDpNh1RVfev1fkjcwn1FSugvisPsudFtdeUoaPqY0bJTZBRz9_WXApOrLza1MECssg8hBImwzHtbrzz2p1v43SnCiVzoLIDv3mbXihRppNMl8K2L3HmZadG6Y8hwRoBWcx66WtYJl71oQk2cezjjLjIXclDyd4FR8GTlEY4IJb5_uWcfdyyfAo0-dWDi4Ih-TK66ABpFCsW-THx-E2VTeBvQOVA4Hn7IHFZ9sCB-LXmvRLRkL2E23qpfFC7bYjfHgCfp6BnyUtR1yPK" class="rounded-lg h-20 object-cover w-full" alt=""/>
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBM7_SB2qxuQSfJSd5ZmafP-cpUksEdKnj6AlGqIGpnlRa68QIt-qicZGFaufMK9wv32H_DsaL3kaU7qSwr92FfxeUPinQ_T5zCMHI7EegDKcdiGuSviNlb3Wkgz0zJ8V7jghjZ7yvozkhAc82s8KSf43AcEDBRi0m1ooCkj2O21YmwLEJmzTNbFm3nxNs16PHgHYoYeW3ro3kYBUzKcv3FZlUMb2_EECkbrlLq54lwMM83OJOrI2AfrKPX4j-Jd_XqXEteMN524lhp" class="rounded-lg h-20 object-cover w-full" alt=""/>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Detail -->
        <section class="bg-white rounded-2xl p-6 tonal-shadow">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold mb-4 <?= statusColor($order['status']) ?>">
                <?= statusLabel($order['status']) ?>
            </span>

            <h3 class="font-semibold text-text-main mb-2">Alamat Pengiriman</h3>
            <p class="text-sm text-on-surface-variant mb-1"><?= e($order['nama_penerima']) ?> • <?= e($order['telepon']) ?></p>
            <p class="text-sm text-on-surface-variant mb-6"><?= e($order['alamat']) ?></p>

            <h3 class="font-semibold text-text-main mb-3">Item Pesanan</h3>
            <div class="space-y-3 mb-6">
                <?php foreach ($details as $d): ?>
                    <div class="flex gap-3">
                        <img src="<?= e($d['gambar']) ?>" class="w-14 h-14 rounded-lg object-cover" alt=""/>
                        <div class="flex-1">
                            <p class="font-medium text-sm"><?= e($d['produk_nama']) ?></p>
                            <p class="text-xs text-text-muted"><?= e($d['petani_nama']) ?> • x<?= $d['qty'] ?></p>
                        </div>
                        <p class="text-sm font-semibold"><?= formatRupiah($d['qty'] * $d['harga']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="border-outline-variant/50 mb-4"/>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span><?= formatRupiah($order['subtotal']) ?></span></div>
                <div class="flex justify-between"><span>Ongkir</span><span><?= formatRupiah($order['ongkir']) ?></span></div>
                <div class="flex justify-between"><span>Biaya Platform</span><span><?= formatRupiah($order['biaya_platform']) ?></span></div>
                <div class="flex justify-between font-bold text-primary text-base pt-2"><span>Total</span><span><?= formatRupiah($order['total']) ?></span></div>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
