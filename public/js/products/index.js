/* =============================================================
   File: public/js/products/index.js
   Deskripsi: Skrip interaksi untuk halaman Daftar Produk & Stok
   Catatan: Semua fungsi dan blok diberi komentar dalam Bahasa Indonesia
   Agar mudah dipahami dan di-maintain.
   ============================================================= */

// Jalankan kode saat DOM sudah siap agar elemen tersedia
document.addEventListener('DOMContentLoaded', function () {
    // Ambil semua elemen dengan kelas 'fade-up' untuk animasi masuk
    const fadeEls = document.querySelectorAll('.fade-up');

    // Terapkan warna avatar produk berdasarkan atribut data-hue (di-generate server-side)
    document.querySelectorAll('.product-avatar[data-hue]').forEach(el => {
        const hue = el.dataset.hue;
        if (hue !== undefined && hue !== null && hue !== '') {
            el.style.background = 'hsl(' + hue + ', 60%, 50%)';
        }
    });

    // Buat IntersectionObserver untuk mendeteksi saat elemen terlihat di viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            // Jika elemen muncul di viewport
            if (entry.isIntersecting) {
                // Tambahkan kelas 'visible' untuk memicu transisi CSS
                entry.target.classList.add('visible');
                // Berhenti mengamati elemen ini agar animasi berjalan sekali saja
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 }); // Trigger saat 5% elemen terlihat

    // Observasi setiap elemen dengan delay kecil agar animasi tidak serentak
    fadeEls.forEach((el, i) => {
        setTimeout(() => observer.observe(el), i * 60);
    });

    // Ambil elemen modal overlay untuk penyesuaian stok
    const stockModal = document.getElementById('stockModal');
    // Ambil form penyesuaian stok
    const adjustStockForm = document.getElementById('adjustStockForm');
    // Ambil input jumlah penyesuaian stok
    const stockAdjustInput = document.getElementById('stockAdjustInput');
    // Ambil elemen tempat menampilkan info produk di modal
    const modalProductInfo = document.getElementById('modal-product-info');

    // Pastikan elemen modal ada sebelum menambahkan event listener
    if (stockModal) {
        // Jika user klik di luar kotak modal (di overlay), tutup modal
        stockModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockModal();
            }
        });
    }

    // Tangani penekanan tombol Escape untuk menutup modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStockModal();
        }
    });

    // Fungsi untuk membuka modal penyesuaian stok dan mengisi data produk
    window.openStockModal = function (productId, productName, currentStock, unit) {
        // Isi informasi produk pada subjudul modal
        if (modalProductInfo) {
            modalProductInfo.textContent = productName + ' — Stok saat ini: ' + currentStock + ' ' + unit;
        }

        // Set action form ke endpoint penyesuaian stok yang sesuai
        if (adjustStockForm) {
            adjustStockForm.action = '/products/' + productId + '/adjust-stock';
        }

        // Kosongkan input agar tidak ada nilai lama
        if (stockAdjustInput) {
            stockAdjustInput.value = '';
        }

        // Tampilkan modal dengan menambahkan kelas 'open'
        if (stockModal) {
            stockModal.classList.add('open');
            // Fokus ke input setelah sedikit delay agar fokus bekerja
            setTimeout(() => { if (stockAdjustInput) stockAdjustInput.focus(); }, 100);
        }
    };

    // Fungsi untuk menutup modal penyesuaian stok
    window.closeStockModal = function () {
        if (stockModal) {
            stockModal.classList.remove('open');
        }
    };

    // Fungsi konfirmasi penghapusan produk
    // Event delegation: tangani klik pada tombol stock dan delete
    // Tombol stock menggunakan data-attributes: data-product-id, data-product-name, data-product-stock, data-product-unit
    document.querySelectorAll('.btn-action.stock').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.productId;
            const name = this.dataset.productName;
            const stock = this.dataset.productStock;
            const unit = this.dataset.productUnit;
            // Panggil fungsi pembuka modal dengan tipe data yang sesuai
            window.openStockModal(id, name, parseInt(stock, 10) || 0, unit);
        });
    });

    // Tombol delete menggunakan data-delete-form-id untuk mengidentifikasi form yang harus disubmit
    document.querySelectorAll('.btn-action.delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const formId = this.dataset.deleteFormId;
            const name = this.dataset.productName;
            const confirmed = confirm(
                'Apakah Anda yakin ingin menghapus produk "' + name + '"?\n\n' +
                'Tindakan ini tidak dapat dibatalkan.'
            );
            if (confirmed && formId) {
                const form = document.getElementById(formId);
                if (form) form.submit();
            }
        });
    });

    // Tangani klik tombol batal di modal (class .btn-modal-cancel)
    document.querySelectorAll('.btn-modal-cancel').forEach(btn => {
        btn.addEventListener('click', function () {
            window.closeStockModal();
        });
    });

});
