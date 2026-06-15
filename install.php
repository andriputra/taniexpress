<?php
/**
 * Reset & install ulang database TaniExpress
 * Jalankan: php install.php
 * Tambahkan --fresh untuk drop database lama
 */
require_once __DIR__ . '/config/config.php';

$fresh = in_array('--fresh', $argv ?? [], true);

echo "=== Instalasi Database TaniExpress ===\n\n";

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($fresh) {
        echo "Menghapus database lama...\n";
        $pdo->exec('DROP DATABASE IF EXISTS ' . DB_NAME);
    }

    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if ($statement !== '') {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                // Skip jika install ulang tanpa --fresh
                if ($e->getCode() === '42S01' || str_contains($msg, 'already exists')) {
                    continue;
                }
                if ($e->getCode() === '23000' || str_contains($msg, 'Duplicate entry')) {
                    continue;
                }
                throw $e;
            }
        }
    }

    $pdo->exec('USE ' . DB_NAME);
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password = ?')->execute([$hash]);

    if (!is_dir(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0755, true);
    }

    // Verifikasi data
    $tables = [
        'users' => 'Akun pengguna',
        'products' => 'Master produk',
        'petani' => 'Master petani',
        'produk_petani' => 'Produk per petani',
        'kurir' => 'Master kurir',
    ];

    echo "\n--- Data Dummy Terinstall ---\n";
    foreach ($tables as $table => $label) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo sprintf("  %-18s %3d baris  (%s)\n", $label . ':', $count, $table);
    }

    echo "\n--- Akun Login ---\n";
    $users = $pdo->query('SELECT email, role, nama FROM users ORDER BY role')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        echo "  [{$u['role']}] {$u['email']} / admin123 — {$u['nama']}\n";
    }

    $products = $pdo->query('
        SELECT p.nama, pp.harga, pp.stok, pt.nama AS petani
        FROM produk_petani pp
        JOIN products p ON p.id = pp.product_id
        JOIN petani pt ON pt.id = pp.petani_id
        LIMIT 3
    ')->fetchAll(PDO::FETCH_ASSOC);

    echo "\n--- Sample Produk (3 dari 8) ---\n";
    foreach ($products as $p) {
        echo "  • {$p['nama']} — Rp " . number_format($p['harga'], 0, ',', '.') . " (stok: {$p['stok']}) — {$p['petani']}\n";
    }

    echo "\n✓ Database siap digunakan!\n";
    echo "  Server : php -S localhost:8000\n";
    echo "  URL    : http://localhost:8000\n\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nTips:\n";
    echo "  - Pastikan MySQL XAMPP sudah running (Start Apache + MySQL)\n";
    echo "  - Cek config/config.php (host, user, password)\n";
    echo "  - Install ulang penuh: php install.php --fresh\n";
    exit(1);
}
