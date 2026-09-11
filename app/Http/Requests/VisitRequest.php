<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'visited_at' => ['required', 'date'],
            'status' => ['sometimes', 'in:done,planned,cancelled'],
            'notes' => ['nullable', 'string'],
            'follow_up_at' => ['nullable', 'date', 'after:visited_at'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
