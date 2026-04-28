<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\TpsController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DataPenggunaController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page (Public)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest routes (belum login)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    // Protected routes (sudah login)
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });

    // Kelola Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::post('/update-status', [LaporanController::class, 'updateStatus'])->name('update-status');
    });

    // Kelola Artikel
    Route::prefix('artikel')->name('artikel.')->group(function () {
        Route::get('/', [ArtikelController::class, 'index'])->name('index');
        Route::delete('/{id}', [ArtikelController::class, 'destroy'])->name('destroy');
        Route::get('/create', [ArtikelController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [ArtikelController::class, 'edit'])->name('edit');
        Route::post('/', [ArtikelController::class, 'store'])->name('store');
        Route::put('/{id}', [ArtikelController::class, 'update'])->name('update');
    });

    // Kelola TPS
    Route::prefix('tps')->name('tps.')->group(function () {
        Route::get('/', [TpsController::class, 'index'])->name('index');
        Route::delete('/{id}', [TpsController::class, 'destroy'])->name('destroy');
        Route::get('/create', [TpsController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [TpsController::class, 'edit'])->name('edit');
        Route::post('/', [TpsController::class, 'store'])->name('store');
        Route::put('/{id}', [TpsController::class, 'update'])->name('update');
    });

    // Kelola Akun
    Route::prefix('akun')->name('akun.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::put('/{id}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountController::class, 'destroy'])->name('destroy');

        // OTP Endpoints
        Route::post('/request-otp', [AccountController::class, 'requestOtp'])->name('request-otp');
        Route::post('/verify-otp', [AccountController::class, 'verifyOtp'])->name('verify-otp');

        // AJAX: Get password (placeholder)
        Route::post('/ajax/get-password', [AccountController::class, 'getPasswordPlaceholder'])->name('ajax.get-password');
        Route::post('/ajax/get-password-raw', [AccountController::class, 'getPasswordRaw'])->name('ajax.get-password-raw');
    });

    // Data Pengguna
    Route::prefix('data-pengguna')->name('data-pengguna.')->group(function () {
        Route::get('/', [DataPenggunaController::class, 'index'])->name('index');
        Route::get('/export', [DataPenggunaController::class, 'export'])->name('export');
    });

    // Bank Sampah Routes
    Route::prefix('bank-sampah')->name('bank-sampah.')->group(function () {
        Route::get('/setor', function () {
            return view('admin.bank-sampah.setor');
        })->name('setor');

        Route::get('/tarik', function () {
            return view('admin.bank-sampah.tarik');
        })->name('tarik');

        Route::get('/jenis-harga', function () {
            return view('admin.bank-sampah.jenis-harga');
        })->name('jenis-harga');

        Route::get('/penjemputan', function () {
            return view('admin.bank-sampah.penjemputan');
        })->name('penjemputan');
    })->middleware('auth:admin');

    // Logout
    //Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/api/users/{id}', function ($id) {
    $user = \App\Models\Masyarakat::find($id);
    return response()->json($user);
})->middleware('auth:admin');

// ✅ Route API untuk fetch detail user
Route::get('/api/users/{id}', function ($id) {
    if (!Auth::guard('admin')->check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = DB::table('masyarakat')
        ->where('id_masyarakat', $id)
        ->first();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json([
        'id_masyarakat' => $user->id_masyarakat,
        'nama' => $user->nama,
        'email' => $user->email,
        'jenis_kelamin' => $user->jenis_kelamin,
        'no_telp' => $user->no_telp,
        'pekerjaan' => $user->pekerjaan,
        'alamat' => $user->alamat,
        'saldo_bank_sampah' => $user->saldo_bank_sampah ?? 0,
        'qr_code_path' => $user->qr_code_path,
        'created_at' => $user->created_at,
    ]);
});
