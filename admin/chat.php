<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
ensureChatTables();

$activeThreadId = (int) ($_GET['thread'] ?? 0);

$threads = getChatThreadsForAdmin();
$unreadTotal = getChatUnreadAdminCount();

$activeThread = $activeThreadId ? getChatThread($activeThreadId) : null;
if ($activeThread && $activeThread['visitor_type'] !== 'customer') {
    $activeThread = null;
}

$pageTitle = 'Live Chat';
$activeMenu = 'chat';
include __DIR__ . '/../includes/admin-layout-start.php';
?>

<style>
    .chat-admin-layout { height: calc(100dvh - 8.5rem); min-height: 420px; }
    @media (min-width: 768px) { .chat-admin-layout { height: calc(100dvh - 7rem); } }
    .chat-thread-item.is-active { background: rgba(216, 243, 220, 0.65); border-color: rgba(15, 82, 56, 0.25); }
    .chat-bubble-admin { background: #0f5238; color: #fff; border-bottom-right-radius: 4px; }
    .chat-bubble-user { background: #fff; color: #1B4332; border: 1px solid #e7e8e7; border-bottom-left-radius: 4px; }
    .chat-messages-area { scroll-behavior: smooth; }
</style>

<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <p class="text-sm text-text-muted">Balas pesan customer secara langsung.</p>
        <?php if ($unreadTotal > 0): ?>
            <p class="text-xs text-primary font-semibold mt-1"><?= $unreadTotal ?> pesan belum dibaca</p>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden tonal-shadow chat-admin-layout flex flex-col md:flex-row">
    <!-- Thread list -->
    <aside class="md:w-80 lg:w-96 border-b md:border-b-0 md:border-r border-outline-variant/30 flex flex-col min-h-0 shrink-0 max-h-[40vh] md:max-h-none">
        <div class="px-4 py-3 border-b border-outline-variant/20 bg-surface-container-low/50">
            <h3 class="font-semibold text-sm text-text-main flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">forum</span>
                Percakapan
                <span id="admin-chat-unread-badge" class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-error-red text-white <?= $unreadTotal ? '' : 'hidden' ?>"><?= $unreadTotal ?></span>
            </h3>
        </div>
        <div id="admin-thread-list" class="flex-1 overflow-y-auto divide-y divide-outline-variant/15">
            <?php if (empty($threads)): ?>
                <p class="p-6 text-sm text-text-muted text-center">Belum ada percakapan.</p>
            <?php else: ?>
                <?php foreach ($threads as $t): ?>
                    <a href="chat.php?thread=<?= $t['id'] ?>"
                       class="chat-thread-item block px-4 py-3 hover:bg-leaf-green-light/30 transition-colors border-l-4 border-transparent <?= $activeThreadId === (int) $t['id'] ? 'is-active border-l-primary' : '' ?>"
                       data-thread-id="<?= $t['id'] ?>">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-text-main truncate"><?= e(chatThreadDisplayName($t)) ?></p>
                            </div>
                            <?php if ($t['unread_admin'] > 0): ?>
                                <span class="shrink-0 min-w-[20px] h-5 px-1.5 rounded-full bg-error-red text-white text-[10px] font-bold flex items-center justify-center thread-unread"><?= $t['unread_admin'] ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-text-muted mt-1.5 line-clamp-2 thread-preview"><?= e($t['last_message'] ?? 'Belum ada pesan') ?></p>
                        <p class="text-[10px] text-outline mt-1"><?= formatChatTime($t['last_message_at']) ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Chat panel -->
    <div class="flex-1 flex flex-col min-h-0 min-w-0">
        <?php
        if (!$activeThread):
        ?>
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-text-muted">
                <span class="material-symbols-outlined text-5xl text-outline mb-3">chat</span>
                <p class="font-medium text-text-main">Pilih percakapan</p>
                <p class="text-sm mt-1">Klik daftar di kiri untuk mulai membalas.</p>
            </div>
        <?php else: ?>
            <div class="px-4 py-3 border-b border-outline-variant/20 flex items-center justify-between gap-3 bg-surface-container-low/40">
                <div class="min-w-0">
                    <p class="font-bold text-text-main truncate"><?= e(chatThreadDisplayName($activeThread)) ?></p>
                    <?php if ($activeThread['visitor_telepon']): ?>
                        <p class="text-xs text-text-muted"><?= e($activeThread['visitor_telepon']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($activeThread['status'] === 'open'): ?>
                    <form method="POST" action="../api/chat.php" id="close-thread-form" class="shrink-0">
                        <input type="hidden" name="action" value="close"/>
                        <input type="hidden" name="thread_id" value="<?= $activeThread['id'] ?>"/>
                        <button type="button" id="close-thread-btn"
                                class="text-xs px-3 py-1.5 rounded-full border border-outline-variant/50 text-text-muted hover:text-error-red hover:border-error-red/30"
                                data-confirm="Percakapan akan ditutup. Customer tidak dapat mengirim pesan baru."
                                data-confirm-title="Tutup Percakapan">
                            Tutup chat
                        </button>
                    </form>
                <?php else: ?>
                    <span class="text-xs px-3 py-1 rounded-full bg-surface-container-high text-outline">Ditutup</span>
                <?php endif; ?>
            </div>

            <div id="admin-chat-messages" class="chat-messages-area flex-1 overflow-y-auto p-4 space-y-3 bg-surface/50" data-thread-id="<?= $activeThread['id'] ?>"></div>

            <form id="admin-chat-form" class="p-3 md:p-4 border-t border-outline-variant/20 bg-white flex gap-2 items-end">
                <input type="hidden" name="thread_id" value="<?= $activeThread['id'] ?>"/>
                <textarea id="admin-chat-input" name="message" rows="1" placeholder="Ketik balasan..." class="input-field text-sm !rounded-2xl flex-1 resize-none max-h-32" <?= $activeThread['status'] !== 'open' ? 'disabled' : '' ?>></textarea>
                <button type="submit" class="w-11 h-11 shrink-0 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-container transition-colors disabled:opacity-40" <?= $activeThread['status'] !== 'open' ? 'disabled' : '' ?>>
                    <span class="material-symbols-outlined">send</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const apiBase = '../api/chat.php';
    const messagesEl = document.getElementById('admin-chat-messages');
    const form = document.getElementById('admin-chat-form');
    const input = document.getElementById('admin-chat-input');
    const threadId = messagesEl ? parseInt(messagesEl.dataset.threadId || '0', 10) : 0;
    let lastId = 0;
    let pollTimer = null;

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function renderMessage(m) {
        const isAdmin = m.sender_role === 'admin';
        return '<div class="flex ' + (isAdmin ? 'justify-end' : 'justify-start') + '">' +
            '<div class="max-w-[85%] sm:max-w-[70%] px-4 py-2.5 rounded-2xl text-sm leading-relaxed ' +
            (isAdmin ? 'chat-bubble-admin' : 'chat-bubble-user') + '">' +
            '<p class="whitespace-pre-wrap break-words">' + escapeHtml(m.message) + '</p>' +
            '<p class="text-[10px] mt-1 opacity-70">' + escapeHtml(m.time_label) + '</p></div></div>';
    }

    function scrollBottom() {
        if (!messagesEl) return;
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function fetchMessages(initial) {
        if (!threadId) return;
        fetch(apiBase + '?action=messages&thread_id=' + threadId + '&after_id=' + (initial ? 0 : lastId))
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return;
                if (initial) messagesEl.innerHTML = '';
                data.messages.forEach(m => {
                    if (m.id > lastId) {
                        messagesEl.insertAdjacentHTML('beforeend', renderMessage(m));
                        lastId = m.id;
                    }
                });
                if (data.messages.length) scrollBottom();
            })
            .catch(() => {});
    }

    function pollThreads() {
        fetch(apiBase + '?action=threads')
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return;
                const badge = document.getElementById('admin-chat-unread-badge');
                if (badge) {
                    badge.textContent = data.unread_total;
                    badge.classList.toggle('hidden', !data.unread_total);
                }
            })
            .catch(() => {});
    }

    if (messagesEl && threadId) {
        fetchMessages(true);
        pollTimer = setInterval(() => fetchMessages(false), 3000);
        setInterval(pollThreads, 5000);
    }

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = (input?.value || '').trim();
        if (!msg || !threadId) return;
        const fd = new FormData();
        fd.append('action', 'send');
        fd.append('thread_id', threadId);
        fd.append('message', msg);
        fetch(apiBase, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.ok && data.message) {
                    input.value = '';
                    messagesEl.insertAdjacentHTML('beforeend', renderMessage(data.message));
                    lastId = data.message.id;
                    scrollBottom();
                }
            });
    });

    input?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form?.requestSubmit();
        }
    });

    document.getElementById('close-thread-btn')?.addEventListener('click', function () {
        const runClose = () => {
            const fd = new FormData(document.getElementById('close-thread-form'));
            fetch(apiBase, { method: 'POST', body: fd }).then(() => location.reload());
        };
        const msg = this.getAttribute('data-confirm') || 'Tutup percakapan ini?';
        const title = this.getAttribute('data-confirm-title') || 'Tutup Percakapan';
        if (window.TaniUI?.showConfirm) {
            window.TaniUI.showConfirm(msg, title).then((ok) => { if (ok) runClose(); });
        } else if (confirm(msg)) {
            runClose();
        }
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-layout-end.php'; ?>
