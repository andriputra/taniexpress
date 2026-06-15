<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$statusFlow = [
    'menunggu_pembayaran' => 'menunggu_verifikasi',
    'menunggu_verifikasi' => 'diproses',
    'diproses' => 'dikemas',
    'dikemas' => 'dikirim',
    'dikirim' => 'sampai',
    'sampai' => 'selesai',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $newStatus = $_POST['status'] ?? '';
        $kurirId = !empty($_POST['kurir_id']) ? (int) $_POST['kurir_id'] : null;

        $order = db()->prepare('SELECT * FROM pesanan WHERE id=?');
        $order->execute([$id]);
        $order = $order->fetch();

        if ($order) {
            if ($newStatus === 'dikirim' && $kurirId) {
                db()->prepare('UPDATE pesanan SET status=?, kurir_id=? WHERE id=?')->execute([$newStatus, $kurirId, $id]);
                db()->prepare('UPDATE kurir SET status="sibuk" WHERE id=?')->execute([$kurirId]);
            } elseif ($newStatus === 'selesai' && $order['kurir_id']) {
                db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $id]);
                db()->prepare('UPDATE kurir SET status="tersedia" WHERE id=?')->execute([$order['kurir_id']]);
            } elseif ($newStatus === 'dibatalkan') {
                // Kembalikan stok
                $details = db()->prepare('SELECT produk_petani_id, qty FROM pesanan_detil WHERE pesanan_id=?');
                $details->execute([$id]);
                foreach ($details->fetchAll() as $d) {
                    db()->prepare('UPDATE produk_petani SET stok = stok + ? WHERE id=?')->execute([$d['qty'], $d['produk_petani_id']]);
                }
                db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $id]);
            } else {
                db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $id]);
            }
            flash('success', 'Status pesanan diperbarui.');
        }
    } elseif ($action === 'verify_payment') {
        db()->prepare('UPDATE pesanan SET status="diproses" WHERE id=? AND status="menunggu_verifikasi"')->execute([$id]);
        flash('success', 'Pembayaran diverifikasi, pesanan diproses.');
    }
    redirect('pesanan.php' . ($id ? '?id=' . $id : ''));
}

$filter = $_GET['status'] ?? '';
$sql = "SELECT p.*, u.nama AS customer_nama, u.email AS customer_email, k.nama AS kurir_nama
        FROM pesanan p JOIN users u ON u.id = p.user_id LEFT JOIN kurir k ON k.id = p.kurir_id";
$params = [];
if ($filter) {
    $sql .= " WHERE p.status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$detailId = (int) ($_GET['id'] ?? 0);
$detail = null;
$detailItems = [];
if ($detailId) {
    $stmt = db()->prepare('SELECT p.*, u.nama AS customer_nama, u.email AS customer_email FROM pesanan p JOIN users u ON u.id = p.user_id WHERE p.id=?');
    $stmt->execute([$detailId]);
    $detail = $stmt->fetch();
    if ($detail) {
        $stmt = db()->prepare('
            SELECT pd.*, pr.nama AS produk_nama, pt.nama AS petani_nama
            FROM pesanan_detil pd
            JOIN produk_petani pp ON pp.id = pd.produk_petani_id
            JOIN products pr ON pr.id = pp.product_id
            JOIN petani pt ON pt.id = pp.petani_id
            WHERE pd.pesanan_id = ?
        ');
        $stmt->execute([$detailId]);
        $detailItems = $stmt->fetchAll();
    }
}

$kurirs = db()->query("SELECT * FROM kurir WHERE status = 'tersedia' OR id IN (SELECT kurir_id FROM pesanan WHERE id = " . ($detailId ?: 0) . ")")->fetchAll();

$pageTitle = 'Manajemen Pesanan';
$activeMenu = 'pesanan';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="flex flex-wrap gap-2 mb-6 no-print">
    <a href="pesanan.php" class="px-4 py-2 rounded-full text-sm font-medium <?= !$filter ? 'bg-primary text-white' : 'bg-white border' ?>">Semua</a>
    <?php foreach (['menunggu_verifikasi', 'diproses', 'dikemas', 'dikirim', 'selesai', 'dibatalkan'] as $s): ?>
        <a href="?status=<?= $s ?>" class="px-4 py-2 rounded-full text-sm font-medium <?= $filter === $s ? 'bg-primary text-white' : 'bg-white border' ?>">
            <?= statusLabel($s) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 <?= $detail ? 'lg:grid-cols-2' : '' ?> gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low">
                <tr>
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Customer</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                <?php foreach ($orders as $o): ?>
                    <tr class="hover:bg-leaf-green-light/20 cursor-pointer <?= $detailId == $o['id'] ? 'bg-leaf-green-light/40' : '' ?>" onclick="location.href='?id=<?= $o['id'] ?><?= $filter ? '&status=' . $filter : '' ?>'">
                        <td class="px-4 py-3 font-mono text-xs"><?= e($o['kode_pesanan']) ?></td>
                        <td class="px-4 py-3"><?= e($o['customer_nama']) ?></td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full <?= statusColor($o['status']) ?>"><?= statusLabel($o['status']) ?></span></td>
                        <td class="px-4 py-3 text-right font-semibold"><?= formatRupiah($o['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($detail): ?>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-outline-variant/30 h-fit">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-lg"><?= e($detail['kode_pesanan']) ?></h3>
                    <p class="text-sm text-text-muted"><?= e($detail['customer_nama']) ?> • <?= date('d M Y H:i', strtotime($detail['created_at'])) ?></p>
                </div>
                <span class="text-xs px-3 py-1 rounded-full <?= statusColor($detail['status']) ?>"><?= statusLabel($detail['status']) ?></span>
            </div>

            <div class="text-sm space-y-1 mb-4 p-3 bg-surface-container-low rounded-lg">
                <p><strong>Penerima:</strong> <?= e($detail['nama_penerima']) ?></p>
                <p><strong>Telepon:</strong> <?= e($detail['telepon']) ?></p>
                <p><strong>Alamat:</strong> <?= e($detail['alamat']) ?></p>
            </div>

            <h4 class="font-semibold text-sm mb-2">Item Pesanan</h4>
            <div class="space-y-2 mb-4">
                <?php foreach ($detailItems as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span><?= e($item['produk_nama']) ?> (<?= e($item['petani_nama']) ?>) x<?= $item['qty'] ?></span>
                        <span><?= formatRupiah($item['qty'] * $item['harga']) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr/>
                <div class="flex justify-between font-bold text-primary"><span>Total</span><span><?= formatRupiah($detail['total']) ?></span></div>
            </div>

            <?php if ($detail['bukti_bayar']): ?>
                <div class="mb-4">
                    <p class="text-sm font-semibold mb-2">Bukti Pembayaran</p>
                    <a href="../<?= e($detail['bukti_bayar']) ?>" target="_blank">
                        <img src="../<?= e($detail['bukti_bayar']) ?>" class="max-h-40 rounded-lg border" alt="Bukti"/>
                    </a>
                </div>
            <?php endif; ?>

            <div class="space-y-3 border-t pt-4">
                <?php if ($detail['status'] === 'menunggu_verifikasi'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="verify_payment"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <button type="submit" class="w-full py-2 bg-success-green text-white rounded-lg text-sm font-semibold">Verifikasi Pembayaran</button>
                    </form>
                <?php endif; ?>

                <?php if (!in_array($detail['status'], ['selesai', 'dibatalkan', 'menunggu_pembayaran'])): ?>
                    <form method="POST" class="space-y-2">
                        <input type="hidden" name="action" value="update_status"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <select name="status" class="input-field text-sm">
                            <?php
                            $nextOptions = array_merge(
                                [$detail['status'] => statusLabel($detail['status']) . ' (saat ini)'],
                                array_filter($statusFlow, fn($k) => true, ARRAY_FILTER_USE_KEY)
                            );
                            $allStatuses = ['diproses', 'dikemas', 'dikirim', 'sampai', 'selesai', 'dibatalkan'];
                            foreach ($allStatuses as $s):
                                if ($s === 'menunggu_pembayaran' || $s === 'menunggu_verifikasi') continue;
                            ?>
                                <option value="<?= $s ?>"><?= statusLabel($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="kurir_id" class="input-field text-sm">
                            <option value="">Pilih Kurir (untuk status Dikirim)</option>
                            <?php foreach ($kurirs as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $detail['kurir_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?> (<?= e($k['telepon']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-semibold">Update Status</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
