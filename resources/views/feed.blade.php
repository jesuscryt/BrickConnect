{{-- Feed principal: muro de publicaciones --}}
@extends('layouts.app')

@section('titulo', 'Feed - BrickConnect')

@section('contenido')
<div class="row">

    {{-- Columna izquierda: tarjeta de perfil rápido --}}
    <div class="col-md-3 mb-4" style="position: sticky; top: 70px; align-self: flex-start;">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                {{-- Avatar --}}
                @if(Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatarUrl() }}"
                         class="rounded-circle mb-2" width="80" height="80"
                         alt="Avatar de {{ Auth::user()->name }}" style="object-fit: cover;">
                @else
                    <i class="bi bi-person-circle display-3 text-muted" aria-hidden="true"></i>
                @endif

                <h6 class="fw-bold mt-2">{{ Auth::user()->name }}</h6>
                @if(Auth::user()->profesion)
                    <p class="text-muted small mb-1">{{ Auth::user()->profesion }}</p>
                    @endif
                <p class="text-muted small">{{ Auth::user()->ubicacion ?? 'Sin ubicación' }}</p>

                <a href="{{ route('perfil.show', Auth::id()) }}" class="btn btn-outline-warning btn-sm w-100">
                    Ver mi perfil
                </a>
            </div>
        </div>

        {{-- Contactos --}}
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body py-2 px-3">
                <a href="{{ route('red.index') }}" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people text-warning me-1"></i> <strong>Contactos</strong></span>
                    <span class="badge bg-warning text-dark">{{ $numContactos }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Columna central: publicaciones --}}
    <div class="col-md-6">

        {{-- Formulario para crear publicación --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <textarea name="contenido" rows="3"
                                  class="form-control @error('contenido') is-invalid @enderror"
                                  placeholder="¿Qué quieres compartir con la comunidad?">{{ old('contenido') }}</textarea>
                        @error('contenido')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        {{-- Subir imagen --}}
                        <label class="btn btn-outline-secondary btn-sm mb-0">
                            <i class="bi bi-image"></i> Imagen
                            <input type="file" name="imagen" id="inputImagenPost" class="d-none" accept="image/*">
                        </label>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold">
                            <i class="bi bi-send"></i> Publicar
                        </button>
                    </div>

                    {{-- Preview de imagen seleccionada --}}
                    <div id="previewImagenPost" class="mt-3" style="display:none; position:relative;">
                        <img id="previewImagenPostImg" src="" alt="Preview"
                             class="img-fluid rounded w-100" style="max-height:300px; object-fit:contain; background:#f8f9fa;">
                        <button type="button" id="btnQuitarImagen"
                                class="position-absolute top-0 end-0 m-2 border-0 rounded-circle d-flex align-items-center justify-content-center"
                                style="width:28px;height:28px;background:rgba(0,0,0,0.55);color:#fff;cursor:pointer;"
                                title="Quitar imagen">
                            <i class="bi bi-x-lg" style="font-size:13px;"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Lista de publicaciones --}}
        @forelse($posts as $post)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    {{-- Cabecera del post: avatar + nombre + fecha + menú --}}
                    <div class="d-flex align-items-center mb-3">
                        @if($post->user->avatar)
                            <img src="{{ $post->user->avatarUrl() }}"
                                 class="rounded-circle me-2" width="45" height="45"
                                 alt="Avatar de {{ $post->user->name }}" style="object-fit: cover;">
                        @else
                            <i class="bi bi-person-circle fs-2 me-2 text-muted" aria-hidden="true"></i>
                        @endif
                        <div class="flex-grow-1">
                            <a href="{{ route('perfil.show', $post->user->id) }}"
                               class="fw-bold text-dark text-decoration-none">
                                {{ $post->user->name }}
                            </a>
                            <br>
                            <small class="text-muted">
                                {{ $post->user->profesion ?? '' }}
                                · {{ $post->created_at->tiempoRelativo() }}
                            </small>
                        </div>
                        @if($post->user_id === Auth::id())
                            <div class="dropdown ms-2">
                                <button class="btn btn-link text-muted p-0" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        aria-label="Opciones de publicación">
                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <form action="{{ route('posts.destroy', $post) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar esta publicación?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i> Eliminar publicación
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Contenido del post --}}
                    <p class="mb-2">{{ $post->contenido }}</p>

                    {{-- Imagen del post (si tiene) --}}
                    @if($post->imagen)
                        <img src="{{ $post->imagenUrl() }}"
                             class="img-fluid rounded mb-2" alt="Imagen del post">
                    @endif

                    {{-- Contador de comentarios --}}
                    @php
                        $comentarios      = $post->comentarios; // ya ordenados latest()
                        $totalComentarios = $comentarios->count();
                    @endphp
                    @if($totalComentarios > 0)
                        <div class="text-end mb-1">
                            <span class="text-muted small">
                                <i class="bi bi-chat-dots"></i>
                                {{ $totalComentarios }} {{ $totalComentarios === 1 ? 'comentario' : 'comentarios' }}
                            </span>
                        </div>
                    @endif

                    {{-- Botones de acción --}}
                    <hr class="my-2">
                    <div class="d-flex">
                        <button type="button"
                                class="btn btn-sm flex-fill text-secondary fw-semibold btn-accion-comentar" style="background-color:#e2e5e9;"
                                data-post-id="{{ $post->id }}"
                                aria-expanded="false">
                            <i class="bi bi-chat-square-dots me-1"></i> Comentar
                        </button>
                        <button type="button"
                                class="btn btn-sm flex-fill text-secondary fw-semibold ms-1" style="background-color:#e2e5e9;"
                                onclick="compartirPost(this, '{{ route('posts.show', $post) }}')">
                            <i class="bi bi-share me-1"></i> Compartir
                        </button>
                    </div>

                    {{-- Panel desplegable: formulario + comentarios recientes --}}
                    <div class="panel-comentarios" id="panel-comentarios-{{ $post->id }}" style="display:none;">
                        <hr class="mt-2 mb-3">

                        {{-- Formulario para nuevo comentario --}}
                        <form action="{{ route('comentarios.store', $post) }}" method="POST"
                              class="d-flex align-items-center gap-2 mb-3">
                            @csrf
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatarUrl() }}"
                                     class="rounded-circle flex-shrink-0" width="36" height="36"
                                     alt="Tu avatar" style="object-fit: cover;">
                            @else
                                <i class="bi bi-person-circle fs-3 text-muted flex-shrink-0" aria-hidden="true"></i>
                            @endif
                            <input type="text" name="contenido"
                                   class="form-control form-control-sm rounded-pill"
                                   placeholder="Añadir un comentario..." maxlength="1000" required>
                            <button class="btn btn-warning btn-sm fw-bold flex-shrink-0 rounded-pill px-3">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>

                        {{-- Comentarios visibles inicialmente (2 más recientes) --}}
                        @foreach($comentarios->take(2) as $comentario)
                            <div class="d-flex align-items-start mb-2">
                                @if($comentario->user->avatar)
                                    <img src="{{ $comentario->user->avatarUrl() }}"
                                         class="rounded-circle me-2 flex-shrink-0" width="32" height="32"
                                         alt="Avatar de {{ $comentario->user->name }}" style="object-fit: cover;">
                                @else
                                    <i class="bi bi-person-circle fs-4 me-2 text-muted flex-shrink-0" aria-hidden="true"></i>
                                @endif
                                <div class="rounded-3 px-3 py-2 flex-grow-1" style="background-color:#e9ecef;">
                                    <span class="fw-bold small">{{ $comentario->user->name }}</span>
                                    <span class="text-muted small ms-1">· {{ $comentario->created_at->tiempoRelativo() }}</span>
                                    <p class="mb-0 small">{{ $comentario->contenido }}</p>
                                </div>
                                @if($comentario->user_id === Auth::id())
                                    <div class="dropdown ms-2 flex-shrink-0">
                                        <button class="btn btn-link text-muted p-0" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                aria-label="Opciones de comentario">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <form action="{{ route('comentarios.destroy', $comentario) }}" method="POST"
                                                      onsubmit="return confirm('¿Eliminar comentario?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i> Eliminar comentario
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Comentarios extra, ocultos, revelados de 10 en 10 --}}
                        @if($totalComentarios > 2)
                            @foreach($comentarios->skip(2) as $comentario)
                                <div class="comentario-extra align-items-start mb-2"
                                     data-post="{{ $post->id }}"
                                     style="display:none;">
                                    @if($comentario->user->avatar)
                                        <img src="{{ $comentario->user->avatarUrl() }}"
                                             class="rounded-circle me-2 flex-shrink-0" width="32" height="32"
                                             alt="Avatar de {{ $comentario->user->name }}" style="object-fit: cover;">
                                    @else
                                        <i class="bi bi-person-circle fs-4 me-2 text-muted flex-shrink-0" aria-hidden="true"></i>
                                    @endif
                                    <div class="rounded-3 px-3 py-2 flex-grow-1" style="background-color:#e9ecef;">
                                        <span class="fw-bold small">{{ $comentario->user->name }}</span>
                                        <span class="text-muted small ms-1">· {{ $comentario->created_at->tiempoRelativo() }}</span>
                                        <p class="mb-0 small">{{ $comentario->contenido }}</p>
                                    </div>
                                    @if($comentario->user_id === Auth::id())
                                        <div class="dropdown ms-2 flex-shrink-0">
                                            <button class="btn btn-link text-muted p-0" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    aria-label="Opciones de comentario">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <form action="{{ route('comentarios.destroy', $comentario) }}" method="POST"
                                                          onsubmit="return confirm('¿Eliminar comentario?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i> Eliminar comentario
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <button type="button"
                                    class="btn btn-link btn-sm text-secondary p-0 mb-1 btn-cargar-mas"
                                    data-post-id="{{ $post->id }}"
                                    data-restantes="{{ $totalComentarios - 2 }}"
                                    data-total-extra="{{ $totalComentarios - 2 }}">
                                <i class="bi bi-arrow-down-circle me-1"></i>
                                Cargar más comentarios ({{ $totalComentarios - 2 }})
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-megaphone display-4"></i>
                <p class="mt-2">No hay publicaciones todavía. ¡Sé el primero en compartir algo!</p>
            </div>
        @endforelse

        {{-- Paginación --}}
        <div class="d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        // Compartir: copia enlace al portapapeles
        function compartirPost(btn, url) {
            if (!navigator.clipboard) return;
            navigator.clipboard.writeText(url).then(function() {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Enlace copiado';
                setTimeout(function() { btn.innerHTML = original; }, 2000);
            });
        }

        // Toggle panel de comentarios
        document.querySelectorAll('.btn-accion-comentar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var postId = this.dataset.postId;
                var panel  = document.getElementById('panel-comentarios-' + postId);
                var abierto = panel.style.display !== 'none';

                if (abierto) {
                    // Cerrar: ocultar panel y resetear comentarios extra
                    panel.style.display = 'none';
                    this.setAttribute('aria-expanded', 'false');

                    // Volver a ocultar todos los comentarios extra
                    panel.querySelectorAll('.comentario-extra').forEach(function(el) {
                        el.style.display = 'none';
                    });

                    // Restaurar el botón "Cargar más" a su estado original
                    var btnMas = panel.querySelector('.btn-cargar-mas');
                    if (btnMas) {
                        btnMas.style.display = '';
                        var totalExtra = btnMas.dataset.totalExtra;
                        btnMas.dataset.restantes = totalExtra;
                        btnMas.innerHTML = '<i class="bi bi-arrow-down-circle me-1"></i> Cargar más comentarios (' + totalExtra + ')';
                    }
                } else {
                    // Abrir
                    panel.style.display = 'block';
                    this.setAttribute('aria-expanded', 'true');
                    var input = panel.querySelector('input[name="contenido"]');
                    if (input) input.focus();
                }
            });
        });

        // Preview de imagen al seleccionar archivo
        var inputImagen = document.getElementById('inputImagenPost');
        var previewDiv  = document.getElementById('previewImagenPost');
        var previewImg  = document.getElementById('previewImagenPostImg');
        var btnQuitar   = document.getElementById('btnQuitarImagen');

        if (inputImagen) {
            inputImagen.addEventListener('change', function() {
                var file = this.files[0];
                if (file && file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewDiv.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewDiv.style.display = 'none';
                    previewImg.src = '';
                }
            });
        }

        if (btnQuitar) {
            btnQuitar.addEventListener('click', function() {
                inputImagen.value = '';
                previewDiv.style.display = 'none';
                previewImg.src = '';
            });
        }

        // Cargar más comentarios (de 10 en 10)
        document.querySelectorAll('.btn-cargar-mas').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var postId = this.dataset.postId;
                var ocultos = Array.from(
                    document.querySelectorAll('.comentario-extra[data-post="' + postId + '"]')
                ).filter(function(el) { return el.style.display === 'none'; });

                // Mostrar los próximos 10
                ocultos.slice(0, 10).forEach(function(el) {
                    el.style.display = 'flex';
                });

                // Actualizar o esconder el botón
                var restantes = ocultos.length - 10;
                if (restantes <= 0) {
                    this.style.display = 'none';
                } else {
                    this.dataset.restantes = restantes;
                    this.innerHTML = '<i class="bi bi-arrow-down-circle me-1"></i> Cargar más comentarios (' + restantes + ')';
                }
            });
        });
    </script>
    @endpush

    {{-- Columna derecha: sugerencias rápidas --}}
    <div class="col-md-3 mb-4" style="position: sticky; top: 70px; align-self: flex-start;">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-briefcase text-warning"></i> Ofertas recientes</h6>
                <hr>
                @forelse($ofertasRecientes as $oferta)
                    <div class="mb-2">
                        <a href="{{ route('ofertas.show', $oferta) }}" class="text-decoration-none">
                            <strong class="text-dark">{{ $oferta->titulo }}</strong>
                        </a>
                        <br>
                        <small class="text-muted">{{ $oferta->empresa }} · {{ $oferta->ubicacion }}</small>
                    </div>
                @empty
                    <p class="text-muted small">No hay ofertas todavía.</p>
                @endforelse
                <a href="{{ route('ofertas.index') }}" class="btn btn-outline-warning btn-sm w-100 mt-2">
                    Ver todas las ofertas
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
