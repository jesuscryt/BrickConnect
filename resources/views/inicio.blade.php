{{-- Página de inicio (landing page) --}}
@extends('layouts.app')

@section('titulo', 'BrickConnect - La Red Profesional de la Construcción')

@section('contenido')
<div class="landing-page">

    {{-- Hero Section --}}
    <div class="text-center py-5">
        <h1 class="display-4 fw-bold text-dark">
            <i class="bi bi-building text-warning"></i> BrickConnect
        </h1>
        <p class="lead text-muted mt-3">
            La red profesional del sector de la construcción.<br>
            Conecta con profesionales, encuentra oportunidades y haz crecer tu carrera.
        </p>

        {{-- Botón principal para iniciar sesión --}}
        <div class="mt-4">
            <a href="{{ route('login') }}" class="btn btn-warning btn-lg px-5 fw-bold">
                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
            </a>
        </div>
    </div>

    {{-- Características --}}
    <div class="row text-center mt-5 g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <i class="bi bi-people-fill display-4 text-warning"></i>
                    <h5 class="card-title mt-3 fw-bold">Conecta</h5>
                    <p class="card-text text-muted">
                        Amplía tu red de contactos con albañiles, arquitectos, ingenieros y más profesionales del sector.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <i class="bi bi-briefcase-fill display-4 text-warning"></i>
                    <h5 class="card-title mt-3 fw-bold">Empleos</h5>
                    <p class="card-text text-muted">
                        Encuentra ofertas de trabajo que se ajusten a tu experiencia en la construcción.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <i class="bi bi-chat-dots-fill display-4 text-warning"></i>
                    <h5 class="card-title mt-3 fw-bold">Comparte</h5>
                    <p class="card-text text-muted">
                        Publica proyectos, noticias y experiencias con la comunidad de la construcción.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón de registro abajo --}}
    <div class="text-center mt-5 py-4 bg-light rounded">
        <h4 class="text-dark">¿Aún no tienes cuenta?</h4>
        <p class="text-muted">Únete gratis a la comunidad de profesionales de la construcción.</p>
        <a href="{{ route('registro') }}" class="btn btn-dark btn-lg px-5">
            <i class="bi bi-person-plus"></i> Registrarse
        </a>
    </div>

</div>
@endsection
