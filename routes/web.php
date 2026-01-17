<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Redirect root to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Illuminate\Http\Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials are incorrect.',
        ])->onlyInput('email');
    });
});

Route::post('/logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', function () {
            return view('inventory.index');
        })->name('index');

        Route::get('/low-stock', function () {
            return view('inventory.low-stock');
        })->name('low-stock');

        Route::get('/expiring', function () {
            return view('inventory.expiring');
        })->name('expiring');

        Route::get('/{id}', function ($id) {
            return view('inventory.show', compact('id'));
        })->name('show');

        Route::get('/{id}/movements', function ($id) {
            return view('inventory.movements', compact('id'));
        })->name('movements');
    });

    // Procurement
    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::get('/', function () {
            return view('procurement.index');
        })->name('index');

        Route::get('/create', function () {
            return view('procurement.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('procurement.show', compact('id'));
        })->name('show');
    });

    // Material Requests
    Route::prefix('material-requests')->name('material-requests.')->group(function () {
        Route::get('/', function () {
            return view('material-requests.index');
        })->name('index');

        Route::get('/create', function () {
            return view('material-requests.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('material-requests.show', compact('id'));
        })->name('show');
    });

    // Recipes
    Route::prefix('recipes')->name('recipes.')->group(function () {
        Route::get('/', function () {
            return view('recipes.index');
        })->name('index');

        Route::get('/create', function () {
            return view('recipes.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('recipes.show', compact('id'));
        })->name('show');
    });

    // Production
    Route::prefix('production')->name('production.')->group(function () {
        Route::get('/', function () {
            return view('production.index');
        })->name('index');

        Route::get('/create', function () {
            return view('production.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('production.show', compact('id'));
        })->name('show');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', function () {
            return view('sales.index');
        })->name('index');

        Route::get('/create', function () {
            return view('sales.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('sales.show', compact('id'));
        })->name('show');
    });

    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', function () {
            return view('expenses.index');
        })->name('index');

        Route::get('/create', function () {
            return view('expenses.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('expenses.show', compact('id'));
        })->name('show');
    });

    // Waste Management
    Route::prefix('waste')->name('waste.')->group(function () {
        Route::get('/', function () {
            return view('waste.index');
        })->name('index');

        Route::get('/create', function () {
            return view('waste.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('waste.show', compact('id'));
        })->name('show');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', function () {
            return view('reports.sales');
        })->name('sales');

        Route::get('/profit-loss', function () {
            return view('reports.profit-loss');
        })->name('profit-loss');

        Route::get('/waste', function () {
            return view('reports.waste');
        })->name('waste');

        Route::get('/expenses', function () {
            return view('reports.expenses');
        })->name('expenses');

        Route::get('/inventory-health', function () {
            return view('reports.inventory-health');
        })->name('inventory-health');

        Route::get('/top-selling', function () {
            return view('reports.top-selling');
        })->name('top-selling');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function () {
            return view('users.index');
        })->name('index');

        Route::get('/create', function () {
            return view('users.create');
        })->name('create');

        Route::get('/{id}', function ($id) {
            return view('users.show', compact('id'));
        })->name('show');

        Route::get('/{id}/edit', function ($id) {
            return view('users.edit', compact('id'));
        })->name('edit');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', function () {
            return view('profile.index');
        })->name('index');

        Route::get('/edit', function () {
            return view('profile.edit');
        })->name('edit');
    });
});
