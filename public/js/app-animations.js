(() => {
    // ============================================================
    // SIMKEU App Animations JS
    // Sistem animasi entrance, counter, progress bar, dan loading
    // ============================================================

    document.querySelectorAll('.modal').forEach((modal) => {
        if (modal.parentElement !== document.body) {
            document.body.append(modal);
        }
    });

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const activeSubmitButtons = new Set();

    // ============================================================
    // 1. NAVIGATION PROGRESS BAR
    // Menampilkan bar tipis di atas halaman saat navigasi
    // ============================================================

    let progressBar = null;
    let progressTimer = null;
    let progressValue = 0;

    function createProgressBar() {
        if (document.getElementById('simkeu-progress')) return;
        progressBar = document.createElement('div');
        progressBar.id = 'simkeu-progress';
        document.body.prepend(progressBar);
    }

    function startProgress() {
        if (reducedMotion.matches) return;
        createProgressBar();
        progressValue = 0;
        progressBar.style.width = '0%';
        progressBar.classList.add('is-loading');
        progressBar.classList.remove('is-done');

        // Simulasi progress: cepat ke 30%, lalu lambat ke 80%
        const steps = [
            { target: 30, duration: 200 },
            { target: 60, duration: 400 },
            { target: 80, duration: 600 },
        ];
        let stepIndex = 0;

        function runStep() {
            if (stepIndex >= steps.length) return;
            const step = steps[stepIndex++];
            progressValue = step.target;
            progressBar.style.width = `${progressValue}%`;
            progressTimer = setTimeout(runStep, step.duration);
        }
        runStep();
    }

    function finishProgress() {
        if (!progressBar) return;
        clearTimeout(progressTimer);
        progressBar.classList.add('is-done');
        progressBar.classList.remove('is-loading');
        setTimeout(() => {
            if (progressBar) progressBar.style.width = '0%';
        }, 600);
    }

    // Trigger progress saat navigasi dimulai
    document.addEventListener('click', (event) => {
        if (reducedMotion.matches) return;
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!link) return;
        if (link.target && link.target.toLowerCase() !== '_self') return;
        if (link.hasAttribute('download')) return;

        try {
            const dest = new URL(link.href, window.location.href);
            if (dest.origin !== window.location.origin) return;
            if (dest.pathname === window.location.pathname && dest.search === window.location.search) return;
            if (dest.hash && !dest.pathname !== window.location.pathname) return;
        } catch (e) {
            return;
        }

        startProgress();
    }, { capture: true });

    window.addEventListener('pageshow', () => {
        finishProgress();
        restoreSubmitButtons();
    });

    window.addEventListener('pagehide', () => {
        startProgress();
    });

    // ============================================================
    // 2. ENTRANCE ANIMATIONS — Intersection Observer
    // Elemen dengan .simkeu-animate muncul saat masuk viewport
    // ============================================================

    function initEntranceAnimations() {
        if (reducedMotion.matches) {
            // Langsung tampilkan semua jika reduced motion
            document.querySelectorAll('.simkeu-animate, .simkeu-animate-left, .simkeu-animate-scale')
                .forEach((el) => el.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.06,
            rootMargin: '0px 0px -24px 0px',
        });

        document.querySelectorAll('.simkeu-animate, .simkeu-animate-left, .simkeu-animate-scale')
            .forEach((el) => observer.observe(el));
    }

    // ============================================================
    // 3. AUTO-ANNOTATE ANIMATABLE ELEMENTS
    // Otomatis tambahkan .simkeu-animate ke elemen yang relevan
    // berdasarkan kelas yang ada di halaman, dengan stagger delay
    // ============================================================

    function annotateAnimatables() {
        if (reducedMotion.matches) return;

        // KPI cards & Employee KPI cards — stagger berurutan
        const kpiCards = document.querySelectorAll(
            '#main-content .kpi-card, ' +
            '#main-content .employee-kpi, ' +
            '#main-content .stat-card'
        );
        kpiCards.forEach((card, i) => {
            if (!card.classList.contains('simkeu-animate')) {
                card.classList.add('simkeu-animate');
                card.setAttribute('data-delay', Math.min((i % 6) + 1, 6));
            }
        });

        // Section titles & page headers — slide dari kiri
        document.querySelectorAll(
            '#main-content .section-title, ' +
            '#main-content .page-header, ' +
            '#main-content .stock-header, ' +
            '#main-content .pos-header, ' +
            '#main-content .employee-manage-header, ' +
            '#main-content .amount-highlight, ' +
            '#main-content .btn-back'
        ).forEach((el, i) => {
            if (!el.classList.contains('simkeu-animate-left')) {
                el.classList.add('simkeu-animate-left');
                el.setAttribute('data-delay', Math.min((i % 4) + 1, 4));
            }
        });

        // Content cards & detail cards — fade up dengan stagger
        const contentCards = document.querySelectorAll(
            '#main-content .content-card, ' +
            '#main-content .detail-card, ' +
            '#main-content .summary-card, ' +
            '#main-content .employee-card, ' +
            '#main-content .table-card, ' +
            '#main-content .filter-bar, ' +
            '#main-content .search-filter-card'
        );
        contentCards.forEach((card, i) => {
            if (!card.classList.contains('simkeu-animate')) {
                card.classList.add('simkeu-animate');
                card.setAttribute('data-delay', Math.min((i % 6) + 1, 6));
            }
        });

        // Form containers & boxes — scale-in
        document.querySelectorAll(
            '#main-content .form-container, ' +
            '#main-content .form-card, ' +
            '#main-content .expense-form, ' +
            '#main-content .pos-form, ' +
            '.login-box, .register-box, .setup-box'
        ).forEach((el, i) => {
            if (!el.classList.contains('simkeu-animate-scale')) {
                el.classList.add('simkeu-animate-scale');
                el.setAttribute('data-delay', Math.min((i % 4) + 1, 4));
            }
        });

        // Nav tabs & pill buttons
        document.querySelectorAll(
            '#main-content .employee-tabs, ' +
            '#main-content .module-tabs, ' +
            '#main-content .nav-tabs'
        ).forEach((el) => {
            if (!el.classList.contains('simkeu-animate')) {
                el.classList.add('simkeu-animate');
            }
        });

        // Charts & visual grids
        document.querySelectorAll(
            '#main-content .two-col, ' +
            '#main-content .two-col-chart, ' +
            '#main-content .chart-wrapper, ' +
            '#main-content .chart-container'
        ).forEach((el, i) => {
            if (!el.classList.contains('simkeu-animate')) {
                el.classList.add('simkeu-animate');
                el.setAttribute('data-delay', Math.min((i % 4) + 2, 6));
            }
        });

        // Alert banners
        document.querySelectorAll('#main-content .alert-banner, #main-content .alert').forEach((el) => {
            if (!el.classList.contains('simkeu-animate')) {
                el.classList.add('simkeu-animate');
            }
        });

        // Semua elemen .fade-up di seluruh halaman
        document.querySelectorAll('#main-content .fade-up, .fade-up').forEach((el, i) => {
            if (!el.classList.contains('simkeu-animate')) {
                el.classList.add('simkeu-animate');
                el.setAttribute('data-delay', Math.min((i % 6) + 1, 6));
            }
        });
    }

    // ============================================================
    // 4. KPI COUNTER ANIMATION
    // Angka KPI menghitung naik saat elemen muncul
    // ============================================================

    function parseNumber(str) {
        // Handles "Rp 1.234.567" atau "1.234" atau "123"
        return parseFloat(str.replace(/[^0-9,.-]/g, '').replace(/\./g, '').replace(',', '.')) || 0;
    }

    function formatNumber(val, original) {
        const isRupiah = original.includes('Rp');
        const hasDecimal = original.includes(',');
        const rounded = Math.round(val);

        if (isRupiah) {
            return 'Rp ' + rounded.toLocaleString('id-ID');
        }
        return rounded.toLocaleString('id-ID');
    }

    function animateCounter(el) {
        if (reducedMotion.matches) return;
        const original = el.textContent.trim();
        const end = parseNumber(original);
        if (!end || end <= 0) return;

        const duration = 700;
        const start = performance.now();

        function frame(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Easing: ease out quart
            const eased = 1 - Math.pow(1 - progress, 4);
            const current = eased * end;

            el.textContent = formatNumber(current, original);

            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                el.textContent = original;
            }
        }

        requestAnimationFrame(frame);
    }

    function initCounters() {
        if (reducedMotion.matches) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.2 });

        document.querySelectorAll(
            '#main-content .kpi-value, ' +
            '#main-content .employee-kpi-value, ' +
            '#main-content .stat-value'
        ).forEach((el) => {
            const text = el.textContent.trim();
            if (/[0-9]/.test(text)) {
                observer.observe(el);
            }
        });
    }

    // ============================================================
    // 5. SUBMIT BUTTON LOADING
    // ============================================================

    function showSubmitLoading(button) {
        if (!button || button.disabled || !button.isConnected) return;

        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML = '<span class="submit-spinner" aria-hidden="true"></span> Memproses…';
        activeSubmitButtons.add(button);
    }

    function showSubmitLoadingAfterDelay(button) {
        return window.setTimeout(() => showSubmitLoading(button), 250);
    }

    function restoreSubmitButtons() {
        activeSubmitButtons.forEach((button) => {
            button.innerHTML = button.dataset.originalHtml || button.innerHTML;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            delete button.dataset.originalHtml;
        });
        activeSubmitButtons.clear();
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loader')
            || (form.target && form.target.toLowerCase() !== '_self')
            || form.method.toLowerCase() === 'dialog') return;

        const button = event.submitter instanceof HTMLButtonElement
            ? event.submitter
            : form.querySelector('button[type="submit"]:not(:disabled), button:not([type]):not(:disabled)');
        if (!button) return;

        window.setTimeout(() => {
            if (!event.defaultPrevented) showSubmitLoadingAfterDelay(button);
        }, 0);
    });

    // ============================================================
    // 6. CHART.JS DEFAULT SETTINGS
    // ============================================================

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Chart) {
            const defaults = window.Chart.defaults;
            defaults.animation.duration = reducedMotion.matches ? 0 : 400;
            defaults.font.family = 'Poppins, sans-serif';
            defaults.color = '#434D27';
            defaults.elements.line.tension = 0;
            defaults.elements.line.fill = false;
            defaults.elements.point.radius = 2;
            defaults.elements.point.hoverRadius = 4;
            defaults.plugins.legend.labels.boxWidth = 10;
            defaults.plugins.legend.labels.boxHeight = 10;
            defaults.plugins.legend.labels.padding = 14;
            defaults.plugins.tooltip.backgroundColor = '#1D2310';
            defaults.plugins.tooltip.titleFont = { family: 'Poppins, sans-serif', size: 12 };
            defaults.plugins.tooltip.bodyFont = { family: 'Poppins, sans-serif', size: 11 };
            defaults.plugins.tooltip.padding = 10;
            defaults.plugins.tooltip.cornerRadius = 8;
        }
    });

    // ============================================================
    // 7. INIT — Jalankan semua saat DOM siap
    // ============================================================

    function init() {
        annotateAnimatables();
        initEntranceAnimations();
        initCounters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('pageshow', () => {
        init();
    });

    // Expose public API
    window.SimkeuLoading = { showSubmitLoadingAfterDelay };
    window.SimkeuAnimate = { refresh: init };
})();
