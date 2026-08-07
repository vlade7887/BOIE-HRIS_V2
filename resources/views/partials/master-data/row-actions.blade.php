<a href="{{ route($routePrefix . '.show', $record) }}" class="btn btn-sm btn-info"><i class="fas fa-eye me-1" aria-hidden="true"></i>View</a>
<a href="{{ route($routePrefix . '.edit', $record) }}" class="btn btn-sm btn-warning"><i class="fas fa-pen me-1" aria-hidden="true"></i>Edit</a>
@if ($record->trashed())
    <form action="{{ route($routePrefix . '.restore', $record->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-rotate-left me-1" aria-hidden="true"></i>Restore</button>
    </form>
@else
    <form action="{{ route($routePrefix . '.archive', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this {{ $archiveLabel }}?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-box-archive me-1" aria-hidden="true"></i>Archive</button>
    </form>
@endif
