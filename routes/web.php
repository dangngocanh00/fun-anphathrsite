<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\JobAdminController;
use App\Http\Controllers\Admin\JobFormController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\PipelineController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\RefController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobController::class, 'index'])->name('home');
Route::get('/jobs/{slug}', [JobController::class, 'show'])->name('jobs.show');
Route::post('/jobs/{slug}/apply', [JobController::class, 'apply'])
    ->middleware('throttle:8,1')
    ->name('jobs.apply');

Route::get('/ref/{code}', [RefController::class, 'enter'])
    ->where('code', '[A-Za-z0-9]{4,20}')
    ->name('ref.enter');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login.show');
    Route::post('login', [LoginController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('inbox', [InboxController::class, 'index'])->name('inbox');
        Route::post('inbox/{candidate}/assign', [InboxController::class, 'assign'])->name('inbox.assign');
        Route::post('inbox/{candidate}/analyze', [InboxController::class, 'analyze'])->name('inbox.analyze');

        Route::get('candidates', [CandidateController::class, 'index'])->name('candidates.index');
        Route::get('candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
        Route::delete('candidates/{candidate}', [CandidateController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('candidates.destroy');

        Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline');
        Route::get('pipeline/{candidate}', [PipelineController::class, 'show'])->name('pipeline.show');
        Route::post('pipeline/{candidate}/move', [PipelineController::class, 'move'])->name('pipeline.move');
        Route::post('pipeline/{candidate}/note', [PipelineController::class, 'note'])->name('pipeline.note');

        Route::middleware('role:admin|hr_manager')->group(function () {
            Route::get('performance', [PerformanceController::class, 'index'])->name('performance');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('jobs', [JobAdminController::class, 'index'])->name('jobs.index');
            Route::get('jobs/create', [JobAdminController::class, 'create'])->name('jobs.create');
            Route::post('jobs', [JobAdminController::class, 'store'])->name('jobs.store');
            Route::get('jobs/{job}/edit', [JobAdminController::class, 'edit'])->name('jobs.edit');
            Route::put('jobs/{job}', [JobAdminController::class, 'update'])->name('jobs.update');
            Route::patch('jobs/{job}/toggle', [JobAdminController::class, 'toggle'])->name('jobs.toggle');
            Route::delete('jobs/{job}', [JobAdminController::class, 'destroy'])->name('jobs.destroy');

            Route::get('jobs/{job}/form', [JobFormController::class, 'index'])->name('jobs.form');
            Route::post('jobs/{job}/form/fields', [JobFormController::class, 'store'])->name('jobs.form.store');
            Route::put('jobs/{job}/form/fields/{field}', [JobFormController::class, 'update'])->name('jobs.form.update');
            Route::delete('jobs/{job}/form/fields/{field}', [JobFormController::class, 'destroy'])->name('jobs.form.destroy');
            Route::post('jobs/{job}/form/reorder', [JobFormController::class, 'reorder'])->name('jobs.form.reorder');

            Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
            Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
            Route::put('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
            Route::put('accounts/{account}/ref-code', [AccountController::class, 'updateRefCode'])->name('accounts.refCode');
            Route::post('accounts/{account}/reset-password', [AccountController::class, 'resetPassword'])->name('accounts.resetPassword');
            Route::patch('accounts/{account}/toggle', [AccountController::class, 'toggleActive'])->name('accounts.toggle');
            Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
        });
    });
});
