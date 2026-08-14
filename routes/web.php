<?php

use App\Http\Controllers\DashboardController;
use App\Models\Property;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProfileController;
use App\Models\Task;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $featured = Property::query()
        ->active()
        ->orderByDesc('is_featured')
        ->orderByDesc('views_count')
        ->limit(6)
        ->get();

    return Inertia::render('Landing', [
        'featured' => $featured,
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Public property listing (no auth required)
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/dashboard/tasks', [DashboardController::class, 'storeTask'])->name('dashboard.tasks.store');
    Route::patch('/dashboard/tasks/{task}', [DashboardController::class, 'updateTask'])->name('dashboard.tasks.update');
    Route::delete('/dashboard/tasks/{task}', [DashboardController::class, 'destroyTask'])->name('dashboard.tasks.destroy');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
