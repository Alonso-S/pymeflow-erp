<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Rutas de Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas Protegidas por Autenticación
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Ventas y Punto de Venta (POS) - Acceso para Administradores y Vendedores
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    // Clientes - Consulta y gestión
    Route::resource('customers', CustomerController::class)->except(['destroy', 'show']);
    Route::patch('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

    // Catálogo de Productos - Consulta para todos los usuarios autenticados
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    // Historial de Movimientos de Inventario - Consulta
    Route::get('/inventory', [InventoryMovementController::class, 'index'])->name('inventory.index');

    // =========================================================================
    // RUTAS EXCLUSIVAS DE ADMINISTRADOR (role:admin)
    // =========================================================================
    Route::middleware('role:admin')->group(function () {
        // Gestión de Productos (Creación, Edición y Estado)
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

        // Categorías
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

        // Proveedores
        Route::resource('suppliers', SupplierController::class)->except(['destroy', 'show']);
        Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

        // Compras y Abastecimiento
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

        // Ajustes manuales de Inventario
        Route::get('/inventory/create', [InventoryMovementController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryMovementController::class, 'store'])->name('inventory.store');

        // Anulación interna de ventas y restitución de stock
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

        // Reportes y Analítica avanzada
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/sales', [ReportController::class, 'exportSalesCsv'])->name('reports.export.sales');
        Route::get('/reports/export/inventory', [ReportController::class, 'exportInventoryCsv'])->name('reports.export.inventory');
    });
});
