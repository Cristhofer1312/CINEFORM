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
            'nivel' => ($esSalir ? 'nullable' : 'required').'|string|in:Básico,Medio,Avanzado',
            'id_modalidad' => ($esSalir ? 'nullable' : 'required').'|exists:'.\Modules\Taller\Entities\Modalidad::class.',id_modalidad',
            'id_actividad_formativa' => ($esSalir ? 'nullable' : 'required').'|exists:'.\Modules\Taller\Entities\ActividadFormativa::class.',id_actividad_formativa',
            'id_aspecto' => ($esSalir ? 'nullable' : 'required').'|exists:'.\Modules\Taller\Entities\Aspecto::class.',id_aspecto',
            'id_modalidad_especial' => ($esSalir ? 'nullable' : 'required').'|exists:'.\Modules\Taller\Entities\ModalidadEspecial::class.',id_modalidad_especial',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del curso es obligatorio.',
            'id_modalidad.required' => 'La modalidad es obligatoria.',
            'id_actividad_formativa.required' => 'La actividad formativa es obligatoria.',
            'nivel.required' => 'El nivel es obligatorio.',
            'nivel.in' => 'El nivel debe ser Básico, Medio o Avanzado.',
            'id_aspecto.required' => 'El aspecto cinematográfico es obligatorio.',
            'id_modalidad_especial.required' => 'El público objetivo es obligatorio.',
            'foto.image' => 'La foto de presentación debe ser una imagen.',
            'foto.mimes' => 'La foto de presentación debe ser un archivo JPG o PNG.',
        ];
    }
}
