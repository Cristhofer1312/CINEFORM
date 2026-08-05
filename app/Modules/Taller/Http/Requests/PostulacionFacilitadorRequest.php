<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Taller\Entities\RequisitoFacilitador;

class PostulacionFacilitadorRequest extends FormRequest
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
     * El formulario envía campos como req_{id_requisito_facilitador}
     * (ej: req_1, req_2, etc.), NO en formato requisitos[0]...
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        // Obtener todos los requisitos activos
        $requisitos = RequisitoFacilitador::activos()->get();

        foreach ($requisitos as $req) {
            $key = 'req_' . $req->id_requisito_facilitador;

            if ($req->tipo === 'documento') {
                // Validar archivos: obligatorio si el requisito lo dice
                if ($req->obligatorio) {
                    $rules[$key] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
                } else {
                    $rules[$key] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                }
            } elseif ($req->tipo === 'pregunta') {
                // Validar texto: obligatorio si el requisito lo dice
                if ($req->obligatorio) {
                    $rules[$key] = 'required|string|max:5000';
                } else {
                    $rules[$key] = 'nullable|string|max:5000';
                }
            }
            // Los recursos son informativos, no se validan
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        $requisitos = RequisitoFacilitador::activos()->get();

        foreach ($requisitos as $req) {
            $key = 'req_' . $req->id_requisito_facilitador;

            if ($req->tipo === 'documento') {
                $messages["{$key}.required"] = "El documento '{$req->titulo}' es obligatorio.";
                $messages["{$key}.mimes"] = "El archivo '{$req->titulo}' debe ser PDF, JPG o PNG.";
                $messages["{$key}.max"] = "El archivo '{$req->titulo}' no debe exceder 5MB.";
            } elseif ($req->tipo === 'pregunta') {
                $messages["{$key}.required"] = "La pregunta '{$req->titulo}' es obligatoria.";
                $messages["{$key}.max"] = "La respuesta de '{$req->titulo}' no debe exceder 5000 caracteres.";
            }
        }

        return $messages;
    }
}