<?php
/** Floating live chat untuk customer (guest & login) */
$user = currentUser();
if ($user && $user['role'] === 'admin') {
    return;
}

$chatLoggedIn = $user && $user['role'] === 'customer';
$chatUserName = $chatLoggedIn ? $user['nama'] : '';
$chatApiUrl = 'api/chat.php';
?>

<style>
    #chat-fab {
        position: fixed;
        right: 1rem;
        bottom: calc(5.25rem + env(safe-area-inset-bottom, 0px));
        z-index: 55;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 9999px;
        background: #0f5238;
        color: #fff;
        box-shadow: 0 8px 24px rgba(15, 82, 56, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, background 0.2s;
    }
    #chat-fab:hover { background: #2d6a4f; transform: scale(1.05); }
    @media (min-width: 768px) {
        #chat-fab { bottom: 1.5rem; }
    }
    #chat-panel {
        position: fixed;
        right: 1rem;
        bottom: calc(9.5rem + env(safe-area-inset-bottom, 0px));
        z-index: 56;
        width: min(100vw - 2rem, 380px);
        height: min(70dvh, 520px);
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 82, 56, 0.2);
        border: 1px solid rgba(191, 201, 193, 0.5);
        display: flex;
        flex-direction: column;
        background: #fff;
        transform: scale(0.95) translateY(12px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s, transform 0.25s;
    }
    #chat-panel.is-open {
        opacity: 1;
        transform: scale(1) translateY(0);
        pointer-events: auto;
    }
    @media (min-width: 768px) {
        #chat-panel { bottom: 5.5rem; }
    }
    .chat-widget-bubble-user {
        background: #D8F3DC;
        color: #1B4332;
        border-bottom-right-radius: 4px;
    }
    .chat-widget-bubble-admin {
        background: #fff;
        color: #1B4332;
        border: 1px solid #e7e8e7;
        border-bottom-left-radius: 4px;
    }
    #chat-fab-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 9999px;
        background: #BC4749;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        display: none;
        align-items: center;
        justify-content: center;
    }
    #chat-closed-notice {
        display: none;
        padding: 1rem;
        border-top: 1px solid #e7e8e7;
        background: #f3f4f3;
        text-align: center;
    }
    #chat-closed-notice.is-visible { display: block; }
    #chat-send-form.is-disabled { display: none; }
    #chat-status-label.is-closed { color: #fed0c1; }
</style>

<button type="button" id="chat-fab" aria-label="Buka live chat" aria-expanded="false">
    <span class="material-symbols-outlined text-[26px]">chat</span>
    <span id="chat-fab-badge">0</span>
</button>

<div id="chat-panel" role="dialog" aria-label="Live chat TaniExpress">
    <div class="bg-primary text-white px-4 py-3 flex items-center gap-3 shrink-0">
        <span class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">support_agent</span>
        </span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-sm">Live Chat</p>
            <p class="text-xs text-white/80" id="chat-status-label">Admin TaniExpress</p>
        </div>
        <button type="button" id="chat-close" class="w-8 h-8 rounded-full hover:bg-white/15 flex items-center justify-center" aria-label="Tutup chat">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    <!-- Init form -->
    <div id="chat-init" class="flex-1 overflow-y-auto p-4 <?= $chatLoggedIn ? 'hidden' : '' ?>">
        <p class="text-sm text-text-muted mb-4">Hubungi admin untuk bantuan belanja atau pertanyaan pesanan.</p>
        <form id="chat-init-form" class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1">Nama Anda</label>
                <input type="text" name="visitor_name" required maxlength="100" class="input-field text-sm" placeholder="Nama lengkap"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-text-muted mb-1">No. Telepon (opsional)</label>
                <input type="tel" name="visitor_telepon" class="input-field text-sm" placeholder="08xxxxxxxxxx"/>
            </div>
            <button type="submit" class="w-full py-3 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors">
                Mulai Chat
            </button>
        </form>
    </div>

    <!-- Messages -->
    <div id="chat-messages-wrap" class="flex-1 flex flex-col min-h-0 <?= $chatLoggedIn ? '' : 'hidden' ?>">
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-surface-container-low/40"></div>
        <div id="chat-closed-notice">
            <span class="material-symbols-outlined text-3xl text-outline mb-2">lock</span>
            <p class="text-sm font-semibold text-text-main">Percakapan ditutup</p>
            <p class="text-xs text-text-muted mt-1 mb-3 leading-relaxed">Admin telah menyelesaikan sesi chat ini. Anda masih bisa membaca riwayat pesan di atas.</p>
            <button type="button" id="chat-reopen-btn" class="w-full py-2.5 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-container transition-colors">
                Hubungi Admin Lagi
            </button>
        </div>
        <form id="chat-send-form" class="p-3 border-t border-outline-variant/20 flex gap-2 items-end bg-white">
            <textarea id="chat-input" rows="1" placeholder="Ketik pesan..." class="input-field text-sm !rounded-2xl flex-1 resize-none max-h-24"></textarea>
            <button type="submit" class="w-10 h-10 shrink-0 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-container">
                <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const apiBase = <?= json_encode($chatApiUrl) ?>;
    const loggedIn = <?= $chatLoggedIn ? 'true' : 'false' ?>;
    const fab = document.getElementById('chat-fab');
    const panel = document.getElementById('chat-panel');
    const closeBtn = document.getElementById('chat-close');
    const initEl = document.getElementById('chat-init');
    const initForm = document.getElementById('chat-init-form');
    const messagesWrap = document.getElementById('chat-messages-wrap');
    const messagesEl = document.getElementById('chat-messages');
    const sendForm = document.getElementById('chat-send-form');
    const input = document.getElementById('chat-input');
    const fabBadge = document.getElementById('chat-fab-badge');
    const statusLabel = document.getElementById('chat-status-label');
    const closedNotice = document.getElementById('chat-closed-notice');
    const reopenBtn = document.getElementById('chat-reopen-btn');
    let threadId = 0;
    let lastId = 0;
    let pollTimer = null;
    let isOpen = false;
    let threadClosed = false;

    function escapeHtml(t) {
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    function renderMsg(m) {
        const mine = m.sender_role === 'user';
        return '<div class="flex ' + (mine ? 'justify-end' : 'justify-start') + '">' +
            '<div class="max-w-[85%] px-3 py-2 rounded-2xl text-sm ' +
            (mine ? 'chat-widget-bubble-user' : 'chat-widget-bubble-admin') + '">' +
            (mine ? '' : '<p class="text-[10px] font-bold text-primary mb-0.5">Admin</p>') +
            '<p class="whitespace-pre-wrap break-words">' + escapeHtml(m.message) + '</p>' +
            '<p class="text-[10px] opacity-60 mt-1">' + escapeHtml(m.time_label) + '</p></div></div>';
    }

    function scrollBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function showChat() {
        initEl.classList.add('hidden');
        messagesWrap.classList.remove('hidden');
    }

    function setThreadClosed(closed) {
        threadClosed = closed;
        closedNotice?.classList.toggle('is-visible', closed);
        sendForm?.classList.toggle('is-disabled', closed);
        if (input) {
            input.disabled = closed;
            input.placeholder = closed ? 'Percakapan ditutup' : 'Ketik pesan...';
        }
        sendForm?.querySelector('button[type="submit"]')?.toggleAttribute('disabled', closed);
        statusLabel?.classList.toggle('is-closed', closed);
        if (statusLabel) {
            statusLabel.textContent = closed ? 'Sesi chat selesai' : 'Admin TaniExpress';
        }
    }

    function pollThreadStatus() {
        if (!threadId) return;
        fetch(apiBase + '?action=thread')
            .then(r => r.json())
            .then(data => {
                if (data.ok && data.thread) {
                    setThreadClosed(data.thread.status === 'closed');
                }
            })
            .catch(() => {});
    }

    function fetchMessages(initial) {
        if (!threadId) return;
        fetch(apiBase + '?action=messages&thread_id=' + threadId + '&after_id=' + (initial ? 0 : lastId))
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return;
                if (data.thread_status) {
                    setThreadClosed(data.thread_status === 'closed');
                }
                if (initial) messagesEl.innerHTML = '';
                data.messages.forEach(m => {
                    if (m.id > lastId) {
                        messagesEl.insertAdjacentHTML('beforeend', renderMsg(m));
                        lastId = m.id;
                    }
                });
                if (data.messages.length) scrollBottom();
            });
    }

    function startPolling() {
        stopPolling();
        pollTimer = setInterval(() => {
            fetchMessages(false);
            pollThreadStatus();
        }, 3000);
    }

    function stopPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = null;
    }

    function checkUnread() {
        if (!threadId || isOpen) return;
        fetch(apiBase + '?action=thread')
            .then(r => r.json())
            .then(data => {
                if (data.ok && data.thread && data.thread.unread_user > 0) {
                    fabBadge.textContent = data.thread.unread_user > 9 ? '9+' : data.thread.unread_user;
                    fabBadge.style.display = 'flex';
                }
            });
    }

    function initThread(fd) {
        fd.append('action', 'init');
        return fetch(apiBase, { method: 'POST', body: fd }).then(r => r.json());
    }

    function bootChat() {
        fetch(apiBase + '?action=thread')
            .then(r => r.json())
            .then(data => {
                if (data.ok && data.thread) {
                    threadId = data.thread.id;
                    setThreadClosed(data.thread.status === 'closed');
                    showChat();
                    fetchMessages(true);
                    if (isOpen) startPolling();
                } else if (loggedIn) {
                    const fd = new FormData();
                    initThread(fd).then(d => {
                        if (d.ok) {
                            threadId = d.thread.id;
                            setThreadClosed(false);
                            showChat();
                            fetchMessages(true);
                            if (isOpen) startPolling();
                        }
                    });
                }
            });
    }

    fab?.addEventListener('click', () => {
        isOpen = !isOpen;
        panel.classList.toggle('is-open', isOpen);
        fab.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (isOpen) {
            fabBadge.style.display = 'none';
            if (threadId) {
                fetchMessages(false);
                startPolling();
            }
        } else {
            stopPolling();
        }
    });

    closeBtn?.addEventListener('click', () => {
        isOpen = false;
        panel.classList.remove('is-open');
        fab.setAttribute('aria-expanded', 'false');
        stopPolling();
    });

    initForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(initForm);
        initThread(fd).then(data => {
            if (data.ok) {
                threadId = data.thread.id;
                setThreadClosed(false);
                showChat();
                fetchMessages(true);
                startPolling();
            } else if (data.error) {
                window.TaniUI?.showToast('error', data.error);
            }
        });
    });

    sendForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (threadClosed) {
            window.TaniUI?.showToast('info', 'Percakapan sudah ditutup. Klik "Hubungi Admin Lagi" untuk melanjutkan.');
            return;
        }
        const msg = (input.value || '').trim();
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
                    messagesEl.insertAdjacentHTML('beforeend', renderMsg(data.message));
                    lastId = data.message.id;
                    scrollBottom();
                } else if (data.closed) {
                    setThreadClosed(true);
                    window.TaniUI?.showToast('info', data.error || 'Percakapan sudah ditutup oleh admin.');
                } else if (data.error) {
                    window.TaniUI?.showToast('error', data.error);
                }
            });
    });

    reopenBtn?.addEventListener('click', function () {
        if (!threadId) return;
        const fd = new FormData();
        fd.append('action', 'reopen');
        fd.append('thread_id', threadId);
        fetch(apiBase, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    setThreadClosed(false);
                    window.TaniUI?.showToast('success', 'Percakapan dibuka kembali. Silakan kirim pesan.');
                    input?.focus();
                } else if (data.error) {
                    window.TaniUI?.showToast('error', data.error);
                }
            });
    });

    input?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendForm.requestSubmit();
        }
    });

    bootChat();
    setInterval(checkUnread, 8000);
})();
</script>
