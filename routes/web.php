<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Support\TicketController;
use App\Http\Controllers\Support\CommentController;
use App\Http\Controllers\Support\EmployeeController;
use App\Http\Controllers\Support\NotificationController;
use App\Http\Controllers\Supervisor\DashboardController;
use App\Http\Controllers\Supervisor\TechnicianController;
use App\Http\Controllers\Supervisor\SlaController;
use App\Http\Controllers\Supervisor\WorkScheduleController;
use App\Http\Controllers\Supervisor\ReportController;
use App\Http\Controllers\Supervisor\TicketController as SupervisorTicketController;
use App\Http\Controllers\Supervisor\NotificationController as SupervisorNotificationController;


// ── Auth Routes (Breeze) ──────────────────────────
require __DIR__.'/auth.php';

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// ── Redirect root berdasarkan role (Sudah Diperbaiki) ───────────────
Route::get('/', function () {
    // 1. Jika BELUM login, langsung redirect ke halaman login dengan bersih (TANPA ERROR FLASH)
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // 2. Jika SUDAH login, pastikan akunnya aktif (Menggantikan fungsi middleware 'active' khusus di halaman ini)
    if (!auth()->user()->is_active) { // 💡 Catatan: Sesuaikan 'is_active' dengan nama kolom status aktif di databasemu
        auth()->logout();
        return redirect()->route('login')->with('error', 'Akun Anda tidak aktif.');
    }

    // 3. Jika aktif, lempar ke dashboard sesuai Role masing-masing
    return match(auth()->user()->role) {
        'it_supervisor' => redirect()->route('supervisor.dashboard'),
        'it_support'    => redirect()->route('support.dashboard'),
        'user'          => redirect()->route('user.dashboard'),
        default         => redirect()->route('login'),
    };
});
Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

// ── Profile (semua role) ──────────────────────────
Route::middleware(['auth', 'active',])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── User Routes ───────────────────────────────────
Route::middleware(['auth', 'active', 'role:user', 'prevent.back'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // User Dashboard
        Route::get('/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])->name('dashboard');
        // ticket history
        Route::get('/tickets/history', [App\Http\Controllers\User\TicketController::class, 'history'])->name('tickets.history');
        // Tickets
        Route::get('/tickets', [App\Http\Controllers\User\TicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets', [App\Http\Controllers\User\TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [App\Http\Controllers\User\TicketController::class, 'show'])->name('tickets.show');

        // Comments
        Route::post('/tickets/{ticket}/comments', [App\Http\Controllers\User\CommentController::class, 'store'])->name('tickets.comments.store');

        // Notifications
        Route::get('/notifications', [App\Http\Controllers\User\NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/mark-all-read', [App\Http\Controllers\User\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
        Route::delete('/notifications/delete-read', [App\Http\Controllers\User\NotificationController::class, 'deleteRead'])->name('notifications.deleteRead');
        Route::patch('/notifications/{id}/read', [App\Http\Controllers\User\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/notifications/{id}/read-redirect', [App\Http\Controllers\User\NotificationController::class, 'readAndRedirect'])->name('notifications.readRedirect');
    });

// ── IT Support Routes ─────────────────────────────
Route::middleware(['auth', 'active', 'role:it_support', 'prevent.back'])
    ->prefix('support')
    ->name('support.')
    ->group(function () {

        // Support routes — tambah di group support
        Route::get('/dashboard', [App\Http\Controllers\Support\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tickets/history', [TicketController::class, 'history'])->name('tickets.history');
        // Tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
        Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
        Route::patch('/tickets/{ticket}/category', [App\Http\Controllers\Support\TicketController::class, 'updateCategory'])->name('tickets.updateCategory');

        // Comments
        Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])->name('tickets.comments.store');

        // Employee management (FR-04)
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/import/template', [App\Http\Controllers\Support\EmployeeImportController::class, 'template'])->name('employees.import.template');
        Route::post('/employees/import', [App\Http\Controllers\Support\EmployeeImportController::class, 'import'])->name('employees.import');
        Route::get('/employees/{user}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::patch('/employees/{user}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::patch('/employees/{user}/toggle', [EmployeeController::class, 'toggle'])->name('employees.toggle');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
        Route::delete('/notifications/delete-read', [NotificationController::class, 'deleteRead'])->name('notifications.deleteRead');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/notifications/{id}/read-redirect', [NotificationController::class, 'readAndRedirect'])->name('notifications.readRedirect');

        // Quick update dari card
        Route::patch('/tickets/{ticket}/priority', [App\Http\Controllers\Support\TicketController::class, 'updatePriority'])->name('tickets.updatePriority');
    });

// ── IT Supervisor Routes ──────────────────────────
Route::middleware(['auth', 'active', 'role:it_supervisor', 'prevent.back'])
    ->prefix('supervisor')
    ->name('supervisor.')
    ->group(function () {

        Route::get('/tickets/history', [SupervisorTicketController::class, 'history'])->name('tickets.history');
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Technician management (FR-05)
        Route::get('/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
        Route::get('/technicians/create', [TechnicianController::class, 'create'])->name('technicians.create');
        Route::post('/technicians', [TechnicianController::class, 'store'])->name('technicians.store');
        Route::get('/technicians/{user}/edit', [TechnicianController::class, 'edit'])->name('technicians.edit');
        Route::patch('/technicians/{user}', [TechnicianController::class, 'update'])->name('technicians.update');
        Route::patch('/technicians/{user}/toggle', [TechnicianController::class, 'toggle'])->name('technicians.toggle');

        // SLA Management (FR-14)
        Route::get('/sla', [SlaController::class, 'index'])->name('sla.index');
        Route::get('/sla/{sla}/edit', [SlaController::class, 'edit'])->name('sla.edit');
        Route::patch('/sla/{sla}', [SlaController::class, 'update'])->name('sla.update');

        // Work Schedule (FR-15)
        Route::get('/work-schedules', [WorkScheduleController::class, 'index'])->name('work-schedules.index');
        Route::patch('/work-schedules/{workSchedule}', [WorkScheduleController::class, 'update'])->name('work-schedules.update');

        // Tickets (view only)
        Route::get('/tickets', [SupervisorTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [SupervisorTicketController::class, 'show'])->name('tickets.show');

        // Reports (FR-17, FR-18)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');

        // Department Management
        Route::get('/departments', [App\Http\Controllers\Supervisor\DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [App\Http\Controllers\Supervisor\DepartmentController::class, 'store'])->name('departments.store');
        Route::patch('/departments/{department}', [App\Http\Controllers\Supervisor\DepartmentController::class, 'update'])->name('departments.update');
        Route::patch('/departments/{department}/toggle', [App\Http\Controllers\Supervisor\DepartmentController::class, 'toggle'])->name('departments.toggle');
        Route::delete('/departments/{department}', [App\Http\Controllers\Supervisor\DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Category Management
        Route::get('/categories', [App\Http\Controllers\Supervisor\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [App\Http\Controllers\Supervisor\CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}', [App\Http\Controllers\Supervisor\CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle', [App\Http\Controllers\Supervisor\CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::delete('/categories/{category}', [App\Http\Controllers\Supervisor\CategoryController::class, 'destroy'])->name('categories.destroy');

        // Category Keywords
        Route::post('/categories/{category}/keywords', [App\Http\Controllers\Supervisor\CategoryController::class, 'storeKeyword'])->name('categories.keywords.store');
        Route::delete('/categories/keywords/{keyword}', [App\Http\Controllers\Supervisor\CategoryController::class, 'destroyKeyword'])->name('categories.keywords.destroy');

        // Priority Keywords
        Route::get('/priority-keywords', [App\Http\Controllers\Supervisor\PriorityKeywordController::class, 'index'])->name('priority-keywords.index');
        Route::post('/priority-keywords', [App\Http\Controllers\Supervisor\PriorityKeywordController::class, 'store'])->name('priority-keywords.store');
        Route::delete('/priority-keywords/{priorityKeyword}', [App\Http\Controllers\Supervisor\PriorityKeywordController::class, 'destroy'])->name('priority-keywords.destroy');


        // Notifications
        Route::get('/notifications', [SupervisorNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/mark-all-read', [SupervisorNotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
        Route::delete('/notifications/delete-read', [SupervisorNotificationController::class, 'deleteRead'])->name('notifications.deleteRead');
        Route::patch('/notifications/{id}/read', [SupervisorNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/notifications/{id}/read-redirect', [SupervisorNotificationController::class, 'readAndRedirect'])->name('notifications.readRedirect');
    });
