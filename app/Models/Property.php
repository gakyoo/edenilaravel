<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['primary_image_url', 'price_label', 'slug', 'public_url'];

    protected $fillable = [
        'agent_id', 'owner_id',
        'title', 'description',
        'address_line', 'city', 'region', 'country', 'postal_code',
        'legal_description', 'latitude', 'longitude',
        'property_type', 'listing_type',
        'lot_size', 'building_area', 'bedrooms', 'bathrooms', 'stories',
        'year_built', 'construction_type', 'roof_type', 'roof_age_years',
        'parking_spaces', 'parking_type', 'zoning_classification', 'amenities',
        'price', 'currency', 'market_value', 'tax_value', 'hoa_fees',
        'rental_income', 'is_negotiable',
        'status', 'is_featured', 'is_verified', 'listed_at', 'sold_at',
        'views_count', 'favorites_count', 'enquiries_count',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'amenities' => 'array',
            'is_negotiable' => 'boolean',
            'is_featured' => 'boolean',
            'is_verified' => 'boolean',
            'listed_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->title ?? 'property');
    }

    public function getPublicUrlAttribute(): string
    {
        return '/properties/'.$this->id.'/'.$this->slug;
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function media()
    {
        return $this->hasMany(PropertyMedia::class);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primary = $this->media->firstWhere('is_primary', true) ?? $this->media->first();

        if ($primary) {
            // Relative path (not asset()) so it works from any host (LAN IP, localhost, domain)
            return '/storage/'.$primary->path;
        }

        // Fallback: branded placeholder SVG per property type
        $type = in_array($this->property_type, ['residential', 'commercial', 'industrial', 'land', 'mixed_use'])
            ? $this->property_type
            : 'default';

        return '/img/placeholders/'.$type.'.svg';
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Property::class, 'favorites')
            ->withTimestamps();
    }

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'sale');
    }

    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'rent');
    }

    public function getPriceLabelAttribute(): string
    {
        return number_format($this->price, 0) . ' ' . $this->currency;
    }

    /**
     * Popular searches — deep links into filtered results, built from real data.
     * Used on landing, search results and property detail pages for SEO.
     */
    public static function popularSearches(): \Illuminate\Support\Collection
    {
        $typeLabels = [
            'residential' => 'Houses',
            'land' => 'Land',
            'commercial' => 'Commercial Property',
            'mixed_use' => 'Mixed-Use Property',
            'industrial' => 'Industrial Property',
        ];

        $regions = self::distinct()->pluck('region')->filter()->values()->take(3);
        $popular = collect();

        foreach ($regions as $region) {
            $popular->push([
                'label' => 'All properties in '.$region,
                'url' => '/properties?region='.urlencode($region),
            ]);
            foreach (['residential', 'land'] as $type) {
                foreach (['sale', 'rent'] as $listing) {
                    $popular->push([
                        'label' => $typeLabels[$type].' for '.$listing.' in '.$region,
                        'url' => '/properties?type='.$type.'&listing='.$listing.'&region='.urlencode($region),
                    ]);
                }
            }
        }

        return $popular;
    }
}
