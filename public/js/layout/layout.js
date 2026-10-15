/* Interaksi layout SIMKEU
 * Mengelola sidebar, filter periode, dan submenu navigasi.
 */
// ============================================================
        // LOGIKA BUKA-TUTUP SIDEBAR
        // Mengelola buka/tutup sidebar baik di desktop maupun mobile
        // ============================================================

        // Ambil referensi elemen utama layout.
        const sidebar = document.getElementById('sidebar');          // Panel sidebar kiri
        const topbar  = document.getElementById('topbar');           // Bar navigasi atas
        const main    = document.getElementById('main-content');     // Area konten utama
        const overlay = document.getElementById('sidebar-overlay'); // Overlay gelap untuk mobile
        const toggle  = document.getElementById('sidebarToggle');   // Tombol toggle sidebar

        /**
         * Periksa apakah layar berada pada mode seluler (lebar < 992px).
         * @returns {boolean} true untuk seluler, false untuk desktop.
         */
        const isMobile = () => window.innerWidth < 992;

        /**
         * Buka sidebar di mode mobile:
         * - Tambahkan kelas 'mobile-open' agar sidebar masuk dari kiri.
         * - Tampilkan lapisan gelap di belakang sidebar.
         */
        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');  // Geser sidebar ke dalam layar
            overlay.classList.add('active');        // Tampilkan overlay latar belakang
        }

        /**
         * Tutup sidebar di mode mobile:
         * - Hapus kelas 'mobile-open' agar sidebar kembali tersembunyi.
         * - Sembunyikan lapisan latar.
         */
        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open'); // Kembalikan sidebar ke luar layar
            overlay.classList.remove('active');       // Sembunyikan overlay
        }

        /**
         * Toggle sidebar di mode desktop:
         * - Ubah sidebar terbuka menjadi ringkas (72px), atau sebaliknya (260px).
         * - Simpan status ke localStorage agar tetap sama setelah halaman dimuat ulang.
         */
        function toggleDesktopSidebar() {
            const collapsed = sidebar.classList.toggle('collapsed'); // Toggle class dan simpan status
            topbar.classList.toggle('sidebar-collapsed', collapsed); // Sesuaikan posisi topbar
            main.classList.toggle('sidebar-collapsed', collapsed);   // Sesuaikan margin konten
            document.documentElement.classList.toggle('sidebar-is-collapsed', collapsed);
            localStorage.setItem('sidebarCollapsed', collapsed ? 'true' : 'false');     // Simpan status ke localStorage
        }

        // Hapus class preload-transitions setelah frame pertama selesai di-paint agar animasi layout stabil
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.body.classList.remove('preload-transitions');
            });
        });

        // Tangani klik tombol hamburger atau toggle.
        toggle?.addEventListener('click', () => {
            if (isMobile()) {
                // Pada seluler, buka atau tutup sidebar dan lapisannya.
                sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                // Pada desktop, ubah sidebar ke mode ringkas atau penuh.
                toggleDesktopSidebar();
            }
        });

        // Klik lapisan pada seluler akan menutup sidebar.
        overlay?.addEventListener('click', closeMobileSidebar);

        // Pulihkan status sidebar dari localStorage saat halaman dimuat di desktop.
        if (sidebar && topbar && main && !isMobile() && (document.documentElement.classList.contains('sidebar-is-collapsed') || localStorage.getItem('sidebarCollapsed') === 'true')) {
            sidebar.classList.add('collapsed');          // Sidebar dalam kondisi collapsed
            topbar.classList.add('sidebar-collapsed');   // Topbar menyesuaikan
            main.classList.add('sidebar-collapsed');     // Konten menyesuaikan
            document.documentElement.classList.add('sidebar-is-collapsed');
        }

        // Tutup sidebar seluler saat ukuran layar berubah menjadi desktop.
        window.addEventListener('resize', () => {
            if (sidebar && overlay && !isMobile()) closeMobileSidebar();
        });

        // ============================================================
        // LOGIKA FILTER PERIODE
        // Mengelola filter periode global pada bilah atas.
        //
        // Cara kerja:
        //   1. Periode URL menjadi state aktif; localStorage & cookie dipakai saat
        //      halaman yang relevan dibuka agar server dan klien selalu sinkron.
        //   2. Gunakan history.replaceState agar halaman tidak dimuat ulang dua kali.
        //   3. Tautan navigasi disinkronkan otomatis dengan periode aktif.
        // ============================================================

        (function initPeriodFilter() {
            // Ambil elemen pilihan periode.
            const periodSelect = document.getElementById('global-period');
            if (!periodSelect) return; // Berhenti jika elemen tidak tersedia.

            // -------------------------------------------------------
            // Tentukan periode aktif: URL, lalu localStorage, kemudian nilai bawaan.
            // -------------------------------------------------------
            const urlParams    = new URLSearchParams(window.location.search); // Baca parameter URL.
            const supportedPeriods = ['all', 'today', 'week', 'month'];
            const periodFromUrl = urlParams.get('period');
            const savedPeriod = localStorage.getItem('dashboardPeriod') || 'month';
            let activePeriod = supportedPeriods.includes(periodFromUrl)
                ? periodFromUrl
                : supportedPeriods.includes(savedPeriod) ? savedPeriod : 'month';

            // Setel pilihan awal sesuai periode aktif.
            periodSelect.value = activePeriod;

            // Simpan periode di localStorage dan cookie agar konsisten antarhalaman.
            localStorage.setItem('dashboardPeriod', activePeriod);
            document.cookie = `dashboardPeriod=${activePeriod}; path=/; max-age=31536000; SameSite=Lax`;

            function supportsPeriod(path) {
                return path === '/' || path === '/dashboard'
                    || path === '/pemasukan' || path.startsWith('/pemasukan/')
                    || path === '/pengeluaran' || path.startsWith('/pengeluaran/')
                    || /^\/employees\/[^/]+\/(manage|sales-dashboard)$/.test(path);
            }

            // Tambahkan parameter periode ke URL tanpa memuat ulang halaman.
            if (supportsPeriod(window.location.pathname) && !supportedPeriods.includes(periodFromUrl)) {
                urlParams.set('period', activePeriod);
                window.history.replaceState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
            }

            function syncPeriodLinks() {
                document.querySelectorAll('a[href]').forEach((link) => {
                    if (link.target && link.target.toLowerCase() !== '_self') return;
                    if (link.hasAttribute('download')) return;
                    try {
                        const destination = new URL(link.href, window.location.href);
                        if (destination.origin === window.location.origin && supportsPeriod(destination.pathname)) {
                            destination.searchParams.set('period', activePeriod);
                            link.href = destination.toString();
                        }
                    } catch (e) {}
                });
            }

            syncPeriodLinks();

            document.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.button !== 0
                    || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!link || link.target && link.target.toLowerCase() !== '_self'
                    || link.hasAttribute('download')) return;

                const destination = new URL(link.href, window.location.href);
                if (destination.origin === window.location.origin && supportsPeriod(destination.pathname)) {
                    destination.searchParams.set('period', activePeriod);
                    link.href = destination.toString();
                }
            }, true);

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'get') return;

                const destination = new URL(form.action, window.location.href);
                if (!supportsPeriod(destination.pathname)) return;

                let periodInput = form.querySelector('input[name="period"]');
                if (!periodInput) {
                    periodInput = document.createElement('input');
                    periodInput.type = 'hidden';
                    periodInput.name = 'period';
                    form.appendChild(periodInput);
                }
                periodInput.value = activePeriod;
            }, true);

            // -------------------------------------------------------
            // Perbarui penyimpanan dan data saat periode pilihan berubah.
            // -------------------------------------------------------
            periodSelect.addEventListener('change', function () {
                const selectedPeriod = this.value; // Periode yang dipilih.
                activePeriod = selectedPeriod;

                // Simpan pilihan ke localStorage dan cookie.
                localStorage.setItem('dashboardPeriod', selectedPeriod);
                document.cookie = `dashboardPeriod=${selectedPeriod}; path=/; max-age=31536000; SameSite=Lax`;
                syncPeriodLinks();

                // Muat ulang data jika halaman mendukung filter periode.
                if (supportsPeriod(window.location.pathname)) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('period', selectedPeriod);
                    ['page', 'sales_page', 'products_page'].forEach((page) => url.searchParams.delete(page));
                    window.location.href = url.toString();
                }
            });
        })();

/* Animasi submenu navigasi samping. */
document.querySelectorAll('.sidebar-nav details > summary').forEach((summary) => {
            const details = summary.parentElement;
            const submenu = details.querySelector(':scope > .nav-submenu');
            if (!submenu) return;

            summary.addEventListener('click', (event) => {
                event.preventDefault();

                const isClosing = details.dataset.closing === 'true';
                const shouldOpen = isClosing || !details.open;
                const currentHeight = submenu.getBoundingClientRect().height;

                submenu.style.height = `${currentHeight}px`;
                submenu.style.opacity = getComputedStyle(submenu).opacity;
                submenu.style.transform = getComputedStyle(submenu).transform;
                submenu.offsetHeight;

                if (shouldOpen) {
                    details.open = true;
                    delete details.dataset.closing;
                    requestAnimationFrame(() => {
                        submenu.style.height = `${submenu.scrollHeight}px`;
                        submenu.style.opacity = '1';
                        submenu.style.transform = 'translateY(0)';
                    });
                    return;
                }

                details.dataset.closing = 'true';
                requestAnimationFrame(() => {
                    submenu.style.height = '0px';
                    submenu.style.opacity = '0';
                    submenu.style.transform = 'translateY(-6px)';
                });
            });

            submenu.addEventListener('transitionend', (event) => {
                if (event.propertyName !== 'height') return;

                if (details.dataset.closing === 'true') {
                    details.open = false;
                    delete details.dataset.closing;
                }
                if (details.open) {
                    submenu.style.removeProperty('height');
                    submenu.style.removeProperty('opacity');
                    submenu.style.removeProperty('transform');
                }
            });
        });
