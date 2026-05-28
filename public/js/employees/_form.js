/* JavaScript khusus komponen form pegawai */
/* File ini memisahkan logika komponen form dari Blade */

document.addEventListener('DOMContentLoaded', function () {
    const formElement = document.querySelector('.form-container form'); /* Cari form pegawai */
    if (!formElement) {
        return; /* Hentikan jika tidak ada form */
    }

    formElement.dataset.formInitialized = 'true'; /* Tandai form telah diinisialisasi */
});

/**
 * Toggle password field visibility (text <-> password)
 * @param {string} inputId - The ID of the input element to toggle
 * @param {HTMLElement} toggleBtn - The button element containing the eye icon
 */
function togglePasswordVisibility(inputId, toggleBtn) {
    const passwordInput = document.getElementById(inputId);
    const icon = toggleBtn.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
