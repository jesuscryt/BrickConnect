{{-- Bandeja de entrada de mensajes --}}
@extends('layouts.app')

@section('titulo', 'Mensajes - BrickConnect')

@section('contenido')
<div class="row">
    <div class="col-md-8 mx-auto">

        <h3 class="fw-bold mb-4"><i class="bi bi-chat-dots text-warning"></i> Mensajes</h3>

        <div class="card shadow-sm border-0 overflow-hidden">
            @forelse($conversaciones as $conv)
                @php $interlocutor = $conv['interlocutor']; @endphp
                <a href="{{ route('chat.show', $interlocutor->id) }}"
                   class="d-flex align-items-center p-3 text-decoration-none text-dark border-bottom chat-item {{ $conv['no_leidos'] > 0 ? 'chat-unread' : '' }}">

                    {{-- Avatar --}}
                    @if($interlocutor->avatar)
                        <img src="{{ $interlocutor->avatarUrl() }}"
                             class="rounded-circle me-3 flex-shrink-0" width="50" height="50"
                             style="object-fit: cover;" alt="Avatar de {{ $interlocutor->name }}">
                    @else
                        <div class="me-3 flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold"
                             style="width:50px;height:50px;font-size:1.2rem;">
                            {{ strtoupper(substr($interlocutor->name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Info --}}
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold {{ $conv['no_leidos'] > 0 ? 'text-dark' : '' }}">
                                {{ $interlocutor->name }}
                            </span>
                            @if($conv['tiene_mensajes'])
                                <small class="text-muted flex-shrink-0 ms-2">
                                    {{ $conv['ultimo_mensaje']->created_at->tiempoRelativo() }}
                                </small>
                            @endif
                        </div>
                        <p class="mb-0 small text-truncate {{ $conv['no_leidos'] > 0 ? 'fw-semibold text-dark' : 'text-muted' }}">
                            @if($conv['tiene_mensajes'])
                                @if($conv['ultimo_mensaje']->sender_id === Auth::id())
                                    <span class="text-secondary fw-normal">Tú: </span>
                                @endif
                                {{ $conv['ultimo_mensaje']->body }}
                            @endif
                        </p>
                    </div>

                    {{-- Badge no leídos --}}
                    @if($conv['no_leidos'] > 0)
                        <span class="badge bg-warning text-dark ms-2 flex-shrink-0">{{ $conv['no_leidos'] }}</span>
                    @endif
                </a>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-people display-4 d-block mb-2" style="color:#ccc;"></i>
                    <p class="mb-1">No tienes contactos todavía.</p>
                    <a href="{{ route('red.index') }}" class="btn btn-outline-warning btn-sm mt-2">
                        <i class="bi bi-person-plus me-1"></i>Buscar contactos
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</div>

<style>
.chat-item {
    transition: background-color 0.15s ease;
}
.chat-item:hover {
    background-color: #fafaf7;
}
.chat-unread {
    background-color: #fffdf5;
    border-left: 3px solid #ffc107 !important;
}
.chat-item:not(.chat-unread) {
    border-left: 3px solid transparent !important;
}
</style>
@endsection
