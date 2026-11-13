/**
 * Debounce / duplicate-submission protection utilities.
 *
 * Usage:
 *   import { debounce, initSearchDebounce, initFormGuard } from './debounce';
 *
 * initSearchDebounce() — attaches debounced auto-submit to every
 *   <input data-debounce-search> (default delay: 450 ms).
 *
 * initFormGuard() — prevents the same form from being submitted more than
 *   once (double-click / Enter spam). Also blocks re-submission on page
 *   reload (popstate / pagehide), which is important for POST forms that
 *   should not trigger twice on the browser's "resubmit?" dialog.
 */

/** @param {Function} fn @param {number} wait @returns {Function} */
export function debounce(fn, wait = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), wait);
    };
}

// ── Search debounce ──────────────────────────────────────────────────────────

/**
 * Attach debounced auto-submit to inputs that carry [data-debounce-search].
 * The form is only submitted if the value actually changed, so navigating
 * back does not re-fire the request.
 *
 * @param {number} [delay=450]
 */
export function initSearchDebounce(delay = 450) {
    document.querySelectorAll('[data-debounce-search]').forEach((input) => {
        const form = input.closest('form');
        if (!form) return;

        // Track the value present when the page loaded (URL param or empty).
        let lastSubmitted = input.value;

        const submit = debounce(() => {
            if (input.value === lastSubmitted) return; // unchanged — skip
            lastSubmitted = input.value;
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }, delay);

        input.addEventListener('input', submit);

        // Pressing Escape clears the field and submits immediately (clear search).
        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape' || input.value === '') return;
            clearTimeout(undefined); // cancel any pending debounce
            input.value = '';
            lastSubmitted = '';
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });
    });
}

// ── Form-submission guard ────────────────────────────────────────────────────

/**
 * Prevent duplicate form submissions (double-click, Enter spam).
 *
 * Skips forms that:
 *   - have [data-no-guard]  — opt-out
 *   - are search / GET forms (search is handled separately above)
 *   - open in a new tab / window
 *
 * On pagehide (back / bfcache) all guards are released so the form is
 * usable again when the user navigates back.
 */
export function initFormGuard() {
    const submitting = new WeakSet();

    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.hasAttribute('data-no-guard')) return;
            if ((form.method || '').toLowerCase() === 'get') return;
            if (form.getAttribute('target') && form.getAttribute('target') !== '_self') return;

            if (submitting.has(form)) {
                event.preventDefault();
                return;
            }

            submitting.add(form);

            // Release the guard on completion (whether page navigates away or
            // an error keeps the user on the same page).
            const release = () => submitting.delete(form);
            window.addEventListener('pageshow', release, { once: true });
            window.addEventListener('pagehide', release, { once: true });
            // Fallback: release after 15 s (same as the loading-overlay failsafe).
            setTimeout(release, 15_000);
        },
        { capture: false },
    );
}
