<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\DostupPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    DostupPanelProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
];
