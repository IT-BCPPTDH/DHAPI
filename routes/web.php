<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PRETESTController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// PRETEST API
Route::get('/getEmployee', [PRETESTController::class, 'getEmployee'])->name('getEmployee');
Route::get('/getUnit', [PRETESTController::class, 'getUnit'])->name('getUnit');
Route::get('/getUnitMapping', [PRETESTController::class, 'getUnitMapping'])->name('getUnitMapping');
