<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Sale;
use App\Models\Role;

echo "--- Users & Roles ---\n";
$users = User::with('role')->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Role: " . ($u->role->name ?? 'None');
    if ($u->role && $u->role->name === 'Frontline Sales') {
        echo " [isSales() should be true]";
    }
    echo "\n";
}

echo "\n--- Recent Sales ---\n";
$sales = Sale::with('user')->latest()->take(5)->get();
foreach ($sales as $s) {
    echo "Sale ID: {$s->id} | Date: {$s->sale_date} | User ID: {$s->user_id} | User Name: " . ($s->user->name ?? 'NULL') . "\n";
}

echo "\n--- Testing Sales Query for a Sales User ---\n";
// Find a sales user
$salesUser = User::whereHas('role', function ($q) {
    $q->where('name', 'Frontline Sales');
})->first();

if ($salesUser) {
    echo "Testing for User: {$salesUser->name} (ID: {$salesUser->id})\n";
    $queryStartDate = now()->startOfDay()->toDateTimeString();
    $queryEndDate = now()->endOfDay()->toDateTimeString();
    echo "Query Range: $queryStartDate to $queryEndDate\n";

    $count = Sale::where('user_id', $salesUser->id)
        ->whereBetween('sale_date', [$queryStartDate, $queryEndDate])
        ->count();

    echo "Sales Count Today: $count\n";

    // Check all time for this user
    $total = Sale::where('user_id', $salesUser->id)->count();
    echo "Total All-Time Sales for this user: $total\n";
} else {
    echo "No Frontline Sales user found.\n";
}
