<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MonthlyReportExport implements FromView
{
    /** @var array<string, mixed> */
    protected array $data;

    /**
     * Inisialisasi export dengan data laporan.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Gunakan view blade untuk men-generate Excel.
     */
    public function view(): View
    {
        return view('reports.monthly', $this->data);
    }
}
