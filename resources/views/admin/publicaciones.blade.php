@extends('layouts.app')

@section('titulo', 'Gestión de Publicaciones - Admin')

@section('contenido')
<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Administración</a></li>
            <li class="breadcrumb-item active">Publicaciones</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-file-text fs-3 text-success me-2"></i>
        <h2 class="mb-0">Gestión de Publicaciones</h2>
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
            <form method="GET" action="{{ route('admin.publicaciones') }}" class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="busqueda" class="form-control"
                           placeholder="Buscar en el contenido..."
                           value="{{ request('busqueda') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if(request('busqueda'))
                        <a href="{{ route('admin.publicaciones') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de publicaciones --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <span class="text-muted small">{{ $posts->total() }} publicaciones encontradas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Autor</th>
                        <th>Contenido</th>
                        <th>Imagen</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr>
                        <td class="text-nowrap">
                            <a href="{{ route('perfil.show', $post->user_id) }}" class="text-decoration-none small fw-semibold">
                                {{ $post->user->name ?? '(eliminado)' }}
                            </a>
                        </td>
                        <td style="max-width:350px;">
                            <div class="text-truncate small">{{ $post->contenido }}</div>
                        </td>
                        <td>
                            @if($post->imagen)
                                <a href="{{ $post->imagenUrl() }}" target="_blank">
                                    <img src="{{ $post->imagenUrl() }}"
                                         width="50" height="50"
                                         style="object-fit:cover;border-radius:4px;" alt="">
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-muted small text-nowrap">
                            {{ $post->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminarPost{{ $post->id }}">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>

                            {{-- Modal confirmación --}}
                            <div class="modal fade" id="modalEliminarPost{{ $post->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title">¿Eliminar publicación?</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body small">
                                            Esta acción no se puede deshacer.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('admin.publicaciones.destroy', $post) }}" method="POST">
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
                        <td colspan="5" class="text-center text-muted py-4">No se encontraron publicaciones.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($posts->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
