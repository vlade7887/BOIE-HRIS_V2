<a href="{{ route($routePrefix . '.show', $record) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route($routePrefix . '.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
@if ($record->trashed())
    <form action="{{ route($routePrefix . '.restore', $record->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success">Restore</button>
    </form>
@else
    <form action="{{ route($routePrefix . '.archive', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this {{ $archiveLabel }}?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-danger">Archive</button>
    </form>
@endif
