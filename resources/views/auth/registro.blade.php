{{-- Formulario de registro --}}
@extends('layouts.app')

@section('titulo', 'Registrarse - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-5">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-building text-warning"></i> BrickConnect</h2>
            <p class="text-muted">Crea tu cuenta profesional</p>
        </div>

        {{-- Tarjeta del formulario --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('registro') }}">
                    @csrf

                    {{-- Nombre completo --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nombre completo</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Juan García López" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Solo letras, espacios y guiones. Ej: José María, Ana-Belén</div>
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="tu@email.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Mínimo 8 caracteres, con al menos una letra y un número.</div>
                    </div>

                    {{-- Confirmar contraseña --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-bold">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control"
                               placeholder="Repite la contraseña" required>
                    </div>

                    {{-- Botón de envío --}}
                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-person-plus"></i> Crear Cuenta
                    </button>
                </form>

            </div>
        </div>

        {{-- Enlace al login --}}
        <div class="text-center mt-3">
            <p class="text-muted">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Inicia sesión</a>
            </p>
        </div>

    </div>
</div>
@endsection
