<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('about-8am', function () {
    $this->info('8AM Coffee QR Order System');
})->purpose('Show project information');

// Mỗi ngày dọn tài khoản nhân viên chưa kích hoạt (email không tồn tại / chưa xác nhận).
Schedule::command('accounts:purge-unconfirmed')->dailyAt('03:00');

