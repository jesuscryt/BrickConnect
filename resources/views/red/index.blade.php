{{-- Mi Red: contactos y solicitudes pendientes --}}
@extends('layouts.app')

@section('titulo', 'Mi Red - BrickConnect')

@section('contenido')
<div class="row">
    <div class="col-md-8 mx-auto">

        <h3 class="fw-bold mb-4"><i class="bi bi-people text-warning"></i> Mi Red</h3>

        {{-- Solicitudes pendientes --}}
        @if($pendientes->count() > 0)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="bi bi-bell text-warning"></i> Solicitudes pendientes</h5>
                    <hr>

                    @foreach($pendientes as $solicitud)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                @if($solicitud->emisor->avatar)
                                <img src="{{ $solicitud->emisor->avatarUrl() }}"
                                         class="rounded-circle me-2" width="45" height="45"
                                         style="object-fit: cover;" alt="Avatar de {{ $solicitud->emisor->name }}">
                                @else
                                    <i class="bi bi-person-circle fs-2 me-2 text-muted" aria-hidden="true"></i>
                                @endif
                                <div>
                                    <a href="{{ route('perfil.show', $solicitud->emisor->id) }}"
                                       class="fw-bold text-dark text-decoration-none">
                                        {{ $solicitud->emisor->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $solicitud->emisor->profesion ?? 'Profesional' }}</small>
                                </div>
                            </div>
                            <div>
                                {{-- Aceptar --}}
                                <form action="{{ route('conexion.aceptar', $solicitud) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-success btn-sm">
                                        <i class="bi bi-check-lg"></i> Aceptar
                                    </button>
                                </form>
                                {{-- Rechazar --}}
                                <form action="{{ route('conexion.rechazar', $solicitud) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-lg"></i> Rechazar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Mis contactos --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold"><i class="bi bi-person-check text-warning"></i> Mis Contactos</h5>
                <hr>

                @forelse($contactos as $contacto)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            @if($contacto->avatar)
                            <img src="{{ $contacto->avatarUrl() }}"
                                     class="rounded-circle me-2" width="45" height="45"
                                     style="object-fit: cover;" alt="Avatar de {{ $contacto->name }}">
                            @else
                                <i class="bi bi-person-circle fs-2 me-2 text-muted" aria-hidden="true"></i>
                            @endif
                            <div>
                                <a href="{{ route('perfil.show', $contacto->id) }}"
                                   class="fw-bold text-dark text-decoration-none">
                                    {{ $contacto->name }}
                                </a>
                                <br>
                                <small class="text-muted">
                                    {{ $contacto->profesion ?? 'Profesional' }}
                                    @if($contacto->empresa) · {{ $contacto->empresa }} @endif
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('perfil.show', $contacto->id) }}" class="btn btn-outline-warning btn-sm">
                                Ver perfil
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-people display-4"></i>
                        <p class="mt-2">Todavía no tienes contactos. ¡Empieza a conectar!</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
