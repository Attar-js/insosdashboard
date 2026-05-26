<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// KKN Pendaftar API Routes
Route::prefix('kkn')->group(function () {
    Route::get('/pendaftar', [App\Http\Controllers\Api\KknApiController::class, 'index']);
    Route::get('/pendaftar/{id}', [App\Http\Controllers\Api\KknApiController::class, 'show']);
    Route::get('/pendaftar/user/{userNim}', [App\Http\Controllers\Api\KknApiController::class, 'getByUserNim']);
    Route::post('/store-from-external', [App\Http\Controllers\Api\KknApiController::class, 'storeFromExternal']);
});

// Proposal API Routes
Route::prefix('proposal')->group(function () {
    Route::post('/store', [App\Http\Controllers\ProposalController::class, 'storeFromExternal']);
    Route::post('/store-from-external', [App\Http\Controllers\ProposalController::class, 'storeFromExternal']);
    Route::get('/list', [App\Http\Controllers\ProposalController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\ProposalController::class, 'show']);
});

// Laporan Akhir API Routes
Route::prefix('laporan-akhir')->group(function () {
    Route::post('/store', [App\Http\Controllers\LaporanAkhirController::class, 'storeFromExternal']);
    Route::post('/store-from-external', [App\Http\Controllers\LaporanAkhirController::class, 'storeFromExternal']);
    Route::get('/list', [App\Http\Controllers\LaporanAkhirController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\LaporanAkhirController::class, 'show']);
});

// Luaran API Routes
Route::prefix('luaran')->group(function () {
    Route::post('/store', [App\Http\Controllers\LuaranController::class, 'storeFromExternal']);
    Route::post('/store-from-external', [App\Http\Controllers\LuaranController::class, 'storeFromExternal']);
    Route::get('/list', [App\Http\Controllers\LuaranController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\LuaranController::class, 'show']);
    Route::put('/{id}/status', [App\Http\Controllers\LuaranController::class, 'status']);
    
    // Test route untuk debugging
    Route::get('/test', function() {
        return response()->json([
            'message' => 'Luaran API is working',
            'timestamp' => now(),
            'data_count' => \App\Models\Luaran::count()
        ]);
    });
});

// Peer Review API Routes
Route::prefix('peer-review')->group(function () {
    Route::post('/store', [App\Http\Controllers\PeerReviewController::class, 'storeFromExternal']);
    Route::post('/store-from-external', [App\Http\Controllers\PeerReviewController::class, 'storeFromExternal']);
    Route::get('/list', [App\Http\Controllers\PeerReviewController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\PeerReviewController::class, 'show']);
    Route::put('/{id}/status', [App\Http\Controllers\PeerReviewController::class, 'verifikasi']);
    
    // Test route untuk debugging
    Route::get('/test', function() {
        return response()->json([
            'message' => 'Peer Review API is working',
            'timestamp' => now(),
            'data_count' => \App\Models\PeerReview::count()
        ]);
    });
});

// Form Kesediaan API Routes
Route::prefix('form-kesediaan')->group(function () {
    Route::post('/store', [App\Http\Controllers\FormKesediaanController::class, 'storeFromExternal']);
    Route::post('/store-from-external', [App\Http\Controllers\FormKesediaanController::class, 'storeFromExternal']);
    Route::get('/list', [App\Http\Controllers\FormKesediaanController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\FormKesediaanController::class, 'show']);
    Route::put('/{id}/status', [App\Http\Controllers\FormKesediaanController::class, 'verifikasi']);
    
    // Test route untuk debugging
    Route::get('/test', function() {
        return response()->json([
            'message' => 'Form Kesediaan API is working',
            'timestamp' => now(),
            'data_count' => \App\Models\FormKesediaan::count()
        ]);
    });
});
