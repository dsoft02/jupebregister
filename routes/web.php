<?php

use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\PublicResultDownloadController;
use App\Http\Controllers\StatementOfResultController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'landing')->name('home');

Route::get('register', [PublicRegistrationController::class, 'create'])
    ->name('register');

Route::post('register', [PublicRegistrationController::class, 'store'])
    ->name('register.store');

Route::get('register/success/{student}', [PublicRegistrationController::class, 'success'])
    ->name('register.success');

Volt::route('verify', 'pages.verify')
    ->name('verify');

Route::get('results/{result}/download', PublicResultDownloadController::class)
    ->name('results.download');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

/*
|--------------------------------------------------------------------------
| Authenticated staff routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:super_admin|programme_officer|director'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Volt::route('dashboard', 'pages.dashboard')
            ->name('dashboard');

        // Students
        Volt::route('students', 'pages.admin.students.index')
            ->name('students.index');

        Volt::route('students/create', 'pages.admin.students.create')
            ->name('students.create');

        Volt::route('students/{student}', 'pages.admin.students.show')
            ->name('students.show');

        Volt::route('students/{student}/edit', 'pages.admin.students.edit')
            ->name('students.edit');

        Volt::route('students/{student}/results', 'pages.admin.results.entry')
            ->name('results.entry');

        // Subjects
        Volt::route('subjects', 'pages.admin.subjects.index')
            ->name('subjects.index');

        // Results
        Volt::route('results', 'pages.admin.results.index')
            ->name('results.index');

        Route::get('results/{result}/pdf', [StatementOfResultController::class, 'download'])
            ->name('results.pdf');

        // Import & Export
        Route::get('import-export', [ImportExportController::class, 'create'])
            ->name('import-export.create');

        Route::post('import', [ImportExportController::class, 'import'])
            ->name('import.store');

        Route::get('export/{format}', [ImportExportController::class, 'export'])
            ->name('export');

        // Results Import & Export
        Route::post('results/import', [ImportExportController::class, 'importResults'])
            ->name('results.import');

        Route::get('results/export/{format}', [ImportExportController::class, 'exportResults'])
            ->name('results.export');

        // Subjects Import
        Route::post('subjects/import', [ImportExportController::class, 'importSubjects'])
            ->name('subjects.import');

        // Sample Templates
        Route::get('sample-template/{type}', [ImportExportController::class, 'sampleTemplate'])
            ->name('sample-template');

        // Settings
        Volt::route('settings', 'pages.admin.settings')
            ->name('settings');

        // Users
        Volt::route('users', 'pages.admin.users.index')
            ->name('users.index');
    });

require __DIR__.'/auth.php';
