<?php
require_once __DIR__ . '/../includes/bootstrap.php';

ensureChatTables();

$user = currentUser();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'unread_admin') {
    if (!$user || $user['role'] !== 'admin') {
        chatJsonResponse(['ok' => false, 'count' => 0], 403);
    }
    chatJsonResponse(['ok' => true, 'count' => getChatUnreadAdminCount()]);
}

if ($action === 'threads') {
    if (!$user || $user['role'] !== 'admin') {
        chatJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 403);
    }
    $filter = $_GET['filter'] ?? null;
    $threads = getChatThreadsForAdmin($filter);
    $list = array_map(static function ($t) {
        return [
            'id' => (int) $t['id'],
            'visitor_name' => chatThreadDisplayName($t),
            'visitor_type' => $t['visitor_type'],
            'visitor_type_label' => chatVisitorTypeLabel($t['visitor_type']),
            'visitor_telepon' => $t['visitor_telepon'],
            'status' => $t['status'],
            'unread_admin' => (int) $t['unread_admin'],
            'last_message' => $t['last_message'] ?? '',
            'last_message_at' => $t['last_message_at'],
            'time_label' => formatChatTime($t['last_message_at']),
        ];
    }, $threads);
    chatJsonResponse(['ok' => true, 'threads' => $list, 'unread_total' => getChatUnreadAdminCount()]);
}

if ($action === 'init' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user && $user['role'] === 'admin') {
        chatJsonResponse(['ok' => false, 'error' => 'Admin tidak perlu init chat'], 400);
    }

    $name = trim($_POST['visitor_name'] ?? '');
    $telepon = trim($_POST['visitor_telepon'] ?? '');

    if ($user && $user['role'] === 'customer') {
        $thread = getOrCreateCustomerThread($user);
    } else {
        if ($name === '') {
            chatJsonResponse(['ok' => false, 'error' => 'Nama wajib diisi'], 422);
        }
        $thread = getOrCreateCustomerThread(null, $name, 'customer', $telepon ?: null);
    }

    chatJsonResponse([
        'ok' => true,
        'thread' => [
            'id' => (int) $thread['id'],
            'visitor_name' => chatThreadDisplayName($thread),
            'visitor_type_label' => chatVisitorTypeLabel($thread['visitor_type']),
            'status' => $thread['status'],
        ],
    ]);
}

if ($action === 'thread') {
    if ($user && $user['role'] === 'customer') {
        $thread = getOrCreateCustomerThread($user);
    } elseif (!empty($_SESSION['chat_guest_token'])) {
        $stmt = db()->prepare('SELECT * FROM chat_threads WHERE guest_token = ?');
        $stmt->execute([$_SESSION['chat_guest_token']]);
        $thread = $stmt->fetch() ?: null;
    } else {
        $thread = null;
    }

    if (!$thread) {
        chatJsonResponse(['ok' => false, 'needs_init' => true]);
    }

    chatJsonResponse([
        'ok' => true,
        'thread' => [
            'id' => (int) $thread['id'],
            'visitor_name' => chatThreadDisplayName($thread),
            'visitor_type_label' => chatVisitorTypeLabel($thread['visitor_type']),
            'unread_user' => (int) $thread['unread_user'],
            'status' => $thread['status'],
        ],
    ]);
}

if ($action === 'messages') {
    $threadId = (int) ($_GET['thread_id'] ?? 0);
    $afterId = (int) ($_GET['after_id'] ?? 0);
    $thread = getChatThread($threadId);

    if (!$thread || !canAccessChatThread($thread, $user)) {
        chatJsonResponse(['ok' => false, 'error' => 'Thread tidak ditemukan'], 404);
    }

    $forRole = ($user && $user['role'] === 'admin') ? 'admin' : 'user';
    markChatThreadRead($threadId, $forRole);

    $messages = getChatMessages($threadId, $afterId);
    $formatted = array_map(static function ($m) {
        return [
            'id' => (int) $m['id'],
            'sender_role' => $m['sender_role'],
            'sender_nama' => $m['sender_role'] === 'admin' ? ($m['sender_nama'] ?? 'Admin') : 'Anda',
            'message' => $m['message'],
            'time_label' => formatChatTime($m['created_at']),
            'created_at' => $m['created_at'],
        ];
    }, $messages);

    chatJsonResponse(['ok' => true, 'messages' => $formatted, 'thread_status' => $thread['status']]);
}

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $threadId = (int) ($_POST['thread_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $thread = getChatThread($threadId);

    if (!$thread || !canAccessChatThread($thread, $user)) {
        chatJsonResponse(['ok' => false, 'error' => 'Thread tidak ditemukan'], 404);
    }

    if ($message === '') {
        chatJsonResponse(['ok' => false, 'error' => 'Pesan kosong'], 422);
    }

    if ($thread['status'] === 'closed') {
        chatJsonResponse([
            'ok' => false,
            'error' => 'Percakapan sudah ditutup oleh admin.',
            'closed' => true,
        ], 403);
    }

    if ($user && $user['role'] === 'admin') {
        $sent = sendChatMessage($threadId, $message, 'admin', (int) $user['id']);
    } elseif ($user && $user['role'] === 'customer' && (int) $thread['user_id'] === (int) $user['id']) {
        $sent = sendChatMessage($threadId, $message, 'user', (int) $user['id']);
    } elseif (!empty($thread['guest_token']) && $thread['guest_token'] === ($_SESSION['chat_guest_token'] ?? '')) {
        $sent = sendChatMessage($threadId, $message, 'user', null);
    } else {
        chatJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 403);
    }

    if (!$sent) {
        chatJsonResponse(['ok' => false, 'error' => 'Gagal mengirim pesan'], 500);
    }

    chatJsonResponse([
        'ok' => true,
        'message' => [
            'id' => (int) $sent['id'],
            'sender_role' => $sent['sender_role'],
            'sender_nama' => $sent['sender_role'] === 'admin' ? ($sent['sender_nama'] ?? 'Admin') : 'Anda',
            'message' => $sent['message'],
            'time_label' => formatChatTime($sent['created_at']),
        ],
    ]);
}

if ($action === 'close' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user || $user['role'] !== 'admin') {
        chatJsonResponse(['ok' => false, 'error' => 'Unauthorized'], 403);
    }
    $threadId = (int) ($_POST['thread_id'] ?? 0);
    $thread = getChatThread($threadId);
    if ($thread) {
        sendChatMessage($threadId, 'Percakapan ini telah ditutup oleh admin. Terima kasih telah menghubungi kami.', 'admin', (int) $user['id']);
        db()->prepare("UPDATE chat_threads SET status = 'closed' WHERE id = ?")->execute([$threadId]);
    }
    chatJsonResponse(['ok' => true]);
}

if ($action === 'reopen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $threadId = (int) ($_POST['thread_id'] ?? 0);
    $thread = getChatThread($threadId);

    if (!$thread || !canAccessChatThread($thread, $user)) {
        chatJsonResponse(['ok' => false, 'error' => 'Thread tidak ditemukan'], 404);
    }

    if ($user && $user['role'] === 'admin') {
        chatJsonResponse(['ok' => false, 'error' => 'Hanya customer yang dapat membuka ulang'], 403);
    }

    reopenChatThread($threadId);
    chatJsonResponse(['ok' => true, 'thread_status' => 'open']);
}

chatJsonResponse(['ok' => false, 'error' => 'Invalid action'], 400);
