<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequisitoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:pregunta,recurso,documento',
            'descripcion' => 'nullable|string|max:1000',
            'obligatorio' => 'nullable|boolean',
            'orden' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'titulo.required' => 'El título del requisito es obligatorio.',
            'titulo.max' => 'El título no debe exceder 255 caracteres.',
            'tipo.required' => 'El tipo de requisito es obligatorio.',
            'tipo.in' => 'El tipo debe ser: pregunta, recurso o documento.',
            'descripcion.max' => 'La descripción no debe exceder 1000 caracteres.',
            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden no puede ser negativo.',
        ];
    }
}