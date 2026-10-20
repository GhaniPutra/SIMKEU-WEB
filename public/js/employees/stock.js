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
        const employeeStock = Number(document.querySelector(`#emp-stock-${productId} [data-stock-value]`)?.textContent.replaceAll('.', '').replaceAll(',', '') ?? 0);
        if (amount > employeeStock) {
            showToast('error', 'Stok Tidak Cukup', `Stok pegawai hanya ${employeeStock.toLocaleString()} unit.`);
            return;
        }

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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            throw new Error('Token keamanan tidak ditemukan. Muat ulang halaman lalu coba lagi.');
        }

        const response = await fetch(getTransferUrl(employeeId), {
            method: 'POST', // Metode request
            headers: {
                'Content-Type': 'application/json', // Tipe konten JSON
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ product_id: productId, amount }) // Data kirim
        });

        const result = await response.json().catch(() => ({})); // Parse JSON respons

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
                const masterStock = Number(result.master_stock);
                const employeeStock = Number(result.employee_stock);
                const total = masterStock + employeeStock; // Hitung total stok
                const barPct = total > 0 ? Math.min(100, Math.round(employeeStock / total * 100)) : 0; // Persentase bar
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
        showToast('error', 'Transfer Gagal', err.message || 'Gagal menghubungi server. Periksa koneksi Anda.'); // Toast jika fetch gagal
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

function bindAllocationReturnSteppers() {
    document.querySelectorAll('[data-return-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const stepper = button.closest('.allocation-return-stepper');
            const input = stepper?.querySelector('[data-return-quantity]');

            if (!input) return;

            const min = Number(input.min || 0);
            const max = Number(input.max || Number.MAX_SAFE_INTEGER);
            const current = Number(input.value || 0);
            const change = Number(button.dataset.returnStep || 0);
            input.value = String(Math.min(max, Math.max(min, current + change)));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

function limitAllocationReviewItems() {
    document.querySelectorAll('.allocation-review-card .employee-card-body').forEach((body) => {
        const items = body.querySelectorAll('.allocation-review-item');

        if (items.length <= 2) {
            body.style.height = '';
            body.style.maxHeight = '';
            return;
        }

        if (!body.getClientRects().length || items[0].getBoundingClientRect().height === 0) return;

        const styles = window.getComputedStyle(body);
        const verticalPadding = Number.parseFloat(styles.paddingTop) + Number.parseFloat(styles.paddingBottom);
        const visibleItemsHeight = items[0].getBoundingClientRect().height + items[1].getBoundingClientRect().height;
        const scrollAreaHeight = `${Math.ceil(visibleItemsHeight + verticalPadding)}px`;
        body.style.height = scrollAreaHeight;
        body.style.maxHeight = scrollAreaHeight;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindStockTransferButtons();
    bindAllocationReturnSteppers();
    limitAllocationReviewItems();

    let allocationReviewResizeFrame;
    window.addEventListener('resize', () => {
        window.cancelAnimationFrame(allocationReviewResizeFrame);
        allocationReviewResizeFrame = window.requestAnimationFrame(limitAllocationReviewItems);
    });

    document.querySelectorAll('[data-tab="stock"], [data-open-tab="stock"]').forEach((tabTrigger) => {
        tabTrigger.addEventListener('click', () => window.requestAnimationFrame(limitAllocationReviewItems));
    });

    document.querySelectorAll('.allocation-review-card img').forEach((image) => {
        image.addEventListener('load', limitAllocationReviewItems, { once: true });
    });
});
