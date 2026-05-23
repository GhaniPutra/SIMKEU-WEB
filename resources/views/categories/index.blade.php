{{-- ====================================================================
     Halaman: Manajemen Kategori — SIMKEU
     Deskripsi: Halaman untuk admin menambah, mengedit, dan menghapus
                kategori produk/stok barang. Kategori yang dibuat akan
                muncul di dropdown KATEGORI pada form tambah/edit produk.
     Extends: layouts.app (layout utama SIMKEU)
==================================================================== --}}
@extends('layouts.app')

@section('title', 'Kategori Produk — SIMKEU')
@section('page-title', 'Kategori Produk')
@section('breadcrumb', 'Manajemen / Produk & Stok / Kategori')

@section('css')
<style>
    /* ============================================================
       KPI CARDS — Kartu statistik ringkasan
    ============================================================ */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .kpi-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .kpi-icon.purple { background: rgba(79,70,229,.12); color: var(--primary); }
    .kpi-icon.teal   { background: rgba(6,182,212,.12);  color: var(--accent); }
    .kpi-icon.green  { background: rgba(16,185,129,.12); color: var(--success); }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    .kpi-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 4px;
        font-weight: 500;
    }

    /* ============================================================
       SECTION HEADER — Judul section + tombol aksi
    ============================================================ */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: var(--primary);
    }

    /* ============================================================
       SEARCH BAR
    ============================================================ */
    .search-form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.8rem;
        pointer-events: none;
    }

    .search-input {
        padding: 8px 12px 8px 32px;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.85rem;
        font-family: inherit;
        color: var(--text-primary);
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 240px;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79,70,229,.08);
    }

    /* ============================================================
       TOMBOL AKSI
    ============================================================ */
    .btn-primary-action {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 9px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-primary-action:hover {
        background: var(--primary-dark);
        color: white;
        transform: translateY(-1px);
    }

    /* ============================================================
       CATEGORY GRID — Kartu kategori
    ============================================================ */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .category-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s, transform 0.2s;
        overflow: hidden;
    }

    .category-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    /* Strip warna di kiri kartu */
    .category-card-accent {
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    .category-card-body {
        padding: 18px 20px 14px;
    }

    .category-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .category-icon-wrap {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, rgba(79,70,229,.12), rgba(6,182,212,.12));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: var(--primary);
        flex-shrink: 0;
    }

    .category-actions {
        display: flex;
        gap: 6px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border: 1px solid var(--border);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        cursor: pointer;
        background: white;
        color: var(--text-muted);
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-icon.edit:hover {
        background: rgba(79,70,229,.08);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-icon.delete:hover {
        background: rgba(239,68,68,.08);
        border-color: var(--danger);
        color: var(--danger);
    }

    .category-name {
        font-size: 0.975rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 5px;
        word-break: break-word;
    }

    .category-desc {
        font-size: 0.8rem;
        color: var(--text-muted);
        line-height: 1.5;
        margin-bottom: 12px;
        min-height: 20px;
        word-break: break-word;
    }

    .category-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px;
        border-top: 1px solid var(--border);
    }

    .product-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .product-count-badge .count-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        background: rgba(79,70,229,.10);
        color: var(--primary);
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .category-date {
        font-size: 0.72rem;
        color: var(--text-muted);
    }

    /* ============================================================
       EMPTY STATE
    ============================================================ */
    .empty-state {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 60px 24px;
        text-align: center;
    }

    .empty-icon {
        width: 72px;
        height: 72px;
        background: rgba(79,70,229,.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: var(--primary);
        margin: 0 auto 18px;
    }

    .empty-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .empty-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    /* ============================================================
       MODAL — Tambah & Edit Kategori
    ============================================================ */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.55);
        z-index: 2000;
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.2s ease;
    }

    .modal-overlay.active {
        display: flex;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    .modal-box {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,.22);
        width: 100%;
        max-width: 480px;
        animation: slideUp 0.25s ease;
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px 16px;
        border-bottom: 1px solid var(--border);
    }

    .modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .modal-title i {
        width: 32px;
        height: 32px;
        background: rgba(79,70,229,.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 0.9rem;
    }

    .modal-close {
        width: 32px;
        height: 32px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: var(--text-primary);
    }

    .modal-body {
        padding: 22px 24px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 24px 20px;
        border-top: 1px solid var(--border);
    }

    /* Form inside modal */
    .modal-form-group {
        margin-bottom: 18px;
    }

    .modal-form-group:last-child {
        margin-bottom: 0;
    }

    .modal-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
    }

    .required-mark {
        color: var(--danger);
        margin-left: 3px;
    }

    .modal-input,
    .modal-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.875rem;
        font-family: inherit;
        color: var(--text-primary);
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .modal-input:focus,
    .modal-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79,70,229,.08);
    }

    .modal-input.is-invalid {
        border-color: var(--danger);
    }

    .field-error {
        font-size: 0.75rem;
        color: var(--danger);
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Tombol batal di modal */
    .btn-modal-cancel {
        padding: 9px 18px;
        background: white;
        color: var(--text-muted);
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover {
        background: #f1f5f9;
        color: var(--text-primary);
    }

    /* Tombol simpan di modal */
    .btn-modal-submit {
        padding: 9px 22px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 9px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: background 0.2s;
    }

    .btn-modal-submit:hover {
        background: var(--primary-dark);
    }

    /* ============================================================
       MODAL HAPUS (Konfirmasi)
    ============================================================ */
    .delete-modal-icon {
        width: 60px;
        height: 60px;
        background: rgba(239,68,68,.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--danger);
        margin: 0 auto 14px;
    }

    .delete-modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 8px;
    }

    .delete-modal-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        text-align: center;
        line-height: 1.55;
    }

    .btn-danger {
        padding: 9px 22px;
        background: var(--danger);
        color: white;
        border: none;
        border-radius: 9px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: background 0.2s;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    /* ============================================================
       PAGINATION
    ============================================================ */
    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: 8px;
    }

    .pagination .page-link {
        border-radius: 8px;
        font-size: 0.85rem;
        color: var(--primary);
        border-color: var(--border);
        margin: 0 2px;
    }

    .pagination .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
    }
</style>
@endsection

@section('content')

{{-- ============================================================
     KPI / STATISTIK KATEGORI
============================================================ --}}
<div class="kpi-grid">
    {{-- Total Kategori --}}
    <div class="kpi-card">
        <div class="kpi-icon purple">
            <i class="fas fa-tags"></i>
        </div>
        <div>
            <div class="kpi-value">{{ $totalCategories }}</div>
            <div class="kpi-label">Total Kategori</div>
        </div>
    </div>

    {{-- Kategori dengan Produk --}}
    <div class="kpi-card">
        <div class="kpi-icon teal">
            <i class="fas fa-box-open"></i>
        </div>
        <div>
            <div class="kpi-value">{{ $totalCategorized }}</div>
            <div class="kpi-label">Kategori Berisi Produk</div>
        </div>
    </div>

    {{-- Kategori Kosong --}}
    <div class="kpi-card">
        <div class="kpi-icon green">
            <i class="fas fa-folder-open"></i>
        </div>
        <div>
            <div class="kpi-value">{{ $totalCategories - $totalCategorized }}</div>
            <div class="kpi-label">Kategori Kosong</div>
        </div>
    </div>
</div>

{{-- ============================================================
     SECTION HEADER: Judul + Cari + Tombol Tambah
============================================================ --}}
<div class="section-header">
    <div class="section-title">
        <i class="fas fa-tags"></i>
        Daftar Kategori
        @if($search)
            <span style="font-size:0.78rem;color:var(--text-muted);font-weight:500;">
                — hasil pencarian "{{ $search }}"
            </span>
        @endif
    </div>

    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        {{-- Form Pencarian --}}
        <form method="GET" action="{{ route('categories.index') }}" class="search-form">
            <div class="search-input-wrap">
                <i class="fas fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Cari kategori..."
                    value="{{ $search }}"
                >
            </div>
            <button type="submit" class="btn-primary-action" style="background:var(--accent);">
                <i class="fas fa-search"></i> Cari
            </button>
            @if($search)
                <a href="{{ route('categories.index') }}" class="btn-primary-action" style="background:#64748b;">
                    <i class="fas fa-x"></i>
                </a>
            @endif
        </form>

        {{-- Tombol Tambah Kategori --}}
        <button class="btn-primary-action" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>
</div>

{{-- ============================================================
     GRID KARTU KATEGORI
============================================================ --}}
@if($categories->count() > 0)
    <div class="category-grid">
        @foreach($categories as $category)
            <div class="category-card">
                {{-- Strip warna gradien atas --}}
                <div class="category-card-accent"></div>

                <div class="category-card-body">
                    <div class="category-card-top">
                        {{-- Ikon kategori --}}
                        <div class="category-icon-wrap">
                            <i class="fas fa-tag"></i>
                        </div>

                        {{-- Tombol edit & hapus --}}
                        <div class="category-actions">
                            <button
                                class="btn-icon edit"
                                title="Edit Kategori"
                                onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')"
                            >
                                <i class="fas fa-pen"></i>
                            </button>
                            <button
                                class="btn-icon delete"
                                title="Hapus Kategori"
                                onclick="openDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->products_count }})"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Nama kategori --}}
                    <div class="category-name">{{ $category->name }}</div>

                    {{-- Deskripsi (atau placeholder jika kosong) --}}
                    <div class="category-desc">
                        {{ $category->description ?: '— Tidak ada deskripsi —' }}
                    </div>

                    {{-- Footer: jumlah produk & tanggal dibuat --}}
                    <div class="category-card-footer">
                        <span class="product-count-badge">
                            <i class="fas fa-box"></i>
                            <span class="count-num">{{ $category->products_count }}</span>
                            Produk terkait
                        </span>
                        <span class="category-date">
                            {{ $category->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($categories->hasPages())
        <div class="pagination-wrap">
            {{ $categories->links() }}
        </div>
    @endif

@else
    {{-- Empty State --}}
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-tags"></i>
        </div>
        <div class="empty-title">
            @if($search)
                Tidak ada kategori yang cocok dengan "{{ $search }}"
            @else
                Belum Ada Kategori
            @endif
        </div>
        <div class="empty-desc">
            @if($search)
                Coba gunakan kata kunci yang berbeda.
            @else
                Tambahkan kategori pertama Anda untuk mulai mengelompokkan produk.
            @endif
        </div>
        @if(!$search)
            <button class="btn-primary-action" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Tambah Kategori Pertama
            </button>
        @endif
    </div>
@endif

{{-- ============================================================
     MODAL — TAMBAH KATEGORI
============================================================ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-plus"></i>
                Tambah Kategori Baru
            </div>
            <button class="modal-close" onclick="closeModal('addModal')" title="Tutup">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('categories.store') }}" id="addForm">
            @csrf
            <div class="modal-body">
                {{-- Field Nama Kategori --}}
                <div class="modal-form-group">
                    <label for="add_name" class="modal-label">
                        Nama Kategori <span class="required-mark">*</span>
                    </label>
                    <input
                        type="text"
                        id="add_name"
                        name="name"
                        class="modal-input {{ session('modal') === 'add' && $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Contoh: Minuman, Makanan, Elektronik..."
                        value="{{ session('modal') === 'add' ? old('name') : '' }}"
                        maxlength="100"
                        required
                        autofocus
                    >
                    @if(session('modal') === 'add' && $errors->has('name'))
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $errors->first('name') }}</p>
                    @endif
                </div>

                {{-- Field Deskripsi --}}
                <div class="modal-form-group">
                    <label for="add_description" class="modal-label">
                        Deskripsi <span style="color:var(--text-muted);font-weight:400;font-size:0.72rem;">(opsional)</span>
                    </label>
                    <textarea
                        id="add_description"
                        name="description"
                        class="modal-textarea"
                        placeholder="Deskripsi singkat kategori ini..."
                        maxlength="500"
                        rows="3"
                    >{{ session('modal') === 'add' ? old('description') : '' }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-modal-submit">
                    <i class="fas fa-floppy-disk"></i> Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     MODAL — EDIT KATEGORI
============================================================ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-pen"></i>
                Edit Kategori
            </div>
            <button class="modal-close" onclick="closeModal('editModal')" title="Tutup">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="" id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                {{-- Field Nama Kategori --}}
                <div class="modal-form-group">
                    <label for="edit_name" class="modal-label">
                        Nama Kategori <span class="required-mark">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_name"
                        name="name"
                        class="modal-input {{ session('modal') === 'edit' && $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Nama kategori..."
                        maxlength="100"
                        required
                    >
                    @if(session('modal') === 'edit' && $errors->has('name'))
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $errors->first('name') }}</p>
                    @endif
                </div>

                {{-- Field Deskripsi --}}
                <div class="modal-form-group">
                    <label for="edit_description" class="modal-label">
                        Deskripsi <span style="color:var(--text-muted);font-weight:400;font-size:0.72rem;">(opsional)</span>
                    </label>
                    <textarea
                        id="edit_description"
                        name="description"
                        class="modal-textarea"
                        placeholder="Deskripsi singkat kategori ini..."
                        maxlength="500"
                        rows="3"
                    ></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-modal-submit">
                    <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     MODAL — KONFIRMASI HAPUS
============================================================ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-trash" style="background:rgba(239,68,68,.1);color:var(--danger);"></i>
                Hapus Kategori
            </div>
            <button class="modal-close" onclick="closeModal('deleteModal')" title="Tutup">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" style="text-align:center;padding:30px 24px;">
            <div class="delete-modal-icon">
                <i class="fas fa-trash"></i>
            </div>
            <div class="delete-modal-title" id="deleteModalTitle">Hapus kategori ini?</div>
            <div class="delete-modal-desc" id="deleteModalDesc">
                Tindakan ini tidak dapat dibatalkan.
            </div>
        </div>

        <div class="modal-footer" style="justify-content:center;">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('deleteModal')">
                <i class="fas fa-xmark"></i> Batal
            </button>
            <form method="POST" id="deleteForm" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash"></i> Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    // ============================================================
    // MODAL HELPERS
    // ============================================================

    /**
     * Membuka modal dengan ID tertentu
     * @param {string} id - ID elemen modal overlay
     */
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Menutup modal dengan ID tertentu
     * @param {string} id - ID elemen modal overlay
     */
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    // Tutup modal saat klik di luar kotak
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => {
                closeModal(m.id);
            });
        }
    });

    // ============================================================
    // MODAL TAMBAH KATEGORI
    // ============================================================

    /**
     * Membuka modal tambah kategori
     */
    function openAddModal() {
        openModal('addModal');
        // Fokus ke field nama setelah animasi selesai
        setTimeout(() => document.getElementById('add_name').focus(), 100);
    }

    // ============================================================
    // MODAL EDIT KATEGORI
    // ============================================================

    /**
     * Membuka modal edit dan mengisi field dengan data kategori yang dipilih
     * @param {number} id          - ID kategori
     * @param {string} name        - Nama kategori saat ini
     * @param {string} description - Deskripsi kategori saat ini
     */
    function openEditModal(id, name, description) {
        // Set action form ke route update kategori yang dipilih
        document.getElementById('editForm').action = '/categories/' + id;

        // Isi field dengan data kategori
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;

        openModal('editModal');
        setTimeout(() => document.getElementById('edit_name').focus(), 100);
    }

    // ============================================================
    // MODAL HAPUS KATEGORI
    // ============================================================

    /**
     * Membuka modal konfirmasi hapus
     * @param {number} id           - ID kategori
     * @param {string} name         - Nama kategori
     * @param {number} productCount - Jumlah produk yang menggunakan kategori ini
     */
    function openDeleteModal(id, name, productCount) {
        // Set action form hapus
        document.getElementById('deleteForm').action = '/categories/' + id;

        // Update teks konfirmasi
        document.getElementById('deleteModalTitle').textContent = 'Hapus kategori "' + name + '"?';

        let desc = 'Tindakan ini tidak dapat dibatalkan.';
        if (productCount > 0) {
            desc += ' <strong>' + productCount + ' produk</strong> yang menggunakan kategori ini akan menjadi tanpa kategori.';
        }
        document.getElementById('deleteModalDesc').innerHTML = desc;

        openModal('deleteModal');
    }

    // ============================================================
    // AUTO-BUKA MODAL jika ada validasi error (setelah submit)
    // ============================================================
    @if(session('modal') === 'add' && $errors->any())
        openAddModal();
    @endif

    @if(session('modal') === 'edit' && $errors->any())
        // Data edit diambil dari session jika ada
        @if(session('edit_id'))
            openEditModal(
                {{ session('edit_id') }},
                '{{ addslashes(old("name", "")) }}',
                '{{ addslashes(old("description", "")) }}'
            );
        @endif
    @endif
</script>
@endsection
