<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\OfertaAceptacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Controlador de ofertas de empleo.
 * CRUD completo para vacantes del sector construcción.
 */
class OfertaController extends Controller
{
    /** Tipos de contrato válidos (usados en filtro y validación) */
    private const TIPOS_CONTRATO = ['Tiempo completo', 'Media jornada', 'Temporal', 'Por obra'];

    /** Listar todas las ofertas */
    public function index(Request $request)
    {
        $query = Oferta::with('user')->latest();

        // Búsqueda de texto libre
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('titulo', 'ilike', "%{$busqueda}%")
                  ->orWhere('empresa', 'ilike', "%{$busqueda}%")
                  ->orWhere('descripcion', 'ilike', "%{$busqueda}%");
            });
        }

        // Filtro por ubicación
        if ($request->filled('ubicacion')) {
            $query->where('ubicacion', 'ilike', "%{$request->ubicacion}%");
        }

        // Filtro por tipo de contrato
        if ($request->filled('tipo_contrato') && in_array($request->tipo_contrato, self::TIPOS_CONTRATO)) {
            $query->where('tipo_contrato', $request->tipo_contrato);
        }

        // Filtro por estado (activa/cerrada), por defecto muestra todas
        if ($request->filled('estado')) {
            if ($request->estado === 'activa') {
                $query->where('activa', true);
            } elseif ($request->estado === 'cerrada') {
                $query->where('activa', false);
            }
        }

        $ofertas = $query->paginate(10)->withQueryString();

        return view('ofertas.index', compact('ofertas'));
    }

    /** Mostrar formulario para crear oferta */
    public function create()
    {
        return view('ofertas.create');
    }

    /** Guardar nueva oferta */
    public function store(Request $request)
    {
        $periodosValidos    = ['mensual', 'anual', 'por hora', 'por día', 'semanal'];
        $tiposSalarioValidos = ['bruto', 'neto'];

        $request->validate([
            'titulo'          => 'required|string|max:255',
            'empresa'         => 'required|string|max:255',
            'ubicacion'       => 'required|string|max:255',
            'descripcion'     => 'required|string|max:5000',
            'tipo_contrato'   => ['required', Rule::in(self::TIPOS_CONTRATO)],
            'salario'         => 'nullable|numeric|min:1|max:500000',
            'salario_periodo' => ['nullable', Rule::in($periodosValidos)],
            'salario_tipo'    => ['nullable', Rule::in($tiposSalarioValidos)],
            'horas_semanales' => 'nullable|integer|min:1|max:168',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'empresa.required'     => 'La empresa es obligatoria.',
            'ubicacion.required'   => 'La ubicación es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        Oferta::create([
            'user_id'         => Auth::id(),
            'titulo'          => $request->titulo,
            'empresa'         => $request->empresa,
            'ubicacion'       => $request->ubicacion,
            'descripcion'     => $request->descripcion,
            'tipo_contrato'   => $request->tipo_contrato,
            'salario'         => $request->salario,
            'salario_periodo' => $request->tipo_contrato === 'Por obra' ? null : $request->salario_periodo,
            'salario_tipo'    => $request->salario_tipo,
            'horas_semanales' => $request->horas_semanales,
            'activa'          => true,
        ]);

        return redirect()->route('ofertas.index');
    }

    /** Ver detalle de una oferta */
    public function show(Oferta $oferta)
    {
        $oferta->load('user', 'aceptaciones.user');

        // Para cada candidato, comprobar si el creador ya es contacto suyo
        $creador = $oferta->user;
        $conexionesCreador = [];

        if (Auth::check() && Auth::id() === $oferta->user_id) {
            // Mapa user_id => conexion_id para los candidatos que son contactos del creador
            $conexionesCreador = $creador->contactos()
                ->whereIn('users.id', $oferta->aceptaciones->pluck('user_id'))
                ->pluck('users.id')
                ->flip()   // para búsqueda O(1)
                ->toArray();
        }

        return view('ofertas.show', compact('oferta', 'conexionesCreador'));
    }

    /** Aceptar/Desaceptar una oferta */
    public function toggleAccept(Oferta $oferta)
    {
        // Validar que no sea el dueño de la oferta
        if ($oferta->user_id === Auth::id()) {
            return response()->json(['error' => 'No puedes aceptar tu propia oferta.'], 403);
        }

        // Validar que la oferta esté activa
        if (!$oferta->activa) {
            return response()->json(['error' => 'Esta oferta ya no está disponible.'], 403);
        }

        $usuarioId = Auth::id();

        // Verificar si el usuario ya aceptó
        $aceptacion = OfertaAceptacion::where('oferta_id', $oferta->id)
                                       ->where('user_id', $usuarioId)
                                       ->first();

        if ($aceptacion) {
            // Si ya existe, eliminar (desaceptar)
            $aceptacion->delete();
            return response()->json(['message' => 'Has desaceptado la oferta.'], 200);
        }

        // Si no existe, crear (aceptar). Capturamos race condition por el UNIQUE constraint
        try {
            $nuevaAceptacion = OfertaAceptacion::create([
                'oferta_id'   => $oferta->id,
                'user_id'     => $usuarioId,
                'aceptada_en' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json(['error' => 'Ya has aceptado esta oferta.'], 409);
        }

        // Notificar al creador de la oferta
        if ($oferta->user_id !== $usuarioId) {
            Notificacion::create([
                'usuario_id'       => $oferta->user_id,
                'emisor_id'        => $usuarioId,
                'tipo'             => 'aceptacion_oferta',
                'notificable_type' => OfertaAceptacion::class,
                'notificable_id'   => $nuevaAceptacion->id,
            ]);
        }

        return response()->json(['message' => '¡Has aceptado la oferta!'], 200);
    }

    /** Desactivar oferta para que nadie más pueda aceptar */
    public function desactivar(Oferta $oferta)
    {
        $this->authorize('update', $oferta);

        $oferta->update(['activa' => false]);

        return redirect()->route('ofertas.show', $oferta);
    }

    /** Reactivar oferta */
    public function activar(Oferta $oferta)
    {
        $this->authorize('update', $oferta);

        $oferta->update(['activa' => true]);

        return redirect()->route('ofertas.show', $oferta);
    }

    /** Eliminar una oferta propia */
    public function destroy(Oferta $oferta)
    {
        $this->authorize('delete', $oferta);

        $oferta->delete();

        return redirect()->route('ofertas.index');
    }
}
