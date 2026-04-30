@extends('layouts.admin')

@section('title', 'RESIK - Data Pengguna')

@section('content')
<div class="page-header">
    <div>
        <h2>Daftar Data Pengguna</h2>
        <p class="text-muted">Kelola data pengguna ASN dan Masyarakat</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.data-pengguna.export', ['filter' => $filter]) }}" class="btn-export">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Filter Buttons -->
<div class="filter-group">
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'all']) }}"
        class="filter-btn {{ $filter == 'all' ? 'active' : '' }}">
        <i class="fas fa-users"></i> Semua
    </a>
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'asn']) }}"
        class="filter-btn {{ $filter == 'asn' ? 'active' : '' }}">
        <i class="fas fa-building"></i> ASN / PNS
    </a>
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'masyarakat']) }}"
        class="filter-btn {{ $filter == 'masyarakat' ? 'active' : '' }}">
        <i class="fas fa-user-friends"></i> Masyarakat
    </a>
</div>

<!-- Tabel Data -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengguna</th>
                <th>Email</th>
                <th>Jenis Kelamin</th>
                <th>No Telp</th>
                <th>Pekerjaan</th>
                <th>Saldo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $key => $user)
            <tr>
                <td>{{ $users->firstItem() + $key }}</td>
                <td>{{ $user->nama }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->jenis_kelamin)
                    <span class="badge {{ $user->jenis_kelamin == 'Laki-laki' ? 'badge-blue' : 'badge-pink' }}">
                        {{ $user->jenis_kelamin }}
                    </span>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $user->no_telp ?? '-' }}</td>
                <td>
                    @if($user->jenis_pengguna === 'PNS')
                    <span class="badge badge-asn">
                        {{ $user->nama_dinas ?? 'ASN/PNS' }}
                    </span>
                    @else
                    <span class="badge badge-masyarakat">
                        Masyarakat
                    </span>
                    @endif
                </td>
                <td><strong>Rp {{ number_format($user->saldo, 0, ',', '.') }}</strong></td>
                <td>
                    @if($user->jenis_pengguna === 'PNS')
                    <button class="btn-icon" onclick="openModal({{ $user->id }}, 'pns')">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    @else
                    <button class="btn-icon" onclick="openModal({{ $user->id }}, 'masyarakat')">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-4">
                    Tidak ada data pengguna untuk kategori ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $users->appends(['filter' => $filter])->links('pagination.custom') }}
    </div>
</div>

<!-- Modal Detail -->
<div id="userModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Detail Pengguna</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="user-grid">
                <div class="info-item full-width">
                    <label>Nama Lengkap</label>
                    <span id="modalNama">-</span>
                </div>
                <div class="info-item">
                    <label>Jenis Kelamin</label>
                    <span id="modalJenisKelamin">-</span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span id="modalEmail">-</span>
                </div>
                <div class="info-item">
                    <label>No Telepon</label>
                    <span id="modalTelp">-</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Lahir</label>
                    <span id="modalTglLahir">-</span>
                </div>
                <div class="info-item">
                    <label>Pekerjaan/Dinas</label>
                    <span id="modalPekerjaan">-</span>
                </div>
                <div class="info-item">
                    <label>Kode Anggota</label>
                    <span id="modalKodeAnggota">-</span>
                </div>
                <div class="info-item">
                    <label>Saldo Bank Sampah</label>
                    <span id="modalSaldo" style="color: #2e8b57; font-weight: bold;">-</span>
                </div>
                <div class="info-item full-width">
                    <label>Terdaftar Sejak</label>
                    <span id="modalCreated">-</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-primary" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .text-muted {
        color: #666;
        font-size: 14px;
        margin-top: 4px;
    }

    /* Filter Buttons */
    .filter-group {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 10px 20px;
        border-radius: 25px;
        background: #f0f0f0;
        color: #555;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 2px solid transparent;
    }

    .filter-btn:hover {
        background: #e0e0e0;
        transform: translateY(-1px);
    }

    .filter-btn.active {
        background: #2e8b57;
        color: white;
        border-color: #2e8b57;
        box-shadow: 0 4px 10px rgba(46, 139, 87, 0.2);
    }

    /* Export Button */
    .btn-export {
        background: #1b5e20;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
    }

    .btn-export:hover {
        background: #144a18;
        transform: translateY(-1px);
    }

    /* Badges */
    .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-blue {
        background: #e3f2fd;
        color: #1565c0;
    }

    .badge-pink {
        background: #fce4ec;
        color: #c2185b;
    }

    .badge-asn {
        background: #e3f2fd;
        color: #1565c0;
    }

    .badge-masyarakat {
        background: #e8f5e9;
        color: #2e7d32;
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    th {
        background: #f8f9fa;
        color: #555;
        font-weight: 600;
    }

    .btn-icon {
        background: none;
        border: none;
        cursor: pointer;
        color: #2e8b57;
        font-size: 14px;
        font-weight: 600;
    }

    .py-4 {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        justify-content: center;
        align-items: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-card {
        background: white;
        width: 90%;
        max-width: 600px;
        border-radius: 15px;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    .modal-header {
        padding: 15px 20px;
        background: #f0f7f4;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
    }

    .modal-header h3 {
        margin: 0;
        color: #333;
        font-size: 16px;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
    }

    .modal-body {
        padding: 20px;
    }

    .user-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .info-item.full-width {
        grid-column: span 2;
    }

    .info-item label {
        display: block;
        font-size: 12px;
        color: #888;
        margin-bottom: 4px;
    }

    .info-item span {
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
    }

    /* Pagination Styles */
    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }

    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 8px;
        align-items: center;
    }

    .pagination li {
        display: inline-block;
    }

    .pagination li a,
    .pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #555;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .pagination li a:hover {
        background: #2e8b57;
        color: white;
        border-color: #2e8b57;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(46, 139, 87, 0.2);
    }

    .pagination li.active span {
        background: linear-gradient(135deg, #2e8b57 0%, #1b5e20 100%);
        color: white;
        border-color: #2e8b57;
        font-weight: 700;
        box-shadow: 0 4px 8px rgba(46, 139, 87, 0.3);
    }

    .pagination li.disabled span {
        color: #aaa;
        background: #f0f0f0;
        border-color: #e0e0e0;
        cursor: not-allowed;
    }

    /* Previous/Next Text */
    .pagination .page-info {
        font-size: 14px;
        color: #666;
        font-weight: 500;
        margin: 0 15px;
    }

    .pagination-info {
        font-size: 14px;
        color: #666;
        font-weight: 500;
        background: #f8f9fa;
        padding: 10px 20px;
        border-radius: 10px;
        border: 2px solid #e9ecef;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .filter-group {
            width: 100%;
        }

        .filter-btn {
            flex: 1;
            justify-content: center;
        }

        .user-grid {
            grid-template-columns: 1fr;
        }

        .info-item.full-width {
            grid-column: span 1;
        }

        .pagination {
            gap: 5px;
        }

        .pagination li a,
        .pagination li span {
            min-width: 35px;
            height: 35px;
            padding: 0 8px;
            font-size: 13px;
        }

        .pagination .page-info {
            display: none;
        }

        .pagination-wrapper {
            flex-direction: column;
            gap: 15px;
        }

        .pagination-info {
            font-size: 13px;
            padding: 8px 15px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function openModal(userId, userType) {
        // Pastikan userId dan userType valid
        if (!userId || !userType) {
            console.error('Invalid userId or userType:', userId, userType);
            alert('Data pengguna tidak valid');
            return;
        }

        // Bersihkan userId dari karakter aneh
        userId = String(userId).trim();

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const url = `/admin/data-pengguna/api/${userType}/${userId}`;

        console.log('Fetching from:', url);

        fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                console.log('Response status:', response.status);

                if (response.status === 404) {
                    throw new Error('Data pengguna tidak ditemukan');
                }

                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }

                return response.json();
            })
            .then(data => {
                console.log('User data:', data);

                // Isi field modal
                document.getElementById('modalNama').textContent = data.nama || '-';
                document.getElementById('modalJenisKelamin').textContent = data.jenis_kelamin || '-';
                document.getElementById('modalEmail').textContent = data.email || '-';
                document.getElementById('modalTelp').textContent = data.no_telepon || '-';
                document.getElementById('modalPekerjaan').textContent = data.nama_dinas || 'Masyarakat Umum';
                document.getElementById('modalSaldo').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.saldo || 0);

                document.getElementById('userModal').classList.add('active');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
    }

    function closeModal() {
        document.getElementById('userModal').classList.remove('active');
    }

    // Close modal when clicking outside
    document.getElementById('userModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endpush
