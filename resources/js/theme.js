// Light/dark theme shared with the Filament panel (same localStorage key and values).
// The inline script in layouts/partials/theme-init applies the class before first paint;
// this store only handles the toggle and system changes afterwards.
const KEY = 'filament:theme:mode';
const media = window.matchMedia('(prefers-color-scheme: dark)');

function read() {
    try {
        return localStorage.getItem(KEY) || 'system';
    } catch (e) {
        return 'system';
    }
}

function apply(mode) {
    const dark = mode === 'dark' || (mode === 'system' && media.matches);
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    return dark;
}

export default function registerTheme(Alpine) {
    Alpine.store('theme', {
        mode: read(),
        isDark: document.documentElement.classList.contains('dark'),
        init() {
            media.addEventListener('change', () => {
                if (this.mode === 'system') {
                    this.isDark = apply('system');
                }
            });
        },
        toggle() {
            this.mode = this.isDark ? 'light' : 'dark';
            try {
                localStorage.setItem(KEY, this.mode);
            } catch (e) {
                // Private mode: the choice lasts only for this page.
            }
            this.isDark = apply(this.mode);
        },
    });
}
