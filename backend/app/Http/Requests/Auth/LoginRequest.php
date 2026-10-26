<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     title="Inicio de sesión",
 *     required={"email","password"},
 *     @OA\Property(property="email", type="string", format="email", example="carlos@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="Secreta123")
 * )
 */
class LoginRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}
