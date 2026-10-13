// JavaScript khusus untuk halaman stock pegawai
// File ini memisahkan semua logika interaksi dari file Blade.

// Ambil ID pegawai dari atribut data di root container.
function getEmployeeId() {
    const stockRoot = document.querySelector('.stock-page, .employee-manage');
    return stockRoot ? stockRoot.dataset.employeeId : null;
}

function getTransferUrl(employeeId) {
    const root = document.querySelector('.stock-page, .employee-manage');
    return root?.dataset.stockTransferUrl || `/employees/${employeeId}/stock/transfer`;
}

// Timer untuk toast notification.
let toastTimer = null;

// Tampilkan toast dengan tipe, judul, dan pesan yang jelas.
function showToast(type, title, msg) {
    const toast = document.getElementById('stock-toast'); // Ambil elemen toast
    const icon = document.getElementById('toast-icon-el'); // Ambil elemen ikon toast

    document.getElementById('toast-title').textContent = title; // Set judul toast
    document.getElementById('toast-msg').textContent = msg; // Set pesan toast

    toast.classList.remove('success', 'error');
    toast.classList.add(type);
    icon.className = type === 'success' ? 'fas fa-check' : 'fas fa-xmark'; // Set ikon

    toast.classList.add('show'); // Tampilkan toast

    clearTimeout(toastTimer); // Hapus timer sebelumnya jika ada
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500); // Sembunyikan setelah 3.5 detik
}

// Tampilkan status loading pada tombol aksi.
function setLoading(productId, loading, showSpinner = true) {
    const btnGive = document.getElementById(`btn-give-${productId}`); // Tombol beri stok
    const btnPull = document.getElementById(`btn-pull-${productId}`); // Tombol tarik stok

    if (!btnGive || !btnPull) {
        return; // Jika elemen tidak ditemukan, hentikan fungsi
    }

    if (loading) {
        if (!btnGive.hasAttribute('aria-busy')) {
            btnGive.dataset.loadingDisabled = String(btnGive.disabled);
            btnGive.dataset.loadingHtml = btnGive.innerHTML;
        }
        if (!btnPull.hasAttribute('aria-busy')) {
            btnPull.dataset.loadingDisabled = String(btnPull.disabled);
            btnPull.dataset.loadingHtml = btnPull.innerHTML;
        }
        btnGive.setAttribute('aria-busy', 'true');
        btnPull.setAttribute('aria-busy', 'true');
        btnGive.disabled = true;
        btnPull.disabled = true;
        if (showSpinner) {
            btnGive.innerHTML = '<span class="spin" aria-hidden="true"></span><span class="visually-hidden">Memproses transfer</span>';
            btnPull.innerHTML = '<span class="spin" aria-hidden="true"></span><span class="visually-hidden">Memproses transfer</span>';
        }
    } else {
        btnGive.innerHTML = btnGive.dataset.loadingHtml || btnGive.innerHTML;
        btnPull.innerHTML = btnPull.dataset.loadingHtml || btnPull.innerHTML;
        btnGive.removeAttribute('aria-busy');
        btnPull.removeAttribute('aria-busy');
        btnGive.disabled = btnGive.dataset.loadingDisabled === 'true';
        btnPull.disabled = btnPull.dataset.loadingDisabled === 'true';
        delete btnGive.dataset.loadingDisabled;
        delete btnPull.dataset.loadingDisabled;
        delete btnGive.dataset.loadingHtml;
        delete btnPull.dataset.loadingHtml;
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

    let amount = Number(amountInput.value); // Ambil nilai transfer
    if (!Number.isInteger(amount) || amount <= 0) {
        showToast('error', 'Input Tidak Valid', 'Masukkan jumlah unit bulat minimal 1.');
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

    setLoading(productId, true, false);
    const spinnerTimer = window.setTimeout(() => setLoading(productId, true), 250);

    try {
        const response = await fetch(getTransferUrl(employeeId), {
            method: 'POST', // Metode request
            headers: {
                'Content-Type': 'application/json', // Tipe konten JSON
                'Accept': 'application/json',
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
                masterEl.querySelector('[data-stock-value]').textContent = Number(result.master_stock).toLocaleString();
                animateBadge(masterEl); // Animasi badge berubah
            }
            if (empEl) {
                empEl.querySelector('[data-stock-value]').textContent = Number(result.employee_stock).toLocaleString();
                animateBadge(empEl); // Animasi badge berubah
            }

            if (barEl) {
                const total = result.master_stock + result.employee_stock; // Hitung total stok
                const barPct = total > 0 ? Math.min(100, Math.round(result.employee_stock / total * 100)) : 0; // Persentase bar
                barEl.style.width = barPct + '%'; // Update lebar bar
            }

            if (btnGive) {
                btnGive.disabled = result.master_stock <= 0; // Nonaktifkan jika stok master habis
                btnGive.dataset.loadingDisabled = String(btnGive.disabled);
            }
            if (btnPull) {
                btnPull.disabled = result.employee_stock <= 0; // Nonaktifkan jika stok pegawai nol
                btnPull.dataset.loadingDisabled = String(btnPull.disabled);
            }

            amountInput.value = 1; // Reset input jumlah
            if (action === 'push') {
                window.refreshPendingStockAllocations?.().catch((error) => console.error(error));
            } else {
                showToast('success', 'Transfer Berhasil', `${Math.abs(amount)} unit berhasil ditarik dari pegawai.`);
            }
            if (document.querySelector('.employee-manage')) {
                window.setTimeout(() => {
                    window.location.reload();
                }, 1100);
            }
        } else {
            showToast('error', 'Transfer Gagal', result.message || 'Terjadi kesalahan pada server.'); // Toast error jika gagal
        }
    } catch (err) {
        console.error(err); // Tampilkan error di konsol
        showToast('error', 'Koneksi Error', 'Gagal menghubungi server. Periksa koneksi Anda.'); // Toast jika fetch gagal
    } finally {
        window.clearTimeout(spinnerTimer);
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

async function reviewAllocation(allocationId, decision) {
    const input = document.querySelector(`.allocation-confirmed-qty[data-allocation-id="${allocationId}"]`);
    const confirmedQuantity = input ? Number(input.value) : null;
    const payload = { decision, confirmed_quantity: confirmedQuantity };

    if (decision === 'decline') {
        const actualInput = window.prompt('Jumlah fisik yang diterima:', '0');
        if (actualInput === null) return;

        const actualQuantity = Number(actualInput);
        if (!Number.isInteger(actualQuantity) || actualQuantity < 0) {
            showToast('error', 'Review Gagal', 'Jumlah fisik harus berupa bilangan bulat nol atau lebih.');
            return;
        }

        const reason = window.prompt('Alasan selisih wajib diisi:');
        if (reason === null) return;
        if (!reason.trim()) {
            showToast('error', 'Review Gagal', 'Alasan selisih wajib diisi.');
            return;
        }

        payload.actual_received_quantity = actualQuantity;
        payload.decline_reason = reason.trim();
    }

    try {
        const response = await fetch(`/stock-allocations/${allocationId}/review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            showToast('error', 'Review Gagal', result.message || 'Terjadi kesalahan saat meninjau alokasi.');
            return;
        }

        showToast('success', 'Review Berhasil', result.message || 'Status alokasi berhasil diperbarui.');
        const row = document.getElementById(`allocation-row-${allocationId}`);
        if (row) {
            row.remove();
        }
    } catch (error) {
        console.error(error);
        showToast('error', 'Koneksi Error', 'Gagal menghubungi server.');
    }
}

function bindAllocationReviewButtons() {
    const buttons = document.querySelectorAll('.btn-allocation-decision');

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const allocationId = button.dataset.allocationId;
            const decision = button.dataset.decision;

            if (!allocationId || !decision) {
                showToast('error', 'Review Gagal', 'Data alokasi tidak valid.');
                return;
            }

            reviewAllocation(allocationId, decision);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initializeStockBarWidths();
    bindStockTransferButtons();
    bindAllocationReviewButtons();
});
