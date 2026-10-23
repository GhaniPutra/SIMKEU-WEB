document.addEventListener('DOMContentLoaded', () => {
    const editPage = document.querySelector('.employee-edit-page');
    if (editPage && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.addEventListener('click', (event) => {
            const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            if (link.target && link.target !== '_self') return;
            const destination = new URL(link.href, window.location.href);
            if (destination.origin !== window.location.origin || destination.href === window.location.href) return;
            event.preventDefault();
            editPage.classList.add('is-leaving');
            window.setTimeout(() => { window.location.assign(destination.href); }, 220);
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const isVisible = input.type === 'password';
            input.type = isVisible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(isVisible));
            button.setAttribute('aria-label', isVisible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            button.querySelector('i')?.classList.toggle('fa-eye', !isVisible);
            button.querySelector('i')?.classList.toggle('fa-eye-slash', isVisible);
        });
    });

    const nameInput = document.getElementById('employee-name');
    const fallback = document.getElementById('employee-photo-fallback');
    nameInput?.addEventListener('input', () => {
        if (fallback && !document.getElementById('employee-photo-preview')?.hidden) return;
        if (fallback) fallback.textContent = nameInput.value.trim().charAt(0).toLocaleUpperCase('id-ID') || 'P';
    });
});
