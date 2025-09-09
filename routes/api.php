<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Internal\InternalFileController;
use App\Http\Controllers\Public\PublicFileController;
use Illuminate\Http\Request;
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

// Public routes (no authentication required)
Route::prefix('public')->name('public.')->group(function () {
    Route::get('files', [PublicFileController::class, 'index'])->name('files.index');
    Route::get('files/{id}', [PublicFileController::class, 'show'])->name('files.show');
    Route::get('files/{id}/download', [PublicFileController::class, 'download'])->name('files.download');
    Route::get('stats', [PublicFileController::class, 'stats'])->name('stats');
});

// Authentication routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', [AuthController::class, 'user'])->name('user');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
    });
});

// Internal routes (authentication required)
Route::prefix('internal')->name('internal.')->middleware('auth:sanctum')->group(function () {
    Route::get('files', [InternalFileController::class, 'index'])->name('files.index');
    Route::post('files', [InternalFileController::class, 'store'])->name('files.store');
    Route::get('files/stats', [InternalFileController::class, 'stats'])->name('files.stats');
    Route::get('files/{id}', [InternalFileController::class, 'show'])->name('files.show');
    Route::put('files/{id}', [InternalFileController::class, 'update'])->name('files.update');
    Route::delete('files/{id}', [InternalFileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{id}/download', [InternalFileController::class, 'download'])->name('files.download');
    Route::post('files/{id}/toggle-publication', [InternalFileController::class, 'togglePublication'])->name('files.toggle-publication');
});

// Health check route
Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Laravel GCS Storage API is running',
        'timestamp' => now(),
    ]);
});