<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Collection $mahasiswas)
    {
    }

    public function collection(): Collection
    {
        return $this->mahasiswas;
    }

    public function headings(): array
    {
        return [
            'No',
            'Fakultas',
            'Prodi',
            'Nama Mahasiswa',
            'NPM',
            'No. HP',
            'No. HP 2',
            'PIC Telemarketing',
            'Status Follow Up',
            'Rencana Wisuda',
            'Pertimbangan',
            'Catatan',
        ];
    }

    public function map($mahasiswa): array
    {
        $fu = $mahasiswa->followUp;

        return [
            $mahasiswa->no,
            $mahasiswa->nama_fakultas,
            $mahasiswa->prodi?->nama,
            $mahasiswa->nama_mahasiswa,
            $mahasiswa->npm,
            $mahasiswa->no_hp,
            $mahasiswa->no_hp_2,
            $fu?->pic?->name,
            $fu?->statusFollowUp?->nama,
            $fu?->rencanaWisuda?->nama,
            $fu?->pertimbangan?->nama ?? $fu?->keterangan,
            $fu?->follow_up_berikutnya,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
