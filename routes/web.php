<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::inertia('dashboard', 'Dashboard')
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    // Export Product ke PDF
    // Hanya Super Admin
    Route::get(
        '/products/export-pdf',
        [ProductController::class, 'exportPdf']
    )
        ->middleware('role:super_admin')
        ->name('products.export-pdf');


    // Export Product ke Excel
    // Hanya Super Admin
    Route::get(
        '/products/export-excel',
        [ProductController::class, 'exportExcel']
    )
        ->middleware('role:super_admin')
        ->name('products.export-excel');


    // Import Product dari Excel
    // Hanya Super Admin
    Route::post(
        '/products/import-excel',
        [ProductController::class, 'importExcel']
    )
        ->middleware('role:super_admin')
        ->name('products.import-excel');


    // CRUD Product
    // Destroy dipisahkan karena hanya Super Admin yang boleh hapus
    Route::resource('products', ProductController::class)
        ->except(['destroy']);


    // Hapus Product
    // Hanya Super Admin
    Route::delete(
        '/products/{product}',
        [ProductController::class, 'destroy']
    )
        ->middleware('role:super_admin')
        ->name('products.destroy');


    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    // Hanya Super Admin
    Route::resource('users', UserController::class)
        ->middleware('role:super_admin');
});

require __DIR__.'/settings.php';