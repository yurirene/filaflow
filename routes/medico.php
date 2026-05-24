<?php

use App\Http\Controllers\Medico\Auth\LogoutController;
use App\Livewire\Fila\Medico as MedicoPainel;
use App\Livewire\Medico\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::prefix('medico')->name('medico.')->group(function () {
    Route::middleware('guest:medico')->group(function () {
        Route::livewire('login', Login::class)->name('login');
    });

    Route::middleware('auth:medico')->group(function () {
        Route::post('logout', LogoutController::class)->name('logout');
        Route::livewire('/', MedicoPainel::class)->name('painel');
    });
});
