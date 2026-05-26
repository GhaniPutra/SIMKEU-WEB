/* JavaScript khusus komponen form pegawai */
/* File ini memisahkan logika komponen form dari Blade */

document.addEventListener('DOMContentLoaded', function () {
    const formElement = document.querySelector('.form-container form'); /* Cari form pegawai */
    if (!formElement) {
        return; /* Hentikan jika tidak ada form */
    }

    formElement.dataset.formInitialized = 'true'; /* Tandai form telah diinisialisasi */
});
