<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    /**
     * Export data to Excel
     */
    public function exportToExcel($exportClass, $filename)
    {
        return Excel::download($exportClass, $filename);
    }

    /**
     * Export data to PDF
     */
    public function exportToPdf($view, $data, $filename, $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', $orientation)
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);

        return $pdf->download($filename);
    }

    /**
     * Generate filename with timestamp
     */
    public function generateFilename($prefix, $extension = 'xlsx')
    {
        return $prefix . '_' . date('Y-m-d_His') . '.' . $extension;
    }
}
