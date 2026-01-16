<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'section_id',
        'sales_user_id',
        'sale_date',
        'total_amount',
        'payment_method'
    ];

    protected $casts = [
        'sale_date' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}

//Sales export
// class SalesExport implements FromCollection
// {
//     public function collection()
//     {
//         return Sale::with('items')->get();
//     }
// }
// return Excel::download(new SalesExport, 'sales.xlsx');

// $pdf = Pdf::loadView('reports.section_pnl', [
//     'data' => $reportData
// ]);

// return $pdf->download('section_pnl.pdf');
