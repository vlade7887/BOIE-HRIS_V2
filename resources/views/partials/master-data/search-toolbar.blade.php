<form method="GET" action="{{ route($routePrefix . '.index') }}" class="row g-2 mb-4 align-items-end">
    <div class="col-md-6">
        <label for="master-data-search" class="form-label">Search records</label>
        <input id="master-data-search" type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
    </div>
    <div class="col-md-2">
        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
    </div>
    <div class="col-md-2">
        <a href="{{ route($routePrefix . '.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a>
    </div>
</form>
