<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\SaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - PymeFlow ERP v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Autenticación pública de API
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Rutas protegidas mediante Sanctum Token
    Route::middleware('auth:sanctum')->group(function () {
        // Sesión de usuario
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard de métricas
        Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);

        // Consulta de Productos (disponible para vendedores y admin)
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);

        // Consulta de Clientes
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);

        // Gestión de Ventas
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);
        Route::post('/sales', [SaleController::class, 'store']);

        // Auditoría de Inventario
        Route::get('/inventory/movements', [InventoryController::class, 'movements']);

        // Rutas exclusivas para rol de Administrador en la API
        Route::middleware('role:admin')->group(function () {
            // Alta y edición de productos
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{id}', [ProductController::class, 'update']);

            // Alta y edición de clientes
            Route::post('/customers', [CustomerController::class, 'store']);
            Route::put('/customers/{id}', [CustomerController::class, 'update']);

            // Anulación interna de ventas en API
            Route::post('/sales/{id}/cancel', [SaleController::class, 'cancel']);

            // Órdenes de Compra y Recepción de Mercadería
            Route::get('/purchases', [PurchaseController::class, 'index']);
            Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
            Route::post('/purchases', [PurchaseController::class, 'store']);
            Route::post('/purchases/{id}/receive', [PurchaseController::class, 'receive']);
        });
    });
});
