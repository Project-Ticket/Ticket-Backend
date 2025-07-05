<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventOrganizerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WilayahController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::get('email/verify/{id}/{hash}', [RegisterController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::get('/login', function () {
        return response()->json([
            'success' => false,
            'code' => 401,
            'message' => 'Please login to continue.',
        ], 401);
    })->name('login');

    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::group(['prefix' => 'ticket-type', 'controller' => TicketTypeController::class], function () {
    Route::get('/{eventId}/available', 'getAvailable');
});

Route::post('/webhook', [WebhookController::class, 'webhook']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('check', [LoginController::class, 'checkAuth']);

        Route::put('/update-profile', [UserController::class, 'updateProfile']);
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/update-password', [UserController::class, 'updatePassword']);
    });

    Route::group(['prefix' => 'event-organizer', 'controller' => EventOrganizerController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::put('/{uuid}/update', 'update');
        Route::get('/{uuid}/show', 'show');
        Route::delete('/{uuid}/delete', 'destroy');

        Route::post('/{uuid}/resubmit-application', 'resubmitApplication');
        Route::post('/{uuid}/regenerate-payment-invoice', 'regeneratePaymentInvoice');
    });

    // Route::middleware(RoleMiddleware::class)->group(function () {

    Route::group(['prefix' => 'ticket-type', 'controller' => TicketTypeController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::get('/{id}/show', 'show');
        Route::put('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destroy');

        Route::patch('/{id}/update-status', 'toggleActive');
    });

    Route::group(['prefix' => 'ticket', 'controller' => TicketController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::get('/{id}/show', 'show');
        Route::put('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destroy');
        Route::patch('/{id}/update-status', 'toggleActive');

        Route::get('/{uuid}/generate-qrcode', 'generateQrCodeImage');

        Route::get('/get-ticket-by-qrcode', 'getTicketFromQrCode');

        Route::post('/use-ticket', 'markTicketAsUsed');
    });

    Route::group(['prefix' => 'event', 'controller' => EventController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::get('/{slug}', 'show');
        Route::put('/{slug}/update', 'update');
        Route::delete('/{slug}/delete', 'destroy');
        Route::patch('/{slug}/update-status', 'updateStatus');
    });
    // });

    Route::group(['prefix' => 'order', 'controller' => OrderController::class], function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::get('/{uuid}/show', 'show');
        Route::put('/{uuid}/update', 'update');
        Route::put('/{uuid}/cancel', 'cancel');
        Route::get('/my-order', 'myOrders');
        Route::get('/statistic', 'statistics');
    });

    Route::group(['prefix' => 'payment-method', 'controller' => PaymentMethodController::class], function () {
        Route::get('/', 'index');
        Route::post('/calculate-fee', 'calculateFee');
    });

    Route::get('/category', [CategoryController::class, 'index']);

    Route::post('/logout', LogoutController::class);
});

Route::group(['prefix' => 'wilayah', 'controller' => WilayahController::class], function () {
    Route::get('/provinces', 'getProvinces');
    Route::get('/regencies/{provinceId}', 'getRegencies');
    Route::get('/districts/{regencyId}', 'getDistricts');
    Route::get('/villages/{districtId}', 'getVillages');
    Route::get('/provinces/{provinceId}/with-regencies', 'getProvinceWithRegencies');
    Route::get('/provinces/search', 'searchProvinces');
});
