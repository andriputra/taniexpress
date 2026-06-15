<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$export = isset($_GET['export']);

$stmt = db()->prepare("
    SELECT p.*, u.nama AS customer_nama, k.nama AS kurir_nama
    FROM pesanan p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN kurir k ON k.id = p.kurir_id
    WHERE DATE(p.created_at) BETWEEN ? AND ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$dari, $sampai]);
$orders = $stmt->fetchAll();

$totalPendapatan = array_sum(array_column(array_filter($orders, fn($o) => !in_array($o['status'], ['dibatalkan', 'menunggu_pembayaran'])), 'total'));
$totalPesanan = count($orders);

if ($export) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8"/>
        <title>Laporan Pesanan TaniExpress</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
            h1 { color: #0f5238; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
            th { background: #D8F3DC; }
            .summary { margin: 20px 0; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        <button class="no-print" onclick="window.print()" style="padding:10px 20px;background:#0f5238;color:white;border:none;border-radius:8px;cursor:pointer;margin-bottom:20px">Cetak / Simpan PDF</button>
        <h1>Laporan Pesanan - TaniExpress</h1>
        <p>Periode: <?= e(date('d M Y', strtotime($dari))) ?> s/d <?= e(date('d M Y', strtotime($sampai))) ?></p>
        <div class="summary">
            <p><strong>Total Pesanan:</strong> <?= $totalPesanan ?></p>
            <p><strong>Total Pendapatan:</strong> <?= formatRupiah($totalPendapatan) ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th><th>Kode</th><th>Tanggal</th><th>Customer</th><th>Status</th><th>Kurir</th><th>Subtotal</th><th>Ongkir</th><th>Platform</th><th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $i => $o): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($o['kode_pesanan']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                        <td><?= e($o['customer_nama']) ?></td>
                        <td><?= statusLabel($o['status']) ?></td>
                        <td><?= e($o['kurir_nama'] ?? '-') ?></td>
                        <td><?= formatRupiah($o['subtotal']) ?></td>
                        <td><?= formatRupiah($o['ongkir']) ?></td>
                        <td><?= formatRupiah($o['biaya_platform']) ?></td>
                        <td><?= formatRupiah($o['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:30px;font-size:11px;color:#666">Dicetak pada <?= date('d M Y H:i') ?> - TaniExpress Admin</p>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = 'Laporan Pesanan';
$activeMenu = 'laporan';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="bg-white rounded-xl p-4 md:p-6 shadow-sm border border-outline-variant/30 mb-4 md:mb-6">
    <form method="GET" class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 sm:items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="text-xs font-medium block mb-1">Dari Tanggal</label>
            <input type="date" name="dari" value="<?= e($dari) ?>" class="input-field text-sm"/>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="text-xs font-medium block mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai" value="<?= e($sampai) ?>" class="input-field text-sm"/>
        </div>
        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary text-white rounded-full text-sm font-semibold">Filter</button>
        <a href="?dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>&export=1" target="_blank" class="w-full sm:w-auto px-6 py-3 bg-tertiary-container text-white rounded-full text-sm font-semibold flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> Export PDF
        </a>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4 mb-4 md:mb-6">
    <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm border">
        <p class="text-sm text-text-muted">Total Pesanan</p>
        <p class="text-2xl md:text-3xl font-bold text-primary"><?= $totalPesanan ?></p>
    </div>
    <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm border">
        <p class="text-sm text-text-muted">Total Pendapatan</p>
        <p class="text-2xl md:text-3xl font-bold text-success-green"><?= formatRupiah($totalPendapatan) ?></p>
    </div>
</div>

<!-- Mobile -->
<div class="md:hidden space-y-3">
    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-xl p-8 text-center text-sm text-text-muted border">Tidak ada data pada periode ini.</div>
    <?php endif; ?>
    <?php foreach ($orders as $o): ?>
        <div class="bg-white rounded-xl p-4 border border-outline-variant/30 shadow-sm">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="font-mono text-xs font-semibold"><?= e($o['kode_pesanan']) ?></p>
                <span class="text-[10px] px-2 py-1 rounded-full shrink-0 <?= statusColor($o['status']) ?>"><?= statusLabel($o['status']) ?></span>
            </div>
            <p class="text-sm"><?= e($o['customer_nama']) ?></p>
            <p class="text-xs text-text-muted mt-1"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?> · <?= e($o['kurir_nama'] ?? 'Tanpa kurir') ?></p>
            <p class="text-sm font-bold text-primary mt-2"><?= formatRupiah($o['total']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Desktop -->
<div class="hidden md:block bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-container-low">
            <tr>
                <th class="text-left px-4 py-3">Kode</th>
                <th class="text-left px-4 py-3">Tanggal</th>
                <th class="text-left px-4 py-3">Customer</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Kurir</th>
                <th class="text-right px-4 py-3">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/20">
            <?php if (empty($orders)): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-text-muted">Tidak ada data pada periode ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <tr class="hover:bg-leaf-green-light/20">
                    <td class="px-4 py-3 font-mono text-xs"><?= e($o['kode_pesanan']) ?></td>
                    <td class="px-4 py-3"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td class="px-4 py-3"><?= e($o['customer_nama']) ?></td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full <?= statusColor($o['status']) ?>"><?= statusLabel($o['status']) ?></span></td>
                    <td class="px-4 py-3"><?= e($o['kurir_nama'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-right font-semibold"><?= formatRupiah($o['total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
