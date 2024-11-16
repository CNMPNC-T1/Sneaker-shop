<?php

use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ProvideController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\GoodsReceipt;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/get-chart-data', [DashboardController::class, 'getChartData'])->name('getChartData');
    Route::get('/get-chart-categories', [DashboardController::class, 'getChartCategories'])->name('getChartCategories');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/export-excel', [ProductController::class, 'export'])->name("export");
    // route product
    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/create', [ProductController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ProductController::class, 'edit'])->name('edit');
        Route::put('/edit/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // route cart
    Route::group(['prefix' => 'bill', 'as' => 'bill.'], function () {
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [BillController::class, 'update'])->name('edit');
        Route::get('/edit/payment-status/{id}', [BillController::class, 'unpdatePaymentStatus'])->name('unpdatePaymentStatus');
    });

    //route brand
    Route::group(['prefix' => 'brand', 'as' => 'brand.'], function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::get('/create', [BrandController::class, 'create'])->name('create');
        Route::post('/create', [BrandController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BrandController::class, 'edit'])->name('edit');
        Route::put('/edit/{id}', [BrandController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [BrandController::class, 'destroy'])->name('destroy');
    });

    //route category
    Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/create', [CategoryController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/edit/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    //route provider
    Route::group(['prefix' => 'provider', 'as' => 'provider.'], function () {
        Route::get('/', [ProviderController::class, 'index'])->name('index');
        Route::get('/create', [ProviderController::class, 'create'])->name('create');
        Route::post('/create', [ProviderController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ProviderController::class, 'edit'])->name('edit');
        Route::put('/edit/{id}', [ProviderController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ProviderController::class, 'destroy'])->name('destroy');
    });

    //route provide
    Route::group(['prefix' => 'provide', 'as' => 'provide.'], function () {
        Route::get('/', [ProvideController::class, 'index'])->name('index');
        Route::get('/create', [ProvideController::class, 'create'])->name('create');
        Route::post('/create', [ProvideController::class, 'store'])->name('store');
        Route::get('/edit/{provider_id}/{product_id}', [ProvideController::class, 'edit'])->name('edit');
        Route::put('/edit/{provider_id}/{product_id}', [ProvideController::class, 'update'])->name('update');
        Route::delete('/destroy/{provider_id}/{product_id}', [ProvideController::class, 'destroy'])->name('destroy');
    });

    // route goods receipt
    Route::group(['prefix' => 'GoodsReceipt', 'as' => 'GoodsReceipt.'], function () {
        Route::get('/', [GoodsReceipt::class, 'index'])->name('index');
        Route::get('/create', [GoodsReceipt::class, 'create'])->name('create');
        Route::post('/create', [GoodsReceipt::class, 'store'])->name('store');
    });


    //route user
    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
    });

    //route setting
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/change', [SettingController::class, 'change'])->name('change');
    });

    // update currency
    Route::get("/update-currency-usd", [CurrencyController::class, 'getSellUSD'])->name("update-currency-usd");
});
