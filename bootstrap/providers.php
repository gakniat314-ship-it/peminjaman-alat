<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

// Mendaftarkan service provider milik aplikasi untuk proses bootstrap Laravel.
return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
