{{-- Listado de ofertas de empleo --}}
@extends('layouts.app')

@section('titulo', 'Ofertas de Empleo - BrickConnect')

@section('contenido')
<div class="row">
    <div class="col-md-8 mx-auto">

        {{-- Cabecera --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-briefcase text-warning"></i> Ofertas de Empleo</h3>
            <a href="{{ route('ofertas.create') }}" class="btn btn-warning fw-bold">
                <i class="bi bi-plus-lg"></i> Publicar Oferta
            </a>
        </div>

        {{-- Formulario de búsqueda y filtros --}}
        <form method="GET" action="{{ route('ofertas.index') }}" class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="busqueda" class="form-control"
                               placeholder="Buscar puesto, empresa..."
                               value="{{ request('busqueda') }}"
                               aria-label="Buscar ofertas">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="ubicacion" class="form-control"
                               placeholder="Ubicación"
                               value="{{ request('ubicacion') }}"
                               aria-label="Filtrar por ubicación">
                    </div>
                    <div class="col-md-3">
                        <select name="tipo_contrato" class="form-select" aria-label="Tipo de contrato">
                            <option value=""> Escoge una opción </option>
                            @foreach(['Tiempo completo','Media jornada','Temporal','Por obra'] as $tipo)
                                <option value="{{ $tipo }}" {{ request('tipo_contrato') === $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select" aria-label="Estado de la oferta">
                            <option value="">Todas</option>
                            <option value="activa" {{ request('estado') === 'activa' ? 'selected' : '' }}>Activas</option>
                            <option value="cerrada" {{ request('estado') === 'cerrada' ? 'selected' : '' }}>Cerradas</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-warning w-100" aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                @if(request()->hasAny(['busqueda','ubicacion','tipo_contrato','estado']))
                    <div class="mt-2">
                        <a href="{{ route('ofertas.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle" aria-hidden="true"></i> Limpiar filtros
                        </a>
                        <span class="text-muted small ms-2">
                            {{ $ofertas->total() }} {{ $ofertas->total() === 1 ? 'resultado' : 'resultados' }}
                        </span>
                    </div>
                @endif
            </div>
        </form>

        {{-- Lista de ofertas --}}
        @forelse($ofertas as $oferta)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <a href="{{ route('ofertas.show', $oferta) }}" class="text-dark text-decoration-none">
                                    {{ $oferta->titulo }}
                                </a>
                            </h5>
                            <p class="text-muted mb-1">
                                <i class="bi bi-building"></i> {{ $oferta->empresa }}
                                · <i class="bi bi-geo-alt"></i> {{ $oferta->ubicacion }}
                            </p>
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
                            @if(!$oferta->activa)
                                <span class="badge bg-danger"><i class="bi bi-lock"></i> Cerrada</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted">{{ $oferta->created_at->tiempoRelativo() }}</small>
                        </div>
                    </div>

                    {{-- Descripción resumida --}}
                    <p class="text-muted mt-2 mb-0">{{ Str::limit($oferta->descripcion, 150) }}</p>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-briefcase display-4"></i>
                <p class="mt-2">No hay ofertas publicadas todavía.</p>
            </div>
        @endforelse

        {{-- Paginación --}}
        <div class="d-flex justify-content-center">
            {{ $ofertas->links() }}
        </div>
    </div>
</div>
@endsection
