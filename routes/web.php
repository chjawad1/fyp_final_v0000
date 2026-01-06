<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController as StudentProjectController;
use App\Http\Controllers\SupervisorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ScopeDocumentController as AdminScopeDocumentController;
use App\Http\Controllers\Admin\DocumentTemplateController;
use App\Http\Controllers\Admin\ReportController;
use App\Models\DocumentTemplate;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController as MainDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Admin\CommitteeController;
use App\Http\Controllers\Admin\DefenceSessionController;
use App\Http\Controllers\Member\SessionEvaluationController;
use App\Http\Controllers\Admin\EvaluatorController as AdminEvaluatorController;
use App\Http\Controllers\Admin\FypPhaseController;
use App\Http\Controllers\Admin\ScopeReviewController;

// --- Publicly Accessible Routes ---
// Route::get('/', function () {
//     return view('welcome');
// });
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/', [AuthenticatedSessionController::class, 'store']);
});

Route::get('/dashboard', [MainDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- General Authenticated Routes ---
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Supervisor Directory
    Route::get('/supervisors', [SupervisorController::class, 'directory'])->name('supervisors.directory');

    // Scope documents download (existing)
    Route::get('/scope-documents/{scope_document}/download', [StudentProjectController::class, 'downloadScopeDocument'])
        ->name('scope.document.download');

    // SDM-4: Document templates download for all authenticated users
    Route::get('/templates/{template}/download', [DocumentTemplateController::class, 'download'])
        ->name('templates.download');

    Route::get('/templates/{template}/view', [DocumentTemplateController::class, 'view'])
        ->name('templates.view');
});

// --- Student Specific Routes ---
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::resource('projects', StudentProjectController::class);
    Route::get('/projects/{project}/scope/create', [StudentProjectController::class, 'createScopeDocument'])->name('projects.scope.create');
    Route::post('/projects/{project}/scope', [StudentProjectController::class, 'storeScopeDocument'])->name('projects.scope.store');
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::delete('/projects/{project}', [StudentProjectController::class, 'destroy'])->name('projects.destroy');
     // Add scope document delete route
    Route::delete('/projects/{project}/scope/{scope_document}', [StudentProjectController::class, 'destroyScopeDocument'])->name('projects.scope.destroy');
});

// --- Supervisor Specific Routes ---
Route::middleware(['auth', 'verified', 'role:supervisor'])->group(function () {
    Route::get('/supervisor/projects', [SupervisorController::class, 'index'])->name('supervisor.projects');
    Route::patch('/supervisor/projects/{project}/approve', [SupervisorController::class, 'approve'])->name('supervisor.projects.approve');
    Route::patch('/supervisor/projects/{project}/reject', [SupervisorController::class, 'reject'])->name('supervisor.projects.reject');
    Route::patch('/supervisor/projects/{project}/complete', [SupervisorController::class, 'complete'])->name('supervisor.projects.complete');
    Route::get('/supervisor/history', [SupervisorController::class, 'history'])->name('supervisor.history');
    Route::get('/supervisor/profile', [SupervisorController::class, 'editProfile'])->name('supervisor.profile.edit');
    Route::patch('/supervisor/profile', [SupervisorController::class, 'updateProfile'])->name('supervisor.profile.update');
    Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('supervisor.dashboard');
    // NEW: Supervisor Scope Document Review Routes
    Route::patch('/supervisor/scope-reviews/{scopeDocument}/approve', [SupervisorController::class, 'approveScopeDocument'])->name('supervisor.scope-reviews.approve');
    Route::patch('/supervisor/scope-reviews/{scopeDocument}/request-revision', [SupervisorController::class, 'requestScopeRevision'])->name('supervisor.scope-reviews.request-revision');
});

// Committee member routes
    Route::middleware(['auth', 'evaluator'])->prefix('member')->name('member.')->group(function ()  {
        Route::get('sessions', [SessionEvaluationController::class, 'index'])->name('sessions.index');
        Route::get('assignments/{assignment}/evaluate', [SessionEvaluationController::class, 'evaluate'])->name('sessions.evaluate');
        Route::post('assignments/{assignment}/submit', [SessionEvaluationController::class, 'submit'])->name('sessions.submit');
    });

// --- Admin Routes ---
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class)->only(['index', 'edit', 'update']);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::get('/users/upload', [AdminUserController::class, 'showUploadForm'])->name('users.upload.form');
    Route::post('/users/upload', [AdminUserController::class, 'processUpload'])->name('users.upload.process');
    Route::get('/users/upload/template', [AdminUserController::class, 'downloadTemplate'])->name('users.template.download');
    Route::get('projects', [AdminProjectController::class, 'index'])->name('projects.index');
    // Enhanced user management routes
    Route::patch('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::patch('/users/{user}/update-slots', [AdminUserController::class, 'updateSlots'])->name('users.update-slots');
    
    // Reports
    Route::resource('reports', ReportController::class)->except(['show', 'edit', 'update']);
    Route::get('reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');

    // SDM-4: Authoritative Templates UI
    Route::get('/templates', [DocumentTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [DocumentTemplateController::class, 'store'])->name('templates.store');
    Route::delete('/templates/{template}', [DocumentTemplateController::class, 'destroy'])->name('templates.destroy');

    // Recycle Bin (soft-deletes)
    Route::get('/templates/trash', [DocumentTemplateController::class, 'trash'])->name('templates.trash');
    Route::patch('/templates/{template}/restore', [DocumentTemplateController::class, 'restore'])->name('templates.restore');
    Route::delete('/templates/{template}/force', [DocumentTemplateController::class, 'forceDelete'])->name('templates.force-delete');
    Route::get('/templates/cleartrash', [DocumentTemplateController::class, 'clearTrash'])->name('templates.trash.clear');
    Route::delete('/templates/cleartrash', [DocumentTemplateController::class, 'clearTrash'])->name('templates.trash.clear');

    // Scope Document versioning mgmt (existing)
    Route::get('/projects/{project}/scope-documents', [AdminScopeDocumentController::class, 'index'])->name('projects.scope-documents.index');
    Route::get('/projects/{project}/scope-documents/create', [AdminScopeDocumentController::class, 'create'])->name('projects.scope-documents.create');
    Route::post('/projects/{project}/scope-documents', [AdminScopeDocumentController::class, 'store'])->name('projects.scope-documents.store');
    Route::resource('projects', AdminProjectController::class);
    Route::patch('/projects/{project}/status', [AdminProjectController::class, 'updateStatus'])->name('projects.update-status');

    // NEW: Evaluator Directory (admin-only)
    Route::get('/evaluators', [AdminEvaluatorController::class, 'index'])->name('evaluators.index');
    Route::get('/evaluators/create', [AdminEvaluatorController::class, 'create'])->name('evaluators.create');
    Route::post('/evaluators', [AdminEvaluatorController::class, 'store'])->name('evaluators.store');
    Route::delete('/evaluators/{evaluator}', [AdminEvaluatorController::class, 'destroy'])->name('evaluators.destroy');

    // Committees (ensure add/remove uses evaluator-only flow)
    Route::resource('committees', \App\Http\Controllers\Admin\CommitteeController::class);
    Route::post('committees/{committee}/members', [\App\Http\Controllers\Admin\CommitteeController::class, 'addMember'])
        ->name('committees.members.add');
    Route::delete('committees/{committee}/members/{user}', [\App\Http\Controllers\Admin\CommitteeController::class, 'removeMember'])
        ->name('committees.members.remove');

    // Defence Sessions
    Route::resource('defence-sessions', \App\Http\Controllers\Admin\DefenceSessionController::class)->parameters([
        'defence-sessions' => 'defenceSession'
    ])->except(['edit', 'update']);
    Route::post('defence-sessions/{defenceSession}/evaluators', [\App\Http\Controllers\Admin\DefenceSessionController::class, 'assignEvaluators'])
        ->name('defence-sessions.assign-evaluators');
    Route::patch('defence-sessions/{defenceSession}/status', [\App\Http\Controllers\Admin\DefenceSessionController::class, 'updateStatus'])
        ->name('defence-sessions.update-status');

    Route::prefix('phases')->name('phases.')->group(function () {
        Route::get('/', [FypPhaseController::class, 'index'])->name('index');
        Route::get('/create', [FypPhaseController::class, 'create'])->name('create');
        Route::post('/', [FypPhaseController::class, 'store'])->name('store');
        Route::get('/{phase}', [FypPhaseController::class, 'show'])->name('show');
        Route::get('/{phase}/edit', [FypPhaseController::class, 'edit'])->name('edit');
        Route::put('/{phase}', [FypPhaseController::class, 'update'])->name('update');
        Route::delete('/{phase}', [FypPhaseController::class, 'destroy'])->name('destroy');
        Route::patch('/{phase}/toggle-status', [FypPhaseController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{phase}/toggle-late', [FypPhaseController::class, 'toggleLate'])->name('toggle-late');
        Route::patch('/{phase}/extend-deadline', [FypPhaseController::class, 'extendDeadline'])->name('extend-deadline');
    });

    Route::prefix('scope-reviews')->name('scope-reviews.')->group(function () {
        Route::get('/', [ScopeReviewController::class, 'index'])->name('index');
        Route::get('/{scopeDocument}', [ScopeReviewController::class, 'show'])->name('show');
        Route::patch('/{scopeDocument}/approve', [ScopeReviewController::class, 'approve'])->name('approve');
        Route::patch('/{scopeDocument}/reject', [ScopeReviewController::class, 'reject'])->name('reject');
        Route::patch('/{scopeDocument}/request-revision', [ScopeReviewController::class, 'requestRevision'])->name('request-revision');
    });
});

require __DIR__.'/auth.php';