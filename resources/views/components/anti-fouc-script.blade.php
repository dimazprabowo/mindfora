{{-- Anti-FOUC: apply stored dark mode & font scale before first paint (kept in sync later by alpine-stores.js) --}}
<script>
    (function () {
        var root = document.documentElement;
        var d = localStorage.getItem('darkMode');
        if (d === 'true' || (d === null && matchMedia('(prefers-color-scheme: dark)').matches)) {
            root.classList.add('dark');
        }
        var f = parseInt(localStorage.getItem('fontScale'), 10);
        if (f > 100) {
            root.style.fontSize = f + '%';
        }
    })();
</script>
