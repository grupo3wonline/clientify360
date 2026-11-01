// Full-screen loading overlay (markup: <x-loading-overlay>).
// Never gets stuck: hidden on pageshow/pagehide (bfcache, back button), after a 15 s
// failsafe, and skipped for downloads, new tabs, hash links, mailto/tel and data-no-loading.
const FAILSAFE_MS = 15000;
const LIVEWIRE_DELAY_MS = 700;

let failsafe = null;

function overlay() {
    return document.getElementById('loading-overlay');
}

function show(text = 'Processando...') {
    const el = overlay();
    if (!el) {
        return;
    }
    const message = el.querySelector('[data-loading-message]');
    if (message) {
        message.textContent = text || 'Processando...';
    }
    el.hidden = false;
    el.setAttribute('aria-busy', 'true');
    clearTimeout(failsafe);
    failsafe = setTimeout(hide, FAILSAFE_MS);
}

function hide() {
    clearTimeout(failsafe);
    failsafe = null;
    const el = overlay();
    if (el) {
        el.hidden = true;
        el.removeAttribute('aria-busy');
    }
}

function opensElsewhere(target) {
    return Boolean(target) && target !== '_self';
}

function onSubmit(event) {
    const form = event.target;
    const submitter = event.submitter || null;
    // Run after every other handler, so a preventDefault() (client validation, confirm dialogs) is seen.
    setTimeout(() => {
        if (event.defaultPrevented || !(form instanceof HTMLFormElement)) {
            return;
        }
        if (form.hasAttribute('data-no-loading') || opensElsewhere(form.getAttribute('target'))) {
            return;
        }
        if (submitter && (submitter.hasAttribute('formtarget') || submitter.hasAttribute('data-no-loading'))) {
            return;
        }
        show(form.dataset.loadingText || submitter?.dataset.loadingText);
    }, 0);
}

function onClick(event) {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link || link.hasAttribute('download') || link.hasAttribute('data-no-loading') || opensElsewhere(link.getAttribute('target'))) {
        return;
    }
    const href = link.getAttribute('href') || '';
    if (href === '' || /^(#|mailto:|tel:|javascript:)/i.test(href)) {
        return;
    }
    let url;
    try {
        url = new URL(link.href, window.location.href);
    } catch (e) {
        return;
    }
    if (url.origin !== window.location.origin) {
        return;
    }
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
        return;
    }
    show(link.dataset.loadingText || 'Carregando...');
}

// Background requests (wire:poll) must never cover the page.
function isPollOnly(request) {
    const actions = Array.from(request?.messages || []).flatMap((message) => Array.from(message.actions || []));
    return actions.length > 0 && actions.every((action) => action.metadata?.type === 'poll');
}

let pendingLivewire = 0;

function hookLivewire() {
    if (!window.Livewire || typeof window.Livewire.interceptRequest !== 'function') {
        return;
    }
    window.Livewire.interceptRequest(({ request, onCancel, onFinish }) => {
        if (isPollOnly(request)) {
            return;
        }
        let finished = false;
        pendingLivewire += 1;
        const timer = setTimeout(() => {
            if (!finished) {
                show('Processando...');
            }
        }, LIVEWIRE_DELAY_MS);
        const done = () => {
            if (finished) {
                return;
            }
            finished = true;
            clearTimeout(timer);
            pendingLivewire = Math.max(0, pendingLivewire - 1);
            if (pendingLivewire === 0) {
                hide();
            }
        };
        // onFinish covers success, error and failure. An aborted request returns early
        // without onFinish, so the abort signal (and onCancel) also release it.
        onFinish(done);
        onCancel(done);
        request?.controller?.signal?.addEventListener('abort', done, { once: true });
    });
}

export function initLoadingOverlay() {
    if (window.loading) {
        return;
    }
    window.loading = { show, hide };
    document.addEventListener('submit', onSubmit);
    document.addEventListener('click', onClick);
    window.addEventListener('pageshow', hide);
    window.addEventListener('pagehide', hide);
    if (window.Livewire) {
        hookLivewire();
    } else {
        document.addEventListener('livewire:init', hookLivewire);
    }
}
