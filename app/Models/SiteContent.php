<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /** Get a content value by key with a default fallback. */
    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /** Get all contents as a key => value map. */
    public static function allMap(): array
    {
        return static::pluck('value', 'key')->map(fn ($v) => (string) $v)->all();
    }
}
