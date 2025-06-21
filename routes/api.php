<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventOrganizerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::get('email/verify/{id}/{hash}', [RegisterController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('check', [LoginController::class, 'checkAuth']);
    });

    Route::group(['prefix' => 'event-organizer', 'controller' => EventOrganizerController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::put('/{uuid}/update', 'update');
        Route::get('/{uuid}/show', 'show');
        Route::delete('/{uuid}/delete', 'destroy');
    });

    Route::group(['prefix' => 'event', 'controller' => EventController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::get('/{slug}', 'show');
        Route::put('/{slug}/update', 'update');
        Route::delete('/{slug}/delete', 'destroy');
        Route::patch('/{slug}/update-status', 'updateStatus');
    });

    Route::post('/logout', LogoutController::class);
});
