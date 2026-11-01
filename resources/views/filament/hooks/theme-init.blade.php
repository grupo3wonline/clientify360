{{-- Migrates the legacy "darkMode" key once; Filament's own script then applies "filament:theme:mode". --}}
<script>
    (function () {
        try {
            var k = 'filament:theme:mode';
            if (localStorage.getItem(k) === null && localStorage.getItem('darkMode') !== null) {
                localStorage.setItem(k, localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light');
                localStorage.removeItem('darkMode');
            }
        } catch (e) {}
    })();
</script>
