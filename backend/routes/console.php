<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\PruneExpiredUploadSessionsJob;

Schedule::job(new PruneExpiredUploadSessionsJob)->hourly();
