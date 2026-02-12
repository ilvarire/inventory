<?php

use App\Models\Sale;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Sales Table Dump:\n";
$sales = Sale::withTrashed()->get(['id', 'sale_date', 'section_id', 'created_at', 'deleted_at']);

if ($sales->isEmpty()) {
    echo "No sales found in database.\n";
} else {
    foreach ($sales as $sale) {
        echo "ID: {$sale->id} | Date: {$sale->sale_date} | Section: {$sale->section_id} | Created: {$sale->created_at} | Deleted: {$sale->deleted_at}\n";
    }
}

echo "\nTotal Sales Count: " . $sales->count() . "\n";
