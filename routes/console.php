<?php

use App\Console\Commands\CheckFleetAlerts;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckFleetAlerts::class)->dailyAt('06:00');

