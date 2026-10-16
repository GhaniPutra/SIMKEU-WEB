@extends('layouts.app')

@section('title', 'Setup Toko - SIMKEU')

@section('css')
    <style>
        /* Sembunyikan sidebar dan topbar untuk fokus ke form setup */
        #sidebar, #topbar {
            display: none !important;
        }

        /* Hapus margin dari main-content agar fullscreen */
        #main-content {
            margin-left: 0 !important;
            margin-top: 0 !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .setup-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 20px;
            background: var(--body-bg);
        }

        .setup-box {
            background: #f0f7fe;
            border-radius: 6px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.10);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .setup-header h1 {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .setup-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Styling input form */
        .form-control {
            padding: 12px 15px;
            border: 1px solid var(--input);
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all var(--transition);
            background: #f0f7fe;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb),0.12);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
        }

        /* Styling tombol */
        .btn-setup {
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            border: none;
            border-radius: 6px;
            color: #f0f7fe;
            font-weight: 600;
            font-size: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--primary-rgb),0.3);
            color: #f0f7fe;
        }

        .input-group-text {
            background-color: var(--card-bg);
            border: 1px solid var(--input);
            color: var(--text-muted);
        }
        
        .setup-icon-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .setup-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--icon-on-primary);
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(var(--primary-rgb),0.4);
        }
    </style>
@endsection

@section('content')
<div class="setup-container">
    <div class="setup-box">
        <div class="setup-icon-wrapper">
            <div class="setup-icon">
                <i class="fas fa-store"></i>
            </div>
        </div>
        <div class="setup-header">
            <h1>Selamat Datang!</h1>
            <p>Lengkapi informasi toko/bisnis Anda sebelum memulai menggunakan SIMKEU.</p>
        </div>

        <form action="{{ route('store.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nama Toko/Bisnis <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-store-alt"></i></span>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Misal: Toko Berkah" required autofocus>
                </div>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Misal: 08123456789">
                </div>
                @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap toko Anda">{{ old('address') }}</textarea>
                </div>
                @error('address')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-setup">
                Simpan & Lanjutkan ke Dashboard <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
    </div>
</div>
@endsection
