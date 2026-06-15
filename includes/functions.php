<?php

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;max-width:500px;margin:80px auto;padding:24px;background:#fff3f3;border:1px solid #f5c6cb;border-radius:12px">
                <h2 style="color:#0f5238">Database Belum Terhubung</h2>
                <p>Jalankan <code>php install.php</code> setelah MySQL aktif, lalu pastikan config/config.php benar.</p>
                <p style="color:#666;font-size:14px">' . htmlspecialchars($e->getMessage()) . '</p>
            </div>');
        }
    }
    return $pdo;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatRupiah(float|int|string $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function generateKodePesanan(): string
{
    return 'TE' . date('ymd') . strtoupper(substr(uniqid(), -4));
}

function statusLabel(string $status): string
{
    return match ($status) {
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'diproses' => 'Pesanan Diproses',
        'dikemas' => 'Sedang Dikemas',
        'dikirim' => 'Sedang Dikirim',
        'sampai' => 'Pesanan Sampai',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        default => ucfirst($status),
    };
}

function statusColor(string $status): string
{
    return match ($status) {
        'menunggu_pembayaran' => 'bg-surface-container-high text-on-surface',
        'menunggu_verifikasi' => 'bg-tertiary-container text-on-tertiary-container',
        'diproses', 'dikemas' => 'bg-leaf-green-light text-primary',
        'dikirim' => 'bg-primary-container text-white',
        'sampai', 'selesai' => 'bg-success-green text-white',
        'dibatalkan' => 'bg-error-red text-white',
        default => 'bg-surface-container-high text-on-surface',
    };
}

function orderSteps(): array
{
    return ['menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikemas', 'dikirim', 'sampai', 'selesai'];
}

function cartCount(): int
{
    $cart = $_SESSION['cart'] ?? [];
    return array_sum($cart);
}

function getCartItems(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = db()->prepare("
        SELECT pp.*, p.nama AS produk_nama, p.satuan, p.gambar, p.kategori,
               pt.nama AS petani_nama
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        WHERE pp.id IN ($placeholders) AND pp.stok > 0
    ");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();

    $result = [];
    foreach ($items as $item) {
        $qty = min($cart[$item['id']], $item['stok']);
        if ($qty > 0) {
            $item['qty'] = $qty;
            $item['subtotal'] = $qty * $item['harga'];
            $result[] = $item;
        }
    }
    return $result;
}

function cartTotal(): float
{
    return array_sum(array_column(getCartItems(), 'subtotal'));
}

function uploadFile(array $file, string $prefix = 'file'): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return null;
    }

    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return UPLOAD_URL . $filename;
    }
    return null;
}

function getCategories(): array
{
    $stmt = db()->query("SELECT DISTINCT kategori FROM products ORDER BY kategori");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getProducts(?string $search = null, ?string $kategori = null, ?int $petaniId = null): array
{
    $sql = "
        SELECT pp.id, pp.stok, pp.harga, p.nama, p.kategori, p.satuan, p.gambar,
               pt.nama AS petani_nama, pt.id AS petani_id
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        WHERE pp.stok > 0
    ";
    $params = [];

    if ($search) {
        $sql .= " AND (p.nama LIKE ? OR pt.nama LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($kategori && $kategori !== 'all') {
        $sql .= " AND p.kategori = ?";
        $params[] = $kategori;
    }
    if ($petaniId) {
        $sql .= " AND pt.id = ?";
        $params[] = $petaniId;
    }

    $sql .= " ORDER BY p.nama ASC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductDetail(int $produkPetaniId): ?array
{
    $stmt = db()->prepare("
        SELECT pp.*, p.nama, p.kategori, p.satuan, p.berat, p.deskripsi, p.gambar,
               pt.id AS petani_id, pt.nama AS petani_nama, pt.alamat AS petani_alamat, pt.foto AS petani_foto
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        WHERE pp.id = ?
    ");
    $stmt->execute([$produkPetaniId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getRelatedProducts(int $produkPetaniId, string $kategori, int $limit = 4): array
{
    $stmt = db()->prepare("
        SELECT pp.id, pp.stok, pp.harga, p.nama, p.kategori, p.satuan, p.gambar,
               pt.nama AS petani_nama
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        WHERE pp.stok > 0 AND pp.id != ? AND p.kategori = ?
        ORDER BY RAND()
        LIMIT ?
    ");
    $stmt->bindValue(1, $produkPetaniId, PDO::PARAM_INT);
    $stmt->bindValue(2, $kategori, PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function orderStatusFlow(): array
{
    return [
        'menunggu_pembayaran' => 'menunggu_verifikasi',
        'menunggu_verifikasi' => 'diproses',
        'diproses' => 'dikemas',
        'dikemas' => 'dikirim',
        'dikirim' => 'sampai',
        'sampai' => 'selesai',
    ];
}

function orderNextStatus(string $status): ?string
{
    return orderStatusFlow()[$status] ?? null;
}

function orderIsTerminal(string $status): bool
{
    return in_array($status, ['selesai', 'dibatalkan'], true);
}

function orderCanCancel(string $status): bool
{
    return !orderIsTerminal($status) && $status !== 'sampai';
}

function restoreOrderStock(int $pesananId): void
{
    $details = db()->prepare('SELECT produk_petani_id, qty FROM pesanan_detil WHERE pesanan_id=?');
    $details->execute([$pesananId]);
    foreach ($details->fetchAll() as $d) {
        db()->prepare('UPDATE produk_petani SET stok = stok + ? WHERE id=?')->execute([$d['qty'], $d['produk_petani_id']]);
    }
}

function releaseKurir(?int $kurirId): void
{
    if ($kurirId) {
        db()->prepare("UPDATE kurir SET status='tersedia' WHERE id=?")->execute([$kurirId]);
    }
}

function assignKurir(int $kurirId): bool
{
    $kurir = db()->prepare("SELECT id, status FROM kurir WHERE id=?");
    $kurir->execute([$kurirId]);
    $kurir = $kurir->fetch();
    if (!$kurir || $kurir['status'] !== 'tersedia') {
        return false;
    }
    db()->prepare("UPDATE kurir SET status='sibuk' WHERE id=?")->execute([$kurirId]);
    return true;
}

/**
 * @return array{ok: bool, message: string}
 */
function updateOrderStatus(int $pesananId, string $newStatus, array $options = []): array
{
    $stmt = db()->prepare('SELECT * FROM pesanan WHERE id=?');
    $stmt->execute([$pesananId]);
    $order = $stmt->fetch();
    if (!$order) {
        return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
    }

    $current = $order['status'];
    if ($current === $newStatus) {
        return ['ok' => false, 'message' => 'Status pesanan sudah ' . statusLabel($current) . '.'];
    }

    if (orderIsTerminal($current)) {
        return ['ok' => false, 'message' => 'Pesanan sudah ' . statusLabel($current) . ' dan tidak dapat diubah.'];
    }

    $allowed = false;

    if ($newStatus === 'dibatalkan') {
        $allowed = orderCanCancel($current);
    } elseif ($newStatus === 'menunggu_pembayaran' && $current === 'menunggu_verifikasi') {
        $allowed = true;
    } elseif ($newStatus === orderNextStatus($current)) {
        $allowed = true;
    } elseif ($newStatus === 'selesai' && $current === 'sampai') {
        $allowed = true;
    }

    if (!$allowed) {
        $next = orderNextStatus($current);
        $hint = $next ? 'Langkah berikutnya: ' . statusLabel($next) . '.' : '';
        return ['ok' => false, 'message' => 'Transisi status tidak valid. ' . $hint];
    }

    if ($newStatus === 'diproses' && $current === 'menunggu_verifikasi' && empty($order['bukti_bayar'])) {
        return ['ok' => false, 'message' => 'Bukti pembayaran belum diunggah customer.'];
    }

    if ($newStatus === 'dikirim') {
        $kurirId = (int) ($options['kurir_id'] ?? $order['kurir_id'] ?? 0);
        if (!$kurirId) {
            return ['ok' => false, 'message' => 'Pilih kurir sebelum mengirim pesanan.'];
        }
        if (!assignKurir($kurirId)) {
            return ['ok' => false, 'message' => 'Kurir tidak tersedia. Pilih kurir lain.'];
        }
        db()->prepare('UPDATE pesanan SET status=?, kurir_id=? WHERE id=?')->execute([$newStatus, $kurirId, $pesananId]);
        return ['ok' => true, 'message' => 'Pesanan sedang dikirim.'];
    }

    if ($newStatus === 'menunggu_pembayaran' && $current === 'menunggu_verifikasi') {
        db()->prepare('UPDATE pesanan SET status=?, bukti_bayar=NULL WHERE id=?')->execute([$newStatus, $pesananId]);
        return ['ok' => true, 'message' => 'Bukti pembayaran ditolak. Customer diminta upload ulang.'];
    }

    if ($newStatus === 'dibatalkan') {
        restoreOrderStock($pesananId);
        releaseKurir($order['kurir_id'] ? (int) $order['kurir_id'] : null);
        db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $pesananId]);
        return ['ok' => true, 'message' => 'Pesanan dibatalkan dan stok dikembalikan.'];
    }

    if ($newStatus === 'selesai') {
        releaseKurir($order['kurir_id'] ? (int) $order['kurir_id'] : null);
        db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $pesananId]);
        return ['ok' => true, 'message' => 'Pesanan selesai.'];
    }

    db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $pesananId]);

    return ['ok' => true, 'message' => 'Status diperbarui menjadi ' . statusLabel($newStatus) . '.'];
}

function orderAdminActionLabel(string $currentStatus, string $nextStatus): string
{
    return match ($nextStatus) {
        'diproses' => 'Verifikasi & Proses Pesanan',
        'dikemas' => 'Tandai Sedang Dikemas',
        'dikirim' => 'Kirim Pesanan',
        'sampai' => 'Tandai Sudah Sampai',
        'selesai' => 'Tandai Selesai',
        default => 'Lanjut ke ' . statusLabel($nextStatus),
    };
}

function orderStatusFlow(): array
{
    return [
        'menunggu_pembayaran' => 'menunggu_verifikasi',
        'menunggu_verifikasi' => 'diproses',
        'diproses' => 'dikemas',
        'dikemas' => 'dikirim',
        'dikirim' => 'sampai',
        'sampai' => 'selesai',
    ];
}

function orderNextStatus(string $status): ?string
{
    return orderStatusFlow()[$status] ?? null;
}

function orderIsTerminal(string $status): bool
{
    return in_array($status, ['selesai', 'dibatalkan'], true);
}

function orderCanCancel(string $status): bool
{
    return !orderIsTerminal($status) && $status !== 'sampai';
}

function restoreOrderStock(int $pesananId): void
{
    $details = db()->prepare('SELECT produk_petani_id, qty FROM pesanan_detil WHERE pesanan_id=?');
    $details->execute([$pesananId]);
    foreach ($details->fetchAll() as $d) {
        db()->prepare('UPDATE produk_petani SET stok = stok + ? WHERE id=?')->execute([$d['qty'], $d['produk_petani_id']]);
    }
}

function releaseKurir(?int $kurirId): void
{
    if ($kurirId) {
        db()->prepare("UPDATE kurir SET status='tersedia' WHERE id=?")->execute([$kurirId]);
    }
}

function assignKurir(int $kurirId): bool
{
    $kurir = db()->prepare("SELECT id, status FROM kurir WHERE id=?");
    $kurir->execute([$kurirId]);
    $kurir = $kurir->fetch();
    if (!$kurir || $kurir['status'] !== 'tersedia') {
        return false;
    }
    db()->prepare("UPDATE kurir SET status='sibuk' WHERE id=?")->execute([$kurirId]);
    return true;
}

/**
 * @return array{ok: bool, message: string}
 */
function updateOrderStatus(int $pesananId, string $newStatus, array $options = []): array
{
    $stmt = db()->prepare('SELECT * FROM pesanan WHERE id=?');
    $stmt->execute([$pesananId]);
    $order = $stmt->fetch();
    if (!$order) {
        return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
    }

    $current = $order['status'];
    if ($current === $newStatus) {
        return ['ok' => false, 'message' => 'Status pesanan sudah ' . statusLabel($current) . '.'];
    }

    if (orderIsTerminal($current)) {
        return ['ok' => false, 'message' => 'Pesanan sudah ' . statusLabel($current) . ' dan tidak dapat diubah.'];
    }

    $allowed = false;

    if ($newStatus === 'dibatalkan') {
        $allowed = orderCanCancel($current);
    } elseif ($newStatus === 'menunggu_pembayaran' && $current === 'menunggu_verifikasi') {
        $allowed = true;
    } elseif ($newStatus === orderNextStatus($current)) {
        $allowed = true;
    } elseif ($newStatus === 'selesai' && $current === 'sampai') {
        $allowed = true;
    }

    if (!$allowed) {
        $next = orderNextStatus($current);
        $hint = $next ? 'Langkah berikutnya: ' . statusLabel($next) . '.' : '';
        return ['ok' => false, 'message' => 'Transisi status tidak valid. ' . $hint];
    }

    if ($newStatus === 'diproses' && $current === 'menunggu_verifikasi' && empty($order['bukti_bayar'])) {
        return ['ok' => false, 'message' => 'Bukti pembayaran belum diunggah customer.'];
    }

    if ($newStatus === 'dikirim') {
        $kurirId = (int) ($options['kurir_id'] ?? $order['kurir_id'] ?? 0);
        if (!$kurirId) {
            return ['ok' => false, 'message' => 'Pilih kurir sebelum mengirim pesanan.'];
        }
        if (!assignKurir($kurirId)) {
            return ['ok' => false, 'message' => 'Kurir tidak tersedia. Pilih kurir lain.'];
        }
        db()->prepare('UPDATE pesanan SET status=?, kurir_id=? WHERE id=?')->execute([$newStatus, $kurirId, $pesananId]);
        return ['ok' => true, 'message' => 'Pesanan dikirim dengan kurir ' . statusLabel($newStatus) . '.'];
    }

    if ($newStatus === 'menunggu_pembayaran' && $current === 'menunggu_verifikasi') {
        db()->prepare('UPDATE pesanan SET status=?, bukti_bayar=NULL WHERE id=?')->execute([$newStatus, $pesananId]);
        return ['ok' => true, 'message' => 'Bukti pembayaran ditolak. Customer diminta upload ulang.'];
    }

    if ($newStatus === 'dibatalkan') {
        restoreOrderStock($pesananId);
        releaseKurir($order['kurir_id'] ? (int) $order['kurir_id'] : null);
        db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $pesananId]);
        return ['ok' => true, 'message' => 'Pesanan dibatalkan dan stok dikembalikan.'];
    }

    if ($newStatus === 'selesai') {
        releaseKurir($order['kurir_id'] ? (int) $order['kurir_id'] : null);
        db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $pesananId]);
        return ['ok' => true, 'message' => 'Pesanan selesai.'];
    }

    db()->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$newStatus, $pesananId]);

    return ['ok' => true, 'message' => 'Status diperbarui menjadi ' . statusLabel($newStatus) . '.'];
}

function orderAdminActionLabel(string $status): string
{
    return match ($status) {
        'diproses' => 'Verifikasi & Proses Pesanan',
        'dikemas' => 'Mulai Kemas Pesanan',
        'dikirim' => 'Kirim Pesanan',
        'sampai' => 'Tandai Sudah Sampai',
        'selesai' => 'Tandai Selesai',
        default => 'Lanjut ke ' . statusLabel($status),
    };
}
