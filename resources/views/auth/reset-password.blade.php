{{-- Formulario para introducir la nueva contraseña --}}
@extends('layouts.app')

@section('titulo', 'Nueva Contraseña - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-building text-warning"></i> BrickConnect</h2>
            <p class="text-muted">Elige tu nueva contraseña</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    {{-- Token oculto --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- Email (prellenado desde el enlace) --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $email ?? '') }}"
                               placeholder="tu@email.com" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nueva contraseña --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Nueva contraseña</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres con letras y números" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmar nueva contraseña --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-bold">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control"
                               placeholder="Repite la nueva contraseña" required>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-check-lg"></i> Restablecer contraseña
                    </button>
                </form>

            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
            </a>
        </div>

    </div>
</div>
@endsection
