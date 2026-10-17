/**
 * Interaksi preview gambar pada halaman edit produk.
 * Berkas ini menggantikan handler inline tanpa mengubah perilaku preview.
 */
(function () {
    /**
     * Menampilkan preview gambar baru yang dipilih user.
     * Menggantikan preview gambar lama (atau placeholder) dengan gambar baru.
     *
     * @param {HTMLInputElement} input - Elemen input file yang berisi gambar baru
     */
    function previewImage(input) {
        // Pastikan ada file yang dipilih
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            // Setelah file selesai dibaca, tampilkan sebagai gambar di kotak preview
            reader.onload = function(e) {
                const box = document.getElementById('imagePreviewBox');
                // Ganti isi kotak preview dengan tag gambar baru
                box.innerHTML = '<img src="' + e.target.result + '" alt="Preview Gambar Baru">';
            };

            // Mulai membaca file sebagai URL data (base64)
            reader.readAsDataURL(input.files[0]);
        }
    }

// Hubungkan kotak preview dan input file setelah elemen halaman tersedia.
const imagePreviewBox = document.getElementById('imagePreviewBox');
const imageInput = document.getElementById('imageInput');

if (imagePreviewBox && imageInput) {
    imagePreviewBox.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', () => previewImage(imageInput));
}

})(); // Tutup lingkup lokal agar nama fungsi tidak berbenturan dengan halaman lain.
