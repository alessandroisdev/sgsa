<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - SGSA</title>
    
    <!-- Vite -->
    @vite(['resources/sass/app.scss', 'resources/ts/app.ts'])
    
    <!-- Custom Page Styles -->
    @stack('styles')
</head>
<body class="bg-light-soft">

    <div class="d-flex w-100 h-100">
        
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 260px;">
            <div class="d-flex align-items-center mb-4 px-2">
                <i class="bi bi-display fs-3 text-white me-2"></i>
                <h4 class="text-white mb-0 fw-bold">SGSA</h4>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link px-3 py-2">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mt-3 mb-1">
                    <small class="text-white-50 px-3 text-uppercase fw-bold" style="font-size: 0.75rem;">Gerenciamento</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.units.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Unidades
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.areas.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.areas.*') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt"></i> Áreas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.services.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                        <i class="bi bi-list-check"></i> Serviços / Filas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.priorities.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.priorities.*') ? 'active' : '' }}">
                        <i class="bi bi-star"></i> Prioridades
                    </a>
                </li>
                <li class="nav-item mt-3 mb-1">
                    <small class="text-white-50 px-3 text-uppercase fw-bold" style="font-size: 0.75rem;">Dispositivos</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.tvs.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.tvs.*') ? 'active' : '' }}">
                        <i class="bi bi-display"></i> TVs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.totems.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.totems.*') ? 'active' : '' }}">
                        <i class="bi bi-terminal"></i> Totens
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.counters.index') }}" class="nav-link px-3 py-2 {{ request()->routeIs('admin.counters.*') ? 'active' : '' }}">
                        <i class="bi bi-person-workspace"></i> Guichês
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
            <!-- Topbar -->
            <header class="bg-white px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 fw-semibold text-dark">@yield('title')</h5>
                </div>
                <div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> Admin
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#">Sair</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-4 flex-grow-1 overflow-auto">
                @yield('content')
            </main>
        </div>
        
    </div>

    <!-- Global Toast Container -->
    <div id="toast-container"></div>
    
    <!-- Custom Page Scripts -->
    @stack('scripts')
</body>
</html>
