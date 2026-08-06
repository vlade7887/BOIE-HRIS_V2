<form method="GET" action="{{ route($routePrefix . '.index') }}" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name">
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
