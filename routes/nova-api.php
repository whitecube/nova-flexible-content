<?php

use Illuminate\Support\Facades\Route;
use Whitecube\NovaFlexibleContent\Http\Controllers\UpdateFieldController;

Route::patch('/{resource}/{resourceId}/update-fields', [UpdateFieldController::class, 'sync']);