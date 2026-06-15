<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $newStatus = $_POST['status'] ?? '';

    if ($action === 'advance_status' && $newStatus) {
        $result = updateOrderStatus($id, $newStatus, [
            'kurir_id' => !empty($_POST['kurir_id']) ? (int) $_POST['kurir_id'] : null,
        ]);
        flash($result['ok'] ? 'success' : 'error', $result['message']);
    } elseif ($action === 'reject_payment') {
        $result = updateOrderStatus($id, 'menunggu_pembayaran');
        flash($result['ok'] ? 'success' : 'error', $result['message']);
    } elseif ($action === 'cancel_order') {
        $result = updateOrderStatus($id, 'dibatalkan');
        flash($result['ok'] ? 'success' : 'error', $result['message']);
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
    $stmt = db()->prepare('SELECT p.*, u.nama AS customer_nama, u.email AS customer_email, k.nama AS kurir_nama, k.telepon AS kurir_telepon FROM pesanan p JOIN users u ON u.id = p.user_id LEFT JOIN kurir k ON k.id = p.kurir_id WHERE p.id=?');
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

$kurirs = db()->query("SELECT * FROM kurir WHERE status = 'tersedia' OR id IN (SELECT kurir_id FROM pesanan WHERE id = " . ($detailId ?: 0) . ") ORDER BY nama")->fetchAll();

$adminSteps = array_keys(orderStatusFlow());
$adminSteps[] = 'selesai';

$pageTitle = 'Manajemen Pesanan';
$activeMenu = 'pesanan';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<div class="admin-chip-scroll no-print mb-5 md:mb-6">
    <a href="pesanan.php" class="shrink-0 whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium <?= !$filter ? 'bg-primary text-white' : 'bg-white border' ?>">Semua</a>
    <?php foreach (['menunggu_verifikasi', 'diproses', 'dikemas', 'dikirim', 'selesai', 'dibatalkan'] as $s): ?>
        <a href="?status=<?= $s ?>" class="shrink-0 whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium <?= $filter === $s ? 'bg-primary text-white' : 'bg-white border' ?>">
            <?= statusLabel($s) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($detail): ?>
    <a href="pesanan.php<?= $filter ? '?status=' . urlencode($filter) : '' ?>" class="lg:hidden inline-flex items-center gap-1.5 text-sm font-semibold text-primary mb-4 no-print">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar
    </a>
<?php endif; ?>

<div class="grid grid-cols-1 <?= $detail ? 'lg:grid-cols-2' : '' ?> gap-4 md:gap-6">
    <div class="<?= $detail ? 'hidden lg:block' : '' ?>">
        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
            <?php if (empty($orders)): ?>
                <div class="bg-white rounded-xl p-8 text-center text-sm text-text-muted border border-outline-variant/30">Belum ada pesanan.</div>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <a href="?id=<?= $o['id'] ?><?= $filter ? '&status=' . urlencode($filter) : '' ?>"
                   class="block bg-white rounded-xl p-4 border border-outline-variant/30 tonal-shadow active:scale-[0.99] transition-transform <?= $detailId == $o['id'] ? 'ring-2 ring-primary/30 border-primary/30' : '' ?>">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <p class="font-mono text-xs font-semibold text-text-main"><?= e($o['kode_pesanan']) ?></p>
                        <span class="text-xs px-2 py-1 rounded-full shrink-0 <?= statusColor($o['status']) ?>"><?= statusLabel($o['status']) ?></span>
                    </div>
                    <p class="text-sm font-medium text-text-main"><?= e($o['customer_nama']) ?></p>
                    <p class="text-sm font-bold text-primary mt-2"><?= formatRupiah($o['total']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
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
    </div>

    <?php if ($detail): ?>
        <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm border border-outline-variant/30 h-fit">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4">
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

            <!-- Progress alur pesanan -->
            <div class="mb-5 p-4 bg-surface-container-low rounded-2xl">
                <p class="text-xs font-bold uppercase tracking-wide text-text-muted mb-3">Alur Pesanan</p>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $detailStepIndex = array_search($detail['status'], $adminSteps);
                    if ($detail['status'] === 'dibatalkan') {
                        $detailStepIndex = false;
                    }
                    foreach ($adminSteps as $i => $step):
                        $done = $detailStepIndex !== false && $i < $detailStepIndex;
                        $active = $detail['status'] === $step;
                    ?>
                        <span class="text-[11px] px-2.5 py-1 rounded-full font-semibold <?= $active ? 'bg-primary text-white' : ($done ? 'bg-leaf-green-light text-primary' : 'bg-white text-outline border border-outline-variant/40') ?>">
                            <?= statusLabel($step) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-3 border-t pt-4">
                <?php
                $nextStatus = orderNextStatus($detail['status']);
                ?>

                <?php if ($detail['status'] === 'menunggu_pembayaran'): ?>
                    <p class="text-sm text-text-muted p-3 bg-surface-container-low rounded-xl">Menunggu customer melakukan pembayaran dan upload bukti.</p>
                <?php endif; ?>

                <?php if ($detail['status'] === 'menunggu_verifikasi'): ?>
                    <form method="POST" data-confirm="Verifikasi pembayaran dan lanjutkan proses pesanan?" data-confirm-title="Verifikasi Pembayaran">
                        <input type="hidden" name="action" value="advance_status"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <input type="hidden" name="status" value="diproses"/>
                        <button type="submit" class="w-full py-3 bg-success-green text-white rounded-full text-sm font-semibold">Verifikasi & Proses Pesanan</button>
                    </form>
                    <form method="POST" data-confirm="Tolak bukti pembayaran? Customer akan diminta upload ulang." data-confirm-title="Tolak Bukti">
                        <input type="hidden" name="action" value="reject_payment"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <button type="submit" class="w-full py-2.5 border border-outline-variant text-on-surface rounded-full text-sm font-semibold hover:bg-surface-container-low">Tolak Bukti Pembayaran</button>
                    </form>
                <?php endif; ?>

                <?php if ($nextStatus && $detail['status'] === 'dikemas'): ?>
                    <form method="POST" class="space-y-2" data-confirm="Kirim pesanan dengan kurir yang dipilih?" data-confirm-title="Kirim Pesanan">
                        <input type="hidden" name="action" value="advance_status"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <input type="hidden" name="status" value="dikirim"/>
                        <label class="block text-xs font-semibold text-text-muted">Pilih Kurir <span class="text-error-red">*</span></label>
                        <select name="kurir_id" required class="input-field text-sm">
                            <option value="">— Pilih kurir tersedia —</option>
                            <?php foreach ($kurirs as $k): ?>
                                <?php if ($k['status'] === 'tersedia' || $detail['kurir_id'] == $k['id']): ?>
                                <option value="<?= $k['id'] ?>" <?= $detail['kurir_id'] == $k['id'] ? 'selected' : '' ?>>
                                    <?= e($k['nama']) ?> (<?= e($k['telepon']) ?>)<?= $k['status'] === 'sibuk' && $detail['kurir_id'] != $k['id'] ? ' — Sibuk' : '' ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="w-full py-3 bg-primary text-white rounded-full text-sm font-semibold"><?= orderAdminActionLabel($detail['status'], $nextStatus) ?></button>
                    </form>
                <?php elseif ($nextStatus && !in_array($detail['status'], ['menunggu_pembayaran', 'menunggu_verifikasi', 'sampai', 'selesai', 'dibatalkan'])): ?>
                    <form method="POST" data-confirm="Lanjutkan ke tahap <?= e(statusLabel($nextStatus)) ?>?" data-confirm-title="Perbarui Status">
                        <input type="hidden" name="action" value="advance_status"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <input type="hidden" name="status" value="<?= $nextStatus ?>"/>
                        <button type="submit" class="w-full py-3 bg-primary text-white rounded-full text-sm font-semibold"><?= orderAdminActionLabel($detail['status'], $nextStatus) ?></button>
                    </form>
                <?php endif; ?>

                <?php if ($detail['status'] === 'dikirim' && $detail['kurir_id']): ?>
                    <p class="text-xs text-text-muted px-1">Kurir: <strong><?= e($detail['kurir_nama'] ?? '—') ?></strong></p>
                <?php endif; ?>

                <?php if ($detail['status'] === 'sampai'): ?>
                    <p class="text-sm text-text-muted p-3 bg-leaf-green-light/50 rounded-xl">Pesanan sudah sampai. Menunggu konfirmasi customer atau admin dapat menandai selesai.</p>
                    <form method="POST" data-confirm="Tandai pesanan sebagai selesai?" data-confirm-title="Selesaikan Pesanan">
                        <input type="hidden" name="action" value="advance_status"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <input type="hidden" name="status" value="selesai"/>
                        <button type="submit" class="w-full py-3 bg-success-green text-white rounded-full text-sm font-semibold">Tandai Selesai</button>
                    </form>
                <?php endif; ?>

                <?php if (orderCanCancel($detail['status'])): ?>
                    <form method="POST" data-confirm="Batalkan pesanan ini? Stok produk akan dikembalikan." data-confirm-title="Batalkan Pesanan">
                        <input type="hidden" name="action" value="cancel_order"/>
                        <input type="hidden" name="id" value="<?= $detail['id'] ?>"/>
                        <button type="submit" class="w-full py-2.5 text-error-red border border-error-red/30 rounded-full text-sm font-semibold hover:bg-red-50">Batalkan Pesanan</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
