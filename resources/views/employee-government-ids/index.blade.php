@extends('layouts.adminlte')

@section('title', 'Employee Government IDs')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Employee Government IDs</h3>
                    <a href="{{ route('employee-government-ids.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Government ID
                    </a>
                </div>
                <div class="card-body">
                    @if($employeeGovernmentIds->isEmpty())
                        <p class="text-muted mb-0">No records found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>SSS</th>
                                        <th>PhilHealth</th>
                                        <th>PAG-IBIG</th>
                                        <th>TIN</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeeGovernmentIds as $employeeGovernmentId)
                                        <tr>
                                            <td>{{ $employeeGovernmentId->employee?->first_name }} {{ $employeeGovernmentId->employee?->last_name }}</td>
                                            <td>{{ $employeeGovernmentId->sss_number }}</td>
                                            <td>{{ $employeeGovernmentId->philhealth_number }}</td>
                                            <td>{{ $employeeGovernmentId->pagibig_number }}</td>
                                            <td>{{ $employeeGovernmentId->tin_number }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('employee-government-ids.show', $employeeGovernmentId) }}" class="btn btn-info btn-sm">View</a>
                                                <a href="{{ route('employee-government-ids.edit', $employeeGovernmentId) }}" class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('employee-government-ids.destroy', $employeeGovernmentId) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Archive</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
