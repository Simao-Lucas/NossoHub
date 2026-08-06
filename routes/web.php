<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImmichProxyController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/linha-do-tempo', TimelineController::class)->name('timeline');

Route::get('/galeria', GalleryController::class)->name('gallery');

Route::prefix('eventos')->name('events.')->group(function (): void {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/criar', [EventController::class, 'create'])->name('create');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
    Route::get('/{event}/editar', [EventController::class, 'edit'])->name('edit');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
});

Route::prefix('wishlist')->name('wishlist.')->group(function (): void {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/', [WishlistController::class, 'store'])->name('store');
    Route::put('/{wishlistItem}', [WishlistController::class, 'update'])->name('update');
    Route::delete('/{wishlistItem}', [WishlistController::class, 'destroy'])->name('destroy');
});

Route::prefix('immich')->name('immich.')->group(function (): void {
    Route::get('/assets/{assetId}/thumbnail', [ImmichProxyController::class, 'thumbnail'])->name('thumbnail');
    Route::get('/assets/{assetId}/original', [ImmichProxyController::class, 'original'])->name('original');
});
