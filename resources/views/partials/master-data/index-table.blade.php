@if ($records->isEmpty())
    <div class="alert alert-light border">No {{ $emptyLabel }} found.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th scope="col">Code</th>
                    <th scope="col">Name</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{ $record->{$codeField} }}</td>
                        <td>{{ $record->{$nameField} }}</td>
                        <td><span class="badge {{ $record->trashed() ? 'badge-archived' : ($record->is_active ? 'badge-active' : 'badge-inactive') }}">{{ $record->trashed() ? 'Archived' : ($record->is_active ? 'Active' : 'Inactive') }}</span></td>
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
