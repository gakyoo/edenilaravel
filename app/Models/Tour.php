<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'property_id', 'user_id', 'name', 'email', 'phone',
        'preferred_date', 'preferred_time', 'message', 'status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date:Y-m-d',
            'preferred_time' => 'string',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
