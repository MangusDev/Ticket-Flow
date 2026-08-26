<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::middleware('auth')->group(function(){
    Route::get('/admin.dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->except(['show']);
    });

require __DIR__.'/auth.php';