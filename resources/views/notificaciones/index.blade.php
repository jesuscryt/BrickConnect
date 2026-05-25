@extends('layouts.app')

@section('titulo', 'Notificaciones - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">

        {{-- Cabecera de sección --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bell text-warning me-2"></i>Notificaciones
                @if($contarNoLeidas > 0)
                    <span class="badge bg-warning text-dark ms-1" style="font-size:0.72rem;">{{ $contarNoLeidas }} sin leer</span>
                @endif
            </h5>
            @if($contarNoLeidas > 0)
                <button class="btn btn-outline-warning btn-sm" onclick="marcarTodasLeidas(this)">
                    <i class="bi bi-check2-all me-1"></i>Marcar todas como leídas
                </button>
            @endif
        </div>

        {{-- Lista de notificaciones --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            @if($notificaciones->count() > 0)
                @foreach($notificaciones as $notificacion)
                    <div class="notif-item d-flex align-items-start p-3 {{ !$loop->last ? 'border-bottom' : '' }} {{ !$notificacion->leida ? 'notif-unread' : '' }}"
                         data-id="{{ $notificacion->id }}">

                        {{-- Avatar del emisor --}}
                        <div class="me-3 flex-shrink-0">
                            @if($notificacion->emisor?->avatar)
                                <img src="{{ $notificacion->emisor->avatarUrl() }}"
                                     class="rounded-circle"
                                     width="46" height="46"
                                     style="object-fit:cover;"
                                     alt="{{ $notificacion->emisor->name }}">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white notif-avatar-placeholder"
                                     style="width:46px;height:46px;font-size:1.1rem;">
                                    {{ strtoupper(substr($notificacion->emisor?->name ?? '?', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        {{-- Contenido --}}
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <p class="mb-1 lh-sm">
                                    @if($notificacion->tipo === 'solicitud_amistad')
                                        <i class="bi bi-person-plus-fill text-warning me-1"></i>
                                        <strong>{{ $notificacion->emisor?->name ?? 'Usuario eliminado' }}</strong>
                                        te envió una solicitud de contacto
                                    @elseif($notificacion->tipo === 'solicitud_aceptada')
                                        <i class="bi bi-person-check-fill text-success me-1"></i>
                                        <strong>{{ $notificacion->emisor?->name ?? 'Usuario eliminado' }}</strong>
                                        aceptó tu solicitud de contacto
                                    @elseif($notificacion->tipo === 'comentario')
                                        <i class="bi bi-chat-fill text-warning me-1"></i>
                                        <strong>{{ $notificacion->emisor?->name ?? 'Usuario eliminado' }}</strong>
                                        comentó en tu publicación
                                    @elseif($notificacion->tipo === 'aceptacion_oferta')
                                        <i class="bi bi-briefcase-fill text-warning me-1"></i>
                                        <strong>{{ $notificacion->emisor?->name ?? 'Un usuario' }}</strong>
                                        aceptó tu oferta de empleo
                                        @if($notificacion->notificable?->oferta)
                                            <span class="text-muted fw-normal">— {{ $notificacion->notificable->oferta->titulo }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">{{ $notificacion->tipo }}</span>
                                    @endif
                                </p>
                                @if(!$notificacion->leida)
                                    <span class="notif-dot flex-shrink-0" title="No leída"></span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>{{ $notificacion->created_at->tiempoRelativo() }}
                                </small>
                                @if($notificacion->tipo === 'solicitud_amistad')
                                    @php $conexionEstado = $notificacion->notificable; @endphp
                                    @if($conexionEstado && $conexionEstado->estado === 'aceptada')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-people-fill me-1"></i>Contacto aceptado
                                        </span>
                                    @elseif($conexionEstado && $conexionEstado->estado === 'rechazada')
                                        <span class="badge bg-secondary">Solicitud rechazada</span>
                                    @endif
                                @elseif($notificacion->tipo === 'solicitud_aceptada')
                                    <span class="badge bg-success">
                                        <i class="bi bi-people-fill me-1"></i>Ahora sois contactos
                                    </span>
                                @endif
                            </div>

                            {{-- Acciones según tipo --}}
                            @if($notificacion->tipo === 'solicitud_amistad')
                                @php $conexion = $notificacion->notificable; @endphp
                                @if($conexion && $conexion->estado === 'pendiente')
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-warning text-dark fw-semibold"
                                                onclick="aceptarSolicitud({{ $conexion->id }}, {{ $notificacion->id }}, this)">
                                            <i class="bi bi-check-lg"></i> Aceptar
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary"
                                                onclick="rechazarSolicitud({{ $conexion->id }}, {{ $notificacion->id }}, this)">
                                            <i class="bi bi-x-lg"></i> Rechazar
                                        </button>
                                        @if($notificacion->emisor)
                                            <a href="{{ route('perfil.show', $notificacion->emisor) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               onclick="marcarLeidaSilencioso({{ $notificacion->id }})">
                                                <i class="bi bi-person me-1"></i>Ver perfil
                                            </a>
                                        @endif
                                    </div>
                                @elseif($conexion && ($conexion->estado === 'aceptada' || $conexion->estado === 'rechazada'))
                                    @if($notificacion->emisor)
                                        <div class="mt-2">
                                            <a href="{{ route('perfil.show', $notificacion->emisor) }}"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-person me-1"></i>Ver perfil
                                            </a>
                                        </div>
                                    @endif
                                @endif

                            @elseif($notificacion->tipo === 'solicitud_aceptada')
                                @if($notificacion->emisor)
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <a href="{{ route('perfil.show', $notificacion->emisor) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           onclick="marcarLeidaSilencioso({{ $notificacion->id }})">
                                            <i class="bi bi-person me-1"></i>Ver perfil
                                        </a>
                                        <a href="{{ route('chat.show', $notificacion->emisor) }}"
                                           class="btn btn-sm btn-outline-success"
                                           onclick="marcarLeidaSilencioso({{ $notificacion->id }})">
                                            <i class="bi bi-chat me-1"></i>Enviar mensaje
                                        </a>
                                    </div>
                                @endif

                            @elseif($notificacion->tipo === 'comentario')
                                @if($notificacion->notificable)
                                    <div class="mt-2">
                                        <a href="{{ route('posts.show', $notificacion->notificable->post_id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           onclick="marcarLeidaSilencioso({{ $notificacion->id }})">
                                            <i class="bi bi-eye me-1"></i>Ver publicación
                                        </a>
                                    </div>
                                @endif

                            @elseif($notificacion->tipo === 'aceptacion_oferta')
                                @php $aceptacion = $notificacion->notificable; @endphp
                                @if($aceptacion?->oferta)
                                    <div class="mt-2">
                                        <a href="{{ route('ofertas.show', $aceptacion->oferta) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           onclick="marcarLeidaSilencioso({{ $notificacion->id }})">
                                            <i class="bi bi-briefcase me-1"></i>Ver oferta
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Paginación: solo si hay más de una página --}}
                @if($notificaciones->hasPages())
                <div class="d-flex justify-content-center py-3 border-top">
                    {{ $notificaciones->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash" style="font-size:3rem; color:#ccc;"></i>
                    <p class="text-muted mt-3 mb-0">No tienes notificaciones</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.notif-item {
    border-left: 3px solid transparent;
    transition: background-color 0.15s ease;
}
.notif-unread {
    background-color: #fffdf5;
    border-left-color: #ffc107 !important;
}
.notif-item:hover {
    background-color: #fafaf7;
}
.notif-dot {
    width: 10px;
    height: 10px;
    min-width: 10px;
    background-color: #ffc107;
    border-radius: 50%;
    margin-top: 5px;
    display: inline-block;
}
.notif-avatar-placeholder {
    background-color: #495057;
}
</style>

@push('scripts')
<script>
function marcarLeidaSilencioso(notificacionId) {
    fetch(`/notificaciones/${notificacionId}/leer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const item = document.querySelector(`.notif-item[data-id="${notificacionId}"]`);
            if (item) {
                item.classList.remove('notif-unread');
                item.querySelector('.notif-dot')?.remove();
            }
        }
    });
}

function marcarTodasLeidas(btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Procesando...';

    fetch('{{ route("notificaciones.marcar-todas") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function aceptarSolicitud(conexionId, notificacionId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    fetch(`/conexion/${conexionId}/aceptar`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Aceptar';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Aceptar';
    });
}

function rechazarSolicitud(conexionId, notificacionId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    fetch(`/conexion/${conexionId}/rechazar`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-lg"></i> Rechazar';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-lg"></i> Rechazar';
    });
}
</script>
@endpush
@endsection
