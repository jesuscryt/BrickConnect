@extends('layouts.app')

@section('titulo', 'Gestión de Usuarios - Admin')

@section('contenido')
<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Administración</a></li>
            <li class="breadcrumb-item active">Usuarios</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-people-fill fs-3 text-primary me-2"></i>
        <h2 class="mb-0">Gestión de Usuarios</h2>
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
            <form method="GET" action="{{ route('admin.usuarios') }}" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="busqueda" class="form-control"
                           placeholder="Buscar por nombre o email..."
                           value="{{ request('busqueda') }}">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="activo"   {{ request('estado') === 'activo'   ? 'selected' : '' }}>Activos</option>
                        <option value="baneado"  {{ request('estado') === 'baneado'  ? 'selected' : '' }}>Baneados</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if(request('busqueda') || request('estado'))
                        <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de usuarios --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <span class="text-muted small">{{ $usuarios->total() }} usuarios encontrados</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Profesión</th>
                        <th>Registro</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($u->avatar)
                                    <img src="{{ $u->avatarUrl() }}"
                                         class="rounded-circle" width="36" height="36"
                                         style="object-fit:cover;" alt="">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;">
                                        <i class="bi bi-person text-white small"></i>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('perfil.show', $u->id) }}" class="text-decoration-none fw-semibold">
                                        {{ $u->name }}
                                    </a>
                                    @if($u->isAdmin())
                                        <span class="badge bg-danger ms-1">Admin</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small">{{ $u->email }}</td>
                        <td class="text-muted small">{{ $u->profesion ?? '—' }}</td>
                        <td class="text-muted small">{{ $u->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($u->isBanned())
                                <span class="badge bg-danger">
                                    <i class="bi bi-slash-circle me-1"></i>Baneado
                                </span>
                                <div class="text-muted" style="font-size:0.7rem;">
                                    @if($u->ban_expires_at)
                                        Hasta {{ $u->ban_expires_at->format('d/m/Y H:i') }}
                                    @else
                                        Permanente
                                    @endif
                                </div>
                                @if($u->ban_reason)
                                    <div class="text-muted fst-italic" style="font-size:0.7rem;" title="{{ $u->ban_reason }}">
                                        {{ Str::limit($u->ban_reason, 40) }}
                                    </div>
                                @endif
                            @elseif($u->banned_at)
                                {{-- Baneo expirado --}}
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock me-1"></i>Ban expirado
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Activo
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(!$u->isAdmin())
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    @if($u->isBanned())
                                        <form action="{{ route('admin.usuarios.desbanear', $u) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                    title="Desbanear usuario">
                                                <i class="bi bi-person-check"></i> Desbanear
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalBanear{{ $u->id }}"
                                                title="Banear usuario">
                                            <i class="bi bi-person-slash"></i> Banear
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminar{{ $u->id }}"
                                            title="Eliminar usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                {{-- Modal banear --}}
                                @if(!$u->isBanned())
                                <div class="modal fade" id="modalBanear{{ $u->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.usuarios.banear', $u) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title">
                                                        <i class="bi bi-person-slash text-warning me-1"></i>
                                                        Banear a <strong>{{ $u->name }}</strong>
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Duración del baneo</label>
                                                        <select name="dias" class="form-select" required>
                                                            <option value="1">1 día</option>
                                                            <option value="3">3 días</option>
                                                            <option value="7" selected>7 días</option>
                                                            <option value="15">15 días</option>
                                                            <option value="30">30 días</option>
                                                            <option value="90">90 días</option>
                                                            <option value="permanente">Permanente</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-1">
                                                        <label class="form-label fw-semibold">
                                                            Motivo <span class="text-muted fw-normal">(opcional)</span>
                                                        </label>
                                                        <textarea name="motivo" class="form-control" rows="3"
                                                                  placeholder="Ej: Spam, contenido inapropiado..."
                                                                  maxlength="500"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="bi bi-person-slash me-1"></i>Confirmar baneo
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Modal eliminar usuario --}}
                                <div class="modal fade" id="modalEliminar{{ $u->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h6 class="modal-title">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Eliminar usuario
                                                </h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body small">
                                                <p>Vas a eliminar permanentemente la cuenta de <strong>{{ $u->name }}</strong>.</p>
                                                <p class="text-danger mb-0">Esta acción no se puede deshacer. Se eliminarán también sus publicaciones, ofertas y mensajes.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('admin.usuarios.eliminar', $u) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash me-1"></i>Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No se encontraron usuarios.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
