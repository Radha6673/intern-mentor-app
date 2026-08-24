<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\InternTaskController;


use App\Http\Controllers\AdminMentorController;
use App\Http\Controllers\AdminInternController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\PerformanceReportController;
use App\Http\Controllers\MemberController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Dynamic Role-based Members Index Route
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/mentor/tasks', [TaskController::class, 'index'])->name('mentor.tasks.index');
    Route::get('/mentor/tasks/history', [TaskController::class, 'history'])->name('mentor.tasks.history');
    Route::get('/mentor/interns', [TaskController::class, 'internsList'])->name('mentor.interns.index');
    Route::post('/mentor/tasks', [TaskController::class, 'store'])->name('mentor.tasks.store');
    Route::post('/mentor/tasks/{task}/review', [TaskController::class, 'review'])->name('mentor.tasks.review');
    Route::get('/intern/task', [InternTaskController::class, 'index'])->name('intern.tasks.index');
    Route::post('/intern/task/{task}/submit', [InternTaskController::class, 'submit'])->name('intern.tasks.submit');

    // Chat Routes
    Route::get('/chat/{conversation?}', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/start/{user}', [ChatController::class, 'startConversation'])->name('chat.start');
    Route::post('/chat/{conversation}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');

    // AI Features Routes
    Route::post('/ai/polish-text', [AiAssistantController::class, 'polishText'])->name('ai.polish');
    Route::post('/ai/task-assistant', [AiAssistantController::class, 'simplifyTask'])->name('ai.task_assistant');
    Route::post('/ai/generate-task', [AiAssistantController::class, 'generateTask'])->name('ai.generate_task');

    // Performance Report Routes
    Route::get('/reports/performance/{user?}', [PerformanceReportController::class, 'show'])->name('reports.performance');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/mentors', [AdminMentorController::class, 'index'])->name('mentors.index');
    Route::post('/mentors', [AdminMentorController::class, 'store'])->name('mentors.store');
    Route::delete('/mentors/{user}', [AdminMentorController::class, 'destroy'])->name('mentors.destroy');

    Route::get('/interns', [AdminInternController::class, 'index'])->name('interns.index');
    Route::post('/interns/{user}/promote', [AdminInternController::class, 'promote'])->name('interns.promote');
});

require __DIR__ . '/auth.php';
