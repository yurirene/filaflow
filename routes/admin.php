<?php

use App\Livewire\Fila\Admin\Alas;
use App\Livewire\Fila\Admin\Configuracoes;
use App\Livewire\Fila\Admin\Consultorios;
use App\Livewire\Fila\Admin\Guiches;
use App\Livewire\Fila\Admin\Intercalacao;
use App\Livewire\Fila\Admin\Medicos;
use App\Livewire\Fila\Admin\Operadores;
use App\Livewire\Fila\Admin\Relatorios;
use App\Livewire\Fila\Admin\Servicos;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/relatorios');

    Route::redirect('dashboard', '/dashboard')->name('dashboard');
    Route::livewire('relatorios', Relatorios::class)->name('relatorios');
    Route::livewire('alas', Alas::class)->name('alas');
    Route::livewire('servicos', Servicos::class)->name('servicos');
    Route::livewire('guiches', Guiches::class)->name('guiches');
    Route::livewire('consultorios', Consultorios::class)->name('consultorios');
    Route::livewire('medicos', Medicos::class)->name('medicos');
    Route::livewire('operadores', Operadores::class)->name('operadores');
    Route::livewire('intercalacao', Intercalacao::class)->name('intercalacao');
    Route::livewire('configuracoes', Configuracoes::class)->name('configuracoes');
});
