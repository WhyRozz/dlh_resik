<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PenjemputanController extends Controller
{
    protected $table = 'penjemputans';

    /**
     * Tampilkan daftar penjemputan
     */
    public function index()
    {
        $penjemputans = DB::table($this->table)
            ->orderBy('waktu', 'desc')
            ->get();

        // ⚠️ Pastikan file view ada di:
        // resources/views/bank_sampah/penjemputan/index.blade.php
        return view('bank-sampah.penjemputan.index', compact('penjemputans'));
    }

    /**
     * Ambil detail penjemputan via AJAX (untuk modal)
     */
    public function show($id)
    {
        $item = DB::table($this->table)->where('id', $id)->first();

        if (!$item) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($item);
    }

    /**
     * Setujui penjemputan
     */
    public function approve($id)
    {
        DB::table($this->table)->where('id', $id)->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Penjemputan berhasil disetujui.');
    }

    /**
     * Tolak / Hapus penjemputan
     */
    public function reject($id)
    {
        DB::table($this->table)->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Penjemputan berhasil ditolak.');
    }
}