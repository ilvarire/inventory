<?php

use App\Jobs\CheckLowStockJob;
use App\Jobs\CheckExpiringItemsJob;
use App\Jobs\CheckHighWastageJob;
use Illuminate\Support\Facades\Schedule;

// Schedule jobs to run daily
Schedule::job(new CheckLowStockJob)->dailyAt('08:00');
Schedule::job(new CheckExpiringItemsJob)->dailyAt('08:30');
Schedule::job(new CheckHighWastageJob)->dailyAt('09:00');
