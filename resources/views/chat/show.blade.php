{{-- Conversación privada con un usuario --}}
@extends('layouts.app')

@section('titulo', 'Chat con ' . $user->name . ' - BrickConnect')

@section('contenido')
<div class="chat-page-wrapper">
    <div class="chat-card shadow">

        {{-- ── Cabecera ── --}}
        <div class="chat-header d-flex align-items-center gap-3 px-3 py-2">
            <a href="{{ route('chat.index') }}" class="btn btn-sm btn-light border-0 p-1" title="Volver">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            @if($user->avatar)
                <img src="{{ $user->avatarUrl() }}" class="rounded-circle flex-shrink-0"
                     width="42" height="42" style="object-fit:cover;" alt="{{ $user->name }}">
            @else
                <div class="chat-avatar-ph flex-shrink-0">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <div>
                <a href="{{ route('perfil.show', $user->id) }}"
                   class="text-dark fw-semibold text-decoration-none d-block lh-sm">
                    {{ $user->name }}
                </a>
            </div>
        </div>

        {{-- ── Área de mensajes ── --}}
        <div class="chat-messages" id="mensajes-container">
            @forelse($mensajes as $msg)
                @php $mio = $msg->sender_id === Auth::id(); @endphp
                <div class="msg-row {{ $mio ? 'msg-mine' : 'msg-theirs' }}" data-id="{{ $msg->id }}">
                    <div class="msg-bubble {{ $mio ? 'bubble-mine' : 'bubble-theirs' }}">
                        <div class="msg-body">{{ $msg->body }}</div>
                        <div class="msg-meta">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($mio)
                                @if($msg->read_at)
                                    <i class="bi bi-check2-all" title="Leído"></i>
                                @else
                                    <i class="bi bi-check2" title="Enviado"></i>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <i class="bi bi-chat-square" style="font-size:2.5rem; color:#aaa;"></i>
                    <p class="mt-2 text-muted mb-0">Sé el primero en escribir algo.</p>
                </div>
            @endforelse
        </div>

        {{-- ── Área de escritura ── --}}
        <div class="chat-input-area">
            <button id="btnBajar" class="btn btn-warning chat-send-btn chat-scroll-btn" title="Ir al último mensaje">
                <i class="bi bi-chevron-double-down"></i>
            </button>
            <form id="chatForm" class="d-flex gap-2 align-items-center w-100">
                @csrf
                <input type="text" id="msgInput" name="body"
                       class="form-control chat-input-field"
                       placeholder="Escribe un mensaje..."
                       autocomplete="off" maxlength="2000" required>
                <button type="submit" class="btn btn-warning chat-send-btn" title="Enviar">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>

    </div>
</div>

<style>
/* ─── Bloquear scroll de la página (solo en el chat) ─── */
html, body {
    overflow: hidden;
}

/* ─── Wrapper ─── */
.chat-page-wrapper {
    max-width: 800px;
    margin: 0 auto;
}

/* ─── Card principal ─── */
.chat-card {
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 104px);
    min-height: 480px;
    background: #ece8e1;
}

/* ─── Cabecera ─── */
.chat-header {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    flex-shrink: 0;
    min-height: 58px;
}
.chat-avatar-ph {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: #6c757d;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 1.1rem;
}

/* ─── Zona de mensajes ─── */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: none;       /* Firefox */
    -ms-overflow-style: none;   /* IE/Edge */
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    background-color: #ece8e1;
    background-image: url("data:image/svg+xml,%3Csvg width='52' height='52' viewBox='0 0 52 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23b8ad9e' fill-opacity='0.18'%3E%3Cpath d='M0 0h4v4H0V0zm8 0h4v4H8V0zM0 8h4v4H0V8zm8 8h4v4H8v-4z'/%3E%3C/g%3E%3C/svg%3E");
}
.chat-messages::-webkit-scrollbar { display: none; } /* Chrome/Safari */
.chat-empty {
    text-align: center;
    margin: auto;
    padding: 2rem;
}

/* ─── Filas y burbujas ─── */
.msg-row {
    display: flex;
}
.msg-mine   { justify-content: flex-end; }
.msg-theirs { justify-content: flex-start; }

.msg-bubble {
    max-width: 68%;
    padding: 6px 10px 4px;
    border-radius: 10px;
    word-break: break-word;
    box-shadow: 0 1px 2px rgba(0,0,0,0.13);
}
.bubble-mine {
    background: #ffc107;
    color: #212529;
    border-bottom-right-radius: 3px;
}
.bubble-theirs {
    background: #ffffff;
    color: #212529;
    border-bottom-left-radius: 3px;
}

/* Texto y meta */
.msg-body { font-size: 0.91rem; line-height: 1.4; }
.msg-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 3px;
    font-size: 0.67rem;
    opacity: 0.62;
    margin-top: 2px;
    white-space: nowrap;
}

/* ─── Área de escritura ─── */
.chat-input-area {
    position: relative;
    background: #f0ede8;
    padding: 8px 12px;
    border-top: 1px solid #ddd8d0;
    flex-shrink: 0;
}
.chat-input-field {
    border-radius: 22px;
    border: 1px solid #ccc;
    background: #fff;
    padding: 0.45rem 1rem;
    font-size: 0.91rem;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.chat-input-field:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.18rem rgba(255,193,7,0.22);
    outline: none;
}
.chat-send-btn {
    border-radius: 50%;
    width: 42px; height: 42px;
    padding: 0;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.chat-scroll-btn {
    position: absolute;
    bottom: calc(100% + 8px);
    right: 12px;
    display: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    z-index: 10;
}
</style>

@push('scripts')
<script>
const authId    = {{ Auth::id() }};
const contactId = {{ $user->id }};
const csrf      = document.querySelector('meta[name="csrf-token"]').content;
const container = document.getElementById('mensajes-container');

let lastId    = {{ $mensajes->last()?->id ?? 0 }};
let isPolling = false;

// ─── Scroll inicial al fondo ───
container.scrollTop = container.scrollHeight;

// ─── Botón bajar al fondo ───
const btnBajar = document.getElementById('btnBajar');
function actualizarBtnBajar() {
    const dist = container.scrollHeight - container.scrollTop - container.clientHeight;
    btnBajar.style.display = dist > 200 ? 'flex' : 'none';
}
container.addEventListener('scroll', actualizarBtnBajar);
btnBajar.addEventListener('click', () => {
    container.scrollTop = container.scrollHeight;
    actualizarBtnBajar();
});

// ─── Envío de mensaje por AJAX ───
document.getElementById('chatForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const input = document.getElementById('msgInput');
    const body  = input.value.trim();
    if (!body) return;

    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;

    try {
        const res = await fetch(`/chat/${contactId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ body })
        });
        const data = await res.json();
        if (data.success) {
            lastId = Math.max(lastId, data.mensaje.id);
            // Evitar duplicado por condición de carrera: el polling pudo haberlo añadido antes
            if (!document.querySelector(`.msg-row[data-id="${data.mensaje.id}"]`)) {
                appendMessage(data.mensaje, true);
            }
            input.value = '';
            container.scrollTop = container.scrollHeight;
            actualizarBtnBajar();
        }
    } catch {
        // fallback: envío normal sin AJAX
        this.action = `/chat/${contactId}`;
        this.method = 'POST';
        HTMLFormElement.prototype.submit.call(this);
    } finally {
        btn.disabled = false;
        input.focus();
    }
});

// ─── Añadir mensaje al DOM ───
function appendMessage(msg, mio) {
    const empty = container.querySelector('.chat-empty');
    if (empty) empty.remove();

    const row = document.createElement('div');
    row.className = `msg-row ${mio ? 'msg-mine' : 'msg-theirs'}`;
    row.dataset.id = msg.id;

    const checks = mio ? `<i class="bi bi-check2" title="Enviado"></i>` : '';

    row.innerHTML = `
        <div class="msg-bubble ${mio ? 'bubble-mine' : 'bubble-theirs'}">
            <div class="msg-body">${escHtml(msg.body)}</div>
            <div class="msg-meta">
                <span>${msg.created_at_time}</span>
                ${checks}
            </div>
        </div>`;
    container.appendChild(row);
}

// ─── Polling cada 5 segundos ───
async function fetchNuevosMensajes() {
    if (isPolling) return;
    isPolling = true;
    try {
        const res = await fetch(`/chat/${contactId}?since=${lastId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.mensajes?.length) return;

        // Capturar posición exacta ANTES de modificar el DOM
        const scrollTopAntes    = container.scrollTop;
        const scrollHeightAntes = container.scrollHeight;
        const eraCercaDelFondo  = (scrollHeightAntes - scrollTopAntes - container.clientHeight) < 150;

        data.mensajes.forEach(msg => {
            if (document.querySelector(`.msg-row[data-id="${msg.id}"]`)) return;
            lastId = Math.max(lastId, msg.id);
            appendMessage(msg, msg.sender_id === authId);
        });

        // Restaurar posición siempre de forma explícita
        if (eraCercaDelFondo) {
            // Estaba al fondo → bajar al nuevo mensaje
            container.scrollTop = container.scrollHeight;
        } else {
            // Estaba leyendo mensajes antiguos → mantener posición exacta
            container.scrollTop = scrollTopAntes;
        }
        actualizarBtnBajar();
    } catch {}
    finally { isPolling = false; }
}

let pollingInterval = setInterval(fetchNuevosMensajes, 5000);

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    } else {
        clearInterval(pollingInterval); // limpiar intervalo anterior por si acaso
        pollingInterval = setInterval(fetchNuevosMensajes, 5000);
        fetchNuevosMensajes();
    }
});

// ─── Utilidad ───
function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>
@endpush
@endsection
