@if ($records->isEmpty())
    <div class="alert alert-light border">No {{ $emptyLabel }} found.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{ $record->{$codeField} }}</td>
                        <td>{{ $record->{$nameField} }}</td>
                        <td>{{ $record->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end">
                            @include('partials.master-data.row-actions', ['record' => $record, 'routePrefix' => $routePrefix, 'archiveLabel' => $archiveLabel])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $records->appends(['search' => $search, 'view' => $view])->links() }}
    </div>
@endif
