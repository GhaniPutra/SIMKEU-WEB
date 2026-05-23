// ============================================================
// JavaScript untuk halaman input pengeluaran baru
// Mengelola logika tampilan kategori dan ringkasan form.
// ============================================================

const categoryInput = document.getElementById('category');
const productWrapper = document.getElementById('productWrapper');
const productInput = document.getElementById('product_id');
const amountInput = document.getElementById('amount');
const descriptionInput = document.getElementById('description');
const summaryCategory = document.getElementById('summaryCategory');
const summaryProduct = document.getElementById('summaryProduct');
const summaryAmount = document.getElementById('summaryAmount');
const summaryDescription = document.getElementById('summaryDescription');

// ============================================================
// Fungsi untuk menampilkan atau menyembunyikan field produk
// berdasarkan pilihan kategori pengeluaran.
// ============================================================
function toggleProductField() {
    const selectedCategory = categoryInput.value;
    const isBeliStok = selectedCategory === 'beli_stok';

    if (isBeliStok) {
        productWrapper.style.display = 'block';
    } else {
        productWrapper.style.display = 'none';
        productInput.value = ''; // reset pilihan produk jika tidak relevan
    }

    updateSummary();
}

// ============================================================
// Fungsi untuk memformat angka menjadi format rupiah.
// ============================================================
function formatRupiah(value) {
    const number = Number(value);
    if (Number.isNaN(number)) {
        return 'Rp 0';
    }
    return 'Rp ' + number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// ============================================================
// Update ringkasan di panel samping saat form berubah.
// ============================================================
function updateSummary() {
    const kategori = categoryInput.options[categoryInput.selectedIndex]?.text || 'Belum ada';
    const produk = productInput.options[productInput.selectedIndex]?.text || 'Tidak ada';
    const jumlah = formatRupiah(amountInput.value || 0);
    const deskripsi = descriptionInput.value.trim() || 'Belum ada keterangan';

    summaryCategory.textContent = kategori;
    summaryProduct.textContent = productWrapper.style.display === 'block' && productInput.value ? produk : 'Tidak ada';
    summaryAmount.textContent = jumlah;
    summaryDescription.textContent = deskripsi;
}

// ============================================================
// Event listeners untuk merespons perubahan input form.
// ============================================================
categoryInput.addEventListener('change', toggleProductField);
productInput.addEventListener('change', updateSummary);
amountInput.addEventListener('input', updateSummary);
descriptionInput.addEventListener('input', updateSummary);

// ============================================================
// Inisialisasi awal ketika halaman dimuat.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    toggleProductField();
    updateSummary();
});
