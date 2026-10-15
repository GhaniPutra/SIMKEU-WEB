/* Status alokasi stok pegawai
 * Memperbarui ikon alokasi tertunda secara berkala pada halaman pengelolaan pegawai.
 */
(() => {
            const page = document.querySelector('.employee-manage');
            if (!page) return;

            window.refreshPendingStockAllocations = async () => {
                const response = await fetch(page.dataset.pendingAllocationsUrl, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                const result = await response.json();
                if (!response.ok || result.success !== true || !Array.isArray(result.data)) {
                    throw new Error(result.message || 'Gagal memperbarui status alokasi stok.');
                }

                const employeeId = page?.dataset.employeeId;
                const pendingProductIds = new Set(result.data
                    .filter((allocation) => String(allocation.employee_id) === String(employeeId))
                    .map((allocation) => String(allocation.product_id)));

                document.querySelectorAll('[data-pending-allocation-icon]').forEach((icon) => {
                    icon.hidden = !pendingProductIds.has(icon.dataset.productId);
                });
            };

            const refresh = () => window.refreshPendingStockAllocations()
                .catch((error) => console.error(error));
            refresh();
            window.setInterval(refresh, 10000);
        })();
