(() => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const activeSubmitButtons = new Set();

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

    window.addEventListener('pageshow', () => {
        activeSubmitButtons.forEach((button) => {
            button.innerHTML = button.dataset.originalHtml || button.innerHTML;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            delete button.dataset.originalHtml;
        });
        activeSubmitButtons.clear();
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Chart) {
            const defaults = window.Chart.defaults;
            defaults.animation.duration = reducedMotion.matches ? 0 : 250;
            defaults.font.family = 'Inter, sans-serif';
            defaults.color = '#64748b';
            defaults.elements.line.tension = 0;
            defaults.elements.line.fill = false;
            defaults.elements.point.radius = 2;
            defaults.elements.point.hoverRadius = 4;
            defaults.plugins.legend.labels.boxWidth = 10;
            defaults.plugins.legend.labels.boxHeight = 10;
            defaults.plugins.legend.labels.padding = 14;
            defaults.plugins.tooltip.backgroundColor = '#1e293b';
            defaults.plugins.tooltip.titleFont = { family: 'Inter, sans-serif', size: 12 };
            defaults.plugins.tooltip.bodyFont = { family: 'Inter, sans-serif', size: 11 };
            defaults.plugins.tooltip.padding = 10;
            defaults.plugins.tooltip.cornerRadius = 8;
        }
    });

    window.SimkeuLoading = { showSubmitLoadingAfterDelay };
})();
