<?php
declare(strict_types=1);

use App\Http\Controllers\BatchController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadResultController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/batch', BatchController::class)->name('batches');
Route::get('/leads', LeadController::class)->name('index');
Route::post('/lead-results', LeadResultController::class)->name('lead-results');
Route::get('/partners', PartnerController::class)->name('partners');
