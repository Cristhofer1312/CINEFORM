<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaso1Request extends FormRequest
{
    /**
     * Paso 1: Identidad y clasificación PROCINEC.
     */
    public function authorize()
    {
        return hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::CREAR_CURSO);
    }

    public function rules()
    {
        $esSalir = $this->input('accion') === 'salir' || $this->filled('guardar_y_salir');

        return [
            'nombre' => $esSalir ? 'nullable|string|max:255' : 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'eliminar_foto' => 'nullable|boolean',
            'nivel' => 'nullable|string|max:50',
            'id_modalidad' => ($esSalir ? 'nullable' : 'required').'|exists:'.\Modules\Taller\Entities\Modalidad::class.',id_modalidad',
            'id_actividad_formativa' => ($esSalir ? 'nullable' : 'required').'|exists:'.\Modules\Taller\Entities\ActividadFormativa::class.',id_actividad_formativa',
            'id_aspecto' => 'nullable|exists:'.\Modules\Taller\Entities\Aspecto::class.',id_aspecto',
            'id_modalidad_especial' => 'nullable|exists:'.\Modules\Taller\Entities\ModalidadEspecial::class.',id_modalidad_especial',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del curso es obligatorio.',
            'id_modalidad.required' => 'La modalidad es obligatoria.',
            'id_actividad_formativa.required' => 'La actividad formativa es obligatoria.',
            'foto.image' => 'La foto de presentación debe ser una imagen.',
            'foto.mimes' => 'La foto de presentación debe ser un archivo JPG o PNG.',
        ];
    }
}
