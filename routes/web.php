<?php

use App\Http\Controllers\ApprovalAuditLogController;
use App\Http\Controllers\ApprovalDemoController;
use App\Http\Controllers\ApprovalDelegationController;
use App\Http\Controllers\ApprovalInboxController;
use App\Http\Controllers\ApprovalWorkflowController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeAddressController;
use App\Http\Controllers\EmployeeClassController;
use App\Http\Controllers\EmployeeContactController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeeEmergencyContactController;
use App\Http\Controllers\EmployeeGovernmentIdController;
use App\Http\Controllers\EmployeeUserMappingController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Organization / Master Data
    |--------------------------------------------------------------------------
    */

    Route::resource('companies', CompanyController::class)->except(['destroy']);
    Route::post('companies/{company}/archive', [CompanyController::class, 'archive'])
        ->name('companies.archive');
    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])
        ->name('companies.restore');

    Route::resource('bases', BaseController::class)
        ->except(['destroy'])
        ->parameters(['bases' => 'base']);

    Route::post('bases/{base}/archive', [BaseController::class, 'archive'])
        ->name('bases.archive');
    Route::post('bases/{id}/restore', [BaseController::class, 'restore'])
        ->name('bases.restore');

    Route::resource('units', UnitController::class)->except(['destroy']);
    Route::post('units/{unit}/archive', [UnitController::class, 'archive'])
        ->name('units.archive');
    Route::post('units/{id}/restore', [UnitController::class, 'restore'])
        ->name('units.restore');

    Route::resource('departments', DepartmentController::class)->except(['destroy']);
    Route::post('departments/{department}/archive', [DepartmentController::class, 'archive'])
        ->name('departments.archive');
    Route::post('departments/{id}/restore', [DepartmentController::class, 'restore'])
        ->name('departments.restore');

    Route::resource('sections', SectionController::class)->except(['destroy']);
    Route::post('sections/{section}/archive', [SectionController::class, 'archive'])
        ->name('sections.archive');
    Route::post('sections/{id}/restore', [SectionController::class, 'restore'])
        ->name('sections.restore');

    Route::resource('positions', PositionController::class)->except(['destroy']);
    Route::post('positions/{position}/archive', [PositionController::class, 'archive'])
        ->name('positions.archive');
    Route::post('positions/{id}/restore', [PositionController::class, 'restore'])
        ->name('positions.restore');

    Route::resource('employment-statuses', EmploymentStatusController::class)
        ->except(['destroy']);
    Route::post(
        'employment-statuses/{employmentStatus}/archive',
        [EmploymentStatusController::class, 'archive']
    )->name('employment-statuses.archive');
    Route::post(
        'employment-statuses/{id}/restore',
        [EmploymentStatusController::class, 'restore']
    )->name('employment-statuses.restore');

    Route::resource('employee-classes', EmployeeClassController::class)
        ->except(['destroy']);
    Route::post(
        'employee-classes/{employeeClass}/archive',
        [EmployeeClassController::class, 'archive']
    )->name('employee-classes.archive');
    Route::post(
        'employee-classes/{id}/restore',
        [EmployeeClassController::class, 'restore']
    )->name('employee-classes.restore');

    Route::resource('leave-types', LeaveTypeController::class)->except(['destroy']);
    Route::post('leave-types/{leaveType}/archive', [LeaveTypeController::class, 'archive'])
        ->name('leave-types.archive');
    Route::post('leave-types/{id}/restore', [LeaveTypeController::class, 'restore'])
        ->name('leave-types.restore');

    Route::resource('holidays', HolidayController::class)->except(['destroy']);
    Route::post('holidays/{holiday}/archive', [HolidayController::class, 'archive'])
        ->name('holidays.archive');
    Route::post('holidays/{id}/restore', [HolidayController::class, 'restore'])
        ->name('holidays.restore');

    Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])
        ->name('leave-requests.create');
    Route::get('leave-requests/preview', [LeaveRequestController::class, 'previewFallback']);
    Route::post('leave-requests/preview', [LeaveRequestController::class, 'preview'])
        ->name('leave-requests.preview');
    Route::post('leave-requests/drafts', [LeaveRequestController::class, 'storeDraft'])
        ->name('leave-requests.drafts.store');

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    Route::resource('employees', EmployeeController::class);

    Route::resource('employee-contacts', EmployeeContactController::class);
    Route::resource('employee-addresses', EmployeeAddressController::class);
    Route::resource('employee-government-ids', EmployeeGovernmentIdController::class);
    Route::resource('employee-emergency-contacts', EmployeeEmergencyContactController::class);
    Route::resource('employee-documents', EmployeeDocumentController::class);

    /*
    |--------------------------------------------------------------------------
    | Employee ↔ User Mapping
    |--------------------------------------------------------------------------
    */

    Route::get(
        'employee-user-mappings',
        [EmployeeUserMappingController::class, 'index']
    )->name('employee-user-mappings.index');

    Route::get(
        'employee-user-mappings/{employee}/edit',
        [EmployeeUserMappingController::class, 'edit']
    )->name('employee-user-mappings.edit');

    Route::put(
        'employee-user-mappings/{employee}',
        [EmployeeUserMappingController::class, 'update']
    )->name('employee-user-mappings.update');

    Route::post(
        'employee-user-mappings/{employee}/unmap',
        [EmployeeUserMappingController::class, 'unmap']
    )->name('employee-user-mappings.unmap');

    /*
    |--------------------------------------------------------------------------
    | Approval Workflows
    |--------------------------------------------------------------------------
    */

    Route::resource('approval-workflows', ApprovalWorkflowController::class)
        ->except(['destroy'])
        ->parameters([
            'approval-workflows' => 'approvalWorkflow',
        ]);

    Route::post(
        'approval-workflows/{approvalWorkflow}/archive',
        [ApprovalWorkflowController::class, 'archive']
    )->name('approval-workflows.archive');

    /*
    |--------------------------------------------------------------------------
    | Approval Delegations
    |--------------------------------------------------------------------------
    */

    Route::resource('approval-delegations', ApprovalDelegationController::class)
        ->except(['show', 'destroy'])
        ->parameters([
            'approval-delegations' => 'approvalDelegation',
        ]);

    Route::post(
        'approval-delegations/{approvalDelegation}/revoke',
        [ApprovalDelegationController::class, 'revoke']
    )->name('approval-delegations.revoke');

    Route::post(
        'approval-delegations/{approvalDelegation}/archive',
        [ApprovalDelegationController::class, 'archive']
    )->name('approval-delegations.archive');

    /*
    |--------------------------------------------------------------------------
    | Approval Audit Logs - Read Only
    |--------------------------------------------------------------------------
    */

    Route::resource('approval-audit-logs', ApprovalAuditLogController::class)
        ->only(['index', 'show'])
        ->parameters([
            'approval-audit-logs' => 'approvalAuditLog',
        ]);

    Route::get('approval-demo', [ApprovalDemoController::class, 'create'])
        ->name('approval-demo.create');
    Route::post('approval-demo/preview', [ApprovalDemoController::class, 'preview'])
        ->name('approval-demo.preview');
    Route::get('approval-demo/approvers/search', [ApprovalDemoController::class, 'search'])
        ->name('approval-demo.approvers.search');
    Route::post('approval-demo', [ApprovalDemoController::class, 'store'])
        ->name('approval-demo.store');
    Route::get('approval-demo/{approvalRequest}', [ApprovalDemoController::class, 'show'])
        ->name('approval-demo.show');

    Route::get('approval-inbox', [ApprovalInboxController::class, 'index'])
        ->name('approval-inbox.index');
    Route::get('approval-inbox/{approvalRequest}', [ApprovalInboxController::class, 'show'])
        ->name('approval-inbox.show');
    Route::post('approval-inbox/{approvalRequest}/approve', [ApprovalInboxController::class, 'approve'])
        ->name('approval-inbox.approve');
    Route::post('approval-inbox/{approvalRequest}/reject', [ApprovalInboxController::class, 'reject'])
        ->name('approval-inbox.reject');
    Route::post('approval-requests/{approvalRequest}/cancel', [ApprovalInboxController::class, 'cancel'])
        ->name('approval-requests.cancel');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
