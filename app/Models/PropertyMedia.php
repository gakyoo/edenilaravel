<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyMedia extends Model
{
    protected $fillable = [
        'property_id', 'type', 'path', 'original_name',
        'mime_type', 'size_bytes', 'is_primary', 'caption',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'size_bytes' => 'integer',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
