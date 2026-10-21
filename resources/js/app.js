import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

window.Swal = Swal;
window.Alpine = Alpine;

Alpine.plugin(collapse);

Alpine.start();

// Document-level anti-double-submit guard (owns button-disable; the inline
// loadingOverlay() keeps owning the spinner/overlay). Bubble phase so an Alpine
// @submit.prevent runs first and sets defaultPrevented, letting AJAX/validated
// forms opt out. A failsafe re-enables the button if no navigation happens, and
// bfcache restores re-enable any button left disabled.
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.method.toLowerCase() === 'get') return; // search forms stay re-submittable
    if (form.classList.contains('hidden') || form.hasAttribute('data-no-loading')) return;
    if (e.defaultPrevented) return; // Alpine @submit.prevent / AJAX forms opt out

    const btn = form.querySelector('button[type="submit"], [type="submit"]');
    if (!btn) return;

    btn.disabled = true;
    btn.classList.add('opacity-60', 'pointer-events-none');

    setTimeout(() => { // failsafe: re-enable if no navigation happened
        btn.disabled = false;
        btn.classList.remove('opacity-60', 'pointer-events-none');
    }, 8000);
}, false);

window.addEventListener('pageshow', () => { // bfcache restore re-enables buttons
    document.querySelectorAll('button[type="submit"][disabled], [type="submit"][disabled]').forEach((b) => {
        b.disabled = false;
        b.classList.remove('opacity-60', 'pointer-events-none');
    });
});
