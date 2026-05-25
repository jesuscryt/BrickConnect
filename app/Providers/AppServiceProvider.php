<?php

namespace App\Providers;

use App\Models\Comentario;
use App\Models\Message;
use App\Models\Oferta;
use App\Models\Post;
use App\Policies\ComentarioPolicy;
use App\Policies\OfertaPolicy;
use App\Policies\PostPolicy;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Usar Bootstrap para la paginación en lugar de Tailwind
        Paginator::useBootstrapFive();

        // Forzar HTTPS en producción para que todos los enlaces y formularios usen HTTPS
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Configurar Carbon en español (para "hace 2 horas", etc.)
        Carbon::setLocale('es');

        // Macro de tiempo relativo personalizado
        Carbon::macro('tiempoRelativo', function () {
            $diff = (int) $this->diffInSeconds(now());

            if ($diff < 60)   return 'hace un momento';

            $minutos = (int) ($diff / 60);
            if ($minutos < 60) return 'hace ' . $minutos . ' ' . ($minutos === 1 ? 'minuto' : 'minutos');

            $horas = (int) ($diff / 3600);
            if ($horas < 24)  return 'hace ' . $horas . ' ' . ($horas === 1 ? 'hora' : 'horas');

            $dias = (int) ($diff / 86400);
            if ($dias < 7)    return 'hace ' . $dias . ' ' . ($dias === 1 ? 'día' : 'días');

            $semanas = (int) ($dias / 7);
            if ($semanas < 5) return 'hace ' . $semanas . ' ' . ($semanas === 1 ? 'semana' : 'semanas');

            $meses = (int) now()->diffInMonths($this);
            if ($meses < 12)  return 'hace ' . $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');

            $anios = (int) now()->diffInYears($this);
            return 'hace ' . $anios . ' ' . ($anios === 1 ? 'año' : 'años');
        });

        // Registro de Policies de autorización
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Oferta::class, OfertaPolicy::class);
        Gate::policy(Comentario::class, ComentarioPolicy::class);

        // View Composer: compartir datos del navbar con el layout en cada request
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                $mensajesNoLeidos = Message::where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $notificacionesNoLeidas = $user->notificacionesNoLeidas()
                    ->with(['emisor', 'notificable'])
                    ->latest()
                    ->limit(5)
                    ->get();

                // Cargar la relación 'oferta' solo en notificables de tipo OfertaAceptacion
                $notificacionesNoLeidas
                    ->filter(fn($n) => $n->notificable instanceof \App\Models\OfertaAceptacion)
                    ->each(fn($n) => $n->notificable->loadMissing('oferta'));

                $contarNoLeidas = $user->notificacionesNoLeidas()->count();

                $view->with(compact('mensajesNoLeidos', 'notificacionesNoLeidas', 'contarNoLeidas'));
            }
        });
    }
}
