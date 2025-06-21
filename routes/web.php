<?php

use App\Helpers\WilayahHelpersDropdown;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\LogoutController;
use App\Http\Controllers\Web\Config\AssignPermissionController;
use App\Http\Controllers\Web\Config\PermissionController;
use App\Http\Controllers\Web\Config\SettingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EventOragnizerController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\UserEventOrganizerController;
use Illuminate\Support\Facades\Route;

Route::get('/wilayah-dropdown', function () {
    $type = request('type'); // province, regency, district, village
    $parent = request('parent'); // ID dari level sebelumnya

    return response()->json(WilayahHelpersDropdown::fetch($type, $parent));
});

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('~admin-panel')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');

    Route::post('/login', [LoginController::class, 'login'])->name('login');

    Route::middleware('auth')->group(function () {

        Route::prefix('user-management')->name('user-management.')->group(function () {
            Route::group(['prefix' => 'user', 'controller' => UserController::class], function () {
                Route::get('/', 'index')->name('user');
                Route::get('/getData', 'getData')->name('user.getData');
                Route::get('/create', 'create')->name('user.create');
                Route::post('/store', 'store')->name('user.store');
                Route::get('/{uuid}/show', 'show')->name('user.show');
                Route::get('/{uuid}/edit', 'edit')->name('user.edit');
                Route::put('/{uuid}/update', 'update')->name('user.update');
                Route::delete('/{id}', 'destroy')->name('user.destroy');
                Route::get('/filter', 'filter')->name('user.filter');

                Route::post('/update-status', 'changeUserStatus')
                    ->name('user.update-status');
                Route::get('/update-password-modal/{uuid}', 'changePasswordModal')
                    ->name('user.update-password-modal');
                Route::post('/update-password', [UserController::class, 'updatePassword'])->name('user.update-password');
            });
        });

        Route::get('/event-organizer', [EventOragnizerController::class, 'index'])->name('event-organizer');
        Route::get('/event-organizer/getData', [EventOragnizerController::class, 'getData'])->name('event-organizer.getData');
        Route::delete('/event-organizer/destroy/{id}', [EventOragnizerController::class, 'destroy'])->name('event-organizer.destroy');
        Route::get('/event-organizer/{id}/show', [EventOragnizerController::class, 'show'])->name('event-organizer.show');
        Route::get('/event-organizer/reject-application-modal/{uuid}', [EventOragnizerController::class, 'rejectApplication'])->name('event-organizer.reject-application-modal');
        Route::get('/event-organizer/reject-verification-modal/{uuid}', [EventOragnizerController::class, 'rejectVerification'])->name('event-organizer.reject-verification-modal');
        Route::post('/event-organizer/update-status/{uuid}', [EventOragnizerController::class, 'updateStatus'])->name('event-organizer.update-status');
        Route::post('/event-organizer/{uuid}/mark-under-review', [EventOragnizerController::class, 'markUnderReview'])
            ->name('event-organizer.mark-under-review');


        Route::prefix('config')->name('config.')->group(function () {
            Route::group(['prefix' => 'permission', 'controller' => PermissionController::class], function () {
                Route::get('/', 'index')->name('permission');
                Route::get('/getData', 'getData')->name('permission.getData');
                Route::get('/create', 'create')->name('permission.create');
                Route::post('/store', 'store')->name('permission.store');
                Route::delete('/destroy/{id}', 'destroy')->name('permission.destroy');
            });

            Route::group(['prefix' => 'assign-permission', 'controller' => AssignPermissionController::class], function () {
                Route::get('', 'index')->name('assign');
                Route::get('/getData', 'getData')->name('assign.getData');
                Route::get('/create/{id}', 'create')->name('assign.create');
                Route::post('/assign', 'assignPermission')->name('assign.assign');
                Route::post('/revoke', 'revokePermission')->name('assign.revoke');
            });

            Route::group(['prefix' => 'setting', 'controller' => SettingController::class], function () {
                Route::get('/', 'index')->name('setting');
                Route::get('/getData', 'getData')->name('setting.getData');
                Route::get('/create', 'create')->name('setting.create');
                Route::post('/store', 'store')->name('setting.store');
                Route::get('/{id}/edit', 'edit')->name('setting.edit');
                Route::put('/{id}', 'update')->name('setting.update');
                Route::delete('/{id}', 'destroy')->name('setting.destroy');
            });
        });
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');

        Route::get('/logout', LogoutController::class)->name('logout');
    });
});
