<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutletMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'district' => $this->district,
            'status' => $this->status,
            'lat' => $this->latitude !== null ? (float) $this->latitude : null,
            'lng' => $this->longitude !== null ? (float) $this->longitude : null,
            'distributor' => $this->distributor?->name,
            'distributor_id' => $this->distributor_id,
            'territory_id' => $this->territory_id,
            'phone' => $this->phone ?? null,
            // Thumbnail foto utama bila ada (aspect dijaga di frontend via object-contain).
            'photo_url' => $this->cover_url ?? null,
            'last_visit_at' => $this->last_visit_at,
            'last_transaction_at' => $this->last_transaction_at,
        ];
    }
}
