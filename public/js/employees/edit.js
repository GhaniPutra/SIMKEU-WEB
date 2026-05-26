/* JavaScript khusus halaman edit pegawai */
/* File ini memisahkan logika halaman edit dari Blade */

document.addEventListener('DOMContentLoaded', function () {
    const formWrapper = document.querySelector('.form-container'); /* Cari wrapper form */
    if (!formWrapper) {
        return; /* Hentikan jika tidak ditemukan */
    }

    formWrapper.dataset.page = 'edit'; /* Tandai halaman edit telah dimuat */
});
