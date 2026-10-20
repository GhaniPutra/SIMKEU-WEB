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
        //   1. Prioritas state mengikuti server: URL, cookie, lalu nilai bawaan.
        //   2. Gunakan history.replaceState agar halaman tidak dimuat ulang dua kali.
        //   3. Tautan navigasi disinkronkan otomatis dengan periode aktif.
        // ============================================================

        (function initPeriodFilter() {
            const filter = document.getElementById('global-period-filter');
            if (!filter) return;

            const trigger = document.getElementById('global-period-trigger');
            const triggerLabel = document.getElementById('global-period-label');
            const menu = document.getElementById('global-period-menu');
            const presetButtons = [...menu.querySelectorAll('[data-period-preset]')];
            const customToggle = document.getElementById('global-period-custom-toggle');
            const calendarPanel = document.getElementById('global-period-calendar-panel');
            const calendarContainer = document.getElementById('global-period-calendars');
            const selectionLabel = document.getElementById('global-period-selection');
            const applyButton = document.getElementById('global-period-apply');
            const periodLabels = {
                all: 'Semua Periode', today: 'Hari Ini', week: 'Minggu Ini', month: 'Bulan Ini'
            };
            const supportedPeriods = [...Object.keys(periodLabels), 'custom'];
            const weekdayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            const weekdayShortNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

            const urlParams = new URLSearchParams(window.location.search);
            const periodFromUrl = urlParams.get('period');
            const readCookie = (name) => document.cookie
                .split('; ')
                .find((cookie) => cookie.startsWith(`${name}=`))
                ?.slice(name.length + 1);
            const cookiePeriod = readCookie('dashboardPeriod');
            let activePeriod = supportedPeriods.includes(periodFromUrl)
                ? periodFromUrl
                : supportedPeriods.includes(cookiePeriod) ? cookiePeriod : 'month';
            let activeStartDate = urlParams.get('start_date') || readCookie('dashboardStartDate') || '';
            let activeEndDate = urlParams.get('end_date') || readCookie('dashboardEndDate') || '';

            const isValidDateValue = (value) => /^\d{4}-\d{2}-\d{2}$/.test(value)
                && !Number.isNaN(new Date(`${value}T00:00:00Z`).getTime())
                && new Date(`${value}T00:00:00Z`).toISOString().slice(0, 10) === value;
            const hasValidRange = (start, end) => isValidDateValue(start)
                && isValidDateValue(end) && start <= end;
            const pad = (value) => String(value).padStart(2, '0');
            const toDateValue = (date) => `${date.getUTCFullYear()}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())}`;
            const dateFromValue = (value) => {
                const [year, month, day] = value.split('-').map(Number);
                return new Date(Date.UTC(year, month - 1, day));
            };
            const firstOfMonth = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), 1));
            const addMonths = (date, amount) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + amount, 1));
            const addDays = (date, amount) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + amount));
            const formatDate = (value, options = { day: 'numeric', month: 'short', year: 'numeric' }) => {
                if (!isValidDateValue(value)) return '';
                return new Intl.DateTimeFormat('id-ID', { ...options, timeZone: 'UTC' }).format(dateFromValue(value));
            };
            // Tanggal hari ini mengikuti zona waktu aplikasi, bukan zona waktu perangkat.
            const todayParts = new Intl.DateTimeFormat('en-CA', {
                timeZone: filter.dataset.timezone || 'Asia/Jakarta',
                year: 'numeric', month: '2-digit', day: '2-digit'
            }).formatToParts(new Date()).reduce((parts, part) => {
                if (part.type !== 'literal') parts[part.type] = part.value;
                return parts;
            }, {});
            const todayValue = `${todayParts.year}-${todayParts.month}-${todayParts.day}`;

            // Rentang dari URL yang tidak valid tidak diterapkan; preset aman menjadi pilihan aktif.
            if (activePeriod === 'custom' && !hasValidRange(activeStartDate, activeEndDate)) {
                activePeriod = 'month';
                activeStartDate = '';
                activeEndDate = '';
            }

            let draftStartDate = activePeriod === 'custom' ? activeStartDate : '';
            let draftEndDate = activePeriod === 'custom' ? activeEndDate : '';
            let calendarCursor = firstOfMonth(
                activePeriod === 'custom' ? dateFromValue(activeStartDate) : dateFromValue(todayValue)
            );
            let calendarExpanded = false;
            let compactCalendar = window.innerWidth <= 640;

            function supportsPeriod(path) {
                return path === '/' || path === '/dashboard' || path === '/activities'
                    || path === '/pemasukan' || path.startsWith('/pemasukan/')
                    || path === '/pengeluaran' || path.startsWith('/pengeluaran/')
                    || /^\/employees\/[^/]+\/(manage|sales-dashboard)$/.test(path);
            }

            function applyPeriodToUrl(destination) {
                destination.searchParams.set('period', activePeriod);
                if (activePeriod === 'custom' && hasValidRange(activeStartDate, activeEndDate)) {
                    destination.searchParams.set('start_date', activeStartDate);
                    destination.searchParams.set('end_date', activeEndDate);
                } else {
                    destination.searchParams.delete('start_date');
                    destination.searchParams.delete('end_date');
                }
            }

            function saveActivePeriod() {
                document.cookie = `dashboardPeriod=${activePeriod}; path=/; max-age=31536000; SameSite=Lax`;
                if (activePeriod === 'custom') {
                    document.cookie = `dashboardStartDate=${activeStartDate}; path=/; max-age=31536000; SameSite=Lax`;
                    document.cookie = `dashboardEndDate=${activeEndDate}; path=/; max-age=31536000; SameSite=Lax`;
                } else {
                    document.cookie = 'dashboardStartDate=; path=/; max-age=0; SameSite=Lax';
                    document.cookie = 'dashboardEndDate=; path=/; max-age=0; SameSite=Lax';
                }
            }

            function updateTrigger() {
                const isCustom = activePeriod === 'custom' && hasValidRange(activeStartDate, activeEndDate);
                const label = isCustom
                    ? `${formatDate(activeStartDate)} – ${formatDate(activeEndDate)}`
                    : periodLabels[activePeriod] || periodLabels.month;
                triggerLabel.textContent = label;
                trigger.setAttribute('aria-label', `Filter periode: ${label}`);
                trigger.title = `Filter periode: ${label}`;
                presetButtons.forEach((button) => {
                    button.setAttribute('aria-pressed', String(button.dataset.periodPreset === activePeriod));
                });
            }

            function updateCalendarSelection() {
                const isComplete = hasValidRange(draftStartDate, draftEndDate);
                applyButton.disabled = !isComplete;
                if (!draftStartDate) {
                    selectionLabel.textContent = 'Pilih tanggal mulai';
                } else if (!draftEndDate) {
                    selectionLabel.textContent = `Mulai: ${formatDate(draftStartDate)} · Pilih tanggal akhir`;
                } else {
                    selectionLabel.textContent = `${formatDate(draftStartDate)} – ${formatDate(draftEndDate)}`;
                }
            }

            function setCalendarExpanded(expanded, focusDate = '') {
                calendarExpanded = expanded;
                customToggle.setAttribute('aria-expanded', String(expanded));
                calendarPanel.hidden = !expanded;
                menu.classList.toggle('has-calendar', expanded);
                if (expanded) {
                    updateCalendarSelection();
                    renderCalendar(focusDate);
                }
            }

            function renderCalendar(focusDate = '') {
                const monthCount = compactCalendar ? 1 : 2;
                const renderedDates = [];
                calendarContainer.replaceChildren();

                for (let monthOffset = 0; monthOffset < monthCount; monthOffset += 1) {
                    const monthDate = addMonths(calendarCursor, monthOffset);
                    const year = monthDate.getUTCFullYear();
                    const month = monthDate.getUTCMonth();
                    const daysInMonth = new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
                    // Geser indeks hari agar posisi Minggu menjadi kolom terakhir setelah Senin.
                    const firstWeekday = (monthDate.getUTCDay() + 6) % 7;
                    const monthSection = document.createElement('div');
                    monthSection.className = 'period-calendar-month';

                    const monthTitle = document.createElement('p');
                    monthTitle.className = 'period-calendar-month-title';
                    monthTitle.textContent = new Intl.DateTimeFormat('id-ID', {
                        month: 'long', year: 'numeric', timeZone: 'UTC'
                    }).format(monthDate);
                    monthSection.appendChild(monthTitle);

                    const weekdays = document.createElement('div');
                    weekdays.className = 'period-calendar-weekdays';
                    weekdayShortNames.forEach((name, index) => {
                        const weekday = document.createElement('span');
                        weekday.className = 'period-calendar-weekday';
                        weekday.textContent = name;
                        weekday.title = weekdayNames[index];
                        weekday.setAttribute('aria-hidden', 'true');
                        weekdays.appendChild(weekday);
                    });
                    monthSection.appendChild(weekdays);

                    const days = document.createElement('div');
                    days.className = 'period-calendar-days';
                    for (let blank = 0; blank < firstWeekday; blank += 1) {
                        const empty = document.createElement('span');
                        empty.className = 'period-calendar-empty';
                        empty.setAttribute('aria-hidden', 'true');
                        days.appendChild(empty);
                    }

                    for (let day = 1; day <= daysInMonth; day += 1) {
                        const date = new Date(Date.UTC(year, month, day));
                        const value = toDateValue(date);
                        const dayButton = document.createElement('button');
                        const isEndpoint = value === draftStartDate || value === draftEndDate;
                        dayButton.className = 'period-calendar-day';
                        dayButton.type = 'button';
                        dayButton.dataset.date = value;
                        dayButton.textContent = String(day);
                        dayButton.setAttribute('aria-label', formatDate(value, {
                            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                        }));
                        dayButton.setAttribute('aria-pressed', String(isEndpoint));
                        dayButton.tabIndex = -1;
                        if (value === todayValue) dayButton.classList.add('is-today');
                        if (draftStartDate && draftEndDate && value > draftStartDate && value < draftEndDate) {
                            dayButton.classList.add('is-in-range');
                        }
                        if (value === draftStartDate) dayButton.classList.add('is-range-start');
                        if (value === draftEndDate) dayButton.classList.add('is-range-end');
                        days.appendChild(dayButton);
                        renderedDates.push(value);
                    }

                    monthSection.appendChild(days);
                    calendarContainer.appendChild(monthSection);
                }

                const preferredDate = focusDate || draftStartDate || todayValue;
                const focusTarget = renderedDates.includes(preferredDate)
                    ? preferredDate
                    : renderedDates[0];
                const focusButton = [...calendarContainer.querySelectorAll('[data-date]')]
                    .find((button) => button.dataset.date === focusTarget);
                if (focusButton) focusButton.tabIndex = 0;
            }

            function syncPeriodLinks() {
                document.querySelectorAll('a[href]').forEach((link) => {
                    if (link.target && link.target.toLowerCase() !== '_self') return;
                    if (link.hasAttribute('download')) return;
                    try {
                        const destination = new URL(link.href, window.location.href);
                        if (destination.origin === window.location.origin && supportsPeriod(destination.pathname)) {
                            applyPeriodToUrl(destination);
                            link.href = destination.toString();
                        }
                    } catch {
                        // Biarkan tautan dengan URL tidak valid tanpa perubahan.
                    }
                });
            }

            function navigateWithActivePeriod() {
                if (!supportsPeriod(window.location.pathname)) return;
                const destination = new URL(window.location.href);
                applyPeriodToUrl(destination);
                ['page', 'sales_page', 'products_page'].forEach((page) => destination.searchParams.delete(page));
                window.location.href = destination.toString();
            }

            function closeMenu(returnFocus = false) {
                menu.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                if (returnFocus) trigger.focus();
            }

            function openMenu() {
                menu.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                if (activePeriod === 'custom') {
                    draftStartDate = activeStartDate;
                    draftEndDate = activeEndDate;
                    calendarCursor = firstOfMonth(dateFromValue(activeStartDate));
                } else {
                    draftStartDate = '';
                    draftEndDate = '';
                }
                setCalendarExpanded(activePeriod === 'custom');
                const currentPreset = presetButtons.find((button) => button.dataset.periodPreset === activePeriod);
                (currentPreset || customToggle).focus();
            }

            // Sinkronkan periode aktif ke URL dan cookie agar navigasi antarhalaman tetap konsisten.
            saveActivePeriod();
            updateTrigger();
            if (supportsPeriod(window.location.pathname)) {
                const currentUrl = new URL(window.location.href);
                applyPeriodToUrl(currentUrl);
                window.history.replaceState({}, '', `${currentUrl.pathname}${currentUrl.search}${currentUrl.hash}`);
            }
            syncPeriodLinks();

            document.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.button !== 0
                    || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!link || (link.target && link.target.toLowerCase() !== '_self') || link.hasAttribute('download')) return;

                const destination = new URL(link.href, window.location.href);
                if (destination.origin === window.location.origin && supportsPeriod(destination.pathname)) {
                    applyPeriodToUrl(destination);
                    link.href = destination.toString();
                }
            }, true);

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'get') return;
                const destination = new URL(form.action, window.location.href);
                if (!supportsPeriod(destination.pathname)) return;

                const syncHiddenInput = (name, value) => {
                    let input = form.querySelector(`input[name="${name}"]`);
                    if (activePeriod !== 'custom') {
                        input?.remove();
                        return;
                    }
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        form.appendChild(input);
                    }
                    input.value = value;
                };

                let periodInput = form.querySelector('input[name="period"]');
                if (!periodInput) {
                    periodInput = document.createElement('input');
                    periodInput.type = 'hidden';
                    periodInput.name = 'period';
                    form.appendChild(periodInput);
                }
                periodInput.value = activePeriod;
                syncHiddenInput('start_date', activeStartDate);
                syncHiddenInput('end_date', activeEndDate);
            }, true);

            trigger.addEventListener('click', () => {
                if (menu.hidden) openMenu();
                else closeMenu();
            });

            presetButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activePeriod = button.dataset.periodPreset;
                    activeStartDate = '';
                    activeEndDate = '';
                    draftStartDate = '';
                    draftEndDate = '';
                    saveActivePeriod();
                    updateTrigger();
                    syncPeriodLinks();
                    closeMenu();
                    navigateWithActivePeriod();
                });
            });

            customToggle.addEventListener('click', () => {
                const willExpand = calendarPanel.hidden;
                if (willExpand && activePeriod === 'custom') {
                    draftStartDate = activeStartDate;
                    draftEndDate = activeEndDate;
                }
                setCalendarExpanded(willExpand, draftStartDate);
                if (willExpand) {
                    const selectedDay = [...calendarContainer.querySelectorAll('[data-date]')]
                        .find((button) => button.dataset.date === draftStartDate);
                    (selectedDay || calendarContainer.querySelector('[data-date]'))?.focus();
                }
            });

            menu.querySelector('[data-calendar-nav="previous"]').addEventListener('click', () => {
                calendarCursor = addMonths(calendarCursor, -1);
                renderCalendar();
                menu.querySelector('[data-calendar-nav="previous"]').focus();
            });
            menu.querySelector('[data-calendar-nav="next"]').addEventListener('click', () => {
                calendarCursor = addMonths(calendarCursor, 1);
                renderCalendar();
                menu.querySelector('[data-calendar-nav="next"]').focus();
            });

            calendarContainer.addEventListener('click', (event) => {
                const dayButton = event.target instanceof Element ? event.target.closest('[data-date]') : null;
                if (!dayButton) return;
                const selectedDate = dayButton.dataset.date;
                if (!draftStartDate || draftEndDate || selectedDate < draftStartDate) {
                    draftStartDate = selectedDate;
                    draftEndDate = '';
                } else {
                    draftEndDate = selectedDate;
                }
                updateCalendarSelection();
                renderCalendar(selectedDate);
                [...calendarContainer.querySelectorAll('[data-date]')]
                    .find((button) => button.dataset.date === selectedDate)?.focus();
            });

            calendarContainer.addEventListener('keydown', (event) => {
                const dayButton = event.target instanceof Element ? event.target.closest('[data-date]') : null;
                if (!dayButton) return;
                const offsets = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
                let offset = offsets[event.key];
                if (event.key === 'Home' || event.key === 'End') {
                    // Home dan End mengikuti awal minggu Senin seperti susunan kalender.
                    const day = dateFromValue(dayButton.dataset.date).getUTCDay();
                    offset = event.key === 'Home' ? -((day + 6) % 7) : (6 - ((day + 6) % 7));
                }
                if (offset === undefined) return;
                event.preventDefault();

                const nextDate = toDateValue(addDays(dateFromValue(dayButton.dataset.date), offset));
                const nextDateMonth = firstOfMonth(dateFromValue(nextDate));
                const lastVisibleMonth = addMonths(calendarCursor, compactCalendar ? 0 : 1);
                if (nextDateMonth < calendarCursor || nextDateMonth > lastVisibleMonth) {
                    calendarCursor = nextDateMonth;
                }
                renderCalendar(nextDate);
                [...calendarContainer.querySelectorAll('[data-date]')]
                    .find((button) => button.dataset.date === nextDate)?.focus();
            });

            applyButton.addEventListener('click', () => {
                if (!hasValidRange(draftStartDate, draftEndDate)) return;
                activePeriod = 'custom';
                activeStartDate = draftStartDate;
                activeEndDate = draftEndDate;
                saveActivePeriod();
                updateTrigger();
                syncPeriodLinks();
                closeMenu();
                navigateWithActivePeriod();
            });

            document.addEventListener('click', (event) => {
                // Target tanggal dapat terlepas saat kalender dirender ulang sebelum event sampai ke document.
                const eventPath = typeof event.composedPath === 'function' ? event.composedPath() : [];
                const clickedInsideFilter = filter.contains(event.target) || eventPath.includes(filter);
                if (!menu.hidden && !clickedInsideFilter) closeMenu();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.hidden) {
                    event.preventDefault();
                    closeMenu(true);
                }
            });
            window.addEventListener('resize', () => {
                const nextCompactState = window.innerWidth <= 640;
                if (nextCompactState === compactCalendar) return;
                compactCalendar = nextCompactState;
                if (calendarExpanded) renderCalendar();
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
