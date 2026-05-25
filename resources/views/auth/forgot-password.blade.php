{{-- Formulario para solicitar recuperación de contraseña --}}
@extends('layouts.app')

@section('titulo', 'Recuperar Contraseña - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-building text-warning"></i> BrickConnect</h2>
            <p class="text-muted">Recupera el acceso a tu cuenta</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('status') }}
                        <div class="mt-2 small text-muted">Serás redirigido al inicio de sesión en <span id="countdown">5</span> segundos...</div>
                    </div>
                    @push('scripts')
                    <script>
                        let seconds = 5;
                        const countdown = document.getElementById('countdown');
                        const interval = setInterval(function () {
                            seconds--;
                            if (countdown) countdown.textContent = seconds;
                            if (seconds <= 0) {
                                clearInterval(interval);
                                window.location.href = '{{ route('login') }}';
                            }
                        }, 1000);
                    </script>
                    @endpush
                @endif

                <p class="text-muted mb-4">
                    Introduce tu email y te enviaremos un enlace para restablecer tu contraseña.
                </p>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

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

                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-envelope"></i> Enviar enlace de recuperación
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
