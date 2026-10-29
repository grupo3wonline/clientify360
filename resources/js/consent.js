// Cookie consent (LGPD, GDPR, CCPA/CPRA, POPIA) and Google tag gating.
//
// - The banner (x-cookie-consent) is shown while there is no c360_consent cookie
//   for the current policy version (window.__consentConfig.version).
// - Google Tag Manager / GA4 are injected ONLY after analytics or marketing consent.
//   x-analytics has already set the Consent Mode v2 default to "denied".
// - Revoking a granted category deletes the _ga* cookies and reloads once,
//   because a loaded GTM cannot be removed from the page.
// - Footer links: [data-cookie-preferences] reopens the panel, [data-do-not-sell]
//   records the CCPA opt-out (marketing off).
// Pages without window.__consentConfig (error pages) are left untouched.

const COOKIE = 'c360_consent';

let config = null;
let current = null;
let tagsLoaded = false;
let reopened = false;

function readConsent() {
    const match = document.cookie.split('; ').find((part) => part.startsWith(COOKIE + '='));
    if (!match) {
        return null;
    }
    try {
        const value = JSON.parse(decodeURIComponent(match.slice(COOKIE.length + 1)));
        return value && value.v === config.version ? value : null;
    } catch (e) {
        return null;
    }
}

function writeConsentFallback(choice) {
    // Only used when the server could not be reached: keeps the choice on this device.
    const value = { v: config.version, id: choice.consent_id || null, necessary: true, analytics: choice.analytics, marketing: choice.marketing, ts: Math.floor(Date.now() / 1000) };
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${COOKIE}=${encodeURIComponent(JSON.stringify(value))}; Max-Age=${180 * 86400}; Path=/; SameSite=Lax${secure}`;
    return value;
}

function gtag() {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(arguments);
}

function injectScript(src) {
    const script = document.createElement('script');
    script.async = true;
    script.src = src;
    document.head.appendChild(script);
}

function loadTags() {
    if (tagsLoaded) {
        return;
    }
    if (config.gtmId) {
        window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
        injectScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(config.gtmId));
        tagsLoaded = true;
    } else if (config.ga4Id) {
        injectScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(config.ga4Id));
        gtag('js', new Date());
        gtag('config', config.ga4Id);
        tagsLoaded = true;
    }
}

function deleteAnalyticsCookies() {
    const host = location.hostname;
    const domains = ['', host, '.' + host, '.' + host.split('.').slice(-2).join('.')];
    document.cookie.split('; ').forEach((part) => {
        const name = part.split('=')[0];
        if (!/^_ga/.test(name) && name !== '_gid' && name !== '_gcl_au') {
            return;
        }
        domains.forEach((domain) => {
            document.cookie = `${name}=; Max-Age=0; Path=/` + (domain ? `; Domain=${domain}` : '');
        });
    });
}

function grantedState(consent) {
    const marketing = consent.marketing ? 'granted' : 'denied';
    return {
        analytics_storage: consent.analytics ? 'granted' : 'denied',
        ad_storage: marketing,
        ad_user_data: marketing,
        ad_personalization: marketing,
    };
}

export function applyConsent(consent, previous = null) {
    const revoked = previous && ((previous.analytics && !consent.analytics) || (previous.marketing && !consent.marketing));

    if (consent.analytics || consent.marketing) {
        gtag('consent', 'update', grantedState(consent));
        loadTags();
    } else if (previous) {
        gtag('consent', 'update', grantedState(consent));
    }

    if (revoked) {
        deleteAnalyticsCookies();
        if (tagsLoaded) {
            window.location.reload();
        }
    }
}

async function saveConsent(choice) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const xsrf = document.cookie.split('; ').find((part) => part.startsWith('XSRF-TOKEN='));
    const headers = { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    if (token) {
        headers['X-CSRF-TOKEN'] = token;
    } else if (xsrf) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf.slice('XSRF-TOKEN='.length));
    }

    try {
        const response = await fetch(config.endpoint, { method: 'POST', headers, credentials: 'same-origin', body: JSON.stringify(choice) });
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return readConsent() || writeConsentFallback(choice);
    } catch (e) {
        return writeConsentFallback(choice);
    }
}

function gpcEnabled() {
    return navigator.globalPrivacyControl === true;
}

async function decide(analytics, marketing) {
    const previous = current;
    let source = reopened ? 'preferences' : 'banner';
    if (gpcEnabled() && !marketing) {
        source = 'gpc';
    }
    hideBanner();
    current = await saveConsent({ analytics, marketing, source, consent_id: previous?.id || null });
    applyConsent(current, previous);
}

// ---- Banner -----------------------------------------------------------------

function banner() {
    return document.querySelector('[data-consent-banner]');
}

function setPanelOpen(open) {
    const root = banner();
    root.querySelector('[data-consent-panel]').hidden = !open;
    root.querySelector('[data-consent-action="save"]').hidden = !open;
    root.querySelector('[data-consent-action="customize"]').setAttribute('aria-expanded', String(open));
}

function showBanner(openPanel = false) {
    const root = banner();
    if (!root) {
        return;
    }
    const state = current || { analytics: false, marketing: false };
    root.querySelector('[data-consent-category="analytics"]').checked = !!state.analytics;
    // GPC: marketing starts off before any choice.
    root.querySelector('[data-consent-category="marketing"]').checked = current ? !!current.marketing : false;
    setPanelOpen(openPanel);
    root.hidden = false;
    root.querySelector('#c360-consent-title')?.focus({ preventScroll: true });
}

function hideBanner() {
    const root = banner();
    if (root) {
        root.hidden = true;
    }
}

function bindBanner() {
    const root = banner();
    if (!root) {
        return;
    }
    root.addEventListener('click', (event) => {
        const action = event.target.closest('[data-consent-action]')?.dataset.consentAction;
        if (action === 'accept') {
            decide(true, true);
        } else if (action === 'reject') {
            decide(false, false);
        } else if (action === 'customize') {
            setPanelOpen(root.querySelector('[data-consent-panel]').hidden);
        } else if (action === 'save') {
            decide(
                root.querySelector('[data-consent-category="analytics"]').checked,
                root.querySelector('[data-consent-category="marketing"]').checked,
            );
        }
    });
}

// ---- Footer links and CTA events ---------------------------------------------

async function doNotSell(link) {
    const previous = current;
    current = await saveConsent({ analytics: !!previous?.analytics, marketing: false, source: 'do_not_sell', consent_id: previous?.id || null });
    hideBanner();
    const target = link.href || config.privacyUrl + '#nao-vender';
    const message = 'Pronto! Não compartilharemos seus dados para publicidade.';
    const Swal = window.Swal || (await import('sweetalert2').then((module) => module.default).catch(() => null));
    if (Swal) {
        await Swal.fire({ icon: 'success', title: message, confirmButtonText: 'OK' });
    } else {
        window.alert(message);
    }
    applyConsent(current, previous);
    window.location.assign(target);
}

function bindDocument() {
    // Capture phase: preventDefault() must run before the loading overlay's bubble
    // listener, otherwise it shows "Carregando..." over the banner (no navigation follows).
    document.addEventListener('click', (event) => {
        const preferences = event.target.closest('[data-cookie-preferences]');
        if (preferences) {
            event.preventDefault();
            window.dispatchEvent(new CustomEvent('open-cookie-preferences'));
            return;
        }
        const optOut = event.target.closest('[data-do-not-sell]');
        if (optOut) {
            event.preventDefault();
            doNotSell(optOut);
            return;
        }
        const cta = event.target.closest('[data-cta]');
        if (cta && event.target.closest('a') && current && (current.analytics || current.marketing)) {
            window.dataLayer.push({ event: 'cta_click', placement: cta.dataset.cta });
        }
    }, { capture: true });

    window.addEventListener('open-cookie-preferences', () => {
        reopened = true;
        showBanner(true);
    });
}

export function initConsent() {
    if (window.__c360ConsentStarted || !window.__consentConfig) {
        return;
    }
    window.__c360ConsentStarted = true;
    config = window.__consentConfig;
    window.dataLayer = window.dataLayer || [];

    const start = () => {
        current = readConsent();
        bindBanner();
        bindDocument();
        if (current) {
            applyConsent(current);
        } else {
            showBanner(false);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
}
