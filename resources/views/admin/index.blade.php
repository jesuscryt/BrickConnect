@extends('layouts.app')

@section('titulo', 'Panel de Administración')

@section('contenido')
<div class="container py-4">

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-shield-lock-fill fs-3 text-danger me-2"></i>
        <h2 class="mb-0">Panel de Administración</h2>
    </div>

    {{-- Mensajes de estado --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tarjetas de estadísticas --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-people-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalUsuarios }}</div>
                        <div class="text-muted small">Usuarios registrados</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-sm btn-outline-primary w-100">
                        Ver usuarios
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-person-slash fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalBaneados }}</div>
                        <div class="text-muted small">Usuarios baneados</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('admin.usuarios', ['estado' => 'baneado']) }}" class="btn btn-sm btn-outline-danger w-100">
                        Ver baneados
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-file-text fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalPosts }}</div>
                        <div class="text-muted small">Publicaciones</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('admin.publicaciones') }}" class="btn btn-sm btn-outline-success w-100">
                        Ver publicaciones
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-briefcase fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalOfertas }}</div>
                        <div class="text-muted small">Ofertas de empleo</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('admin.ofertas') }}" class="btn btn-sm btn-outline-warning w-100">
                        Ver ofertas
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimos usuarios registrados --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Últimos usuarios registrados</h5>
            <a href="{{ route('admin.usuarios') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Registrado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosUsuarios as $u)
                    <tr>
                        <td>
                            <a href="{{ route('perfil.show', $u->id) }}" class="text-decoration-none">
                                {{ $u->name }}
                            </a>
                            @if($u->isAdmin())
                                <span class="badge bg-danger ms-1">Admin</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $u->email }}</td>
                        <td class="text-muted small">{{ $u->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($u->isBanned())
                                <span class="badge bg-danger">Baneado</span>
                            @else
                                <span class="badge bg-success">Activo</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
