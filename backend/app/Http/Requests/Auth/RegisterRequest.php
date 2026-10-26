<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     title="Registro de cliente",
 *     required={"name","email","password","password_confirmation"},
 *     @OA\Property(property="name", type="string", example="Carlos Pérez"),
 *     @OA\Property(property="email", type="string", format="email", example="carlos@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="Secreta123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="Secreta123"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+503 7000-0000"),
 *     @OA\Property(property="address", type="string", nullable=true, example="Soyapango, San Salvador")
 * )
 */
class RegisterRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El registro es público, cualquiera puede intentar crear una cuenta.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Mensajes de validación personalizados en español.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.unique' => 'Ya existe una cuenta registrada con este correo electrónico.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
