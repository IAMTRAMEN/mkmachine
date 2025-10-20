<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfiguratorController;

Route::post('/configurator/validate', [ConfiguratorController::class, 'validateConfiguration']);