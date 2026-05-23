/* ==========================================
   JavaScript untuk halaman pegawai (employees)
   Semua interaksi ditempatkan di file terpisah
   ========================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Cari semua tombol hapus pegawai yang berada di dalam form hapus
    const deleteButtons = document.querySelectorAll('.delete-form .btn-delete');

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            // Tampilkan konfirmasi sebelum menghapus data pegawai
            const confirmed = confirm('Yakin ingin menghapus pegawai ini?');

            if (confirmed) {
                const form = button.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    });
});
