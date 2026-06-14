<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/simulations/buy', [SimulationController::class, 'buyForm'])->name('simulations.buy.form');
    Route::get('/simulations/buy/preview', [SimulationController::class, 'buyPreview'])->name('simulations.buy.preview');
    Route::post('/simulations/buy', [SimulationController::class, 'buyStore'])->name('simulations.buy.store');
    Route::get('/simulations/sell', [SimulationController::class, 'sellForm'])->name('simulations.sell.form');
    Route::get('/simulations/sell/preview', [SimulationController::class, 'sellPreview'])->name('simulations.sell.preview');
    Route::post('/simulations/sell', [SimulationController::class, 'sellStore'])->name('simulations.sell.store');
    Route::get('/simulations/history', [SimulationController::class, 'history'])->name('simulations.history');
    Route::get('/simulations/exchange', [SimulationController::class, 'exchangeForm'])->name('simulations.exchange.form');
    Route::get('/simulations/exchange/preview', [SimulationController::class, 'exchangePreview'])->name('simulations.exchange.preview');
    Route::post('/simulations/exchange', [SimulationController::class, 'exchangeStore'])->name('simulations.exchange.store');
    Route::get('/prices', [PriceController::class, 'index'])->name('prices.index');
    Route::get('/prices/history', [PriceController::class, 'history'])->name('prices.history');
    Route::get('/prices/current', [PriceController::class, 'current'])->name('prices.current');
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/simulations/export', [SimulationController::class, 'export'])->name('simulations.export');
    Route::get('/simulations/export-pdf', [SimulationController::class, 'exportPdf'])->name('simulations.export.pdf');

    // Ajustes / customización del portafolio
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/currency', [SettingsController::class, 'updateCurrency'])->name('settings.currency');
    Route::post('/settings/fund', [SettingsController::class, 'fund'])->name('settings.fund');
    Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    Route::post('/settings/favorites/toggle', [SettingsController::class, 'toggleFavorite'])->name('settings.favorites.toggle');
});

require __DIR__ . '/auth.php';
