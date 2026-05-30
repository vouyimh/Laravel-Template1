<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\TotalBooking;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StaffTaskController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\NotificationController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/2fa-setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa-verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
});

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        'admin'  => redirect()->route('dashboard-analytics'),
        'staff'  => redirect()->route('staff.task'),
        'client' => redirect()->route('client.task'),
        default  => redirect()->route('login'),
    };
})->name('home');

Route::middleware(['auth', '2fa', 'role:admin'])->group(function () {
    Route::get('/admin/analytics', [Analytics::class, 'index'])->name('dashboard-analytics');
    Route::get('/admin', fn() => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/dashboard/total-booking', [TotalBooking::class, 'index'])->name('dashboard-total-booking');

    Route::get('admin/client', [ClientController::class, 'index'])->name('admin.client.index');
    Route::get('admin/client/add-client', [ClientController::class, 'addClient'])->name('admin.client.add-client');

    Route::get('admin/staff/pages-staff-list', [StaffController::class, 'staffList'])->name('admin.staff.pages-staff-list');
    Route::match(['get', 'post'], 'admin/staff/pages-staff-add', [StaffController::class, 'addStaff'])->name('admin.staff.pages-staff-add');
    Route::post('admin/staff/pages-staff-add', [StaffController::class, 'storeStaff'])->name('admin.staff.pages-staff-store');
    Route::get('admin/staff/pages-staff-edit/{id}', [StaffController::class, 'editStaff'])->name('admin.staff.pages-staff-edit');
    Route::put('admin/staff/pages-staff-edit/{id}', [StaffController::class, 'updateStaff'])->name('admin.staff.pages-staff-update');
    Route::delete('admin/staff/pages-staff-delete/{id}', [StaffController::class, 'deleteStaff'])->name('admin.staff.pages-staff-delete');
});

Route::middleware(['auth', '2fa', 'role:staff'])->group(function () {
    Route::get('/staff', [TaskController::class, 'index'])->name('staff.task');
});

Route::middleware(['auth', '2fa', 'role:client'])->group(function () {
    Route::get('/client', [TaskController::class, 'index'])->name('client.task');
});

Route::middleware(['auth', '2fa'])->group(function () {
    Route::resource('/tasks', TaskController::class);

    // Staff workflow actions (admin can also call these)
    Route::post('/tasks/{task}/start',           [TaskController::class, 'startTask'])->name('tasks.start');
    Route::post('/tasks/{task}/upload',          [TaskController::class, 'uploadProof'])->name('tasks.upload');
    Route::delete('/tasks/{task}/files/{file}',  [TaskController::class, 'removeProof'])->name('tasks.files.destroy');
    Route::post('/tasks/{task}/complete',        [TaskController::class, 'completeTask'])->name('tasks.complete');

    // StaffTask Kanban board
    Route::get('/stafftask',                        [StaffTaskController::class, 'index'])->name('stafftask.board');
    Route::get('/stafftask/create',                 [StaffTaskController::class, 'create'])->name('stafftask.create');
    Route::post('/stafftask',                       [StaffTaskController::class, 'store'])->name('stafftask.store');
    Route::get('/stafftask/{task}',                 [StaffTaskController::class, 'show'])->name('stafftask.show');
    Route::get('/stafftask/{task}/edit',            [StaffTaskController::class, 'edit'])->name('stafftask.edit');
    Route::put('/stafftask/{task}',                 [StaffTaskController::class, 'update'])->name('stafftask.update');
    Route::delete('/stafftask/{task}',              [StaffTaskController::class, 'destroy'])->name('stafftask.destroy');
    Route::post('/stafftask/{task}/move',           [StaffTaskController::class, 'move'])->name('stafftask.move');
    Route::post('/stafftask/{task}/start',          [StaffTaskController::class, 'startTask'])->name('stafftask.start');
    Route::post('/stafftask/{task}/upload',         [StaffTaskController::class, 'uploadProof'])->name('stafftask.upload');
    Route::get('/stafftask/{task}/files/{file}',    [StaffTaskController::class, 'serveFile'])->name('stafftask.files.show');
    Route::delete('/stafftask/{task}/files/{file}', [StaffTaskController::class, 'removeProof'])->name('stafftask.files.destroy');
    Route::post('/stafftask/{task}/complete',       [StaffTaskController::class, 'completeTask'])->name('stafftask.complete');

    // Calendar
    Route::get('/calendar',                   [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events',            [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendar/events',           [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{event}',    [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // Chat
    Route::get('/room/{roomId}', [RoomController::class, 'oneRoom'])->name('room');
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::post('/reactions', [MessageController::class, 'react']);
    Route::post('/start_chat', [MessageController::class, 'startChat']);
    Route::post('/upload-file', [MessageController::class, 'uploadFile']);
    Route::get('/chat-by-phone/{phoneNumber}', [MessageController::class, 'chatByPhone']);
    Route::get('/chat-users', [MessageController::class, 'getUserListForChat']);
    Route::get('/users', fn() => \App\Models\User::select('id', 'name')->get());

    // Web Push
    Route::get('/push/public-key', [\App\Http\Controllers\PushSubscriptionController::class, 'publicKey']);
    Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store']);
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy']);

    // Notifications
    Route::get('/notifications',                 [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count',    [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{id}/read',     [NotificationController::class, 'markAsRead']);
});

Route::get('/lang/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'fr'])) {
        abort(404);
    }
    session(['locale' => $locale]);
    return back();
})->name('lang.switch');

// Layout demo pages
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// Account settings
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::post('/pages/account-settings-account', [AccountSettingsAccount::class, 'update'])->name('pages-account-settings-account.update');

// Auth demo pages
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// UI
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');

require __DIR__ . '/auth.php';
