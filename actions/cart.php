<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? $_GET['qty'] ?? 1));
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'cart.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        $product = getProductDetail($id);
        if (!$product) {
            flash('error', 'Produk tidak ditemukan.');
            break;
        }
        $current = $_SESSION['cart'][$id] ?? 0;
        $newQty = min($current + $qty, $product['stok']);
        $_SESSION['cart'][$id] = $newQty;
        flash('success', $product['nama'] . ' ditambahkan ke keranjang.');
        $redirect = $_POST['from'] ?? 'cart.php';
        break;

    case 'update':
        if (isset($_SESSION['cart'][$id])) {
            $product = getProductDetail($id);
            if ($product) {
                $_SESSION['cart'][$id] = min(max(1, $qty), $product['stok']);
            }
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$id]);
        flash('success', 'Produk dihapus dari keranjang.');
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        flash('success', 'Keranjang dikosongkan.');
        break;
}

header('Location: ../' . ltrim($redirect, '/'));
exit;
