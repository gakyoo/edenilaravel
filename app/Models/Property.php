<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['primary_image_url', 'price_label'];

    protected $fillable = [
        'agent_id', 'owner_id',
        'parcel_number', 'title', 'description',
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
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
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
}
