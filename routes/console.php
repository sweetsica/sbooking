<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Quét lịch sắp diễn ra trong ~60-70 phút tới, gửi nhắc hẹn cho BS/KTV.
Schedule::command('lich:nhac-hen')->everyTenMinutes()->withoutOverlapping();
