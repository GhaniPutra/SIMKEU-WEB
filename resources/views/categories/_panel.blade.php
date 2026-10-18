<div class="kpi-grid category-kpi-grid">
    <div class="kpi-card primary fade-up">
        <div class="kpi-top"><span class="kpi-icon"><i class="fas fa-tags"></i></span><span class="kpi-label">Total Kategori</span></div>
        <div class="kpi-value">{{ number_format($totalCategories) }}</div>
        <div class="kpi-sub">Jumlah kategori produk saat ini</div>
    </div>
    <div class="kpi-card success fade-up">
        <div class="kpi-top"><span class="kpi-icon"><i class="fas fa-box-open"></i></span><span class="kpi-label">Kategori Berisi Produk</span></div>
        <div class="kpi-value">{{ number_format($totalCategorized) }}</div>
        <div class="kpi-sub">Kategori dengan setidaknya satu produk</div>
    </div>
    <div class="kpi-card warning fade-up">
        <div class="kpi-top"><span class="kpi-icon"><i class="fas fa-folder-minus"></i></span><span class="kpi-label">Kategori Kosong</span></div>
        <div class="kpi-value">{{ number_format($totalCategories - $totalCategorized) }}</div>
        <div class="kpi-sub">Kategori tanpa produk saat ini</div>
    </div>
</div>

<p class="section-title"><i class="fas fa-tags"></i> Daftar Kategori</p>
<div class="content-card fade-up">
    <div class="content-card-header">
        <span class="content-card-title"><i class="fas fa-table-list"></i> Kategori Produk</span>
        @if(Auth::user()->hasPermission('categories.create'))<button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#categoryAddModal">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>@endif
    </div>
    <form method="GET" action="{{ route('products.index') }}" class="toolbar">
        <input type="hidden" name="tab" value="kategori">
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="category_id" value="{{ $categoryFilter }}">
        @if(request('products_page'))
            <input type="hidden" name="products_page" value="{{ request('products_page') }}">
        @endif
        <div class="toolbar-search">
            <i class="fas fa-magnifying-glass search-icon"></i>
            <input type="search" name="category_search" placeholder="Cari nama atau deskripsi kategori..."
                   value="{{ $categorySearch }}" aria-label="Cari kategori">
        </div>
        <button type="submit" class="btn-add"><i class="fas fa-search"></i> Cari</button>
        @if($categorySearch)
            <a href="{{ route('products.index', array_merge(request()->except(['category_search', 'categories_page']), ['tab' => 'kategori'])) }}"
               class="btn-add category-reset reset-button"><i class="fas fa-xmark"></i> Reset</a>
        @endif
    </form>

    <div class="content-card-body">
        <div class="category-table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th style="width:40px;">#</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah Produk</th><th>Dibuat</th><th style="width:100px;">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($categoryList as $index => $category)
                        <tr>
                            <td style="color:var(--text-muted);font-size:.75rem;">{{ $categoryList->firstItem() + $index }}</td>
                            <td><div class="category-table-name"><i class="fas fa-tag"></i> {{ $category->name }}</div></td>
                            <td>{{ $category->description ?: '—' }}</td>
                            <td><span class="category-product-count">{{ number_format($category->products_count) }}</span></td>
                            <td>{{ $category->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="category-actions">
                                    @if(Auth::user()->hasPermission('categories.update'))<button type="button" class="btn-action edit category-edit"
                                            title="Edit kategori"
                                            data-bs-toggle="modal" data-bs-target="#categoryEditModal"
                                            data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}"
                                            data-description="{{ $category->description ?? '' }}"
                                            data-update-url="{{ route('categories.update', array_merge(request()->query(), ['category' => $category->id])) }}">
                                        <i class="fas fa-pen"></i>
                                    </button>@endif
                                    @if(Auth::user()->hasPermission('categories.delete'))<button type="button" class="btn-action delete category-delete"
                                            title="Hapus kategori"
                                            data-bs-toggle="modal" data-bs-target="#categoryDeleteModal"
                                            data-name="{{ $category->name }}"
                                            data-products-count="{{ $category->products_count }}"
                                            data-delete-url="{{ route('categories.destroy', array_merge(request()->query(), ['category' => $category->id])) }}">
                                        <i class="fas fa-trash-can"></i>
                                    </button>@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="category-empty-row">
                            <i class="fas fa-tags"></i>
                            @if($categorySearch)
                                Tidak ada kategori yang cocok dengan "{{ $categorySearch }}".
                            @else
                                Belum ada kategori. Tambahkan kategori pertama.
                            @endif
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($categoryList->hasPages())
        <div class="pagination-wrapper">
            <span class="pagination-info">
                Menampilkan {{ $categoryList->firstItem() }}–{{ $categoryList->lastItem() }}
                dari {{ $categoryList->total() }} kategori
            </span>
            {{ $categoryList->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@if(Auth::user()->hasPermission('categories.create'))<div class="modal fade category-modal" id="categoryAddModal" tabindex="-1" aria-labelledby="categoryAddTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('categories.store', request()->query()) }}">
                @csrf
                <input type="hidden" name="_category_modal" value="add">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="categoryAddTitle"><i class="fas fa-plus"></i> Tambah Kategori</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <label class="category-modal-label" for="categoryAddName">Nama Kategori <span>*</span></label>
                    <input id="categoryAddName" class="form-control category-modal-input" type="text" name="name"
                           value="{{ old('_category_modal') === 'add' ? old('name') : '' }}"
                           maxlength="100" required>
                    <label class="category-modal-label" for="categoryAddDescription">Deskripsi (opsional)</label>
                    <textarea id="categoryAddDescription" class="form-control category-modal-input" name="description"
                              maxlength="500" rows="3">{{ old('_category_modal') === 'add' ? old('description') : '' }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-modal-save"><i class="fas fa-floppy-disk"></i><span>Simpan Kategori</span></button>
                </div>
            </form>
        </div>
    </div>
</div>@endif

@if(Auth::user()->hasPermission('categories.update'))<div class="modal fade category-modal" id="categoryEditModal" tabindex="-1" aria-labelledby="categoryEditTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="categoryEditForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_category_modal" value="edit">
                <input type="hidden" name="_category_id" value="{{ old('_category_modal') === 'edit' ? old('_category_id') : '' }}">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="categoryEditTitle"><i class="fas fa-pen"></i> Edit Kategori</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <label class="category-modal-label" for="categoryEditName">Nama Kategori <span>*</span></label>
                    <input id="categoryEditName" class="form-control category-modal-input" type="text" name="name"
                           value="{{ old('_category_modal') === 'edit' ? old('name') : '' }}"
                           maxlength="100" required>
                    <label class="category-modal-label" for="categoryEditDescription">Deskripsi (opsional)</label>
                    <textarea id="categoryEditDescription" class="form-control category-modal-input" name="description"
                              maxlength="500" rows="3">{{ old('_category_modal') === 'edit' ? old('description') : '' }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-modal-save"><i class="fas fa-floppy-disk"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>@endif

@if(Auth::user()->hasPermission('categories.delete'))<div class="modal fade category-modal" id="categoryDeleteModal" tabindex="-1" aria-labelledby="categoryDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="categoryDeleteForm">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="categoryDeleteTitle"><i class="fas fa-trash-can"></i> Hapus Kategori</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body category-delete-message">
                    <p id="categoryDeleteDescription">Apakah Anda yakin ingin menghapus kategori ini?</p>
                    <p class="category-delete-note">Kategori pada produk terkait akan dikosongkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-danger"><i class="fas fa-trash-can"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>@endif

<div id="category-modal-state" data-modal="{{ old('_category_modal') }}"
     data-id="{{ old('_category_id') }}" hidden></div>
