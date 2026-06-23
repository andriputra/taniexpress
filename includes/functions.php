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

function mediaSrc(?string $path, bool $fromAdmin = false): ?string
{
    if (!$path) {
        return null;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return ($fromAdmin ? '../' : '') . ltrim($path, '/');
}

function appLogoSrc(bool $fromAdmin = false): string
{
    return mediaSrc(APP_LOGO, $fromAdmin) ?? APP_LOGO;
}

function appFaviconSrc(bool $fromAdmin = false): string
{
    return mediaSrc(APP_FAVICON, $fromAdmin) ?? APP_FAVICON;
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
        SELECT pp.id, pp.stok, pp.harga, p.id AS product_id, p.nama, p.kategori, p.satuan, p.gambar,
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

function getPopularProducts(int $limit = 8): array
{
    $limit = max(1, min(8, $limit));

    $salesStmt = db()->prepare("
        SELECT pp.product_id, SUM(pd.qty) AS total_terjual
        FROM pesanan_detil pd
        INNER JOIN pesanan ps ON ps.id = pd.pesanan_id AND ps.status != 'dibatalkan'
        INNER JOIN produk_petani pp ON pp.id = pd.produk_petani_id
        GROUP BY pp.product_id
        ORDER BY total_terjual DESC
        LIMIT ?
    ");
    $salesStmt->bindValue(1, $limit, PDO::PARAM_INT);
    $salesStmt->execute();
    $topSales = $salesStmt->fetchAll();

    $result = [];

    if ($topSales) {
        $productIds = array_column($topSales, 'product_id');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $orderList = implode(',', array_map('intval', $productIds));

        $listingStmt = db()->prepare("
            SELECT pp.id, pp.stok, pp.harga, p.id AS product_id, p.nama, p.kategori, p.satuan, p.gambar,
                   pt.nama AS petani_nama, pt.id AS petani_id
            FROM produk_petani pp
            JOIN products p ON p.id = pp.product_id
            JOIN petani pt ON pt.id = pp.petani_id
            WHERE pp.product_id IN ($placeholders) AND pp.stok > 0
            ORDER BY FIELD(p.id, $orderList), pp.harga ASC
        ");
        $listingStmt->execute($productIds);

        $seen = [];
        foreach ($listingStmt->fetchAll() as $row) {
            if (isset($seen[$row['product_id']])) {
                continue;
            }
            $seen[$row['product_id']] = true;
            $result[] = $row;
        }
    }

    if (count($result) < $limit) {
        $shownIds = array_column($result, 'product_id');
        foreach (getProducts() as $row) {
            if (in_array($row['product_id'], $shownIds, true)) {
                continue;
            }
            $result[] = $row;
            $shownIds[] = $row['product_id'];
            if (count($result) >= $limit) {
                break;
            }
        }
    }

    return array_slice($result, 0, $limit);
}

function getProductDetail(int $produkPetaniId): ?array
{
    ensurePetaniCeritaColumn();
    $stmt = db()->prepare("
        SELECT pp.*, p.nama, p.kategori, p.satuan, p.berat, p.deskripsi, p.gambar,
               pt.id AS petani_id, pt.nama AS petani_nama, pt.alamat AS petani_alamat,
               pt.foto AS petani_foto, pt.cerita_petani AS petani_cerita
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
    $products = db()->prepare('
        SELECT DISTINCT pp.product_id FROM pesanan_detil pd
        JOIN produk_petani pp ON pp.id = pd.produk_petani_id
        WHERE pd.pesanan_id = ?
    ');
    $products->execute([$pesananId]);
    foreach ($products->fetchAll(PDO::FETCH_COLUMN) as $productId) {
        refreshProductStockTotal((int) $productId);
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

function getAdminProducts(?string $search = null, ?string $kategori = null, int $page = 1, int $perPage = 12): array
{
    $where = [];
    $params = [];

    if ($search) {
        $where[] = '(p.nama LIKE ? OR p.sku LIKE ? OR p.kategori LIKE ? OR p.deskripsi LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term, $term);
    }
    if ($kategori) {
        $where[] = 'p.kategori = ?';
        $params[] = $kategori;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = db()->prepare("SELECT COUNT(*) FROM products p $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $page = max(1, $page);
    $perPage = max(1, min(50, $perPage));
    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $stmt = db()->prepare("
        SELECT p.*, COUNT(pp.id) AS jumlah_listing,
               COALESCE(SUM(pp.stok), p.stok, 0) AS stok_toko
        FROM products p
        LEFT JOIN produk_petani pp ON pp.product_id = p.id
        $whereSql
        GROUP BY p.id
        ORDER BY p.nama ASC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages,
    ];
}

function getAdminProdukPetani(?string $search = null, int $page = 1, int $perPage = 15): array
{
    $where = [];
    $params = [];

    if ($search) {
        $where[] = '(p.nama LIKE ? OR pt.nama LIKE ? OR p.sku LIKE ? OR p.kategori LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term, $term);
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = db()->prepare("
        SELECT COUNT(*)
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        $whereSql
    ");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $page = max(1, $page);
    $perPage = max(1, min(50, $perPage));
    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $stmt = db()->prepare("
        SELECT pp.*, p.nama AS produk_nama, p.satuan, p.sku, p.kategori, pt.nama AS petani_nama
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        $whereSql
        ORDER BY p.nama ASC, pt.nama ASC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages,
    ];
}

function adminQueryString(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    return $params ? '?' . http_build_query($params) : '';
}

/** Sinkronkan stok master produk ke semua listing petani */
function syncProdukPetaniStock(int $productId, int $stok): void
{
    db()->prepare('UPDATE produk_petani SET stok = ? WHERE product_id = ?')->execute([$stok, $productId]);
}

/** Hitung ulang stok master dari total listing petani */
function refreshProductStockTotal(int $productId): void
{
    db()->prepare('
        UPDATE products SET stok = COALESCE((
            SELECT SUM(stok) FROM produk_petani WHERE product_id = ?
        ), 0) WHERE id = ?
    ')->execute([$productId, $productId]);
}

function produkPetaniExists(int $productId, int $petaniId, ?int $excludeId = null): bool
{
    $sql = 'SELECT id FROM produk_petani WHERE product_id = ? AND petani_id = ?';
    $params = [$productId, $petaniId];
    if ($excludeId) {
        $sql .= ' AND id != ?';
        $params[] = $excludeId;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetch();
}

function pdoErrorMessage(PDOException $e): string
{
    $msg = $e->getMessage();
    if ($e->getCode() === '23000' || str_contains($msg, '1062') || str_contains($msg, 'Duplicate entry')) {
        if (str_contains($msg, 'unique_produk_petani')) {
            return 'Produk ini sudah dijual oleh petani yang dipilih. Edit listing yang ada atau pilih kombinasi lain.';
        }
        if (str_contains($msg, 'sku')) {
            return 'SKU sudah digunakan produk lain.';
        }
        return 'Data duplikat. Periksa kembali input Anda.';
    }
    if (str_contains($msg, '1451') || str_contains($msg, 'foreign key constraint')) {
        return 'Data tidak dapat dihapus karena masih terhubung dengan data lain.';
    }
    return 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
}

function ensureAppSettingsTable(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $exists = db()->query("SHOW TABLES LIKE 'app_settings'")->fetch();
    if (!$exists) {
        db()->exec('
            CREATE TABLE app_settings (
                setting_key VARCHAR(50) PRIMARY KEY,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ');
    }
}

function getAppSetting(string $key, ?string $default = null): ?string
{
    ensureAppSettingsTable();
    $stmt = db()->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

function setAppSetting(string $key, ?string $value): void
{
    ensureAppSettingsTable();
    if ($value === null || $value === '') {
        db()->prepare('DELETE FROM app_settings WHERE setting_key = ?')->execute([$key]);
        return;
    }
    db()->prepare('
        INSERT INTO app_settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ')->execute([$key, $value]);
}

function removeUploadedFile(?string $relativePath): void
{
    if (!$relativePath || str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
        return;
    }
    $file = UPLOAD_DIR . basename($relativePath);
    if (is_file($file)) {
        @unlink($file);
    }
}

function getQrisImage(): ?string
{
    $path = getAppSetting('qris_image');
    return $path ?: null;
}

function getQrisMerchantName(): string
{
    return getAppSetting('qris_merchant_name', APP_NAME) ?: APP_NAME;
}

function isQrisConfigured(): bool
{
    return getQrisImage() !== null;
}

function ensurePetaniCeritaColumn(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $hasPetani = db()->query("SHOW TABLES LIKE 'petani'")->fetch();
    if (!$hasPetani) {
        return;
    }

    $hasCerita = db()->query("SHOW COLUMNS FROM petani LIKE 'cerita_petani'")->fetch();
    if (!$hasCerita) {
        db()->exec('ALTER TABLE petani ADD COLUMN cerita_petani TEXT DEFAULT NULL AFTER alamat');
    }

    $hasProfil = db()->query("SHOW COLUMNS FROM petani LIKE 'profil_petani'")->fetch();
    if (!$hasProfil) {
        db()->exec('ALTER TABLE petani ADD COLUMN profil_petani TEXT DEFAULT NULL AFTER cerita_petani');
    }
}

function getPetaniDetail(int $id): ?array
{
    ensurePetaniCeritaColumn();
    $stmt = db()->prepare('
        SELECT p.*, COUNT(pp.id) AS jumlah_produk
        FROM petani p
        LEFT JOIN produk_petani pp ON pp.petani_id = p.id AND pp.stok > 0
        WHERE p.id = ?
        GROUP BY p.id
    ');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function heroGradientOptions(): array
{
    return [
        'from-primary/80' => 'Hijau gelap',
        'from-primary/75' => 'Hijau medium',
        'from-primary/70' => 'Hijau terang',
        'from-secondary/80' => 'Orange gelap',
        'from-secondary/75' => 'Orange medium',
        'from-tertiary/80' => 'Kuning/emas',
    ];
}

function ensureHeroSlidesTable(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $exists = db()->query("SHOW TABLES LIKE 'hero_slides'")->fetch();
    if (!$exists) {
        db()->exec("
            CREATE TABLE hero_slides (
                id INT AUTO_INCREMENT PRIMARY KEY,
                badge VARCHAR(100) NOT NULL,
                judul VARCHAR(255) NOT NULL,
                deskripsi TEXT NOT NULL,
                gambar VARCHAR(500) NOT NULL,
                gradient VARCHAR(80) NOT NULL DEFAULT 'from-primary/80',
                btn_utama_label VARCHAR(100) NOT NULL DEFAULT 'Mulai Belanja',
                btn_utama_url VARCHAR(255) NOT NULL DEFAULT 'home.php',
                btn_sekunder_label VARCHAR(100) DEFAULT 'Daftar Gratis',
                btn_sekunder_url VARCHAR(255) DEFAULT 'register.php',
                urutan INT NOT NULL DEFAULT 0,
                aktif TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
}

function seedHeroSlidesIfEmpty(): void
{
    static $seeded = false;
    if ($seeded) {
        return;
    }
    $seeded = true;

    ensureHeroSlidesTable();
    $count = (int) db()->query('SELECT COUNT(*) FROM hero_slides')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $defaults = [
        ['Smart Distribution', 'Empowering Farmers Through Smart Distribution.', 'Memperpendek rantai distribusi yang panjang dan menciptakan peluang yang lebih adil melalui distribusi cerdas', 'assets/slide.png', 'from-primary/80', 1],
        ['Community Empowerment', 'Lebih dari sekedar Marketplace', 'Menghubungkan petani lokal dengan konsumen sekaligus memberdayakan petani, masyarakat dan generasi muda', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDpNh1RVfev1fkjcwn1FSugvisPsudFtdeUoaPqY0bJTZBRz9_WXApOrLza1MECssg8hBImwzHtbrzz2p1v43SnCiVzoLIDv3mbXihRppNMl8K2L3HmZadG6Y8hwRoBWcx66WtYJl71oQk2cezjjLjIXclDyd4FR8GTlEY4IJb5_uWcfdyyfAo0-dWDi4Ih-TK66ABpFCsW-THx-E2VTeBvQOVA4Hn7IHFZ9sCB-LXmvRLRkL2E23qpfFC7bYjfHgCfp6BnyUtR1yPK', 'from-primary/75', 2],
        ['Suistainable Future', 'Dari keranjang anda, untuk masa depan petani', 'Dengan membeli produk di tanixpress anda sedang membantu petani lokal untuk mendapatkan harga yang lebih layak atas kerja keras mereka', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCPTGFjQjiaPhoPYKiXH5crblDIQ0HNEHZ3yneW9DRyFT1KFM-FjEvs0XA7JQC-pjEnCU6JwOe3LFh1JjE6ceP71Rfd9Un4wz_wDbICTqc_v_A1nnwba2_2DRr2y_PSMvpLfj3hAfXQUR4rEPHKrBcLLRmp55d3dbLWYp8GIxlNqRWiw9BIFeI7kEWhIbReeB7XmO7FVopIjrAAGwKailfQIasWG4DOlpesNFHNZh_Ru4Q0785DUyJsMbIxOn_J8lFGZ5XLzdOBhWKX', 'from-primary/70', 3],
        ['Youth-Led Innovation', 'Dari generasi muda, untuk masa depan pertanian', 'Dari kepedulian siswa SMA AL-KARIM terhadap tantangan yang di hadapi petani di sekitar', 'https://lh3.googleusercontent.com/aida-public/AB6AXuByrmnW0m3b70g_r3ihu4n2RejxRvjWqdBS4Dg27n2FYgqTjRIGXhV2d-8AW7CnviMU7KaK_xqRWYSCmXpsebyXTwJH3cspatmBqmXhoxjJZ6jRBOKI6jHQaXSnTQ4BTh3yVVE-Ux0J4qkEegw7Wb9LrZZyfOukfWD4o0nmRY0SNIayQJBaA4HFMFyVheCliiYt_331b4TD7Ij-qTnwUss_zqL9RmG1dUw5BZYuahH1pTpOTTNOPrTFRxcPrBGhpomC630pMqRLdkMy', 'from-secondary/75', 4],
    ];

    $stmt = db()->prepare('
        INSERT INTO hero_slides (badge, judul, deskripsi, gambar, gradient, urutan)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    foreach ($defaults as $slide) {
        $stmt->execute($slide);
    }
}

function getHeroSlides(bool $activeOnly = true): array
{
    ensureHeroSlidesTable();
    seedHeroSlidesIfEmpty();

    $sql = 'SELECT * FROM hero_slides';
    if ($activeOnly) {
        $sql .= ' WHERE aktif = 1';
    }
    $sql .= ' ORDER BY urutan ASC, id ASC';

    $rows = db()->query($sql)->fetchAll();
    foreach ($rows as &$row) {
        $row['img'] = mediaSrc($row['gambar']) ?? $row['gambar'];
        $row['title'] = $row['judul'];
        $row['desc'] = $row['deskripsi'];
    }
    unset($row);

    return $rows;
}

function getHeroSlide(int $id): ?array
{
    ensureHeroSlidesTable();
    $stmt = db()->prepare('SELECT * FROM hero_slides WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getNextHeroSlideOrder(): int
{
    ensureHeroSlidesTable();
    return (int) db()->query('SELECT COALESCE(MAX(urutan), 0) + 1 FROM hero_slides')->fetchColumn();
}

function swapHeroSlideOrder(int $id, string $direction): void
{
    $slide = getHeroSlide($id);
    if (!$slide) {
        return;
    }

    $operator = $direction === 'up' ? '<' : '>';
    $sort = $direction === 'up' ? 'DESC' : 'ASC';
    $stmt = db()->prepare("SELECT id, urutan FROM hero_slides WHERE urutan $operator ? ORDER BY urutan $sort LIMIT 1");
    $stmt->execute([(int) $slide['urutan']]);
    $neighbor = $stmt->fetch();
    if (!$neighbor) {
        return;
    }

    $pdo = db();
    $pdo->prepare('UPDATE hero_slides SET urutan = ? WHERE id = ?')->execute([(int) $neighbor['urutan'], $id]);
    $pdo->prepare('UPDATE hero_slides SET urutan = ? WHERE id = ?')->execute([(int) $slide['urutan'], (int) $neighbor['id']]);
}
