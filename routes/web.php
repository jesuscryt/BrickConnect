<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\ConexionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (sin autenticación)
|--------------------------------------------------------------------------
*/

// Página de inicio / landing
Route::get('/', function () {
    // Si ya está logueado, redirigir al feed
    if (auth()->check()) {
        return redirect()->route('feed');
    }
    return view('inicio');
})->name('inicio');

// Mostrar formulario de login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Procesar login (máximo 5 intentos por minuto por IP)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Mostrar formulario de registro
Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
// Procesar registro (máximo 5 intentos por minuto por IP)
Route::post('/registro', [AuthController::class, 'registro'])->middleware('throttle:5,1');

// ── Recuperación de contraseña ──
Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email')->middleware('throttle:5,1');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (requieren autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Feed (publicaciones) ──
    Route::get('/feed', [PostController::class, 'index'])->name('feed');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // ── Comentarios ──
    Route::post('/posts/{post}/comentarios', [ComentarioController::class, 'store'])->name('comentarios.store');
    Route::delete('/comentarios/{comentario}', [ComentarioController::class, 'destroy'])->name('comentarios.destroy');

    // ── Ofertas de empleo ──
    Route::get('/ofertas', [OfertaController::class, 'index'])->name('ofertas.index');
    Route::get('/ofertas/crear', [OfertaController::class, 'create'])->name('ofertas.create');
    Route::post('/ofertas', [OfertaController::class, 'store'])->name('ofertas.store');
    Route::get('/ofertas/{oferta}', [OfertaController::class, 'show'])->name('ofertas.show');
    Route::post('/ofertas/{oferta}/toggle-accept', [OfertaController::class, 'toggleAccept'])->name('ofertas.toggleAccept');
    Route::post('/ofertas/{oferta}/desactivar', [OfertaController::class, 'desactivar'])->name('ofertas.desactivar');
    Route::post('/ofertas/{oferta}/activar', [OfertaController::class, 'activar'])->name('ofertas.activar');
    Route::delete('/ofertas/{oferta}', [OfertaController::class, 'destroy'])->name('ofertas.destroy');

    // ── Red de contactos ──
    Route::get('/red', [ConexionController::class, 'index'])->name('red.index');
    Route::post('/conexion/{user}', [ConexionController::class, 'enviar'])->name('conexion.enviar');
    Route::put('/conexion/{conexion}/aceptar', [ConexionController::class, 'aceptar'])->name('conexion.aceptar');
    Route::put('/conexion/{conexion}/rechazar', [ConexionController::class, 'rechazar'])->name('conexion.rechazar');
    Route::delete('/conexion/{conexion}', [ConexionController::class, 'eliminar'])->name('conexion.eliminar');

    // ── Perfil ──
    Route::get('/perfil/editar', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::get('/perfil/{user}', [PerfilController::class, 'show'])->name('perfil.show');

    // ── Notificaciones ──
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.marcar-leida');
    Route::post('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar-todas');

    // ── Chat / Mensajes privados ──
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{user}', [ChatController::class, 'send'])->name('chat.send');
});

/*
|--------------------------------------------------------------------------
| Rutas de Administración
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // Gestión de usuarios
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
    Route::post('/usuarios/{user}/banear', [AdminController::class, 'banear'])->name('usuarios.banear');
    Route::post('/usuarios/{user}/desbanear', [AdminController::class, 'desbanear'])->name('usuarios.desbanear');
    Route::delete('/usuarios/{user}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.eliminar');

    // Gestión de publicaciones
    Route::get('/publicaciones', [AdminController::class, 'publicaciones'])->name('publicaciones');
    Route::delete('/publicaciones/{post}', [AdminController::class, 'eliminarPublicacion'])->name('publicaciones.destroy');

    // Gestión de ofertas
    Route::get('/ofertas', [AdminController::class, 'ofertas'])->name('ofertas');
    Route::delete('/ofertas/{oferta}', [AdminController::class, 'eliminarOferta'])->name('ofertas.destroy');
});
