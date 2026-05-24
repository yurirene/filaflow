<?php

use App\Livewire\Dashboard;
use App\Livewire\Documentacao;
use App\Livewire\Fila\Painel;
use App\Livewire\Fila\Totem;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::livewire('totem', Totem::class)->name('totem');
Route::livewire('painel', Painel::class)->name('painel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('documentacao', Documentacao::class)->name('documentacao');
});

require __DIR__.'/operador.php';
require __DIR__.'/medico.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
