@extends('layouts.admin')

@section('title', isset($artikel) ? 'Edit Artikel - SIMPELSI' : 'Tambah Artikel - SIMPELSI')
@section('page-title', isset($artikel) ? 'Edit Artikel' : 'Tambah Artikel')
@section('page-title-mobile', isset($artikel) ? 'EDIT' : 'TAMBAH')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/artikel-form.css') }}">
@endpush

@section('content')
<div class="content-header">
    <h2>{{ isset($artikel) ? 'Edit' : 'Tambah' }} Artikel Edukasi</h2>
</div>

<div class="form-container">
    <div class="form-title">{{ isset($artikel) ? 'Edit' : 'Tambah' }} Artikel</div>

    <form id="artikelForm"
          method="POST"
          action="{{ isset($artikel) ? route('admin.artikel.update', $artikel->id_artikel) : route('admin.artikel.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($artikel))
            @method('PUT')
        @endif

        <input type="hidden" name="id" value="{{ $artikel->id_artikel ?? '' }}">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Upload Foto</label>
                <div class="upload-area" id="uploadArea">
                    <span class="upload-icon">📁</span>
                    <div class="upload-text">Klik untuk upload foto artikel</div>
                    <div class="upload-hint">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
                    <input type="file" id="fotoInput" name="foto" accept="image/jpeg,image/png,image/gif">

                    <div class="upload-preview {{ $artikel?.foto ? 'show' : '' }}" id="uploadPreview">
                        @if(isset($artikel) && $artikel->foto)
                            <img src="{{ asset('storage/' . $artikel->foto) }}" alt="Foto artikel">
                            <a href="javascript:void(0)" class="remove-btn">🗑️ Hapus gambar</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Judul Artikel *</label>
                <input type="text"
                       class="form-input"
                       name="judul"
                       value="{{ old('judul', $artikel->judul ?? '') }}"
                       maxlength="255"
                       placeholder="Masukkan judul artikel">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tanggal Publikasi *</label>
                <input type="datetime-local"
                       class="form-input"
                       name="tanggal"
                       value="{{ old('tanggal', $artikel?->tanggal?->format('Y-m-d\TH:i') ?? date('Y-m-d\TH:i')) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Deskripsi Artikel *</label>
                <textarea class="form-textarea"
                          name="deskripsi"
                          placeholder="Tulis konten artikel di sini...">{{ old('deskripsi', $artikel->deskripsi ?? '') }}</textarea>
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('admin.artikel.index') }}" class="btn btn-secondary">❌ BATAL</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($artikel) ? '💾 PERBARUI' : '📤 PUBLIKASI' }} ARTIKEL
            </button>
        </div>
    </form>
</div>

{{-- Error Popup --}}
<div id="errorPopup" class="popup-overlay">
    <div class="popup-content error">
        <h3>Kesalahan!</h3>
        <p id="errorMessage">Terjadi kesalahan.</p>
        <div style="margin-top: 15px;">
            <button class="popup-btn" onclick="closeErrorPopup()">Tutup</button>
        </div>
    </div>
</div>

{{-- Success Popup (for redirect) --}}
<div id="successPopup" class="popup-overlay">
    <div class="popup-content success">
        <h3>Berhasil!</h3>
        <p id="successMessage">Artikel berhasil disimpan.</p>
        <div style="margin-top: 15px;">
            <button class="popup-btn" onclick="closeSuccessPopup('{{ route('admin.artikel.index') }}')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/artikel-form.js') }}"></script>

    {{-- Show PHP error as popup --}}
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showErrorPopup("{!! addslashes($errors->first()) !!}");
        });
    </script>
    @endif

    {{-- Show success message from session --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showSuccessPopup("{{ session('success') }}");
        });
    </script>
    @endif
@endpush
