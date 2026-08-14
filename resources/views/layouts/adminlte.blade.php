<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'BOIE HRIS'))</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.4/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle navigation"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="User menu">
                        <i class="fas fa-user-circle"></i>
                        <span class="ml-2">{{ Auth::user()?->name ?? 'User' }}</span>
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar elevation-4">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-text font-weight-light">BOIE HRIS</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link {{ request()->routeIs('companies.*', 'bases.*', 'units.*', 'departments.*', 'sections.*', 'positions.*', 'employment-statuses.*', 'employee-classes.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-sitemap"></i>
                                <p>Organization <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}"><p>Company</p></a></li>
                                <li class="nav-item"><a href="{{ route('bases.index') }}" class="nav-link {{ request()->routeIs('bases.*') ? 'active' : '' }}"><p>Base</p></a></li>
                                <li class="nav-item"><a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><p>Unit</p></a></li>
                                <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}"><p>Department</p></a></li>
                                <li class="nav-item"><a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}"><p>Section</p></a></li>
                                <li class="nav-item"><a href="{{ route('positions.index') }}" class="nav-link {{ request()->routeIs('positions.*') ? 'active' : '' }}"><p>Position</p></a></li>
                                <li class="nav-item"><a href="{{ route('employment-statuses.index') }}" class="nav-link {{ request()->routeIs('employment-statuses.*') ? 'active' : '' }}"><p>Employment Status</p></a></li>
                                <li class="nav-item"><a href="{{ route('employee-classes.index') }}" class="nav-link {{ request()->routeIs('employee-classes.*') ? 'active' : '' }}"><p>Employee Class</p></a></li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Employees <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a href="{{ route('employees.index') }}" class="nav-link"><p>Employee List</p></a></li>
                            </ul>
                        </li>

                        <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Attendance</p></a></li>
                        <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Leave</p></a></li>
                        <li class="nav-item"><a href="{{ route('approval-inbox.index') }}" class="nav-link {{ request()->routeIs('approval-inbox.*') ? 'active' : '' }}"><i class="nav-icon fas fa-inbox"></i><p>Approval Inbox</p></a></li>
                        <li class="nav-item"><a href="{{ route('approval-demo.create') }}" class="nav-link {{ request()->routeIs('approval-demo.*') ? 'active' : '' }}"><i class="nav-icon fas fa-route"></i><p>Approval Demo</p></a></li>
                        <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-money-bill-wave"></i><p>Payroll</p></a></li>
                        <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Reports</p></a></li>
                        <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-cogs"></i><p>Settings</p></a></li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>@yield('page_title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                @if ($errors->any())
                    <div class="container-fluid mb-3">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="container-fluid mb-3">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">BOIE HRIS</a>.</strong>
            <div class="float-right d-none d-sm-inline-block">All rights reserved.</div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.4/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html>
