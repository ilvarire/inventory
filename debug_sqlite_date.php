<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing SQLite Date Functions:\n";

// 1. Check what date() returns for our specific format
$sampleDate = '2026-01-31 00:00:00';
$sql1 = "select date(?) as d, datetime(?) as dt";
$res1 = DB::select($sql1, [$sampleDate, $sampleDate]);
echo "Source: '$sampleDate'\n";
echo "date(): " . ($res1[0]->d ?? 'NULL') . "\n";
echo "datetime(): " . ($res1[0]->dt ?? 'NULL') . "\n";

// 2. Test whereDate equivalent vs string comparison
$start = '2026-01-13';
$end = '2026-02-12';

echo "\nTesting Queries:\n";

// Original failing query logic
$sqlOriginal = "select count(*) as c from sales where date(sale_date) >= ? and date(sale_date) <= ?";
$resOriginal = DB::select($sqlOriginal, [$start, $end]);
echo "Original (date() function): " . $resOriginal[0]->c . " matches\n";

// Proposed fix: String comparison (works well in SQLite if format is ISO8601)
$sqlFix = "select count(*) as c from sales where sale_date >= ? and sale_date <= ?";
// Append time to make it inclusive/exclusive correctly
$startFull = $start . ' 00:00:00';
$endFull = $end . ' 23:59:59';
$resFix = DB::select($sqlFix, [$startFull, $endFull]);
echo "Proposed (string comparison): " . $resFix[0]->c . " matches\n";

// Alternative: strftime
$sqlStrftime = "select count(*) as c from sales where strftime('%Y-%m-%d', sale_date) >= ? and strftime('%Y-%m-%d', sale_date) <= ?";
$resStrftime = DB::select($sqlStrftime, [$start, $end]);
echo "Alternative (strftime): " . $resStrftime[0]->c . " matches\n";
