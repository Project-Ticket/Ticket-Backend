<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\LogoutController;
use App\Http\Controllers\Web\Config\AssignPermissionController;
use App\Http\Controllers\Web\Config\PermissionController;
use App\Http\Controllers\Web\Config\SettingController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('~admin-panel')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');

    Route::post('/login', [LoginController::class, 'login'])->name('login');

    Route::middleware('auth')->group(function () {

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
