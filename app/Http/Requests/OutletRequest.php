<?php

namespace App\Http\Requests;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;

class OutletRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'google_maps_url' => ['required', 'url', 'max:2000'],
            'notes' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ImageUpload::rules(false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama outlet',
            'address' => 'alamat',
            'city' => 'kota',
            'district' => 'kecamatan',
            'phone' => 'no. HP outlet',
            'google_maps_url' => 'link Google Maps',
            'notes' => 'catatan',
            'images' => 'foto outlet',
            'images.*' => 'foto outlet',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'google_maps_url.required' => 'Link Google Maps wajib diisi. Buka outlet di Google Maps lalu salin link-nya.',
            'google_maps_url.url' => 'Link Google Maps tidak valid. Pastikan diawali http(s)://.',
        ];
    }
}
