{{-- Detalle de una oferta de empleo --}}
@extends('layouts.app')

@section('titulo', $oferta->titulo . ' - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-8">

        {{-- Botón volver --}}
        <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Volver a ofertas
        </a>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                {{-- Título y empresa --}}
                <h3 class="fw-bold">{{ $oferta->titulo }}</h3>
                <p class="text-muted mb-2">
                    <i class="bi bi-building"></i> {{ $oferta->empresa }}
                    · <i class="bi bi-geo-alt"></i> {{ $oferta->ubicacion }}
                </p>

                {{-- Badges --}}
                <div class="mb-3">
                    <span class="badge bg-warning text-dark">{{ $oferta->tipo_contrato }}</span>
                    @if($oferta->salario)
                        <span class="badge bg-success">
                            {{ number_format($oferta->salario, 0, ',', '.') }}€
                            @if($oferta->salario_periodo) / {{ $oferta->salario_periodo }}@endif
                            @if($oferta->salario_tipo) ({{ $oferta->salario_tipo }})@endif
                        </span>
                    @endif
                    @if($oferta->horas_semanales)
                        <span class="badge bg-info text-dark">
                            <i class="bi bi-clock"></i> {{ $oferta->horas_semanales }} horas/semana
                        </span>
                    @endif
                    <span class="badge bg-secondary">Publicada {{ $oferta->created_at->tiempoRelativo() }}</span>
                    @if($oferta->contarAceptaciones() > 0)
                        <span class="badge bg-info">
                            <i class="bi bi-check-circle"></i> {{ $oferta->contarAceptaciones() }} {{ $oferta->contarAceptaciones() == 1 ? 'aceptación' : 'aceptaciones' }}
                        </span>
                    @endif
                    @if(!$oferta->activa)
                        <span class="badge bg-danger">
                            <i class="bi bi-lock"></i> Oferta Cerrada
                        </span>
                    @endif
                </div>

                <hr>

                {{-- Descripción completa --}}
                <h5 class="fw-bold">Descripción del puesto</h5>
                <p class="text-dark" style="white-space: pre-line;">{{ $oferta->descripcion }}</p>

                <hr>

                {{-- Publicado por --}}
                <div class="d-flex align-items-center">
                    <span class="text-muted me-2">Publicada por:</span>
                    <a href="{{ route('perfil.show', $oferta->user->id) }}" class="text-decoration-none fw-bold">
                        {{ $oferta->user->name }}
                    </a>
                </div>

                {{-- Botón eliminar y botón para aceptar --}}
                @if($oferta->user_id === Auth::id())
                    {{-- VISTA DEL CREADOR --}}
                    <hr>
                    <h5 class="fw-bold">Aceptaciones</h5>
                    
                    @if($oferta->contarAceptaciones() > 0)
                        <div class="list-group mb-3">
                            @foreach($oferta->aceptaciones as $aceptacion)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <a href="{{ route('perfil.show', $aceptacion->user->id) }}" class="text-decoration-none fw-bold">
                                                {{ $aceptacion->user->name }}
                                            </a>
                                            <div class="text-muted small">{{ $aceptacion->user->profesion ?? 'Profesional' }}</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted">{{ $aceptacion->aceptada_en->format('d/m/Y H:i') }}</small>
                                            @if(array_key_exists($aceptacion->user_id, $conexionesCreador))
                                                {{-- Ya son contactos: puede chatear --}}
                                                <a href="{{ route('chat.show', $aceptacion->user->id) }}"
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-chat-dots" aria-hidden="true"></i> Mensaje
                                                </a>
                                            @else
                                                {{-- No son contactos: mostrar Conectar --}}
                                                <button class="btn btn-sm btn-warning"
                                                        onclick="enviarSolicitudConexionOferta({{ $aceptacion->user_id }}, this)"
                                                        data-user-id="{{ $aceptacion->user_id }}">
                                                    <i class="bi bi-person-plus" aria-hidden="true"></i> Conectar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-3">Aún nadie ha aceptado esta oferta.</p>
                    @endif

                    <div class="text-end mt-3">
                        {{-- Botón desactivar/activar --}}
                        @if($oferta->activa)
                            <form action="{{ route('ofertas.desactivar', $oferta) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-lock"></i> Desactivar oferta
                                </button>
                            </form>
                        @else
                            <form action="{{ route('ofertas.activar', $oferta) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-unlock"></i> Reactivar oferta
                                </button>
                            </form>
                        @endif

                        {{-- Botón eliminar --}}
                        <form action="{{ route('ofertas.destroy', $oferta) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta oferta?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i> Eliminar oferta
                            </button>
                        </form>
                    </div>

                @else
                    {{-- VISTA DE OTROS USUARIOS --}}
                    @if($oferta->estaAceptadaPor(Auth::user()))
                        {{-- Usuario ya ha aceptado --}}
                        <div class="alert alert-success" role="alert">
                            <div class="text-center">
                                <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                                <h5 class="mt-2 mb-0"><strong>¡Has aceptado esta oferta!</strong></h5>
                            </div>
                        </div>
                    @elseif($oferta->activa)
                        {{-- Usuario no ha aceptado y oferta está activa --}}
                        <div class="d-grid gap-2" id="contenedorAceptacion">
                            <button type="button" class="btn btn-success w-100" id="btnAceptarOferta" data-bs-toggle="modal" data-bs-target="#modalConfirmarAceptacion">
                                <i class="bi bi-check-circle"></i> Aceptar oferta
                            </button>
                        </div>
                    @else
                        {{-- Oferta no está activa --}}
                        <div class="alert alert-danger mb-0" role="alert">
                            <i class="bi bi-lock"></i> Esta oferta ya no está disponible
                        </div>
                    @endif
                @endif

            </div>
        </div>

    </div>
</div>
@endsection

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmarAceptacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalLabel">
                    <i class="bi bi-question-circle text-warning"></i> Confirmar aceptación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres aceptar esta oferta?</p>
                <p class="text-muted small">Una vez aceptada, el creador de la oferta podrá ver tu aceptación.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarAceptacion">
                    <i class="bi bi-check-circle"></i> Aceptar oferta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const ofertaId = {{ $oferta->id }};

    // Procesar aceptación cuando confirma
    const btnConfirmar = document.getElementById('btnConfirmarAceptacion');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', async () => {
            btnConfirmar.disabled = true;
            const textoOriginal = btnConfirmar.innerHTML;
            btnConfirmar.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                document.querySelector('input[name="_token"]')?.value;

                const response = await fetch(`/ofertas/${ofertaId}/toggle-accept`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarAceptacion'));
                    if (modal) modal.hide();
                    
                    // Mostrar mensaje de éxito
                    mostrarMensajeExito();
                    
                    // Recargar la página después de 2 segundos
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    const error = await response.json();
                    throw new Error(error.error || 'Error al procesar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = textoOriginal;
            }
        });
    }

    function mostrarMensajeExito() {
        const contenedorBtn = document.getElementById('contenedorAceptacion');
        if (contenedorBtn) {
            contenedorBtn.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="text-center">
                        <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                        <h5 class="mt-3 mb-2"><strong>¡Oferta aceptada correctamente!</strong></h5>
                        <p class="mb-0 text-muted">El creador de la oferta ha sido notificado de tu aceptación.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    }

    function enviarSolicitudConexionOferta(usuarioId, btn) {
        btn.disabled = true;
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Enviando...';

        fetch(`/conexion/${usuarioId}`, {
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
                btn.outerHTML = '<span class="badge bg-info text-dark"><i class="bi bi-hourglass-split"></i> Solicitud enviada</span>';
            } else {
                alert(data.error || 'Error al enviar solicitud');
                btn.disabled = false;
                btn.innerHTML = original;
            }
        })
        .catch(() => {
            alert('Error de conexión');
            btn.disabled = false;
            btn.innerHTML = original;
        });
    }
</script>
