<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


//mudar esta rota depois
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});


//Rotas admin 
Route::middleware('auth')->group(function(){
    Route::get('/admin.dashboard', function () {
        return view('admin.dashboard');
    })->name('/admin.dashboard');
});

//Middleware para rotas de admin, Route::resource para rotas de CRUD do users 
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('/admin.dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->except(['show']);

        Route::resource('areas', \App\Http\Controllers\Admin\AreaController::class)
            ->except(['show']);
    });




require __DIR__.'/auth.php';