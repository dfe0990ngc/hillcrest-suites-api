<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\Auth\VerifyEmailController;

Route::get('/zStyDjwIXspS1C/session',[DatabaseController::class,'sessionTable'])->name('artisan.session');
Route::get('/zStyDjwIXspS1C/migrate',[DatabaseController::class,'migrate'])->name('artisan.migrate');
Route::get('/zStyDjwIXspS1C/migrate-fresh',[DatabaseController::class,'migrateFresh'])->name('artisan.migrate-fresh');
Route::get('/zStyDjwIXspS1C/migrate-rollback',[DatabaseController::class,'migrateRollabck'])->name('artisan.migrate-rollback');
Route::get('/zStyDjwIXspS1C/seed',[DatabaseController::class,'dbSeed'])->name('artisan.db-seed');
Route::get('/zStyDjwIXspS1C/optimize',[DatabaseController::class,'optimizeClear'])->name('artisan.optimize-clear');
Route::get('/zStyDjwIXspS1C/clear-cache',[DatabaseController::class,'clearCache'])->name('artisan.clear-cache');


Route::get('/', function () {
    return view('welcome');
});


Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed'])->name('verification.verify');
