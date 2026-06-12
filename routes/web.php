<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SimulationController;

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
});

require __DIR__ . '/auth.php';
