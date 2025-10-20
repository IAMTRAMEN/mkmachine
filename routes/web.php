<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfiguratorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MachineController;
use App\Http\Controllers\Admin\ComponentController;
use App\Http\Controllers\Admin\CompatibilityRuleController;
use App\Http\Controllers\Admin\QuoteController;

// Public Configurator Routes
Route::get('/', function () {
    return redirect('/configurator');
});

Route::prefix('configurator')->group(function () {
    Route::get('/', [ConfiguratorController::class, 'index'])->name('configurator.index');
    Route::get('/machine/{id}', [ConfiguratorController::class, 'show'])->name('configurator.show');
    Route::post('/validate', [ConfiguratorController::class, 'validateConfiguration'])->name('configurator.validate');
    Route::post('/quote', [ConfiguratorController::class, 'generateQuote'])->name('configurator.quote');
    Route::get('/quote/{id}/pdf', [ConfiguratorController::class, 'exportPdf'])->name('configurator.export-pdf');
     Route::post('/save', [ConfiguratorController::class, 'saveConfiguration'])->name('configurator.save');
    Route::get('/load/{configurationNumber}', [ConfiguratorController::class, 'loadConfiguration'])->name('configurator.load');
    Route::get('/compare', [ConfiguratorController::class, 'compare'])->name('configurator.compare');
    Route::post('/compare/data', [ConfiguratorController::class, 'getComparisonData'])->name('configurator.compare.data');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/admin/dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Resource routes
    Route::resource('machines', MachineController::class)->names('admin.machines');
    Route::resource('components', ComponentController::class)->names('admin.components');
    Route::resource('rules', CompatibilityRuleController::class)->names('admin.rules');
    Route::resource('quotes', QuoteController::class)->names('admin.quotes');
    
    // Additional quote routes
    Route::post('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('admin.quotes.status');
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'exportPdf'])->name('admin.quotes.export-pdf');
});