<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request base para toda la API: garantiza que cualquier error de
 * validación se devuelva en un formato JSON consistente en toda la
 * aplicación (ver requisito de "manejo de errores").
 */
abstract class BaseApiRequest extends FormRequest
{
    /**
     * Respuesta JSON consistente cuando la validación falla.
     */
    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Los datos proporcionados no son válidos.',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Respuesta JSON consistente cuando la autorización falla.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'No tienes autorización para realizar esta acción.',
        ], 403));
    }
}
