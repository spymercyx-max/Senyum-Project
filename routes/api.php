<?php

use App\Http\Controllers\Api\Developer\MapController as DevMapApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| INTERNAL MAP API — data geografis nyata, bukan dummy.
| Auth: session (same-origin fetch) + role developer + throttle.
| Tidak pernah mengembalikan password/token/secret.
|--------------------------------------------------------------------------
*/
Route::prefix('developer/map')->name('api.developer.map.')->middleware(['web', 'auth', 'role:developer', 'throttle:120,1'])->group(function () {
    Route::get('/statistics', [DevMapApiController::class, 'statistics'])->name('statistics');
    Route::get('/territories', [DevMapApiController::class, 'territories'])->name('territories');
    Route::get('/distributors', [DevMapApiController::class, 'distributors'])->name('distributors');
    Route::get('/outlets', [DevMapApiController::class, 'outlets'])->name('outlets');
    Route::get('/activity', [DevMapApiController::class, 'activity'])->name('activity');
    Route::get('/coverage', [DevMapApiController::class, 'coverage'])->name('coverage');
    Route::get('/search', [DevMapApiController::class, 'search'])->name('search');
});
