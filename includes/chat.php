<?php

function ensureChatTables(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if (!db()->query("SHOW TABLES LIKE 'chat_threads'")->fetch()) {
        db()->exec("
            CREATE TABLE chat_threads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                guest_token VARCHAR(64) DEFAULT NULL,
                visitor_name VARCHAR(100) NOT NULL,
                visitor_type ENUM('customer', 'petani') NOT NULL DEFAULT 'customer',
                visitor_telepon VARCHAR(20) DEFAULT NULL,
                status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
                last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unread_admin INT NOT NULL DEFAULT 0,
                unread_user INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_thread (user_id),
                UNIQUE KEY unique_guest_token (guest_token),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    if (!db()->query("SHOW TABLES LIKE 'chat_messages'")->fetch()) {
        db()->exec("
            CREATE TABLE chat_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                thread_id INT NOT NULL,
                sender_role ENUM('user', 'admin') NOT NULL,
                sender_user_id INT DEFAULT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
                FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
    }
}

function chatGuestToken(): string
{
    if (empty($_SESSION['chat_guest_token'])) {
        $_SESSION['chat_guest_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['chat_guest_token'];
}

function chatThreadDisplayName(array $thread): string
{
    return $thread['visitor_name'] ?: 'Pengunjung';
}

function chatVisitorTypeLabel(string $type): string
{
    return $type === 'petani' ? 'Petani' : 'Customer';
}

function getChatUnreadAdminCount(): int
{
    ensureChatTables();
    return (int) db()->query("SELECT COALESCE(SUM(unread_admin), 0) FROM chat_threads WHERE status = 'open' AND visitor_type = 'customer'")->fetchColumn();
}

function getChatThread(int $threadId): ?array
{
    ensureChatTables();
    $stmt = db()->prepare('SELECT * FROM chat_threads WHERE id = ?');
    $stmt->execute([$threadId]);
    return $stmt->fetch() ?: null;
}

function canAccessChatThread(array $thread, ?array $user): bool
{
    if ($user && $user['role'] === 'admin') {
        return true;
    }
    if ($user && $user['role'] === 'customer' && (int) ($thread['user_id'] ?? 0) === (int) $user['id']) {
        return true;
    }
    if (!empty($thread['guest_token']) && $thread['guest_token'] === ($_SESSION['chat_guest_token'] ?? '')) {
        return true;
    }
    return false;
}

function getOrCreateCustomerThread(?array $user, ?string $visitorName = null, ?string $visitorType = null, ?string $telepon = null): array
{
    ensureChatTables();

    if ($user && $user['role'] === 'customer') {
        $stmt = db()->prepare('SELECT * FROM chat_threads WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $existing = $stmt->fetch();
        if ($existing) {
            return $existing;
        }

        db()->prepare('
            INSERT INTO chat_threads (user_id, visitor_name, visitor_type, visitor_telepon)
            VALUES (?, ?, ?, ?)
        ')->execute([
            $user['id'],
            $user['nama'],
            'customer',
            $user['telepon'] ?? null,
        ]);

        return getChatThread((int) db()->lastInsertId());
    }

    $token = chatGuestToken();
    $stmt = db()->prepare('SELECT * FROM chat_threads WHERE guest_token = ?');
    $stmt->execute([$token]);
    $existing = $stmt->fetch();
    if ($existing) {
        return $existing;
    }

    $name = trim($visitorName ?? '') ?: 'Pengunjung';

    db()->prepare('
        INSERT INTO chat_threads (guest_token, visitor_name, visitor_type, visitor_telepon)
        VALUES (?, ?, ?, ?)
    ')->execute([
        $token,
        $name,
        'customer',
        $telepon ? trim($telepon) : null,
    ]);

    return getChatThread((int) db()->lastInsertId());
}

function getChatThreadsForAdmin(?string $filter = null): array
{
    ensureChatTables();
    $sql = "
        SELECT t.*,
               (SELECT message FROM chat_messages WHERE thread_id = t.id ORDER BY id DESC LIMIT 1) AS last_message
        FROM chat_threads t
        WHERE t.visitor_type = 'customer'
    ";
    $sql .= ' ORDER BY t.last_message_at DESC, t.id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getChatMessages(int $threadId, int $afterId = 0): array
{
    ensureChatTables();
    if ($afterId > 0) {
        $stmt = db()->prepare('
            SELECT m.*, u.nama AS sender_nama
            FROM chat_messages m
            LEFT JOIN users u ON u.id = m.sender_user_id
            WHERE m.thread_id = ? AND m.id > ?
            ORDER BY m.id ASC
        ');
        $stmt->execute([$threadId, $afterId]);
    } else {
        $stmt = db()->prepare('
            SELECT m.*, u.nama AS sender_nama
            FROM chat_messages m
            LEFT JOIN users u ON u.id = m.sender_user_id
            WHERE m.thread_id = ?
            ORDER BY m.id ASC
            LIMIT 200
        ');
        $stmt->execute([$threadId]);
    }
    return $stmt->fetchAll();
}

function sendChatMessage(int $threadId, string $message, string $senderRole, ?int $senderUserId = null): ?array
{
    ensureChatTables();
    $message = trim($message);
    if ($message === '') {
        return null;
    }

    $thread = getChatThread($threadId);
    if (!$thread || $thread['status'] === 'closed') {
        return null;
    }

    db()->prepare('
        INSERT INTO chat_messages (thread_id, sender_role, sender_user_id, message)
        VALUES (?, ?, ?, ?)
    ')->execute([$threadId, $senderRole, $senderUserId, $message]);

    $messageId = (int) db()->lastInsertId();

    if ($senderRole === 'admin') {
        db()->prepare('
            UPDATE chat_threads SET last_message_at = NOW(), unread_user = unread_user + 1
            WHERE id = ?
        ')->execute([$threadId]);
    } else {
        db()->prepare('
            UPDATE chat_threads SET last_message_at = NOW(), unread_admin = unread_admin + 1
            WHERE id = ?
        ')->execute([$threadId]);
    }

    $stmt = db()->prepare('
        SELECT m.*, u.nama AS sender_nama
        FROM chat_messages m
        LEFT JOIN users u ON u.id = m.sender_user_id
        WHERE m.id = ?
    ');
    $stmt->execute([$messageId]);
    return $stmt->fetch() ?: null;
}

function markChatThreadRead(int $threadId, string $forRole): void
{
    ensureChatTables();
    if ($forRole === 'admin') {
        db()->prepare('UPDATE chat_threads SET unread_admin = 0 WHERE id = ?')->execute([$threadId]);
    } else {
        db()->prepare('UPDATE chat_threads SET unread_user = 0 WHERE id = ?')->execute([$threadId]);
    }
}

function chatJsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function formatChatTime(string $datetime): string
{
    $ts = strtotime($datetime);
    if (date('Y-m-d', $ts) === date('Y-m-d')) {
        return date('H:i', $ts);
    }
    return date('d/m H:i', $ts);
}

function reopenChatThread(int $threadId): bool
{
    ensureChatTables();
    $thread = getChatThread($threadId);
    if (!$thread) {
        return false;
    }
    db()->prepare("UPDATE chat_threads SET status = 'open', last_message_at = NOW() WHERE id = ?")->execute([$threadId]);
    return true;
}
