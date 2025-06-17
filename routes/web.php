<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.pages.dashboard.index');
});
Route::get('/login', function () {
    return view('admin.pages.login.index');
});
