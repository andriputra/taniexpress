<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

// Statistik
$totalStok = db()->query('SELECT COALESCE(SUM(stok), 0) FROM produk_petani')->fetchColumn();
$pesananBaru = db()->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('menunggu_verifikasi', 'menunggu_pembayaran')")->fetchColumn();
$kurirSibuk = db()->query("SELECT COUNT(*) FROM kurir WHERE status = 'sibuk'")->fetchColumn();
$kurirTersedia = db()->query("SELECT COUNT(*) FROM kurir WHERE status = 'tersedia'")->fetchColumn();

$recentOrders = db()->query("
    SELECT p.*, u.nama AS customer_nama
    FROM pesanan p JOIN users u ON u.id = p.user_id
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

$kurirList = db()->query("SELECT k.*, COUNT(p.id) AS active_orders FROM kurir k LEFT JOIN pesanan p ON p.kurir_id = k.id AND p.status = 'dikirim' GROUP BY k.id")->fetchAll();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30">
        <p class="text-sm text-text-muted">Total Stok Produk</p>
        <p class="text-3xl font-bold text-primary mt-1"><?= number_format($totalStok) ?></p>
        <span class="text-xs text-success-green">unit tersedia</span>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30">
        <p class="text-sm text-text-muted">Pesanan Perlu Aksi</p>
        <p class="text-3xl font-bold text-tertiary-container mt-1"><?= $pesananBaru ?></p>
        <span class="text-xs text-text-muted">baru / verifikasi</span>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30">
        <p class="text-sm text-text-muted">Kurir Mengantar</p>
        <p class="text-3xl font-bold text-primary-container mt-1"><?= $kurirSibuk ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-outline-variant/30">
        <p class="text-sm text-text-muted">Kurir Tersedia</p>
        <p class="text-3xl font-bold text-success-green mt-1"><?= $kurirTersedia ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant/30 flex justify-between items-center">
            <h3 class="font-semibold">Pesanan Terbaru</h3>
            <a href="pesanan.php" class="text-sm text-primary hover:underline">Lihat Semua</a>
        </div>
        <div class="divide-y divide-outline-variant/20">
            <?php foreach ($recentOrders as $o): ?>
                <a href="pesanan.php?id=<?= $o['id'] ?>" class="flex justify-between items-center px-5 py-3 hover:bg-leaf-green-light/30">
                    <div>
                        <p class="font-medium text-sm"><?= e($o['kode_pesanan']) ?></p>
                        <p class="text-xs text-text-muted"><?= e($o['customer_nama']) ?> • <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full <?= statusColor($o['status']) ?>"><?= statusLabel($o['status']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant/30">
            <h3 class="font-semibold">Status Kurir</h3>
        </div>
        <div class="divide-y divide-outline-variant/20">
            <?php foreach ($kurirList as $k): ?>
                <div class="flex justify-between items-center px-5 py-3">
                    <div>
                        <p class="font-medium text-sm"><?= e($k['nama']) ?></p>
                        <p class="text-xs text-text-muted"><?= e($k['telepon']) ?></p>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full <?= $k['status'] === 'tersedia' ? 'bg-leaf-green-light text-primary' : 'bg-tertiary-container text-white' ?>">
                        <?= $k['status'] === 'tersedia' ? 'Tersedia' : 'Sibuk' ?> (<?= $k['active_orders'] ?> aktif)
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
