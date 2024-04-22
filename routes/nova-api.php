<?php

use Illuminate\Support\Facades\Route;
use Whitecube\NovaFlexibleContent\Http\Controllers\DependentFieldSupportController;

Route::patch('/{resource}/{resourceId}/update-fields', [DependentFieldSupportController::class, 'updateFieldSync']);
Route::patch('/{resource}/creation-fields', [DependentFieldSupportController::class, 'creationFieldSync']);
