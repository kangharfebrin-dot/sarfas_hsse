<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SarfasController;
use App\Http\Controllers\ChecklistTemplateController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\EnsureRole;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/login/quick', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected application routes
Route::middleware([EnsureApiAuthenticated::class])->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Sarfas Assets
    Route::get('/sarfas', [SarfasController::class, 'index'])->name('sarfas.index');
    Route::post('/sarfas', [SarfasController::class, 'store'])->name('sarfas.store')->middleware(EnsureRole::class . ':super_admin,admin_hsse');
    Route::get('/sarfas/{id}', [SarfasController::class, 'show'])->name('sarfas.show');
    Route::match(['post', 'put', 'patch'], '/sarfas/{id}', [SarfasController::class, 'update'])->name('sarfas.update')->middleware(EnsureRole::class . ':super_admin,admin_hsse');

    // 3. Master Checklist Templates
    Route::get('/master-checklist', [ChecklistTemplateController::class, 'index'])->name('master-checklist.index');
    Route::post('/master-checklist', [ChecklistTemplateController::class, 'store'])->name('master-checklist.store')->middleware(EnsureRole::class . ':super_admin,admin_hsse');
    Route::get('/master-checklist/{id}', [ChecklistTemplateController::class, 'show'])->name('master-checklist.show');

    // 4. Checklist Inspections
    Route::get('/inspections', [InspectionController::class, 'index'])->name('inspections.index');
    Route::get('/inspections/create', [InspectionController::class, 'create'])->name('inspections.create')->middleware(EnsureRole::class . ':super_admin,admin_hsse,inspektor');
    Route::get('/inspections/form/{id?}', [InspectionController::class, 'form'])->name('inspections.form')->middleware(EnsureRole::class . ':super_admin,admin_hsse,inspektor');
    Route::post('/inspections/draft/{id?}', [InspectionController::class, 'saveDraft'])->name('inspections.saveDraft')->middleware(EnsureRole::class . ':super_admin,admin_hsse,inspektor');
    Route::post('/inspections/{id}/submit', [InspectionController::class, 'submit'])->name('inspections.submit')->middleware(EnsureRole::class . ':super_admin,admin_hsse,inspektor');
    Route::get('/inspections/{id}', [InspectionController::class, 'show'])->name('inspections.show');
    Route::post('/inspections/{id}/review', [InspectionController::class, 'review'])->name('inspections.review')->middleware(EnsureRole::class . ':super_admin,admin_hsse,approver');

    // 5. Findings & Follow-up
    Route::get('/findings', [FindingController::class, 'index'])->name('findings.index');
    Route::get('/findings/{id}', [FindingController::class, 'show'])->name('findings.show');
    Route::post('/findings/{id}/action', [FindingController::class, 'updateAction'])->name('findings.updateAction');
    Route::post('/findings/{id}/verify', [FindingController::class, 'verifyClose'])->name('findings.verifyClose')->middleware(EnsureRole::class . ':super_admin,admin_hsse,approver');

    // 6. Inspection Schedules
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store')->middleware(EnsureRole::class . ':super_admin,admin_hsse');

    // 7. Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // 8. User Management (Only Super Admin & Admin HSSE)
    Route::middleware([EnsureRole::class . ':super_admin,admin_hsse'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::match(['post', 'put', 'patch'], '/users/{id}', [UserController::class, 'update'])->name('users.update');
    });

    // 9. Audit Logs (Only Super Admin & Admin HSSE)
    Route::middleware([EnsureRole::class . ':super_admin,admin_hsse'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
