<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'BrickConnect')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>

    {{-- Barra de navegación: solo visible para usuarios autenticados --}}
    @auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
        <div class="container">
            {{-- Logo / Nombre --}}
            <a class="navbar-brand fw-bold" href="{{ route('feed') }}">
                <i class="bi bi-building"></i> BrickConnect
            </a>

            {{-- Botón hamburguesa para móvil --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Enlaces de navegación --}}
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('feed') }}">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('red.index') }}">
                            <i class="bi bi-people"></i> Mi Red
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ofertas.index') }}">
                            <i class="bi bi-briefcase"></i> Empleos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('chat.index') }}">
                            <i class="bi bi-chat-dots"></i> Mensajes
                            @if($mensajesNoLeidos > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size:0.65rem;">
                                    {{ $mensajesNoLeidos }}
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>

                {{-- Menú del usuario --}}
                <ul class="navbar-nav">
                    {{-- Notificaciones --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i> Notificaciones
                            @if($contarNoLeidas > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size:0.65rem;">
                                    {{ $contarNoLeidas }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-0 overflow-hidden" style="min-width:340px; max-width:360px;">
                            @if($notificacionesNoLeidas->count() > 0)
                                <li class="px-3 py-2 border-bottom bg-dark">
                                    <span class="text-white small fw-semibold">
                                        <i class="bi bi-bell-fill text-warning me-1"></i>
                                        {{ $contarNoLeidas }} notificación{{ $contarNoLeidas !== 1 ? 'es' : '' }} sin leer
                                    </span>
                                </li>
                                @foreach($notificacionesNoLeidas as $notificacion)
                                    @php
                                        $urlNotificacion = match($notificacion->tipo) {
                                            'solicitud_amistad'  => route('red.index'),
                                            'solicitud_aceptada' => $notificacion->emisor
                                                ? route('perfil.show', $notificacion->emisor)
                                                : route('red.index'),
                                            'comentario'        => $notificacion->notificable
                                                ? route('posts.show', $notificacion->notificable->post_id)
                                                : route('feed'),
                                            'aceptacion_oferta' => $notificacion->notificable?->oferta
                                                ? route('ofertas.show', $notificacion->notificable->oferta)
                                                : route('ofertas.index'),
                                            default             => route('notificaciones.index'),
                                        };
                                    @endphp
                                    <li>
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-start gap-2 notif-dropdown-item"
                                           href="{{ $urlNotificacion }}"
                                           style="border-left: 3px solid #ffc107; white-space: normal;">
                                            <div class="flex-shrink-0 mt-1">
                                                @if($notificacion->tipo === 'solicitud_amistad')
                                                    <i class="bi bi-person-plus-fill text-warning"></i>
                                                @elseif($notificacion->tipo === 'solicitud_aceptada')
                                                    <i class="bi bi-person-check-fill text-success"></i>
                                                @elseif($notificacion->tipo === 'comentario')
                                                    <i class="bi bi-chat-fill text-warning"></i>
                                                @elseif($notificacion->tipo === 'aceptacion_oferta')
                                                    <i class="bi bi-briefcase-fill text-warning"></i>
                                                @else
                                                    <i class="bi bi-bell-fill text-warning"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <span class="d-block" style="font-size:0.85rem; line-height:1.3;">
                                                    @if($notificacion->tipo === 'solicitud_amistad')
                                                        <strong>{{ $notificacion->emisor?->name ?? 'Usuario' }}</strong> te envió una solicitud de contacto
                                                    @elseif($notificacion->tipo === 'solicitud_aceptada')
                                                        <strong>{{ $notificacion->emisor?->name ?? 'Usuario' }}</strong> aceptó tu solicitud de contacto
                                                    @elseif($notificacion->tipo === 'comentario')
                                                        <strong>{{ $notificacion->emisor?->name ?? 'Usuario' }}</strong> comentó en tu publicación
                                                    @elseif($notificacion->tipo === 'aceptacion_oferta')
                                                        <strong>{{ $notificacion->emisor?->name ?? 'Un usuario' }}</strong> aceptó tu oferta de empleo
                                                    @endif
                                                </span>
                                                <small class="text-muted">{{ $notificacion->created_at->tiempoRelativo() }}</small>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                                <li class="border-top">
                                    <a class="dropdown-item text-center py-2 text-warning small fw-semibold" href="{{ route('notificaciones.index') }}">
                                        Ver todas las notificaciones
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a class="dropdown-item px-3 py-4 text-center d-block text-decoration-none" href="{{ route('notificaciones.index') }}">
                                        <i class="bi bi-bell-slash d-block mb-2" style="font-size:1.5rem; color:#aaa;"></i>
                                        <span class="text-muted small">No tienes notificaciones nuevas</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('perfil.show', Auth::id()) }}">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('perfil.edit') }}">
                                    <i class="bi bi-gear"></i> Editar Perfil
                                </a>
                            </li>
                            @if(Auth::user()->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('admin.index') }}">
                                    <i class="bi bi-shield-lock"></i> Panel Admin
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth

    {{-- Mensajes flash de éxito/error (las páginas de administración los muestran internamente) --}}
    @unless(request()->is('admin*'))
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>
    @endunless

    {{-- Contenido principal --}}
    <main class="container py-4" style="padding-top: calc(1.5rem + 56px) !important;">
        @yield('contenido')
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Loading state: deshabilita botones submit al enviar formularios normales -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form').forEach(function (form) {
                // Ignorar formularios con onsubmit (tienen su propio confirm/lógica)
                if (form.onsubmit) return;
                // Ignorar formularios GET (filtros/búsqueda): el botón es muy pequeño para el texto
                if (form.method.toLowerCase() === 'get') return;
                form.addEventListener('submit', function () {
                    var btn = form.querySelector('[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Procesando...';
                        // Restaurar tras 15s por si hay error de validación con redirección
                        setTimeout(function () {
                            btn.disabled = false;
                        }, 15000);
                    }
                });
            });
        });
    </script>
    <!-- JS personalizado -->
    @stack('scripts')
</body>
</html>
