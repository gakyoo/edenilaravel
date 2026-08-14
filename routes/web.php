<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Models\Property;
use App\Models\SiteContent;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedSearchController;
use App\Models\Task;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $featured = Property::query()
        ->with('media:id,property_id,path,is_primary')
        ->active()
        ->orderByDesc('is_featured')
        ->orderByDesc('views_count')
        ->limit(6)
        ->get();

    return Inertia::render('Landing', [
        'featured' => $featured,
        'favoriteIds' => auth()->check() ? auth()->user()->favorites()->pluck('property_id') : collect(),
        'popularSearches' => Property::popularSearches(),
        'siteContent' => SiteContent::allMap(),
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Public property listing (no auth required)
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}/{slug?}', [PropertyController::class, 'show'])->name('properties.show');

// Favorites (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/properties/{property}/favorite', [PropertyController::class, 'favorite'])->name('properties.favorite');
    Route::delete('/properties/{property}/favorite', [PropertyController::class, 'unfavorite'])->name('properties.unfavorite');
    Route::get('/favorites', [PropertyController::class, 'favorites'])->name('favorites');
});

// Tour booking (public — no auth required)
Route::post('/properties/{property}/tour', [PropertyController::class, 'storeTour'])->name('properties.tour.store');

// Saved searches (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/saved-searches', [SavedSearchController::class, 'store'])->name('saved-searches.store');
    Route::delete('/saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// Merged backend: dashboard holds everything (admin features merged in)
// Admin-only management routes — non-admin users get their own dashboard instead
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/dashboard/tasks', [DashboardController::class, 'storeTask'])->name('dashboard.tasks.store');
    Route::patch('/dashboard/tasks/{task}', [DashboardController::class, 'updateTask'])->name('dashboard.tasks.update');
    Route::delete('/dashboard/tasks/{task}', [DashboardController::class, 'destroyTask'])->name('dashboard.tasks.destroy');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // Property management (was admin)
    Route::get('/dashboard/properties/create', [DashboardController::class, 'createProperty'])->name('dashboard.properties.create');
    Route::post('/dashboard/properties', [DashboardController::class, 'storeProperty'])->name('dashboard.properties.store');
    Route::get('/dashboard/properties/{property}/edit', [DashboardController::class, 'editProperty'])->name('dashboard.properties.edit');
    Route::put('/dashboard/properties/{property}', [DashboardController::class, 'updateProperty'])->name('dashboard.properties.update');
    Route::delete('/dashboard/properties/{property}', [DashboardController::class, 'destroyProperty'])->name('dashboard.properties.destroy');

    // Enquiry management (was admin)
    Route::patch('/dashboard/enquiries/{enquiry}', [DashboardController::class, 'updateEnquiry'])->name('dashboard.enquiries.update');
    Route::delete('/dashboard/enquiries/{enquiry}', [DashboardController::class, 'destroyEnquiry'])->name('dashboard.enquiries.destroy');

    // Tour management (merged admin)
    Route::patch('/dashboard/tours/{tour}', [DashboardController::class, 'updateTour'])->name('dashboard.tours.update');
    Route::delete('/dashboard/tours/{tour}', [DashboardController::class, 'destroyTour'])->name('dashboard.tours.destroy');

    // Site content management
    Route::get('/dashboard/content', [DashboardController::class, 'content'])->name('dashboard.content');
    Route::post('/dashboard/content', [DashboardController::class, 'updateContent'])->name('dashboard.content.update');

    // User management (admin)
    Route::patch('/dashboard/users/{user}', [DashboardController::class, 'updateUser'])->name('dashboard.users.update');
    Route::delete('/dashboard/users/{user}', [DashboardController::class, 'destroyUser'])->name('dashboard.users.destroy');
});

// Legacy /admin links redirect into the merged dashboard
Route::redirect('/admin', '/dashboard');
Route::redirect('/admin/properties', '/dashboard');
Route::redirect('/admin/enquiries', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
