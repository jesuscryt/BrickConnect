{{-- Perfil de usuario --}}
@extends('layouts.app')

@section('titulo', $user->name . ' - BrickConnect')

@section('contenido')
<div class="row">
    <div class="col-md-8 mx-auto">

        {{-- Tarjeta de perfil --}}
        <div class="card shadow-sm border-0 mb-4">
            {{-- Banner superior --}}
            <div class="bg-warning" style="height: 100px; border-radius: 0.375rem 0.375rem 0 0;"></div>

            <div class="card-body text-center" style="margin-top: -50px;">
                {{-- Avatar --}}
                @if($user->avatar)
                    <img src="{{ $user->avatarUrl() }}"
                         class="rounded-circle border border-4 border-white shadow"
                         width="100" height="100" alt="Avatar de {{ $user->name }}"
                         style="object-fit: cover;">
                @else
                    <div class="d-inline-block bg-light rounded-circle border border-4 border-white shadow p-2"
                         style="width: 100px; height: 100px; line-height: 80px;">
                        <i class="bi bi-person-fill display-4 text-muted"></i>
                    </div>
                @endif

                <h4 class="fw-bold mt-2 mb-0">{{ $user->name }}</h4>
                @if($user->profesion)
                <p class="text-muted mb-1">{{ $user->profesion }}</p>
                @endif

                @if($user->empresa)
                    <p class="text-muted small mb-1">
                        <i class="bi bi-building"></i> {{ $user->empresa }}
                    </p>
                @endif

                @if($user->ubicacion)
                    <p class="text-muted small mb-1">
                        <i class="bi bi-geo-alt"></i> {{ $user->ubicacion }}
                    </p>
                @endif

                {{-- Botones de acción --}}
                <div class="mt-3">
                    @if($user->id === Auth::id())
                        {{-- Si es mi propio perfil --}}
                        <a href="{{ route('perfil.edit') }}" class="btn btn-warning btn-sm fw-bold">
                            <i class="bi bi-pencil"></i> Editar Perfil
                        </a>
                    @else
                        @if($estasConectado)
                            {{-- Ya estás conectado --}}
                            <span class="badge bg-success me-1">
                                <i class="bi bi-check-circle"></i> Ya estás conectado
                            </span>
                            @if($conexionAceptada)
                                <form action="{{ route('conexion.eliminar', $conexionAceptada) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar a {{ addslashes($user->name) }} de tus contactos?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                            aria-label="Desconectar de {{ $user->name }}">
                                        <i class="bi bi-person-dash" aria-hidden="true"></i> Desconectar
                                    </button>
                                </form>
                            @endif
                        @elseif($solicitudEnviada)
                            {{-- Solicitud pending que enviaste --}}
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-hourglass-split"></i> Solicitud enviada
                            </span>
                        @elseif($conexionRecibida)
                            {{-- Solicitud pending que recibiste --}}
                            <button class="btn btn-success btn-sm fw-bold" onclick="aceptarSolicitud()">
                                <i class="bi bi-check-lg"></i> Aceptar
                            </button>
                            <button class="btn btn-outline-danger btn-sm fw-bold" onclick="rechazarSolicitud()">
                                <i class="bi bi-x-lg"></i> Rechazar
                            </button>
                        @else
                            {{-- Sin conexión --}}
                            <button class="btn btn-warning btn-sm fw-bold" onclick="enviarSolicitudConexion({{ $user->id }})">
                                <i class="bi bi-person-plus"></i> Conectar
                            </button>
                        @endif
                    @endif
                </div>

                @if($user->id !== Auth::id())
                    {{-- Scripts para aceptar/rechazar solicitudes y enviar conexión --}}
                    <script>
                        const usuarioPerfilId = {{ $user->id }};

                        function enviarSolicitudConexion(usuarioId) {
                            fetch(`/conexion/${usuarioId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    alert(data.error || 'Error al enviar solicitud');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error al enviar solicitud');
                            });
                        }

                        function aceptarSolicitud() {
                            @if($conexionRecibida)
                                fetch(`/conexion/{{ $conexionRecibida->id }}/aceptar`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    }
                                });
                            @endif
                        }

                        function rechazarSolicitud() {
                            @if($conexionRecibida)
                                fetch(`/conexion/{{ $conexionRecibida->id }}/rechazar`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    }
                                });
                            @endif
                        }
                    </script>
                @endif
            </div>
        </div>

        {{-- Sobre mí --}}
        @if($user->sobre_mi)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="bi bi-info-circle text-warning"></i> Sobre mí</h5>
                    <p class="text-dark mb-0">{{ $user->sobre_mi }}</p>
                </div>
            </div>
        @endif

        {{-- Información de contacto: visible solo para el propio usuario o sus contactos --}}
        @if(($user->telefono || $user->email) && ($user->id === Auth::id() || $estasConectado))
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="bi bi-telephone text-warning"></i> Contacto</h5>
                    @if($user->email)
                        <p class="mb-1"><i class="bi bi-envelope"></i> {{ $user->email }}</p>
                    @endif
                    @if($user->telefono)
                        <p class="mb-0"><i class="bi bi-phone"></i> {{ $user->telefono }}</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Publicaciones del usuario --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-file-post text-warning"></i> Publicaciones</h5>

                @forelse($posts as $post)
                    <div class="border-bottom pb-3 mb-3">
                        <p class="mb-1">{{ $post->contenido }}</p>
                        @if($post->imagen)
                            <img src="{{ $post->imagenUrl() }}"
                                 class="img-fluid rounded mb-2" alt="Imagen del post de {{ $user->name }}">
                        @endif
                        <small class="text-muted">{{ $post->created_at->tiempoRelativo() }}</small>

                        {{-- Comentarios del post (collapsible) --}}
                        @if($post->comentarios->count() > 0)
                            <div class="mt-2">
                                <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#comentarios-perfil-{{ $post->id }}"
                                        aria-expanded="false"
                                        aria-controls="comentarios-perfil-{{ $post->id }}">
                                    <i class="bi bi-chat-dots" aria-hidden="true"></i>
                                    {{ $post->comentarios->count() }} {{ $post->comentarios->count() === 1 ? 'comentario' : 'comentarios' }}
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </button>
                                <div class="collapse mt-2" id="comentarios-perfil-{{ $post->id }}">
                                    @foreach($post->comentarios as $comentario)
                                        <div class="d-flex align-items-start mb-2">
                                            @if($comentario->user->avatar)
                                                <img src="{{ $comentario->user->avatarUrl() }}"
                                                     class="rounded-circle me-2 flex-shrink-0" width="28" height="28"
                                                     style="object-fit:cover;"
                                                     alt="Avatar de {{ $comentario->user->name }}">
                                            @else
                                                <i class="bi bi-person-circle fs-5 me-2 text-muted flex-shrink-0" aria-hidden="true"></i>
                                            @endif
                                            <div class="bg-light rounded px-2 py-1 flex-grow-1">
                                                <span class="fw-bold small">{{ $comentario->user->name }}</span>
                                                <span class="text-muted small ms-1">· {{ $comentario->created_at->tiempoRelativo() }}</span>
                                                <p class="mb-0 small">{{ $comentario->contenido }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">Este usuario no ha publicado nada todavía.</p>
                @endforelse

                {{ $posts->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
