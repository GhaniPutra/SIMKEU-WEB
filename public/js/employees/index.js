// Script khusus untuk halaman daftar pegawai di folder employees

// Pasang event listener saat semua elemen DOM telah selesai dimuat
document.addEventListener('DOMContentLoaded', function () {
    // Cari semua tombol hapus yang berada di dalam form hapus pegawai
    const deleteButtons = document.querySelectorAll('.delete-form .btn-delete');

    // Jika ada tombol hapus, lakukan iterasi untuk memasang konfirmasi
    deleteButtons.forEach(function (button) {
        // Ketika tombol diklik, hentikan submit otomatis
        button.addEventListener('click', function (event) {
            event.preventDefault();

            // Tampilkan dialog konfirmasi kepada pengguna sebelum menghapus data pegawai
            const confirmed = confirm('Yakin ingin menghapus pegawai ini?');

            // Jika pengguna mengonfirmasi, submit form hapus
            if (confirmed) {
                const form = button.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    });
});
