<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireCustomer();

$user = currentUser();
$stmt = db()->prepare('SELECT p.*, k.nama AS kurir_nama FROM pesanan p LEFT JOIN kurir k ON k.id = p.kurir_id WHERE p.user_id = ? ORDER BY p.created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'Pesanan Saya';
$activeNav = 'orders';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/app-header.php';
?>

<main class="page-main">
    <div class="page-container pt-6">
        <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-text-main mb-6">Riwayat Pesanan</h1>

        <?php if (empty($orders)): ?>
            <?php renderEmptyState('receipt_long', 'Belum Ada Pesanan', 'Mulai belanja sayuran segar dan pesananmu akan muncul di sini.', 'Mulai Belanja', 'home.php'); ?>
        <?php else: ?>
            <div class="space-y-4 max-w-2xl">
                <?php foreach ($orders as $order): ?>
                    <a href="order.php?id=<?= $order['id'] ?>" class="block bg-white rounded-2xl p-5 tonal-shadow hover:border-primary/30 border border-transparent transition-all group">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div class="min-w-0">
                                <p class="font-bold text-text-main group-hover:text-primary transition-colors"><?= e($order['kode_pesanan']) ?></p>
                                <p class="text-xs text-text-muted mt-0.5"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                            </div>
                            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold <?= statusColor($order['status']) ?>"><?= statusLabel($order['status']) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-text-muted truncate"><?= e($order['nama_penerima']) ?></span>
                            <span class="font-bold text-primary shrink-0 ml-2"><?= formatRupiah($order['total']) ?></span>
                        </div>
                        <?php if ($order['status'] === 'menunggu_pembayaran'): ?>
                            <p class="text-xs text-tertiary font-semibold mt-3 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">payments</span> Lanjutkan pembayaran →
                            </p>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/app-footer.php'; ?>
