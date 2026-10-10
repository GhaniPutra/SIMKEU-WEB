(() => {
    const root = document.documentElement;
    const overlay = document.getElementById('navigation-overlay');
    const progress = document.getElementById('navigation-progress');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const revealSelector = [
        '#main-content .fade-up',
        '#main-content .kpi-card',
        '#main-content .content-card',
        '#main-content .summary-card',
        '#main-content .employee-kpi',
        '#main-content .table-card',
        '#main-content .form-container',
        '#main-content .pos-header',
        '#main-content .login-container',
        '#main-content .register-container',
        '#main-content .setup-container',
        '#main-content .employee-manage-header',
        '#main-content .stock-header',
    ].join(',');
    let navigationActive = false;
    let overlayTimer;
    let navigationTimer;

    function reveal(element) {
        element.classList.remove('motion-pending');
        element.classList.add('motion-visible');
    }

    function initializeReveal() {
        const candidates = [...document.querySelectorAll(revealSelector)]
            .filter((element) => !element.parentElement?.closest(revealSelector));

        candidates.forEach((element, index) => {
            element.classList.add('motion-pending');
            element.style.setProperty('--motion-delay', `${Math.min(index, 5) * 24}ms`);
        });

        if (reducedMotion.matches || !('IntersectionObserver' in window)) {
            candidates.forEach(reveal);
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                reveal(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });

        candidates.forEach((element) => observer.observe(element));
    }

    function stopNavigation() {
        navigationActive = false;
        window.clearTimeout(overlayTimer);
        window.clearTimeout(navigationTimer);
        document.body.classList.remove('is-navigating', 'navigation-taking-time');
        overlay?.setAttribute('aria-hidden', 'true');
        progress?.setAttribute('aria-hidden', 'true');
    }

    function startNavigation() {
        if (navigationActive) return;
        navigationActive = true;
        document.body.classList.add('is-navigating');
        progress?.setAttribute('aria-hidden', 'false');

        overlayTimer = window.setTimeout(() => {
            document.body.classList.add('navigation-taking-time');
            overlay?.setAttribute('aria-hidden', 'false');
        }, 320);

        navigationTimer = window.setTimeout(stopNavigation, 15000);
    }

    function isExcluded(element) {
        return element.closest('[data-no-loader]') !== null;
    }

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0
            || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!link || isExcluded(link) || link.hasAttribute('download')
            || (link.target && link.target.toLowerCase() !== '_self')
            || link.relList.contains('external')) return;

        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin
            || !['http:', 'https:'].includes(destination.protocol)
            || (destination.pathname === window.location.pathname
                && destination.search === window.location.search
                && destination.hash)) return;

        window.setTimeout(() => {
            if (!event.defaultPrevented) startNavigation();
        }, 0);
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || isExcluded(form)
            || form.target && form.target.toLowerCase() !== '_self'
            || form.method.toLowerCase() === 'dialog') return;

        window.setTimeout(() => {
            if (event.defaultPrevented) return;
            const destination = new URL(form.action, window.location.href);
            if (destination.origin === window.location.origin) startNavigation();
        }, 0);
    });

    function animateTab(tab) {
        window.setTimeout(() => {
            window.requestAnimationFrame(() => {
                const panelId = tab.getAttribute('aria-controls');
                const panelSelector = tab.dataset.bsTarget;
                const panel = panelId
                    ? document.getElementById(panelId)
                    : panelSelector?.startsWith('#')
                        ? document.querySelector(panelSelector)
                        : tab.dataset.tab || tab.dataset.openTab
                            ? document.getElementById(`tab-${tab.dataset.tab || tab.dataset.openTab}`)
                            : null;

                if (!panel || (!panel.classList.contains('active') && !panel.classList.contains('show'))) return;
                panel.querySelectorAll('.motion-pending').forEach(reveal);
                if (reducedMotion.matches) return;
                panel.classList.remove('motion-tab-enter');
                void panel.offsetWidth;
                panel.classList.add('motion-tab-enter');
                panel.addEventListener('animationend', () => {
                    panel.classList.remove('motion-tab-enter');
                }, { once: true });
            });
        }, 0);
    }

    document.addEventListener('click', (event) => {
        const tab = event.target instanceof Element
            ? event.target.closest('[role="tab"], [data-open-tab]')
            : null;
        if (tab && !tab.matches('[data-bs-toggle="tab"]')) animateTab(tab);
    });

    document.addEventListener('shown.bs.tab', (event) => animateTab(event.target));
    window.addEventListener('pageshow', stopNavigation);
    window.SimkeuMotion = { startNavigation, stopNavigation };

    reducedMotion.addEventListener?.('change', (event) => {
        if (event.matches) {
            document.querySelectorAll('#main-content .motion-pending').forEach(reveal);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Chart) {
            window.Chart.defaults.animation.duration = reducedMotion.matches ? 0 : 420;
        }
    });

    root.classList.add('motion-ready');
    initializeReveal();
})();
