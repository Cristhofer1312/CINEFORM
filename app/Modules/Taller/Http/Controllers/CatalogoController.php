<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Taller\Entities\Aspecto;
use Modules\Taller\Entities\ActividadFormativa;
use App\Constants\SecurityAction;

class CatalogoController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permiso:taller.catalogos.index,' . SecurityAction::VER)
            ->only(['index']);
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * Muestra la vista de gestión de catálogos (Actividades + Aspectos).
     */
    public function index()
    {
        $actividades = ActividadFormativa::withCount('cursos')->orderBy('status', 'desc')->orderBy('nombre')->get();
        $aspectos    = Aspecto::withCount('cursos')->orderBy('status', 'desc')->orderBy('nombre')->get();

        return view('taller::a.Catalogos', compact('actividades', 'aspectos'));
    }

    // =========================================================================
    // ACTIVIDADES FORMATIVAS
    // =========================================================================

    /**
     * Crea una nueva Actividad Formativa.
     */
    public function storeActividad(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'abreviatura' => 'required|string|max:4|unique:taller.actividades_formativas,abreviatura',
        ], [
            'nombre.required'           => 'El nombre de la actividad es obligatorio.',
            'abreviatura.required'      => 'La abreviatura es obligatoria.',
            'abreviatura.max'           => 'La abreviatura no puede tener más de 4 caracteres.',
            'abreviatura.unique'        => 'Ya existe una actividad con esa abreviatura.',
        ]);

        ActividadFormativa::create([
            'nombre'      => strtoupper(trim($request->nombre)),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
            'status'      => 'Activo',
        ]);

        return redirect()->route('taller.catalogos.index')
            ->with('success_actividad', 'Actividad Formativa "' . strtoupper($request->nombre) . '" creada exitosamente.');
    }

    /**
     * Actualiza una Actividad Formativa existente.
     */
    public function updateActividad(Request $request, ActividadFormativa $actividad)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'abreviatura' => 'required|string|max:4|unique:taller.actividades_formativas,abreviatura,' . $actividad->id_actividad_formativa . ',id_actividad_formativa',
        ], [
            'nombre.required'      => 'El nombre de la actividad es obligatorio.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede tener más de 4 caracteres.',
            'abreviatura.unique'   => 'Ya existe otra actividad con esa abreviatura.',
        ]);

        $actividad->update([
            'nombre'      => strtoupper(trim($request->nombre)),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
        ]);

        return redirect()->route('taller.catalogos.index')
            ->with('success_actividad', 'Actividad Formativa actualizada correctamente.');
    }

    /**
     * Cambia el status de una Actividad Formativa entre Activo e Inactivo.
     */
    public function toggleActividad(ActividadFormativa $actividad)
    {
        $nuevo = $actividad->status === 'Activo' ? 'Inactivo' : 'Activo';
        $actividad->update(['status' => $nuevo]);

        $msg = $nuevo === 'Activo'
            ? "Actividad \"{$actividad->nombre}\" activada. Ya aparece en los selectores de cursos."
            : "Actividad \"{$actividad->nombre}\" desactivada. No aparecerá en nuevos cursos.";

        return redirect()->route('taller.catalogos.index')
            ->with('success_actividad', $msg);
    }

    // =========================================================================
    // ASPECTOS DE FORMACIÓN
    // =========================================================================

    /**
     * Crea un nuevo Aspecto de Formación.
     */
    public function storeAspecto(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'abreviatura' => 'required|string|max:4|unique:taller.aspectos,abreviatura',
        ], [
            'nombre.required'      => 'El nombre del aspecto es obligatorio.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede tener más de 4 caracteres.',
            'abreviatura.unique'   => 'Ya existe un aspecto con esa abreviatura.',
        ]);

        Aspecto::create([
            'nombre'      => ucwords(strtolower(trim($request->nombre))),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
            'status'      => 'Activo',
        ]);

        return redirect()->route('taller.catalogos.index')
            ->with('success_aspecto', 'Aspecto de Formación "' . $request->nombre . '" creado exitosamente.');
    }

    /**
     * Actualiza un Aspecto de Formación existente.
     */
    public function updateAspecto(Request $request, Aspecto $aspecto)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'abreviatura' => 'required|string|max:4|unique:taller.aspectos,abreviatura,' . $aspecto->id_aspecto . ',id_aspecto',
        ], [
            'nombre.required'      => 'El nombre del aspecto es obligatorio.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede tener más de 4 caracteres.',
            'abreviatura.unique'   => 'Ya existe otro aspecto con esa abreviatura.',
        ]);

        $aspecto->update([
            'nombre'      => ucwords(strtolower(trim($request->nombre))),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
        ]);

        return redirect()->route('taller.catalogos.index')
            ->with('success_aspecto', 'Aspecto de Formación actualizado correctamente.');
    }

    /**
     * Cambia el status de un Aspecto entre Activo e Inactivo.
     */
    public function toggleAspecto(Aspecto $aspecto)
    {
        $nuevo = $aspecto->status === 'Activo' ? 'Inactivo' : 'Activo';
        $aspecto->update(['status' => $nuevo]);

        $msg = $nuevo === 'Activo'
            ? "Aspecto \"{$aspecto->nombre}\" activado. Ya aparece en los selectores de cursos."
            : "Aspecto \"{$aspecto->nombre}\" desactivado. No aparecerá en nuevos cursos.";

        return redirect()->route('taller.catalogos.index')
            ->with('success_aspecto', $msg);
    }
}
