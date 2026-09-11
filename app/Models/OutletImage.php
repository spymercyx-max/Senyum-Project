<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'path',
        'disk',
        'original_name',
        'size',
        'mime',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function url(): string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);
    }
}
