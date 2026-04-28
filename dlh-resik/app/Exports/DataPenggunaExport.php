<?php

namespace App\Exports;

use App\Models\Masyarakat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataPenggunaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        $query = Masyarakat::query();

        if ($this->filter === 'asn') {
            $query->whereNotNull('pekerjaan')
                  ->where('pekerjaan', '!=', 'Umum')
                  ->where('pekerjaan', '!=', 'Swasta');
        } elseif ($this->filter === 'masyarakat') {
            $query->where(function($q) {
                $q->whereNull('pekerjaan')
                  ->orWhere('pekerjaan', '')
                  ->orWhere('pekerjaan', 'Umum')
                  ->orWhere('pekerjaan', 'Swasta');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Pengguna',
            'Email',
            'Jenis Kelamin',
            'No Telepon',
            'Pekerjaan',
            'Alamat',
            'Saldo Bank Sampah',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        return [
            $user->id_masyarakat,
            $user->nama,
            $user->email,
            $user->jenis_kelamin ?? '-',
            $user->no_telp ?? '-',
            $user->pekerjaan ?? 'Masyarakat',
            $user->alamat ?? '-',
            'Rp ' . number_format($user->saldo_bank_sampah, 0, ',', '.'),
            $user->created_at->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:I1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E8B57']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ]
        ];
    }
}
