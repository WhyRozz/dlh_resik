@extends('layouts.admin')

@section('title', 'Kelola Informasi TPS - SIMPELSI')
@section('page-title', 'Kelola TPS')
@section('page-title-mobile', 'TPS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tps.css') }}">
@endpush

@section('content')
{{-- Notifikasi sukses --}}
@if(session('success'))
    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #28a745;">
        {{ session('success') }}
    </div>
@endif

<div class="search-bar">
    <input type="text" class="search-input" id="searchInput" placeholder="Cari TPS berdasarkan nama atau lokasi...">
</div>

<div class="table-container">
    <div class="header-artikel">
        <div class="table-title">Daftar Informasi TPS</div>
        <a href="{{ route('admin.tps.create') }}" class="btn-add">
            <span>➕</span> BUAT INFO TPS
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>NAMA TPS</th>
                <th>LOKASI</th>
                <th>KAPASITAS</th>
                <th>KETERANGAN</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody id="tpsTableBody">
            @forelse($tpsList as $tps)
            <tr data-id="{{ $tps->id_tps }}">  {{-- ✅ TAMBAHKAN data-id --}}
                    <td>{{ $tps->id_tps }}</td>
                    <td>{{ $tps->nama_tps }}</td>
                    <td>
                        @if($tps->lokasi && preg_match('/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $tps->lokasi))
                            <a href="https://maps.google.com/maps?q={{ urlencode($tps->lokasi) }}"
                               target="_blank"
                               class="maps-link">
                                🗺️ Lihat di Maps
                            </a>
                        @else
                            {{ $tps->lokasi ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $tps->kapasitas ?? '-' }}</td>
                    <td>{{ Str::limit($tps->keterangan ?? '', 30) }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.tps.edit', $tps->id_tps) }}"
                               class="btn-action btn-edit"
                               title="Edit">✏️</a>
                            <button type="button"
                                    class="btn-action btn-delete"
                                    title="Hapus"
                                    onclick="konfirmasiHapus({{ $tps->id_tps }})">🗑️</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                        Belum ada data TPS.
                        <a href="{{ route('admin.tps.create') }}" style="color: #2e8b57;">Tambah data sekarang</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Popup Modals --}}
@include('admin.tps.partials.modals')

{{-- ✅ TAMBAHKAN INI sebelum @endsection --}}
<div id="errorPopup" class="popup-overlay">
    <div class="popup-content error">
        <h3>Kesalahan!</h3>
        <p id="errorMessage">Terjadi kesalahan.</p>
        <button type="button" class="popup-btn" onclick="closeErrorPopup()">Tutup</button>
    </div>
</div>

<script>
function closeErrorPopup() {
    const popup = document.getElementById('errorPopup');
    popup.querySelector('.popup-content').classList.remove('show');
    setTimeout(() => {
        popup.classList.remove('active');
    }, 300);
}

// Show error popup if validation errors exist
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        const errorMsg = "{{ $errors->first() }}";
        document.getElementById('errorMessage').textContent = errorMsg;
        const popup = document.getElementById('errorPopup');
        popup.classList.add('active');
        setTimeout(() => {
            popup.querySelector('.popup-content').classList.add('show');
        }, 10);
    });
@endif
</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/tps.js') }}"></script>
@endpush
