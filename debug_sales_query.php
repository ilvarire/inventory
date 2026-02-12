<?php

use Illuminate\Support\Facades\DB;
use App\Models\Sale;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Running Raw SQL Test:\n";

// The exact query from the log
$sql = "select * from `sales` where date(`sale_date`) >= ? and date(`sale_date`) <= ? and `sales`.`deleted_at` is null order by `sale_date` desc";
$bindings = ["2026-01-13", "2026-02-12"];

echo "Query: $sql\n";
echo "Bindings: " . json_encode($bindings) . "\n";

$results = DB::select($sql, $bindings);

echo "Results Count: " . count($results) . "\n";
if (count($results) > 0) {
    print_r($results[0]);
} else {
    echo "No matching records found via SQL.\n";
}

echo "\nChecking Sale Date format in DB:\n";
$all = DB::select("select id, sale_date, deleted_at from sales");
foreach ($all as $row) {
    echo "ID: {$row->id}, Date: {$row->sale_date}, Deleted: " . ($row->deleted_at ?? 'NULL') . "\n";

    // Test date function directly
    $dateCheck = DB::select("select date(?) as d", [$row->sale_date]);
    echo "  DATE() function returns: " . $dateCheck[0]->d . "\n";
}
