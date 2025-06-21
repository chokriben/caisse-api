<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\CashClosureController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "api" middleware group. Build something great!
|
*/

// Authentification
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Récupérer l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
  Route::get('/users', [AuthController::class, 'getUsers']);

   Route::post('/close-cash', [CashClosureController::class, 'closeCashRegister']);
   // Route pour voir la clôture
    Route::get('/closures', [CashClosureController::class, 'getClosures']);
});


 // Produits
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('{product}', [ProductController::class, 'update']);
        Route::delete('{product}', [ProductController::class, 'destroy']);
    });

    // Ventes
    Route::prefix('sales')->group(function () {
        Route::post('/close-day', [SaleController::class, 'closeDay']);
        Route::get('/stats', [SaleController::class, 'stats']);
        Route::get('/', [SaleController::class, 'index']);
        Route::get('{id}', [SaleController::class, 'show']);
        Route::post('/', [SaleController::class, 'store']);
    });
