// JavaScript khusus untuk halaman stock pegawai
// File ini memisahkan semua logika interaksi dari file Blade.

// Ambil ID pegawai dari atribut data di root container.
function getEmployeeId() {
    const stockRoot = document.querySelector('.stock-page');
    return stockRoot ? stockRoot.dataset.employeeId : null;
}

// Timer untuk toast notification.
let toastTimer = null;

// Tampilkan toast dengan tipe, judul, dan pesan yang jelas.
function showToast(type, title, msg) {
    const toast = document.getElementById('stock-toast'); // Ambil elemen toast
    const icon = document.getElementById('toast-icon-el'); // Ambil elemen ikon toast

    document.getElementById('toast-title').textContent = title; // Set judul toast
    document.getElementById('toast-msg').textContent = msg; // Set pesan toast

    toast.className = type; // Tambahkan kelas berdasarkan tipe ('success' atau 'error')
    icon.className = type === 'success' ? 'fas fa-check' : 'fas fa-xmark'; // Set ikon

    toast.classList.add('show'); // Tampilkan toast

    clearTimeout(toastTimer); // Hapus timer sebelumnya jika ada
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500); // Sembunyikan setelah 3.5 detik
}

// Tampilkan status loading pada tombol aksi.
function setLoading(productId, loading) {
    const btnGive = document.getElementById(`btn-give-${productId}`); // Tombol beri stok
    const btnPull = document.getElementById(`btn-pull-${productId}`); // Tombol tarik stok

    if (!btnGive || !btnPull) {
        return; // Jika elemen tidak ditemukan, hentikan fungsi
    }

    if (loading) {
        btnGive.disabled = true; // Nonaktifkan saat loading
        btnPull.disabled = true; // Nonaktifkan saat loading
        btnGive.innerHTML = '<span class="spin"></span>'; // Ganti isi tombol dengan spinner
        btnPull.innerHTML = '<span class="spin"></span>'; // Ganti isi tombol dengan spinner
    } else {
        btnGive.innerHTML = '<i class="fas fa-arrow-right"></i> Berikan'; // Kembalikan teks tombol beri
        btnPull.innerHTML = '<i class="fas fa-arrow-left"></i> Tarik'; // Kembalikan teks tombol tarik
    }
}

// Animasi badge ketika nilai berubah.
function animateBadge(el) {
    if (!el) {
        return; // Pastikan elemen ada
    }

    el.classList.remove('num-updated'); // Hapus kelas lama
    void el.offsetWidth; // Reflow browser untuk memicu animasi ulang
    el.classList.add('num-updated'); // Tambahkan kembali kelas animasi
}

// Transfer stok antara gudang dan pegawai.
async function transferStock(productId, action) {
    const amountInput = document.getElementById(`amount-${productId}`); // Ambil input jumlah
    if (!amountInput) {
        showToast('error', 'Terjadi Kesalahan', 'Elemen jumlah transfer tidak ditemukan.');
        return;
    }

    let amount = parseInt(amountInput.value, 10); // Ambil nilai sebagai integer
    if (isNaN(amount) || amount <= 0) {
        showToast('error', 'Input Tidak Valid', 'Masukkan jumlah minimal 1.');
        return; // Keluar jika nilai tidak valid
    }

    if (action === 'pull') {
        amount = -amount; // Jika tarik, ubah jumlah menjadi negatif
    }

    const employeeId = getEmployeeId();
    if (!employeeId) {
        showToast('error', 'Terjadi Kesalahan', 'ID pegawai tidak ditemukan.');
        return;
    }

    setLoading(productId, true); // Set status loading

    try {
        const response = await fetch(`/employees/${employeeId}/stock/transfer`, {
            method: 'POST', // Metode request
            headers: {
                'Content-Type': 'application/json', // Tipe konten JSON
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Token CSRF
            },
            body: JSON.stringify({ product_id: productId, amount }) // Data kirim
        });

        const result = await response.json(); // Parse JSON respons

        if (response.ok && result.success) {
            const masterEl = document.getElementById(`master-stock-${productId}`); // Badge stok master
            const empEl = document.getElementById(`emp-stock-${productId}`); // Badge stok pegawai
            const barEl = document.getElementById(`bar-${productId}`); // Bar stok
            const btnGive = document.getElementById(`btn-give-${productId}`); // Tombol beri
            const btnPull = document.getElementById(`btn-pull-${productId}`); // Tombol tarik

            if (masterEl) {
                masterEl.innerHTML = `<i class="fas fa-warehouse badge-icon"></i> ${result.master_stock}`; // Update stok gudang
                animateBadge(masterEl); // Animasi badge berubah
            }
            if (empEl) {
                empEl.innerHTML = `<i class="fas fa-user badge-icon"></i> ${result.employee_stock}`; // Update stok pegawai
                animateBadge(empEl); // Animasi badge berubah
            }

            if (barEl) {
                const total = result.master_stock + result.employee_stock; // Hitung total stok
                const barPct = total > 0 ? Math.min(100, Math.round(result.employee_stock / total * 100)) : 0; // Persentase bar
                barEl.style.width = barPct + '%'; // Update lebar bar
            }

            if (btnGive) {
                btnGive.disabled = result.master_stock <= 0; // Nonaktifkan jika stok master habis
            }
            if (btnPull) {
                btnPull.disabled = result.employee_stock <= 0; // Nonaktifkan jika stok pegawai nol
            }

            amountInput.value = 1; // Reset input jumlah
            const dir = action === 'push' ? 'diberikan ke pegawai' : 'ditarik dari pegawai'; // Pesan arah transfer
            showToast('success', 'Transfer Berhasil', `${Math.abs(amount)} unit berhasil ${dir}.`); // Tampilkan toast sukses
        } else {
            showToast('error', 'Transfer Gagal', result.message || 'Terjadi kesalahan pada server.'); // Toast error jika gagal
        }
    } catch (err) {
        console.error(err); // Tampilkan error di konsol
        showToast('error', 'Koneksi Error', 'Gagal menghubungi server. Periksa koneksi Anda.'); // Toast jika fetch gagal
    } finally {
        setLoading(productId, false); // Matikan loading
    }
}

function initializeStockBarWidths() {
    const bars = document.querySelectorAll('.stock-bar-fill[data-fill]');

    bars.forEach((bar) => {
        const fillValue = Number(bar.dataset.fill);
        if (!Number.isNaN(fillValue)) {
            bar.style.width = `${Math.min(100, Math.max(0, fillValue))}%`;
        }
    });
}

function bindStockTransferButtons() {
    const transferButtons = document.querySelectorAll('.btn-transfer');

    transferButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const productId = button.dataset.productId;
            const action = button.dataset.action;

            if (!productId || !action) {
                showToast('error', 'Transfer Gagal', 'Data produk tidak valid.');
                return;
            }

            transferStock(productId, action);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initializeStockBarWidths();
    bindStockTransferButtons();
});

