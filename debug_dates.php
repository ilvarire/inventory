<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Sale;
use Illuminate\Support\Carbon;

echo "Current Server Time (now): " . now()->format('Y-m-d H:i:s') . "\n";
echo "Current Server Date (today): " . today()->format('Y-m-d') . "\n";
echo "Config Timezone: " . config('app.timezone') . "\n";

$latestSale = Sale::latest('created_at')->first();

if ($latestSale) {
    echo "Latest Sale ID: " . $latestSale->id . "\n";
    echo "Latest Sale Date (DB): " . $latestSale->sale_date . "\n";
    echo "Latest Sale Created At (DB): " . $latestSale->created_at . "\n";

    // Test the Admin Dashboard Query Logic
    $startDate = now()->toDateString();

    echo "Querying for date: $startDate\n";

    $count = Sale::whereDate('sale_date', $startDate)->count();
    $allCount = Sale::count();

    echo "Sales count for today ($startDate): $count\n";
    echo "Total sales in DB: $allCount\n";

} else {
    echo "No sales found in database.\n";
}
