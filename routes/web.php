<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Student;
use App\Http\Controllers\TransactionFileDownloadController;
use App\Http\Controllers\TransactionReceiptController;
use Illuminate\Support\Facades\Route;

// Publik (hanya tamu)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');

    // Pendaftaran siswa (akun menunggu persetujuan Admin)
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,10')->name('register.store');
});

// Halaman pembuka untuk tamu; pengguna login diarahkan ke dashboard (lihat HomeController)
Route::get('/', HomeController::class)->name('home');

// Akses bersama (Admin & Siswa)
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/password', [ProfileController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

// Unduh/lihat berkas dokumen (otorisasi diperiksa di controller lewat Policy)
Route::middleware(['auth', 'active', 'password.changed'])->group(function () {
    Route::get('/documents/{document}/download', DocumentDownloadController::class)->name('documents.download');
    Route::get('/transaction-files/{file}/download', TransactionFileDownloadController::class)->name('transaction-files.download');
    Route::get('/transactions/{transaction}/receipt', TransactionReceiptController::class)->name('transactions.receipt');

    // Notifikasi dalam aplikasi (hanya milik pengguna yang login)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
});

// Admin
Route::middleware(['auth', 'active', 'role:admin', 'password.changed'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

        Route::post('users/{user}/reset-password', [Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/{user}/approve-registration', [Admin\RegistrationController::class, 'approve'])->name('users.approve-registration');
        Route::post('users/{user}/reject-registration', [Admin\RegistrationController::class, 'reject'])->name('users.reject-registration');
        Route::resource('users', Admin\UserController::class)->except(['show']);

        Route::resource('categories', Admin\CategoryController::class)->except(['show']);
        Route::resource('service-types', Admin\ServiceTypeController::class)->only(['index', 'edit', 'update']);
        Route::resource('documents', Admin\DocumentController::class);

        // Antrean, verifikasi, dan serah terima layanan
        Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/review', [Admin\VerificationController::class, 'review'])->name('transactions.review');
        Route::post('transactions/{transaction}/advance', [Admin\VerificationController::class, 'advance'])->name('transactions.advance');
        Route::post('transactions/{transaction}/handover', [Admin\HandoverController::class, 'store'])->name('transactions.handover');
        Route::post('transactions/{transaction}/return', [Admin\HandoverController::class, 'returned'])->name('transactions.return');

        // Riwayat perubahan status seluruh transaksi
        Route::get('histories', [Admin\HistoryController::class, 'index'])->name('histories.index');

        // Laporan: tampilan, cetak, PDF, dan Excel
        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/print', [Admin\ReportController::class, 'print'])->name('reports.print');
        Route::get('reports/pdf', [Admin\ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('reports/excel', [Admin\ReportController::class, 'excel'])->name('reports.excel');
    });

// Siswa
Route::middleware(['auth', 'active', 'role:student', 'password.changed'])
    ->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', Student\DashboardController::class)->name('dashboard');
        Route::get('/documents', Student\DocumentController::class)->name('documents.index');

        // Tiga layanan utama
        Route::get('/services/loan', [Student\LoanController::class, 'create'])->name('loan.create');
        Route::post('/services/loan', [Student\LoanController::class, 'store'])->name('loan.store');
        Route::get('/services/legalization', [Student\LegalizationController::class, 'create'])->name('legalization.create');
        Route::post('/services/legalization', [Student\LegalizationController::class, 'store'])->name('legalization.store');
        Route::get('/services/diploma', [Student\DiplomaController::class, 'create'])->name('diploma.create');
        Route::post('/services/diploma', [Student\DiplomaController::class, 'store'])->name('diploma.store');

        // Riwayat dan detail pengajuan milik sendiri
        Route::get('/transactions', [Student\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [Student\TransactionController::class, 'show'])->name('transactions.show');
        Route::put('/transactions/{transaction}/resubmit', [Student\DiplomaController::class, 'resubmit'])->name('diploma.resubmit');
    });
