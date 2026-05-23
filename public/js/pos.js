// ============================================================
// Logika JavaScript untuk halaman POS
// Mengelola daftar item keranjang, menghitung total dengan
// pajak dan diskon per produk, serta diskon tambahan kasir.
//
// Alur Perhitungan:
//   Per Item:
//     harga_setelah_diskon = sell_price × (1 - discount_rate/100)
//     subtotal_item        = harga_setelah_diskon × quantity
//     pajak_item           = subtotal_item × (tax_rate/100)
//
//   Total Transaksi:
//     subtotal_sebelum_diskon = Σ (sell_price × quantity)
//     total_diskon_produk     = Σ (sell_price × quantity × discount_rate/100)
//     subtotal                = subtotal_sebelum_diskon - total_diskon_produk
//     total_pajak             = Σ pajak_item
//     diskon_tambahan         = input kasir (Rp)
//     grand_total             = subtotal + total_pajak - diskon_tambahan
// ============================================================

const productSelect = document.getElementById('productSelect');
const itemQuantity = document.getElementById('itemQuantity');
const itemPrice = document.getElementById('itemPrice');
const itemStock = document.getElementById('itemStock');
const addItemBtn = document.getElementById('addItemBtn');
const cartBody = document.getElementById('cartBody');
const cartSubtotal = document.getElementById('cartSubtotal');
const cartDiscountProduct = document.getElementById('cartDiscountProduct');
const cartTax = document.getElementById('cartTax');
const cartDiscountExtra = document.getElementById('cartDiscountExtra');
const cartGrandTotal = document.getElementById('cartGrandTotal');
const cartInputs = document.getElementById('cartInputs');
const extraDiscountInput = document.getElementById('extraDiscount');
const productBadges = document.getElementById('productBadges');
const badgeTax = document.getElementById('badgeTax');
const badgeDiscount = document.getElementById('badgeDiscount');

// Array untuk menyimpan data item keranjang
let cartItems = [];

/**
 * Format angka ke format Rupiah Indonesia.
 * @param {number} value - Nilai yang akan diformat
 * @returns {string} String format Rupiah (contoh: "150.000")
 */
function formatRupiah(value) {
    return Math.round(value).toLocaleString('id-ID');
}

/**
 * Ubah tampilan harga, stok, dan badge pajak/diskon
 * ketika user memilih produk dari dropdown.
 */
function refreshProductInfo() {
    const selected = productSelect.options[productSelect.selectedIndex];
    if (!selected || !selected.value) {
        itemPrice.textContent = 'Rp 0';
        itemStock.textContent = '-';
        productBadges.style.display = 'none';
        return;
    }

    const price = Number(selected.dataset.price || 0);
    const stock = Number(selected.dataset.stock || 0);
    const unit = selected.dataset.unit || '';
    const tax = Number(selected.dataset.tax || 0);
    const discount = Number(selected.dataset.discount || 0);

    // Tampilkan harga dan stok produk
    itemPrice.textContent = `Rp ${formatRupiah(price)}`;
    itemStock.textContent = `${stock} ${unit}`;

    // Tampilkan badge pajak dan diskon jika ada
    badgeTax.innerHTML = `<i class="fas fa-receipt"></i> Pajak: ${tax}%`;
    badgeTax.style.display = tax > 0 ? 'inline-flex' : 'none';
    badgeDiscount.innerHTML = `<i class="fas fa-tag"></i> Diskon: ${discount}%`;
    badgeDiscount.style.display = discount > 0 ? 'inline-flex' : 'none';

    // Sembunyikan seluruh area badge jika tidak ada pajak maupun diskon
    if (tax === 0 && discount === 0) {
        productBadges.style.display = 'none';
    } else {
        productBadges.style.display = 'flex';
    }
}

/**
 * Hitung ulang ringkasan keranjang (subtotal, pajak, diskon, grand total)
 * dan perbarui tampilan di UI.
 */
function updateCartSummary() {
    // Hitung total sebelum diskon (harga asli × jumlah)
    let subtotalBeforeDiscount = 0;
    // Total diskon produk (dari discount_rate masing-masing item)
    let totalProductDiscount = 0;
    // Total pajak dari semua item
    let totalTax = 0;

    cartItems.forEach(item => {
        const rawSubtotal = item.price * item.quantity;
        const itemDiscount = rawSubtotal * (item.discount_rate / 100);
        const afterDiscount = rawSubtotal - itemDiscount;
        const itemTax = afterDiscount * (item.tax_rate / 100);

        subtotalBeforeDiscount += rawSubtotal;
        totalProductDiscount += itemDiscount;
        totalTax += itemTax;
    });

    // Subtotal setelah diskon produk
    const subtotalAfterDiscount = subtotalBeforeDiscount - totalProductDiscount;

    // Diskon tambahan dari kasir
    const extraDiscount = Number(extraDiscountInput.value || 0);

    // Grand total = subtotal setelah diskon + pajak - diskon tambahan
    const grandTotal = Math.max(0, subtotalAfterDiscount + totalTax - extraDiscount);

    // Update tampilan ringkasan
    cartSubtotal.textContent = `Rp ${formatRupiah(subtotalBeforeDiscount)}`;
    cartDiscountProduct.textContent = `- Rp ${formatRupiah(totalProductDiscount)}`;
    cartTax.textContent = `+ Rp ${formatRupiah(totalTax)}`;
    cartDiscountExtra.textContent = `- Rp ${formatRupiah(extraDiscount)}`;
    cartGrandTotal.textContent = `Rp ${formatRupiah(grandTotal)}`;
}

/**
 * Render ulang seluruh tabel keranjang dari data cartItems.
 * Juga membuat hidden inputs untuk dikirim ke server.
 */
function updateCartCountBadge() {
    const badge = document.getElementById('cartCount');
    if (!badge) return;
    const total = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    badge.textContent = total > 0 ? `${total} item` : '0 item';
}

function renderCart() {
    cartBody.innerHTML = '';
    cartInputs.innerHTML = '';

    updateCartCountBadge();

    if (cartItems.length === 0) {
        const row = document.createElement('tr');
        row.classList.add('empty-row');
        row.id = 'emptyRow';
        row.innerHTML = `<td colspan="4">
            <div class="cart-empty-state">
                <i class="fas fa-cart-shopping"></i>
                <p>Keranjang masih kosong</p>
                <small>Tambahkan produk dari panel kiri</small>
            </div>
        </td>`;
        cartBody.appendChild(row);
        updateCartSummary();
        return;
    }

    // Hitung total pajak dan diskon untuk hidden inputs
    let totalTax = 0;
    let totalDiscount = 0;

    cartItems.forEach((item, index) => {
        const rawSubtotal = item.price * item.quantity;
        const itemDiscount = rawSubtotal * (item.discount_rate / 100);
        const afterDiscount = rawSubtotal - itemDiscount;
        const itemTax = afterDiscount * (item.tax_rate / 100);

        totalDiscount += itemDiscount;
        totalTax += itemTax;

        // Buat baris tabel untuk item ini
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div style="font-weight:600;font-size:.85rem;">${item.name}</div>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">
                    Rp ${formatRupiah(item.price)} / satuan
                    ${item.discount_rate > 0 ? `<span class="cell-badge cell-badge-discount" style="margin-left:4px;">${item.discount_rate}%</span>` : ''}
                    ${item.tax_rate > 0 ? `<span class="cell-badge cell-badge-tax" style="margin-left:2px;">${item.tax_rate}% pajak</span>` : ''}
                </div>
            </td>
            <td class="text-center" style="font-weight:700;">${item.quantity}</td>
            <td class="text-right" style="font-weight:700;color:var(--primary);">Rp ${formatRupiah(afterDiscount)}</td>
            <td><button type="button" class="cart-remove" data-index="${index}"><i class="fas fa-trash"></i></button></td>
        `;
        cartBody.appendChild(row);

        // Hidden inputs: data item untuk dikirim ke server
        const fields = [
            { name: `items[${index}][product_id]`, value: item.product_id },
            { name: `items[${index}][quantity]`, value: item.quantity },
            { name: `items[${index}][price_at_sale]`, value: item.price },
            { name: `items[${index}][subtotal]`, value: Number(afterDiscount.toFixed(2)) },
        ];

        fields.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = f.name;
            input.value = f.value;
            cartInputs.appendChild(input);
        });
    });

    updateCartSummary();
}

/**
 * Hapus item dari keranjang berdasarkan indeks.
 * @param {number} index - Indeks item yang akan dihapus
 */
function removeCartItem(index) {
    cartItems.splice(index, 1);
    renderCart();
}

/**
 * Tambahkan item ke keranjang ketika tombol "Tambah ke Keranjang" ditekan.
 * Memvalidasi stok dan duplikasi produk.
 */
function addItemToCart() {
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
        alert('Silakan pilih produk terlebih dahulu.');
        return;
    }

    const productId = Number(selectedOption.value);
    const name = selectedOption.dataset.name || 'Produk tidak diketahui';
    const price = Number(selectedOption.dataset.price || 0);
    const stock = Number(selectedOption.dataset.stock || 0);
    const taxRate = Number(selectedOption.dataset.tax || 0);
    const discountRate = Number(selectedOption.dataset.discount || 0);
    const quantity = Number(itemQuantity.value || 1);

    // Validasi jumlah minimum
    if (quantity <= 0) {
        alert('Jumlah produk harus minimal 1.');
        return;
    }

    // Validasi ketersediaan stok
    if (quantity > stock) {
        alert('Jumlah melebihi stok yang tersedia.');
        return;
    }

    // Cek apakah produk sudah ada di keranjang (gabungkan jumlah)
    const existingIndex = cartItems.findIndex(item => item.product_id === productId);
    if (existingIndex !== -1) {
        const existing = cartItems[existingIndex];
        const newQty = existing.quantity + quantity;
        if (newQty > stock) {
            alert('Jumlah total melebihi stok yang tersedia.');
            return;
        }
        existing.quantity = newQty;
    } else {
        // Tambah item baru ke keranjang
        cartItems.push({
            product_id: productId,
            name,
            quantity,
            price,
            tax_rate: taxRate,
            discount_rate: discountRate,
        });
    }

    // Reset pilihan dan render ulang keranjang
    renderCart();
}

// ============================================================
// EVENT LISTENERS
// ============================================================

// Tangani klik tombol hapus pada tabel keranjang
cartBody.addEventListener('click', function (event) {
    if (!event.target.matches('.cart-remove')) {
        return;
    }
    const index = Number(event.target.dataset.index);
    removeCartItem(index);
});

// Perbarui info harga/stok/pajak/diskon ketika produk berubah
productSelect.addEventListener('change', refreshProductInfo);

// Tambah item ketika tombol ditekan
addItemBtn.addEventListener('click', addItemToCart);

// Update ringkasan ketika diskon tambahan berubah
extraDiscountInput.addEventListener('input', updateCartSummary);

// Set informasi awal ketika halaman dimuat
refreshProductInfo();
renderCart();
