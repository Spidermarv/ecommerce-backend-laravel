<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductCrudController;
use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\UserCrudController;

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::crud('category', CategoryCrudController::class);
    Route::crud('user', UserCrudController::class);
    Route::crud('product', ProductCrudController::class);
}); // this should be the absolute last line of this file


/**
 * DO NOT ADD ANYTHING HERE.
 */
