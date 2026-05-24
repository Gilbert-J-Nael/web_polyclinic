<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\HistoryController;

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

Route::get('/', [AuthenticationController::class, 'index_login']);
Route::post('/login/authentication', [AuthenticationController::class, 'login_function']);

Route::get('/register', [AuthenticationController::class, 'index_register']);
Route::post('/register/store', [AuthenticationController::class, 'register_function']);

Route::post('/logout', [AuthenticationController::class, 'logout']) ->name('logout');

Route::middleware(['usersession:FRONTDESK'])->group(function() {
    Route::get('/dashboard-frontdesk', [DashboardController::class, 'index_dashboard_frontdesk']);
Route::get('/patients/search', [DashboardController::class, 'searchPatients'])->name('patients.search');
// Route::get('/schedules/search', [DashboardController::class, 'searchSchedules'])->name('schedules.search');
Route::post('/polyclinic-queue/create', [DashboardController::class, 'create_polyclinic_queue']);
Route::post('/queue/panggil', [DashboardController::class, 'queuePanggil']);
Route::post('/queue/missed', [DashboardController::class, 'queueMissed']);
Route::post('/queue/selesai', [DashboardController::class, 'queueSelesai']);

Route::get('/master-pasien', [MasterDataController::class, 'index_pasien']);
Route::get('/master-dokter', [MasterDataController::class, 'index_dokter']);
Route::get('/master-jadwal-dokter', [MasterDataController::class, 'index_jadwal_dokter']);

Route::get('/riwayat-kunjungan',        [HistoryController::class, 'index_riwayat_kunjungan'])->name('riwayat.index');
Route::get('/riwayat-kunjungan/print',  [HistoryController::class, 'print_riwayat_kunjungan'])->name('riwayat.print');
});

Route::middleware(['usersession:PATIENT'])->group(function() {
    Route::get('/dashboard-pasien', [DashboardController::class, 'index_dashboard_pasien']);
});