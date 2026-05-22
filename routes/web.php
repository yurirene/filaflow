<?php

use App\Livewire\Fila\Operador;
use App\Livewire\Fila\Painel;
use App\Livewire\Fila\Totem;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::livewire('totem', Totem::class)->name('totem');
Route::livewire('painel', Painel::class)->name('painel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('operador', Operador::class)->name('operador');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
