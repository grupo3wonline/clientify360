{{-- Applies the saved theme before the first paint (no flash). Shares Filament's key; migrates the legacy "darkMode" key once. --}}
<script>
    (function () {
        try {
            var k = 'filament:theme:mode', m = localStorage.getItem(k);
            if (m === null && localStorage.getItem('darkMode') !== null) {
                m = localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light';
                localStorage.setItem(k, m); localStorage.removeItem('darkMode');
            }
            m = m || 'system';
            var dark = m === 'dark' || (m === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        } catch (e) {}
    })();
</script>
