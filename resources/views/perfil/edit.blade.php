{{-- Formulario para editar el perfil propio --}}
@extends('layouts.app')

@section('titulo', 'Editar Perfil - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-7">

        <h3 class="fw-bold mb-4"><i class="bi bi-pencil-square text-warning"></i> Editar Perfil</h3>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Avatar actual --}}
                    <div class="text-center mb-3">
                        @if($user->avatar)
                            <img src="{{ $user->avatarUrl() }}"
                                 class="rounded-circle mb-2" width="80" height="80"
                                 style="object-fit: cover;" alt="Avatar de {{ $user->name }}">
                        @else
                            <i class="bi bi-person-circle display-3 text-muted" aria-hidden="true"></i>
                        @endif
                    </div>

                    {{-- Subir nuevo avatar --}}
                    <div class="mb-3">
                        <label for="avatar" class="form-label fw-bold">Foto de perfil</label>
                        <input type="file" name="avatar" id="avatar"
                               class="form-control @error('avatar') is-invalid @enderror"
                               accept="image/*">
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nombre completo</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Profesión --}}
                    <div class="mb-3">
                        <label for="profesion" class="form-label fw-bold">Profesión</label>
                        <input type="text" name="profesion" id="profesion"
                               class="form-control @error('profesion') is-invalid @enderror"
                               value="{{ old('profesion', $user->profesion) }}"
                               placeholder="Ej: Arquitecto, Albañil, Ingeniero Civil...">
                        @error('profesion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Empresa --}}
                    <div class="mb-3">
                        <label for="empresa" class="form-label fw-bold">Empresa actual</label>
                        <input type="text" name="empresa" id="empresa"
                               class="form-control @error('empresa') is-invalid @enderror"
                               value="{{ old('empresa', $user->empresa) }}"
                               placeholder="¿Dónde trabajas?">
                        @error('empresa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ubicación --}}
                    <div class="mb-3">
                        <label for="ubicacion" class="form-label fw-bold">Ubicación</label>
                        <input type="text" name="ubicacion" id="ubicacion"
                               class="form-control @error('ubicacion') is-invalid @enderror"
                               value="{{ old('ubicacion', $user->ubicacion) }}"
                               placeholder="Ciudad / Provincia">
                        @error('ubicacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Teléfono --}}
                    <div class="mb-3">
                        <label for="telefono" class="form-label fw-bold">Teléfono</label>
                        <input type="text" name="telefono" id="telefono"
                               class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono', $user->telefono) }}"
                               placeholder="Ej: 612 345 678">
                        @error('telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sobre mí --}}
                    <div class="mb-3">
                        <label for="sobre_mi" class="form-label fw-bold">Sobre mí</label>
                        <textarea name="sobre_mi" id="sobre_mi" rows="4"
                                  class="form-control @error('sobre_mi') is-invalid @enderror"
                                  placeholder="Cuéntanos sobre tu experiencia profesional...">{{ old('sobre_mi', $user->sobre_mi) }}</textarea>
                        @error('sobre_mi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('perfil.show', $user->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="bi bi-check-lg"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>


    </div>
</div>
@endsection
