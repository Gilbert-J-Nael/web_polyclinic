<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDataController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index_dashboard']);
Route::get('/patients/search', [DashboardController::class, 'searchPatients'])->name('patients.search');
// Route::get('/schedules/search', [DashboardController::class, 'searchSchedules'])->name('schedules.search');
Route::post('/polyclinic-queue/create', [DashboardController::class, 'create_polyclinic_queue']);

Route::get('/master-pasien', [MasterDataController::class, 'index_pasien']);
Route::get('/master-dokter', [MasterDataController::class, 'index_dokter']);
Route::get('/master-jadwal-dokter', [MasterDataController::class, 'index_jadwal_dokter']);