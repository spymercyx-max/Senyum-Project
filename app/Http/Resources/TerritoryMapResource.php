<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city' => $this->city,
            'district' => $this->district,
            'code' => $this->code,
            'status' => $this->status,
            'distributors_count' => (int) ($this->distributors_count ?? 0),
            'outlets_count' => (int) ($this->outlets_count ?? 0),
            'active_outlets_count' => (int) ($this->active_outlets_count ?? 0),
            // Centroid dari outlet berkoordinat (null bila belum ada).
            'lat' => $this->center_lat !== null ? (float) $this->center_lat : null,
            'lng' => $this->center_lng !== null ? (float) $this->center_lng : null,
            'coverage' => $this->when(isset($this->coverage), $this->coverage ?? null),
        ];
    }
}
