<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributorMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'status' => $this->distributorProfile?->status,
            'territory' => $this->distributorProfile?->territory
                ? $this->distributorProfile->territory->city.' — '.$this->distributorProfile->territory->district
                : null,
            'territory_id' => $this->distributorProfile?->territory_id,
            'outlets_count' => (int) ($this->outlets_count ?? 0),
            'lat' => $this->center_lat !== null ? (float) $this->center_lat : null,
            'lng' => $this->center_lng !== null ? (float) $this->center_lng : null,
        ];
    }
}
