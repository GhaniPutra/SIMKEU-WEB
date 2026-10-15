/* Status awal layout
 * Membaca preferensi sidebar sebelum DOM ditampilkan agar tidak terjadi kedipan.
 */
(function() {
            try {
                if (window.innerWidth >= 992 && localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                }
            } catch (e) {}
        })();
