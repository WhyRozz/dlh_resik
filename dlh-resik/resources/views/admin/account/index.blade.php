@extends('layouts.admin')

@section('title', 'Kelola Akun Admin - SIMPELSI')
@section('page-title', 'Kelola Akun')
@section('page-title-mobile', 'AKUN')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush

@section('content')
<div class="content-header">
    <h2>Kelola Akun Admin ({{ count($admins) }}/3)</h2>
</div>

{{-- Messages --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="accounts-grid">
    {{-- AKUN UTAMA --}}
    <div class="account-card">
        <div class="card-header">
            <div class="card-title">
                <span>🔒</span> Akun Utama
            </div>
            <span class="badge-default">DEFAULT</span>
        </div>
        <div class="account-info">
            <label>Email:</label>
            <span>{{ $akunUtama ? htmlspecialchars($akunUtama->email) : 'Belum dibuat' }}</span>
        </div>
        <div class="account-info">
            <label>Password:</label>
            <span>{{ $akunUtama ? '••••••••' : '—' }}</span>
        </div>
        <div class="btn-group">
            @if($akunUtama)
                <button class="btn btn-outline"
                    onclick="requestOTPForAction('edit', {{ $akunUtama->id_admin }}, '{{ addslashes($akunUtama->email) }}')">
                    Edit
                </button>
            @else
                <button class="btn btn-primary" onclick="createDefaultAccount()">
                    Buat Akun Utama
                </button>
            @endif
        </div>
    </div>

    {{-- SLOT TAMBAHAN (max 2) --}}
    @for($i = 0; $i < 2; $i++)
        @if(isset($tambahan[$i]))
            @php($a = $tambahan[$i])
            <div class="account-card">
                <div class="card-header">
                    <div class="card-title">
                        <span>👤</span> Akun Tambahan
                    </div>
                    <span class="badge-tambah">TAMBAHAN</span>
                </div>
                <div class="account-info">
                    <label>Email:</label>
                    <span>{{ htmlspecialchars($a->email) }}</span>
                </div>
                <div class="account-info">
                    <label>Password:</label>
                    <span>••••••••</span>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline"
                        onclick="requestOTPForAction('edit', {{ $a->id_admin }}, '{{ addslashes($a->email) }}')">
                        Edit
                    </button>
                    <button class="btn btn-danger"
                        onclick="requestOTPForAction('delete', {{ $a->id_admin }}, '{{ addslashes($a->email) }}')">
                        Hapus
                    </button>
                </div>
            </div>
        @else
            <div class="slot-tambah" onclick="showAddForm()">
                <div class="slot-title">➕ Tambah Akun Baru</div>
                <div class="slot-desc">Buat akun admin cadangan untuk login alternatif.</div>
                <button class="slot-btn">Tambah Akun</button>
            </div>
        @endif
    @endfor
</div>

{{-- FORM TAMBAH/EDIT --}}
<div class="form-section" id="formSection" style="display:none;">
    <h3 id="formTitle">Tambah Akun Baru</h3>

    <form method="POST" id="accountForm" action="">
        @csrf
        <input type="hidden" name="action" value="simpan">
        <input type="hidden" name="id" id="formIdAdmin" value="">

        {{-- Dynamic action based on edit mode --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('accountForm');
                const idInput = document.getElementById('formIdAdmin');

                // Update form action based on whether we're editing
                if (idInput && idInput.value) {
                    form.action = `/admin/akun/${idInput.value}`;
                    // Add PUT method spoofing
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);
                } else {
                    form.action = '/admin/akun';
                }
            });
        </script>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" class="form-control"
                       autocomplete="off" {{ old('password') ? 'value="'.old('password').'"' : '' }}>
                <span class="toggle-password" id="togglePassword">
                    <img src="{{ asset('assets/hide.png') }}" alt="Hide Password" id="eyeIconImg">
                </span>
            </div>
        </div>

        <div class="form-note">
            Untuk edit: biarkan kosong jika tidak ingin ganti password.
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" class="btn btn-outline" onclick="resetForm()">Batal</button>
        </div>
    </form>
</div>

{{-- Modals --}}
@include('admin.account.partials.modals')
@endsection

@push('scripts')
    <script src="{{ asset('js/account.js') }}"></script>
@endpush
