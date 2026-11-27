<?php

declare(strict_types=1);

use App\Http\Controllers\ShowController;
use Illuminate\Support\Facades\Route;

Route::get('/shows', [ShowController::class, 'index'])->name('shows.index');
Route::get('/shows/{id}', [ShowController::class, 'show'])->where('id', '[0-9]{1,10}')->name('shows.show');
