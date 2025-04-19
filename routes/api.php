<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserLocationController;


Route::controller(AuthController::class)->prefix('auth')->group(function($cb){
    Route::post('/register', 'register')->name('register');
    Route::post('/login', 'login')->name('login');
    Route::post('/refresh-token', 'refreshToken')->name('refresh-token');
    Route::post('/logout', 'logout')->middleware('auth:sanctum')->name('logout');
});

Route::controller(UserController::class)->prefix('users')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "store");
    Route::get("/me", 'getCurrentUser');
    Route::get("/{id}", 'show')->name('user.show');
    Route::put("/{id}", 'update');
    Route::delete("/{id}", 'destroy');
});

Route::controller(ProductController::class)->prefix('products')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "store");
    Route::get("/{id}", "show")->name('product.show');
    Route::put("/{id}", "update");
    Route::delete("/{id}", "destroy");
    Route::post('/{id}/image', 'updateImage');
    Route::post('/{id}/gallery', 'updateGallery');
    Route::post("/add-to-wishlist/{id}", "addTowishlist");
    Route::get("/me/wishlist", "getWishlist");
    Route::delete("/remove-from-wishlist/{id}", "removeFromWishlist");
});

Route::controller(RolesController::class)->prefix('roles')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "store");
    Route::get("/{id}", "show")->name('role.show');
    Route::put("/{id}", "update");
    Route::delete("/{id}", "destroy");
});

Route::controller(ProductTypeController::class)->prefix('product-types')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "store");
    Route::get("/{id}", "show")->name('product_types.show');
    Route::put("/{id}", "update");
    Route::post('/{id}/image', 'updateImage');
    Route::delete("/{id}", "destroy");
});

Route::controller(ProductCategoryController::class)->prefix('product-categories')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "store");
    Route::get("/trash", "getDeleted");
    Route::get("/{id}", "show")->name('product_category.show');
    Route::put("/{id}", "update");
    Route::delete("/{id}", "destroy");
    Route::post('/{id}/image', 'updateImage');
    Route::delete("/delete/multiple", "deleteMultiple");
    Route::post("/restore/{id}", "restore");
    Route::delete("/force-delete/{id}", "forceDelete");
});

Route::controller(OrderController::class)->prefix('orders')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "create");
    Route::get("/{id}", "show")->name('orders.show');
    Route::put("/{id}", "update");
    Route::put('/{id}/status', "updateStatus");
    Route::delete("/{id}", "destroy");
    Route::get("/user/me", "getCurrentUserOrders");
    Route::get("/user/{user_id}", "getUserOrders");
    Route::post("/{order_id}/assign/{delivery_man_id}", "assignDeliveryman");
});

Route::controller(UserLocationController::class)->prefix('user-locations')->middleware("auth:sanctum")->group(function($cb){
    Route::get("/", 'index');
    Route::post("/", "store");
    Route::get("/{id}", "show")->name('user_location.show');
    Route::put("/{id}", "update");
    Route::delete("/{id}", "destroy");
});
