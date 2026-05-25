@extends('layouts.app')

@section('titulo', 'Gestión de Ofertas - Admin')

@section('contenido')
<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Administración</a></li>
            <li class="breadcrumb-item active">Ofertas</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-briefcase fs-3 text-warning me-2"></i>
        <h2 class="mb-0">Gestión de Ofertas de Empleo</h2>
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

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ofertas') }}" class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="busqueda" class="form-control"
                           placeholder="Buscar por título o empresa..."
                           value="{{ request('busqueda') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if(request('busqueda'))
                        <a href="{{ route('admin.ofertas') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de ofertas --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <span class="text-muted small">{{ $ofertas->total() }} ofertas encontradas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Empresa</th>
                        <th>Publicado por</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ofertas as $oferta)
                    <tr>
                        <td>
                            <a href="{{ route('ofertas.show', $oferta) }}" class="text-decoration-none small fw-semibold">
                                {{ $oferta->titulo }}
                            </a>
                        </td>
                        <td class="small">{{ $oferta->empresa }}</td>
                        <td class="small">
                            <a href="{{ route('perfil.show', $oferta->user_id) }}" class="text-decoration-none text-muted">
                                {{ $oferta->user->name ?? '(eliminado)' }}
                            </a>
                        </td>
                        <td class="small text-muted">{{ $oferta->ubicacion }}</td>
                        <td>
                            @if($oferta->activa)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Cerrada</span>
                            @endif
                        </td>
                        <td class="text-muted small text-nowrap">
                            {{ $oferta->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminarOferta{{ $oferta->id }}">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>

                            {{-- Modal confirmación --}}
                            <div class="modal fade" id="modalEliminarOferta{{ $oferta->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title">¿Eliminar oferta?</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body small">
                                            Se eliminará la oferta "<strong>{{ $oferta->titulo }}</strong>".
                                            Esta acción no se puede deshacer.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('admin.ofertas.destroy', $oferta) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No se encontraron ofertas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ofertas->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $ofertas->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
