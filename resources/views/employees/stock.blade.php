@extends('layouts.app')

@section('title', 'Atur Stok Pegawai - SIMKEU')
@section('page-title', 'Distribusi Stok: ' . $employee->name)
@section('breadcrumb', 'Pegawai / Atur Stok')


@section('css')
    <style>
        /* ── Layout ── */
        .stock-page { 
            display: grid; 
            gap: 28px;
            animation: fade-up 0.6s ease;
        }
        .stock-header.fade-up { animation-delay: 0.1s; }

        /* ── Header ── */
        .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        .stock-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stock-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0 0 8px;
            line-height: 1.2;
        }
        .stock-subtitle {
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.7;
            max-width: 640px;
            margin: 0;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition);
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
        }
        .btn-back:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* ── Summary Cards ── (Dashboard-aligned) */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: all var(--transition);
        }
        .summary-card:hover, .summary-card.fade-up {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
        }
        .summary-card.c-products::before { background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
        .summary-card.c-master::before  { background: linear-gradient(90deg, var(--accent), #0ea5e9); }
        .summary-card.c-employee::before { background: linear-gradient(90deg, var(--success), #34d399); }
        .summary-card.c-lowstock::before { background: linear-gradient(90deg, var(--warning), var(--danger)); }

        .sc-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.125rem;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }
        .c-products .sc-icon  { background: rgba(var(--primary-rgb, 79 70 229), 0.12);  color: var(--primary); }
        .c-master .sc-icon    { background: rgba(59,130,246,0.12); color: var(--accent); }
        .c-employee .sc-icon  { background: rgba(var(--success-rgb, 16 185 129), 0.12); color: var(--success); }
        .c-lowstock .sc-icon  { background: rgba(var(--warning-rgb, 245 158 11), 0.14); color: var(--warning); }

        .sc-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .sc-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 8px;
        }
        .sc-note {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ── Table Card ── (Dashboard content-card style) */
        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            animation: fade-up 0.6s ease 0.2s both;
        }
        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid var(--border);
            gap: 16px;
            flex-wrap: wrap;
        }
        .table-card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .table-card-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .table-scroll { overflow-x: auto; }

        /* ── Table ── (Consistent with dashboard data-table) */
        .stock-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
        }
        .stock-table th {
            padding: 16px 20px;
            background: rgba(var(--primary-rgb, 79 70 229), 0.04);
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
            text-align: left;
            white-space: nowrap;
        }
        .stock-table td {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        .stock-table tbody tr { 
            transition: background var(--transition); 
        }
        .stock-table tbody tr:hover { 
            background: rgba(var(--primary-rgb, 79 70 229), 0.025); 
        }
        .stock-table tbody tr:last-child td { border-bottom: none; }

        /* ── Product cell ── */
        .product-name { font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .product-meta { font-size: 0.85rem; color: var(--text-muted); }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .badge-master   { background: rgba(59,130,246,0.12);  color: var(--accent); }
        .badge-employee { background: rgba(var(--success-rgb, 16 185 129), 0.12);  color: var(--success); }
        .badge-low      { background: rgba(var(--warning-rgb, 245 158 11), 0.15); color: var(--warning); }
        .badge-zero     { background: rgba(var(--danger-rgb, 239 68 68), 0.12);  color: var(--danger); }

        /* ── Stock bar ── */
        .stock-bar-wrap { margin-top: 8px; }
        .stock-bar {
            height: 6px;
            border-radius: 99px;
            background: var(--border);
            overflow: hidden;
            width: 90px;
        }
        .stock-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--success), #34d399);
            transition: width 0.5s ease;
            box-shadow: 0 2px 8px rgba(var(--success-rgb, 16 185 129), 0.3);
        }
        .stock-bar-fill.low   { background: linear-gradient(90deg, var(--warning), #fbbf24); }
        .stock-bar-fill.empty { background: var(--danger); box-shadow: 0 2px 8px rgba(var(--danger-rgb, 239 68 68), 0.3); }

        /* ── Actions ── (btn-qa aligned) */
        .actions-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
        }
        .qty-input {
            width: 76px;
            padding: 10px 12px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--body-bg);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
            transition: all var(--transition);
        }
        .qty-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 79 70 229), 0.1);
        }
        .btn-give, .btn-pull {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: var(--radius);
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all var(--transition);
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
        }
        .btn-give {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }
        .btn-give:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .btn-pull {
            background: rgba(var(--danger-rgb, 239 68 68), 0.08);
            color: var(--danger);
            border: 1px solid rgba(var(--danger-rgb, 239 68 68), 0.2);
        }
        .btn-pull:hover:not(:disabled) {
            background: var(--danger);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .btn-give:disabled, .btn-pull:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* ── Footer ── */
        .table-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 0.875rem;
            color: var(--text-muted);
            background: rgba(var(--primary-rgb, 79 70 229), 0.02);
        }
        .footer-hint { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            color: var(--primary);
            font-weight: 500;
        }

        /* ── Toast ── (Enhanced dashboard style) */
        #stock-toast {
            position: fixed;
            bottom: 32px;
            right: 32px;
            z-index: 9999;
            min-width: 320px;
            max-width: 400px;
            padding: 20px 24px;
            border-radius: var(--radius);
            background: var(--card-bg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1);
            pointer-events: none;
        }
        #stock-toast.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        #stock-toast .toast-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }
        #stock-toast.success .toast-icon { 
            background: rgba(var(--success-rgb, 16 185 129), 0.12); 
            color: var(--success); 
        }
        #stock-toast.error .toast-icon { 
            background: rgba(var(--danger-rgb, 239 68 68), 0.12); 
            color: var(--danger); 
        }
        .toast-body { flex: 1; min-width: 0; }
        .toast-title { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .toast-msg { font-size: 0.875rem; color: var(--text-muted); line-height: 1.5; }

        /* ── Animations ── */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes numPop {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.3); color: var(--primary); }
            100% { transform: scale(1); }
        }
        .num-updated, .fade-up { animation: fade-up 0.6s ease; }
        .spin {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .summary-grid { grid-template-columns: 1fr; }
            .stock-header { flex-direction: column; align-items: stretch; text-align: center; }
            .btn-back { justify-content: center; }
        }
    </style>
@endsection


@section('content')
@php
    $pageMasterStock   = 0;
    $pageEmployeeStock = 0;
    $lowStockCount     = 0;
    foreach ($products as $product) {
        $pageMasterStock += $product->stock;
        $empProd = $product->users->first();
        $pageEmployeeStock += $empProd ? $empProd->pivot->stock : 0;
        if ($product->stock > 0 && $product->stock <= 10) $lowStockCount++;
    }
@endphp

<div class="stock-page">

    {{-- ── Header ── --}}
    <div class="stock-header">
        <div>
            <div class="stock-eyebrow"><i class="fas fa-boxes-stacking"></i> Distribusi Stok Pegawai</div>
            <h1 class="stock-title">{{ $employee->name }}</h1>
            <p class="stock-subtitle">
                Kelola distribusi stok dari gudang utama ke pegawai. Berikan atau tarik stok per produk secara real-time tanpa perlu refresh halaman.
            </p>
        </div>

    <a href="{{ route('employees.index') }}" class="btn-back fade-up">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    </div>

    {{-- ── Summary Cards ── --}}
    <div class="summary-grid">
        <div class="summary-card c-products">
            <div class="sc-icon"><i class="fas fa-layer-group"></i></div>
            <div class="sc-label">Total Produk</div>
            <div class="sc-value">{{ $products->total() }}</div>
            <div class="sc-note">{{ $products->count() }} ditampilkan di halaman ini</div>
        </div>
        <div class="summary-card c-master">
            <div class="sc-icon"><i class="fas fa-warehouse"></i></div>
            <div class="sc-label">Stok Gudang</div>
            <div class="sc-value">{{ $pageMasterStock }}</div>
            <div class="sc-note">Total stok master halaman ini</div>
        </div>
        <div class="summary-card c-employee">
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
            <div class="sc-label">Stok Pegawai</div>
            <div class="sc-value">{{ $pageEmployeeStock }}</div>
            <div class="sc-note">Total stok dimiliki {{ $employee->name }}</div>
        </div>
        <div class="summary-card c-lowstock">
            <div class="sc-icon"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="sc-label">Stok Rendah</div>
            <div class="sc-value">{{ $lowStockCount }}</div>
            <div class="sc-note">Produk dengan gudang ≤ 10 unit</div>
        </div>
    </div>

    {{-- ── Table Card ── --}}
    <div class="table-card">
        <div class="table-card-header">
            <div>

    <div class="table-card-title"><i class="fas fa-table-list"></i> Daftar Produk & Stok</div>

                <div class="table-card-meta">Halaman {{ $products->currentPage() }} · Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk</div>
            </div>
        </div>

        <div class="table-scroll">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok Gudang</th>
                        <th>Stok Pegawai</th>
                        <th>Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $empProd  = $product->users->first();
                            $empStock = $empProd ? $empProd->pivot->stock : 0;
                            $isLow    = $product->stock > 0 && $product->stock <= 10;
                            $isEmpty  = $product->stock === 0;
                            $barPct   = $product->stock > 0 ? min(100, round($empStock / ($product->stock + $empStock) * 100)) : 0;
                        @endphp
                        <tr id="row-{{ $product->id }}">
                            <td>
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-meta">{{ $product->unit }} &middot; {{ $product->category ? $product->category->name : 'Tanpa kategori' }}</div>
                            </td>
                            <td>
                                <span style="font-size:0.82rem;color:var(--text-muted);">
                                    {{ $product->category ? $product->category->name : '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-master" id="master-stock-{{ $product->id }}">
                                    <i class="fas fa-warehouse" style="font-size:0.68rem;"></i>
                                    {{ $product->stock }}
                                </span>
                                @if($isEmpty)
                                    <span class="badge badge-zero" style="margin-top:4px;display:inline-flex;"><i class="fas fa-xmark"></i> Kosong</span>
                                @elseif($isLow)
                                    <span class="badge badge-low" style="margin-top:4px;display:inline-flex;"><i class="fas fa-exclamation"></i> Rendah</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-employee" id="emp-stock-{{ $product->id }}">
                                    <i class="fas fa-user" style="font-size:0.68rem;"></i>
                                    {{ $empStock }}
                                </span>
                                <div class="stock-bar-wrap">
                                    <div class="stock-bar">
                                        <div class="stock-bar-fill {{ $isLow ? 'low' : ($isEmpty ? 'empty' : '') }}"
                                             id="bar-{{ $product->id }}"
                                             style="width:{{ $barPct }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="actions-wrap">
                                    <input type="number"
                                           id="amount-{{ $product->id }}"
                                           class="qty-input"
                                           min="1"
                                           value="1"
                                           aria-label="Jumlah transfer">
                                    <button type="button"
                                            class="btn-give"
                                            id="btn-give-{{ $product->id }}"
                                            onclick="transferStock({{ $product->id }}, 'push')"
                                            {{ $isEmpty ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-right"></i> Berikan
                                    </button>
                                    <button type="button"
                                            class="btn-pull"
                                            id="btn-pull-{{ $product->id }}"
                                            onclick="transferStock({{ $product->id }}, 'pull')"
                                            {{ $empStock <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-left"></i> Tarik
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-footer">

    <div class="footer-hint">
        <i class="fas fa-circle-info"></i>
        Perubahan stok berlaku secara langsung tanpa reload halaman.
    </div>

            <div>{{ $lowStockCount }} produk stok rendah di halaman ini</div>
        </div>
    </div>

    {{-- ── Pagination ── --}}
    <div class="pagination-wrap">{{ $products->links() }}</div>

</div>

{{-- ── Toast Notification ── --}}
<div id="stock-toast">
    <div class="toast-icon"><i class="fas fa-check" id="toast-icon-el"></i></div>
    <div class="toast-body">
        <div class="toast-title" id="toast-title">Berhasil</div>
        <div class="toast-msg" id="toast-msg">Transfer stok berhasil diproses.</div>
    </div>
</div>
@endsection

@section('js')
<script>
const employeeId = {{ $employee->id }};
let toastTimer = null;

function showToast(type, title, msg) {
    const toast = document.getElementById('stock-toast');
    const icon  = document.getElementById('toast-icon-el');
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-msg').textContent   = msg;
    toast.className = type; // 'success' | 'error'
    icon.className  = type === 'success' ? 'fas fa-check' : 'fas fa-xmark';
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
}

function setLoading(productId, loading) {
    const btnGive = document.getElementById(`btn-give-${productId}`);
    const btnPull = document.getElementById(`btn-pull-${productId}`);
    if (loading) {
        btnGive.disabled = true;
        btnPull.disabled = true;
        btnGive.innerHTML = '<span class="spin"></span>';
        btnPull.innerHTML = '<span class="spin"></span>';
    } else {
        btnGive.innerHTML = '<i class="fas fa-arrow-right"></i> Berikan';
        btnPull.innerHTML = '<i class="fas fa-arrow-left"></i> Tarik';
    }
}

function animateBadge(el) {
    el.classList.remove('num-updated');
    void el.offsetWidth; // reflow
    el.classList.add('num-updated');
}

async function transferStock(productId, action) {
    const amountInput = document.getElementById(`amount-${productId}`);
    let amount = parseInt(amountInput.value);

    if (isNaN(amount) || amount <= 0) {
        showToast('error', 'Input Tidak Valid', 'Masukkan jumlah minimal 1.');
        return;
    }

    if (action === 'pull') amount = -amount;

    setLoading(productId, true);

    try {
        const response = await fetch(`/employees/${employeeId}/stock/transfer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ product_id: productId, amount })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            const masterEl = document.getElementById(`master-stock-${productId}`);
            const empEl    = document.getElementById(`emp-stock-${productId}`);
            const barEl    = document.getElementById(`bar-${productId}`);
            const btnGive  = document.getElementById(`btn-give-${productId}`);
            const btnPull  = document.getElementById(`btn-pull-${productId}`);

            // Update numbers
            masterEl.innerHTML = `<i class="fas fa-warehouse" style="font-size:.68rem;"></i> ${result.master_stock}`;
            empEl.innerHTML    = `<i class="fas fa-user" style="font-size:.68rem;"></i> ${result.employee_stock}`;
            animateBadge(masterEl);
            animateBadge(empEl);

            // Update bar
            const total  = result.master_stock + result.employee_stock;
            const barPct = total > 0 ? Math.min(100, Math.round(result.employee_stock / total * 100)) : 0;
            if (barEl) barEl.style.width = barPct + '%';

            // Re-enable buttons based on new values
            btnGive.disabled = result.master_stock <= 0;
            btnPull.disabled = result.employee_stock <= 0;

            amountInput.value = 1;

            const dir = action === 'push' ? 'diberikan ke pegawai' : 'ditarik dari pegawai';
            showToast('success', 'Transfer Berhasil', `${Math.abs(amount)} unit berhasil ${dir}.`);
        } else {
            showToast('error', 'Transfer Gagal', result.message || 'Terjadi kesalahan pada server.');
        }
    } catch (err) {
        console.error(err);
        showToast('error', 'Koneksi Error', 'Gagal menghubungi server. Periksa koneksi Anda.');
    } finally {
        setLoading(productId, false);
    }
}
</script>
@endsection
