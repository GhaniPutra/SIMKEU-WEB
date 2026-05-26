/* JavaScript khusus halaman tambah pegawai */
/* File ini memisahkan logika halaman create dari Blade */

document.addEventListener('DOMContentLoaded', function () {
    const formWrapper = document.querySelector('.form-container'); /* Cari wrapper form */
    if (!formWrapper) {
        return; /* Hentikan jika tidak ditemukan */
    }

    formWrapper.dataset.page = 'create'; /* Tandai halaman create telah dimuat */
});
