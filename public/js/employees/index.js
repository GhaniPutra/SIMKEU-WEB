document.addEventListener('DOMContentLoaded', () => {
    const createModal = document.getElementById('employeeCreateModal');
    if (createModal && createModal.querySelector('.employee-form-errors') && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(createModal).show();
    }

    document.querySelectorAll('.employee-row-clickable[data-href]').forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('a, button, form, input, select, textarea, label')) return;
            window.location.href = row.dataset.href;
        });

        row.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            if (event.target.closest('a, button, form, input, select, textarea, label')) return;
            event.preventDefault();
            window.location.href = row.dataset.href;
        });
    });

    document.querySelectorAll('.delete-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm('Yakin ingin menghapus pegawai ini?')) {
                event.preventDefault();
                return;
            }
            window.SimkeuLoading?.showSubmitLoadingAfterDelay(form.querySelector('button[type="submit"]'));
        });
    });
});
