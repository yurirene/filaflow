<?php

use App\Http\Controllers\Operador\Auth\LogoutController;
use App\Livewire\Fila\Operador as OperadorPainel;
use App\Livewire\Operador\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::prefix('operador')->name('operador.')->group(function () {
    Route::middleware('guest:operador')->group(function () {
        Route::livewire('login', Login::class)->name('login');
    });

    Route::middleware('auth:operador')->group(function () {
        Route::post('logout', LogoutController::class)->name('logout');
        Route::livewire('/', OperadorPainel::class)->name('painel');
    });
});
