<?php

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
use App\Http\Controllers\EmploymentStatusController;
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
    Route::resource('companies', CompanyController::class);
    Route::post('companies/{company}/archive', [CompanyController::class, 'archive'])->name('companies.archive');
    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::resource('bases', BaseController::class);
    Route::resource('units', UnitController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('employment-statuses', EmploymentStatusController::class);
    Route::resource('employee-classes', EmployeeClassController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('employee-contacts', EmployeeContactController::class);
    Route::resource('employee-addresses', EmployeeAddressController::class);
    Route::resource('employee-government-ids', EmployeeGovernmentIdController::class);
    Route::resource('employee-emergency-contacts', EmployeeEmergencyContactController::class);
    Route::resource('employee-documents', EmployeeDocumentController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
