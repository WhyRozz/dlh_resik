@extends('layouts.penjemputan')
@section('title', 'Daftar Penjemputan')

@section('content')
<div class="content-card">
    <h2>Daftar Penjemputan</h2>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Gambar</th>
                <th>Nama Admin</th>
                <th>Waktu</th>
                <th>Berat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjemputans as $index => $item)
            <tr onclick="showDetail({{ $item->id }})" style="cursor: pointer;">
                <td>{{ $index + 1 }}</td>
                <td>
                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto Penjemputan">
                </td>
                <td>{{ $item->nama_admin }}</td>
                <td>{{ \Carbon\Carbon::parse($item->waktu)->format('d-m-Y, H:i') }}</td>
                <td>{{ $item->berat }} Kg</td>
                <td onclick="event.stopPropagation()">
                    <div class="aksi-wrapper">
                        {{-- Tombol Setujui --}}
                        <form action="{{ route('admin.bank-sampah.penjemputan.approve', $item->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-approve" title="Setujui">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>

                        {{-- Tombol Tolak --}}
                        <form action="{{ route('admin.bank-sampah.penjemputan.reject', $item->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-reject" title="Tolak">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-inbox" style="font-size:28px; margin-bottom:8px; display:block;"></i>
                        Tidak ada data penjemputan.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Detail Penjemputan</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="detail-content">
                <div class="detail-image">
                    <img id="modalFoto" src="" alt="Foto Penjemputan">
                </div>
                
                <div class="detail-form">
                    <div class="form-group">
                        <label>No</label>
                        <input type="text" id="modalNo" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Admin</label>
                        <input type="text" id="modalNamaAdmin" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Waktu</label>
                        <input type="text" id="modalWaktu" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Berat</label>
                        <input type="text" id="modalBerat" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" id="modalLokasi" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea id="modalKeterangan" rows="3" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn-tutup" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
function showDetail(id) {
    fetch(`/admin/bank-sampah/penjemputan/${id}/detail`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalNo').value = data.id;
            document.getElementById('modalNamaAdmin').value = data.nama_admin;
            document.getElementById('modalWaktu').value = new Date(data.waktu).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('modalBerat').value = data.berat + ' Kg';
            document.getElementById('modalLokasi').value = data.lokasi || '-';
            document.getElementById('modalKeterangan').value = data.keterangan || '-';
            document.getElementById('modalFoto').src = '/storage/' + data.foto;
            
            document.getElementById('detailModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil data detail');
        });
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target == modal) {
        closeModal();
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection