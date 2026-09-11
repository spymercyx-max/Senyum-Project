<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDistributorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'experience' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
