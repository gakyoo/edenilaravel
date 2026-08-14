<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $fillable = ['user_id', 'name', 'filters'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Build the /properties URL that re-runs this saved search. */
    public function url(): string
    {
        $filters = is_array($this->filters) ? $this->filters : [];
        $params = array_filter($filters, fn ($v) => $v !== null && $v !== '');

        return '/properties'.(count($params) ? '?'.http_build_query($params) : '');
    }

    /** Human-readable label of the filters, e.g. "Houses for sale in Arusha". */
    public function label(): string
    {
        $f = is_array($this->filters) ? $this->filters : [];

        $typeLabels = [
            'residential' => 'Houses',
            'land' => 'Land',
            'commercial' => 'Commercial Property',
            'mixed_use' => 'Mixed-Use Property',
            'industrial' => 'Industrial Property',
        ];
        $type = $typeLabels[$f['type'] ?? null] ?? null;
        $listing = ($f['listing'] ?? null) === 'rent' ? 'for rent' : (($f['listing'] ?? null) === 'sale' ? 'for sale' : null);
        $region = $f['region'] ?? null;

        $parts = array_filter([$type, $listing, $region ? 'in '.$region : null]);

        return $this->name ?: (count($parts) ? implode(' ', $parts) : 'All properties');
    }
}
