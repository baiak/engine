<?php

use App\Models\ServiceLabor;
use App\Models\ServiceLaborLog;
use Illuminate\Support\Facades\Route;
use App\Filament\Resources\OrderResource\RelationManagers\ServiceRelationManager;

Route::get('/', function () {
    return view('welcome');
});
