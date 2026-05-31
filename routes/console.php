<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-8am', function () {
    $this->info('8AM Coffee QR Order System');
})->purpose('Show project information');
