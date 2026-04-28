<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataPenggunaExport;

class DataPenggunaController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = Masyarakat::query();

        // Filter logic
        if ($filter === 'asn') {
            // ASN = yang punya pekerjaan selain null/Umum/Swasta
            $query->whereNotNull('pekerjaan')
                  ->where('pekerjaan', '!=', 'Umum')
                  ->where('pekerjaan', '!=', 'Swasta')
                  ->where('pekerjaan', '!=', '');
        } elseif ($filter === 'masyarakat') {
            // Masyarakat = pekerjaan null, kosong, Umum, atau Swasta
            $query->where(function($q) {
                $q->whereNull('pekerjaan')
                  ->orWhere('pekerjaan', '')
                  ->orWhere('pekerjaan', 'Umum')
                  ->orWhere('pekerjaan', 'Swasta');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.data-pengguna.index', compact('users', 'filter'));
    }

    /**
     * Export Excel sesuai filter
     */
    public function export(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $filename = 'data_pengguna_' . ($filter === 'all' ? 'semua' : $filter) . '_' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new DataPenggunaExport($filter), $filename);
    }
}
