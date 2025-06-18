<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
