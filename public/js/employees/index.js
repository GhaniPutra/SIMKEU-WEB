// Script khusus untuk halaman daftar pegawai di folder employees

// Pasang event listener saat semua elemen DOM telah selesai dimuat
document.addEventListener('DOMContentLoaded', function () {
    const employeeRows = document.querySelectorAll('.employee-row-clickable[data-href]');
    employeeRows.forEach(function (row) {
        function openRow(event) {
            if (event.target.closest('a, button, form, input, select, textarea, label')) return;
            window.location.href = row.dataset.href;
        }

        row.addEventListener('click', openRow);
        row.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            openRow(event);
        });
    });

    const modal = document.getElementById('employeeModal');
    const form = document.getElementById('employeeForm');
    const modalTitle = document.getElementById('employeeModalLabel');
    const submitButton = form.querySelector('button[type="submit"]');
    const nameInput = document.getElementById('employeeName');
    const emailInput = document.getElementById('employeeEmail');
    const roleInput = document.getElementById('employeeRole');
    const passwordInput = document.getElementById('employeePassword');
    const passwordConfirmationInput = document.getElementById('employeePasswordConfirmation');

    function resetModalForm() {
        form.reset();
        const createUrl = document.querySelector('[data-bs-target="#employeeModal"][data-mode="create"]')?.dataset.createUrl || '/employees';
        form.action = createUrl;
        form.method = 'POST';
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
        modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Tambah Pegawai';
        submitButton.innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan';
        passwordInput.required = true;
        passwordConfirmationInput.required = true;
    }

    const deleteButtons = document.querySelectorAll('.delete-form .btn-delete');
    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const confirmed = confirm('Yakin ingin menghapus pegawai ini?');
            if (confirmed) {
                const form = button.closest('form');
                if (form) {
                    window.SimkeuLoading?.showSubmitLoadingAfterDelay(button);
                    form.submit();
                }
            }
        });
    });

    const createButton = document.querySelector('[data-bs-target="#employeeModal"][data-mode="create"]');
    if (createButton) {
        createButton.addEventListener('click', resetModalForm);
    }

    const editButtons = document.querySelectorAll('.employee-edit');
    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const employeeId = button.dataset.id;
            const name = button.dataset.name;
            const email = button.dataset.email;
            const role = button.dataset.role;
            const updateUrl = button.dataset.updateUrl;

            form.action = updateUrl;
            form.method = 'POST';
            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            }

            nameInput.value = name || '';
            emailInput.value = email || '';
            roleInput.value = role || 'pegawai';
            passwordInput.value = '';
            passwordConfirmationInput.value = '';
            passwordInput.required = false;
            passwordConfirmationInput.required = false;
            modalTitle.innerHTML = '<i class="fas fa-user-gear"></i> Edit Pegawai';
            submitButton.innerHTML = '<i class="fas fa-save"></i> Perbarui';
        });
    });

    modal.addEventListener('hidden.bs.modal', function () {
        resetModalForm();
    });
});
