<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventMediaController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImmichProxyController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TimelineController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/linha-do-tempo', TimelineController::class)->name('timeline');

Route::get('/galeria', GalleryController::class)->name('gallery');

Route::get('/midia/{media}', [EventMediaController::class, 'show'])->name('media.show');

Route::prefix('calendario')->name('calendar.')->group(function (): void {
    Route::get('/', [CalendarController::class, 'index'])->name('index');
    Route::get('/criar', [CalendarController::class, 'create'])->name('create');
    Route::get('/emails', [CalendarController::class, 'emails'])->name('emails');
    Route::get('/{appointment}/ics', [CalendarController::class, 'ics'])->name('ics');
    Route::get('/{appointment}/editar', [CalendarController::class, 'edit'])->name('edit');
    Route::get('/{appointment}', [CalendarController::class, 'show'])->name('show');
    Route::delete('/{appointment}', [CalendarController::class, 'destroy'])->name('destroy');
});

Route::prefix('eventos')->name('events.')->group(function (): void {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/criar', [EventController::class, 'create'])->name('create');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
    Route::get('/{event}/editar', [EventController::class, 'edit'])->name('edit');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
});

Route::prefix('planos')->name('plans.')->group(function (): void {
    Route::get('/', [PlanController::class, 'index'])->name('index');
    Route::get('/criar', [PlanController::class, 'create'])->name('create');
    Route::get('/categorias', [PlanController::class, 'categories'])->name('categories');
    Route::get('/{planItem}', [PlanController::class, 'show'])->name('show');
    Route::get('/{planItem}/editar', [PlanController::class, 'edit'])->name('edit');
    Route::delete('/{planItem}', [PlanController::class, 'destroy'])->name('destroy');
});

Route::prefix('immich')->name('immich.')->group(function (): void {
    Route::get('/assets/{assetId}/thumbnail', [ImmichProxyController::class, 'thumbnail'])->name('thumbnail');
    Route::get('/assets/{assetId}/original', [ImmichProxyController::class, 'original'])->name('original');
});
