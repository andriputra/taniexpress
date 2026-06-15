<?php

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function register(array $data): bool|string
{
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return 'Email sudah terdaftar.';
    }

    $stmt = db()->prepare('
        INSERT INTO users (nama, email, password, role, telepon, alamat)
        VALUES (?, ?, ?, "customer", ?, ?)
    ');
    $stmt->execute([
        $data['nama'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['telepon'],
        $data['alamat'],
    ]);

    $id = (int) db()->lastInsertId();
    $_SESSION['user_id'] = $id;
    $_SESSION['user_role'] = 'customer';
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
        session_start();
    }
}

function requireLogin(): void
{
    if (!currentUser()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

function requireCustomer(): void
{
    requireLogin();
    if (currentUser()['role'] !== 'customer') {
        redirect('admin/index.php');
    }
}

function requireAdmin(): void
{
    if (!currentUser() || currentUser()['role'] !== 'admin') {
        flash('error', 'Akses admin diperlukan.');
        redirect('admin/login.php');
    }
}
