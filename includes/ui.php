<?php

/**
 * Komponen UI reusable TaniExpress
 */

function uiNavItems(): array
{
    return [
        'beranda'  => ['label' => 'Beranda',  'url' => 'index.php',  'icon' => 'spa'],
        'belanja'  => ['label' => 'Belanja',  'url' => 'home.php',   'icon' => 'storefront'],
        'cart'     => ['label' => 'Keranjang','url' => 'cart.php',   'icon' => 'shopping_basket'],
        'orders'   => ['label' => 'Pesanan',  'url' => 'orders.php', 'icon' => 'receipt_long'],
        'account'  => ['label' => 'Akun',     'url' => 'account.php','icon' => 'person'],
    ];
}

function uiNavClass(string $key, string $activeNav, string $variant = 'desktop'): string
{
    $active = $activeNav === $key;
    if ($variant === 'desktop') {
        return $active
            ? 'text-primary font-semibold border-b-2 border-primary pb-0.5'
            : 'text-on-surface-variant hover:text-primary transition-colors font-medium';
    }
    if ($variant === 'mobile-bottom') {
        return $active
            ? 'bg-secondary-container text-on-secondary-container rounded-full px-4 py-1'
            : 'text-outline hover:bg-surface-container-high rounded-xl p-2';
    }
    return $active ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-primary';
}

function renderFlashMessages(): void
{
    if ($success = flash('success')) {
        pushToast('success', $success);
    }
    if ($error = flash('error')) {
        pushToast('error', $error);
    }
}

function pushToast(string $type, string $message): void
{
    $GLOBALS['_toast_queue'] = $GLOBALS['_toast_queue'] ?? [];
    $GLOBALS['_toast_queue'][] = ['type' => $type, 'message' => $message];
}

function passwordToggleCss(): string
{
    return <<<'CSS'
        .password-toggle-wrap { position: relative; }
        .password-toggle-input { padding-right: 3rem !important; }
        .password-toggle-btn {
            position: absolute;
            right: 0.625rem;
            top: 50%;
            transform: translateY(-50%);
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #707973;
            border-radius: 9999px;
            transition: color .2s, background .2s;
        }
        .password-toggle-btn:hover { color: #0f5238; background: #D8F3DC; }
        .password-toggle-btn .material-symbols-outlined { font-size: 20px; line-height: 1; }
CSS;
}

function renderPasswordField(string $label, string $name, array $attrs = []): void
{
    $id = $attrs['id'] ?? $name;
    $required = !empty($attrs['required']) ? ' required' : '';
    $minlength = isset($attrs['minlength']) ? ' minlength="' . (int) $attrs['minlength'] . '"' : '';
    $autocomplete = isset($attrs['autocomplete']) ? ' autocomplete="' . e($attrs['autocomplete']) . '"' : '';
    $value = isset($attrs['value']) ? ' value="' . e($attrs['value']) . '"' : '';
    $labelClass = $attrs['labelClass'] ?? 'block text-sm font-medium text-text-muted mb-1.5';
    ?>
    <div>
        <label class="<?= e($labelClass) ?>" for="<?= e($id) ?>"><?= e($label) ?></label>
        <div class="password-toggle-wrap">
            <input
                type="password"
                id="<?= e($id) ?>"
                name="<?= e($name) ?>"
                class="input-field password-toggle-input"<?= $required ?><?= $minlength ?><?= $autocomplete ?><?= $value ?>
            />
            <button type="button" class="password-toggle-btn" aria-label="Tampilkan password" data-password-toggle>
                <span class="material-symbols-outlined icon-show">visibility</span>
                <span class="material-symbols-outlined icon-hide hidden">visibility_off</span>
            </button>
        </div>
    </div>
    <?php
}

function renderUiShell(): void
{
    static $rendered = false;
    if ($rendered) {
        return;
    }
    $rendered = true;
    ?>
    <div id="toast-root" class="fixed top-20 md:top-4 right-0 z-[200] flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4 md:pr-6"></div>
    <div id="confirm-modal" class="fixed inset-0 z-[210] hidden items-center justify-center p-4" aria-hidden="true" role="dialog">
        <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]" data-confirm-dismiss></div>
        <div id="confirm-dialog" class="relative bg-white rounded-3xl tonal-shadow max-w-sm w-full p-6 transform scale-95 opacity-0 transition-all duration-200">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mb-4 mx-auto">
                <span class="material-symbols-outlined text-error-red text-[26px]">help</span>
            </div>
            <h3 id="confirm-title" class="text-lg font-bold text-text-main text-center mb-2">Konfirmasi</h3>
            <p id="confirm-message" class="text-sm text-text-muted text-center mb-6 leading-relaxed"></p>
            <div class="flex gap-3">
                <button type="button" data-confirm-dismiss class="flex-1 py-3 rounded-full border border-outline-variant text-on-surface font-semibold text-sm hover:bg-surface-container-low transition-colors">Batal</button>
                <button type="button" id="confirm-ok" class="flex-1 py-3 rounded-full bg-primary text-white font-semibold text-sm hover:bg-primary-container transition-colors">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
    <?php
}

function renderUiScripts(): void
{
    $queue = $GLOBALS['_toast_queue'] ?? [];
    $json = json_encode($queue, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?>
    <script>
    window.TaniUI = (function () {
        const icons = { success: 'check_circle', error: 'error', info: 'info' };
        const styles = {
            success: 'bg-white border-l-4 border-success-green text-text-main shadow-lg',
            error: 'bg-white border-l-4 border-error-red text-text-main shadow-lg',
            info: 'bg-white border-l-4 border-primary text-text-main shadow-lg'
        };
        const iconColors = { success: 'text-success-green', error: 'text-error-red', info: 'text-primary' };

        function showToast(type, message, duration) {
            const root = document.getElementById('toast-root');
            if (!root || !message) return;
            duration = duration || 4200;
            const el = document.createElement('div');
            el.className = 'pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-2xl border border-outline-variant/30 ' + (styles[type] || styles.info) + ' translate-x-8 opacity-0 transition-all duration-300';
            const icon = document.createElement('span');
            icon.className = 'material-symbols-outlined text-[22px] shrink-0 ' + (iconColors[type] || iconColors.info);
            icon.textContent = icons[type] || icons.info;
            const text = document.createElement('p');
            text.className = 'text-sm font-medium flex-1 pt-0.5';
            text.textContent = message;
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'shrink-0 text-outline hover:text-text-main transition-colors';
            closeBtn.setAttribute('aria-label', 'Tutup');
            closeBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
            el.append(icon, text, closeBtn);
            const close = () => {
                el.classList.add('translate-x-8', 'opacity-0');
                setTimeout(() => el.remove(), 300);
            };
            closeBtn.addEventListener('click', close);
            root.appendChild(el);
            requestAnimationFrame(() => el.classList.remove('translate-x-8', 'opacity-0'));
            setTimeout(close, duration);
        }

        let confirmResolve = null;
        const modal = document.getElementById('confirm-modal');
        const dialog = document.getElementById('confirm-dialog');

        function showConfirm(message, title) {
            title = title || 'Konfirmasi';
            return new Promise((resolve) => {
                if (!modal || !dialog) { resolve(window.confirm(message)); return; }
                document.getElementById('confirm-title').textContent = title;
                document.getElementById('confirm-message').textContent = message;
                confirmResolve = resolve;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                requestAnimationFrame(() => dialog.classList.remove('scale-95', 'opacity-0'));
            });
        }

        function closeConfirm(result) {
            if (!modal || !dialog) return;
            dialog.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                if (confirmResolve) { confirmResolve(result); confirmResolve = null; }
            }, 180);
        }

        document.getElementById('confirm-ok')?.addEventListener('click', () => closeConfirm(true));
        modal?.querySelectorAll('[data-confirm-dismiss]').forEach((el) => el.addEventListener('click', () => closeConfirm(false)));

        document.addEventListener('submit', (e) => {
            const form = e.target.closest('form[data-confirm]');
            if (!form || form.dataset.confirmed === '1') return;
            e.preventDefault();
            const msg = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'Konfirmasi';
            showConfirm(msg, title).then((ok) => {
                if (ok) { form.dataset.confirmed = '1'; form.requestSubmit(); }
            });
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-confirm]');
            if (!btn || btn.tagName === 'FORM') return;
            const form = btn.closest('form');
            if (form) return;
            e.preventDefault();
            const msg = btn.getAttribute('data-confirm');
            const href = btn.getAttribute('href');
            const title = btn.getAttribute('data-confirm-title') || 'Konfirmasi';
            showConfirm(msg, title).then((ok) => { if (ok && href) location.href = href; });
        });

        const initial = <?= $json ?: '[]' ?>;
        initial.forEach((t) => showToast(t.type, t.message));

        function initPasswordToggles(root) {
            (root || document).querySelectorAll('[data-password-toggle]').forEach((btn) => {
                if (btn.dataset.bound === '1') return;
                btn.dataset.bound = '1';
                const input = btn.closest('.password-toggle-wrap')?.querySelector('input');
                const iconShow = btn.querySelector('.icon-show');
                const iconHide = btn.querySelector('.icon-hide');
                if (!input) return;
                btn.addEventListener('click', () => {
                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    iconShow?.classList.toggle('hidden', !visible);
                    iconHide?.classList.toggle('hidden', visible);
                    btn.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
                });
            });
        }

        initPasswordToggles();

        return { showToast, showConfirm, initPasswordToggles };
    })();
    </script>
    <?php
}

function renderProductCard(array $p, string $redirect = 'home.php'): void
{
    $cartAction = $redirect === 'index.php' ? 'index.php' : 'home.php';
    ?>
    <div class="product-card bg-white rounded-2xl overflow-hidden tonal-shadow group border border-transparent hover:border-primary/20 transition-all duration-300">
        <a href="product.php?id=<?= $p['id'] ?>" class="block">
            <div class="aspect-square relative overflow-hidden bg-surface-container-low">
                <img src="<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                <?php if ($p['stok'] < 10): ?>
                    <span class="absolute top-3 right-3 px-2 py-1 bg-sun-tint text-on-tertiary-fixed text-[10px] font-bold rounded-lg uppercase">Stok Terbatas</span>
                <?php endif; ?>
            </div>
        </a>
        <div class="p-4">
            <p class="text-label-sm text-success-green mb-1">Petani: <?= e($p['petani_nama']) ?></p>
            <a href="product.php?id=<?= $p['id'] ?>" class="font-semibold text-text-main line-clamp-1 hover:text-primary block mb-3"><?= e($p['nama']) ?></a>
            <div class="flex items-center justify-between">
                <span class="font-bold text-primary text-sm">
                    <?= formatRupiah($p['harga']) ?>
                    <span class="text-[10px] font-normal text-outline">/ <?= e($p['satuan']) ?></span>
                </span>
                <form action="actions/cart.php" method="POST">
                    <input type="hidden" name="action" value="add"/>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                    <input type="hidden" name="qty" value="1"/>
                    <input type="hidden" name="from" value="<?= e($cartAction) ?>"/>
                    <button type="submit" class="add-btn w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-container active:scale-90 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function renderEmptyState(string $icon, string $title, string $desc, string $btnLabel, string $btnUrl): void
{
    ?>
    <div class="text-center py-16 px-6">
        <div class="w-20 h-20 mx-auto mb-6 bg-leaf-green-light rounded-2xl flex items-center justify-center">
            <span class="material-symbols-outlined text-4xl text-primary"><?= e($icon) ?></span>
        </div>
        <h3 class="text-headline-md text-text-main mb-2"><?= e($title) ?></h3>
        <p class="text-body-md text-text-muted mb-8 max-w-sm mx-auto"><?= e($desc) ?></p>
        <a href="<?= e($btnUrl) ?>" class="btn-primary inline-flex items-center gap-2"><?= e($btnLabel) ?></a>
    </div>
    <?php
}

function renderPageHero(string $title, ?string $subtitle = null, ?string $backUrl = null): void
{
    ?>
    <div class="bg-white border-b border-outline-variant/40 mb-6">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-5 flex items-center gap-4">
            <?php if ($backUrl): ?>
                <a href="<?= e($backUrl) ?>" class="icon-btn" aria-label="Kembali">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <h1 class="text-headline-lg-mobile md:text-headline-md text-text-main font-bold truncate"><?= e($title) ?></h1>
                <?php if ($subtitle): ?>
                    <p class="text-sm text-text-muted mt-0.5"><?= e($subtitle) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
