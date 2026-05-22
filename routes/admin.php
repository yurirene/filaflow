<?php

use App\Livewire\Fila\Admin\Configuracoes;
use App\Livewire\Fila\Admin\Dashboard;
use App\Livewire\Fila\Admin\Guiches;
use App\Livewire\Fila\Admin\Intercalacao;
use App\Livewire\Fila\Admin\Notificacoes;
use App\Livewire\Fila\Admin\Relatorios;
use App\Livewire\Fila\Admin\Servicos;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/dashboard');

    Route::livewire('dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('relatorios', Relatorios::class)->name('relatorios');
    Route::livewire('servicos', Servicos::class)->name('servicos');
    Route::livewire('guiches', Guiches::class)->name('guiches');
    Route::livewire('intercalacao', Intercalacao::class)->name('intercalacao');
    Route::livewire('notificacoes', Notificacoes::class)->name('notificacoes');
    Route::livewire('configuracoes', Configuracoes::class)->name('configuracoes');
});
