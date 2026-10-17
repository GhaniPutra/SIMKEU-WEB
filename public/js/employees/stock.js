// Interaksi tab stok pada halaman kelola pegawai.

function getEmployeeId() {
    return document.querySelector('.employee-manage')?.dataset.employeeId || null;
}

function getTransferUrl(employeeId) {
    const root = document.querySelector('.employee-manage');
    return root?.dataset.stockTransferUrl || `/employees/${employeeId}/stock/transfer`;
}

// Satukan pesan interaktif halaman di panel notifikasi navbar.
function showToast(type, title, msg) {
    window.SimkeuNotifications?.add({ type, title, message: msg });
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
            }
            if (empEl) {
                empEl.querySelector('[data-stock-value]').textContent = Number(result.employee_stock).toLocaleString();
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
    bindStockTransferButtons();
});
