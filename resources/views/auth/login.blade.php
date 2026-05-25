{{-- Formulario de inicio de sesión --}}
@extends('layouts.app')

@section('titulo', 'Iniciar Sesión - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-5">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-building text-warning"></i> BrickConnect</h2>
            <p class="text-muted">Inicia sesión en tu cuenta</p>
        </div>

        {{-- Tarjeta del formulario --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="tu@email.com" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label fw-bold mb-0">Contraseña</label>
                            <a href="{{ route('password.request') }}" class="small text-muted text-decoration-none">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Tu contraseña" required>
                    </div>

                    {{-- Recordar sesión --}}
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="recordar" class="form-check-input" id="recordar">
                        <label class="form-check-label" for="recordar">Recordarme</label>
                    </div>

                    {{-- Botón de envío --}}
                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </button>
                </form>

            </div>
        </div>

        {{-- Enlace a registro --}}
        <div class="text-center mt-3">
            <p class="text-muted">
                ¿No tienes cuenta?
                <a href="{{ route('registro') }}" class="text-decoration-none fw-bold">Regístrate aquí</a>
            </p>
        </div>

    </div>
</div>
@endsection
