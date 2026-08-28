<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\Onboarding\CreatePlatformController;
use App\Http\Controllers\Onboarding\SetPasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site: landing pages + the "create your platform" wizard.
| No {tenant} prefix — these are defined first and unprefixed, so they
| always win over the catch-all /{tenant}/... group below regardless of
| what slugs get taken later. (Signup also blocks these as slugs — see
| config/tenancy.php reserved_slugs — this ordering is a second, belt-
| and-braces layer of protection, not the only one.)
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/about', [LandingController::class, 'about'])->name('landing.about');
Route::get('/services', [LandingController::class, 'services'])->name('landing.services');
Route::get('/services/{service}', [LandingController::class, 'serviceDetails'])->name('landing.service-details');
Route::get('/contact', [LandingController::class, 'contact'])->name('landing.contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->name('landing.contact.store')
    ->middleware('throttle:5,1'); // 5 submissions per minute per IP — cheap spam guard on a public, unauthenticated form.

Route::prefix('create')->name('onboarding.')->group(function () {
    Route::get('/', [CreatePlatformController::class, 'step1'])->name('step1');
    Route::post('/', [CreatePlatformController::class, 'step1Store'])->name('step1.store');

    Route::get('/name-a-link', [CreatePlatformController::class, 'step2'])->name('step2');
    Route::post('/name-a-link', [CreatePlatformController::class, 'step2Store'])->name('step2.store');
    Route::get('/check-slug', [CreatePlatformController::class, 'checkSlug'])->name('check-slug');

    Route::get('/your-email', [CreatePlatformController::class, 'step3'])->name('step3');
    Route::post('/your-email', [CreatePlatformController::class, 'step3Store'])->name('step3.store');

    Route::get('/thank-you/{tenant}', [CreatePlatformController::class, 'thankYou'])->name('thank-you');
});

Route::get('/set-password/{tenant}/{token}', [SetPasswordController::class, 'show'])
    ->name('tenants.set-password.show')
    ->middleware('signed');
Route::post('/set-password/{tenant}/{token}', [SetPasswordController::class, 'store'])
    ->name('tenants.set-password.store')
    ->middleware('signed');

/*
|--------------------------------------------------------------------------
| Tenant app: every community's committee panel, unchanged from the
| single-tenant version of this app except for the {tenant}/ prefix and
| the 'tenant-web' middleware group (see Kernel) instead of 'web'.
|--------------------------------------------------------------------------
*/

Route::prefix('{tenant}')->middleware('tenant-web')->group(function () {
    Route::redirect('/', '/{tenant}/login');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::get('/residents/create', [ResidentController::class, 'create'])->name('residents.create');
        Route::post('/residents', [ResidentController::class, 'store'])->name('residents.store');
        Route::get('/residents/{resident}/edit', [ResidentController::class, 'edit'])->name('residents.edit');
        Route::put('/residents/{resident}', [ResidentController::class, 'update'])->name('residents.update');
        Route::patch('/residents/{resident}/toggle', [ResidentController::class, 'deactivate'])->name('residents.toggle');

        Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
        Route::get('/fees/create', [FeeController::class, 'create'])->name('fees.create');
        Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
        Route::get('/fees/{fee}/edit', [FeeController::class, 'edit'])->name('fees.edit');
        Route::put('/fees/{fee}', [FeeController::class, 'update'])->name('fees.update');
        Route::patch('/fees/{fee}/toggle', [FeeController::class, 'deactivate'])->name('fees.toggle');
        Route::get('/fees/{fee}/unpaid', [FeeController::class, 'unpaid'])->name('fees.unpaid');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

        Route::get('/funds', [FundController::class, 'index'])->name('funds.index');
        Route::get('/funds/create', [FundController::class, 'create'])->name('funds.create');
        Route::post('/funds', [FundController::class, 'store'])->name('funds.store');
        Route::get('/funds/{fund}/edit', [FundController::class, 'edit'])->name('funds.edit');
        Route::put('/funds/{fund}', [FundController::class, 'update'])->name('funds.update');
        Route::patch('/funds/{fund}/toggle', [FundController::class, 'archive'])->name('funds.toggle');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::patch('/projects/{project}/toggle', [ProjectController::class, 'archive'])->name('projects.toggle');

        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::patch('/employees/{employee}/toggle', [EmployeeController::class, 'toggle'])->name('employees.toggle');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');

        // Read-only: no store/update/destroy routes exist for the audit log.
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    });
});
