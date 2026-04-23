@extends('layouts.admin')

@section('title', 'Daftar Artikel - SIMPELSI')
@section('page-title', 'Kelola Artikel')
@section('page-title-mobile', 'ARTIKEL')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/artikel.css') }}">
@endpush

@section('content')
<div class="content-header">
    <h2>Kelola Artikel</h2>
</div>

<div class="table-container">
    <div class="table-title">Daftar Artikel Edukasi</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($artikelList as $index => $artikel)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Str::limit($artikel->judul, 50) }}</td>
                    <td>{{ \Carbon\Carbon::parse($artikel->tanggal)->format('d-m-Y H:i') }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.artikel.edit', $artikel->id_artikel) }}"
                               class="btn-action btn-edit"
                               title="Edit">✏️</a>
                            <a href="javascript:void(0)"
                               class="btn-action btn-delete"
                               title="Hapus"
                               onclick="konfirmasiHapus({{ $artikel->id_artikel }})">🗑️</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #888;">
                        Belum ada artikel.
                        <a href="{{ route('admin.artikel.create') }}" style="color: #2e8b57;">Tambah artikel sekarang</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('admin.artikel.create') }}" class="btn-add">
        <span>➕</span> TAMBAH ARTIKEL
    </a>
</div>

{{-- Popup Modals --}}
@include('admin.artikel.partials.modals')
@endsection

@push('scripts')
    <script src="{{ asset('js/artikel.js') }}"></script>
@endpush
