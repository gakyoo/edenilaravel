<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Tour;
use App\Mail\TourBooked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $favoriteIds = $request->user()?->favorites()->pluck('property_id') ?? collect();

        $query = Property::query()
            ->with(['agent:id,name,company_name,phone', 'media:id,property_id,path,is_primary'])
            ->active()
            ->when($request->filled('type'), fn ($q) => $q->where('property_type', $request->type))
            ->when($request->filled('listing'), fn ($q) => $q->where('listing_type', $request->listing))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->filled('bedrooms'), fn ($q) => $q->where('bedrooms', '>=', $request->bedrooms))
            ->when($request->filled('bathrooms'), fn ($q) => $q->where('bathrooms', '>=', $request->bathrooms))
            ->when($request->filled('min_area'), fn ($q) => $q->where('building_area', '>=', $request->min_area))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', '%'.$request->q.'%')
                        ->orWhere('description', 'like', '%'.$request->q.'%')
                        ->orWhere('city', 'like', '%'.$request->q.'%')
                        ->orWhere('region', 'like', '%'.$request->q.'%')
                        ->orWhere('address_line', 'like', '%'.$request->q.'%');
                });
            });

        // ---------- Distance sort / filter (Haversine) ----------
        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $radiusKm = $request->float('radius_km');

        if ($lat && $lng) {
            $distanceSql = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

            // Filter by radius if provided
            if ($radiusKm > 0) {
                $query->whereRaw($distanceSql.' <= ?', [$lat, $lng, $lat, $radiusKm]);
            }

            // Sort by distance when requested
            if ($request->filled('sort') && $request->sort === 'distance') {
                $query->orderByRaw($distanceSql, [$lat, $lng, $lat]);
            }
        }

        // ---------- Sorting ----------
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price');
                    break;
                case 'price_desc':
                    $query->orderByDesc('price');
                    break;
                case 'title_asc':
                    $query->orderBy('title');
                    break;
                case 'title_desc':
                    $query->orderByDesc('title');
                    break;
                case 'newest':
                    $query->orderByDesc('listed_at');
                    break;
                case 'views':
                    $query->orderByDesc('views_count');
                    break;
                case 'area_desc':
                    $query->orderByDesc('building_area');
                    break;
                default:
                    $query->orderByDesc('is_featured')->orderByDesc('listed_at');
            }
        } else {
            $query->orderByDesc('is_featured')->orderByDesc('listed_at');
        }

        // Ensure deterministic order for distance sort (featured first)
        if (!($request->filled('sort') && $request->sort === 'distance')) {
            $query->orderByDesc('is_featured');
        }

        $filters = $request->only([
            'q', 'type', 'listing', 'region', 'city',
            'min_price', 'max_price', 'bedrooms', 'bathrooms', 'min_area',
            'lat', 'lng', 'radius_km', 'sort',
        ]);

        return Inertia::render('Properties/Index', [
            'properties' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
            'favoriteIds' => $favoriteIds,
            'popularSearches' => Property::popularSearches(),
            'filterOptions' => [
                'regions' => Property::distinct()->pluck('region')->filter()->values(),
                'cities' => Property::distinct()->pluck('city')->filter()->values(),
            ],
        ]);
    }

    public function show(Request $request, Property $property, ?string $slug = null)
    {
        // SEO: redirect to canonical URL if the slug doesn't match
        if ($slug !== $property->slug) {
            return redirect()->route('properties.show', [$property->id, $property->slug]);
        }

        $property->load('agent:id,name,company_name,phone', 'media');

        // Track view (demand analytics)
        $property->increment('views_count');

        // Similar homes in the same region (Zillow-style)
        $similar = Property::query()
            ->with('media:id,property_id,path,is_primary')
            ->where('id', '!=', $property->id)
            ->where('region', $property->region)
            ->where('status', 'active')
            ->orderByDesc('views_count')
            ->limit(4)
            ->get();

        // Popular searches — deep links into filtered results, built from real data
        $popularSearches = Property::popularSearches();

        return Inertia::render('Properties/Show', [
            'property' => $property,
            'similar' => $similar,
            'favoriteIds' => $request->user()?->favorites()->pluck('property_id') ?? collect(),
            'popularSearches' => $popularSearches,
        ]);
    }

    // ---------- Favorites ----------

    public function favorite(Request $request, Property $property)
    {
        $user = $request->user();
        $already = $user->favorites()->where('property_id', $property->id)->exists();

        if (!$already) {
            $user->favorites()->attach($property->id);
            $property->increment('favorites_count');
        }

        return back()->with('success', 'Added to favorites');
    }

    public function unfavorite(Request $request, Property $property)
    {
        $user = $request->user();
        $removed = $user->favorites()->detach($property->id);

        if ($removed > 0) {
            $property->decrement('favorites_count');
        }

        return back()->with('success', 'Removed from favorites');
    }

    public function favorites(Request $request)
    {
        $properties = $request->user()->favorites()
            ->with('media:id,property_id,path,is_primary')
            ->active()
            ->latest('favorites.created_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Properties/Favorites', [
            'properties' => $properties,
            'favoriteIds' => $properties->pluck('id'),
        ]);
    }

    // ---------- Tour scheduling ----------

    public function storeTour(Request $request, Property $property)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $tour = $property->tours()->create([
            ...$data,
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);
        $property->increment('tours_count');

        // Transactional email: confirm the booking to the requester
        try {
            Mail::to($tour->email)->send(new TourBooked($tour));
        } catch (\Throwable $e) {
            // Don't block the booking if mail is not configured yet
            report($e);
        }

        return back()->with('success', 'Tour requested! We will confirm your visit soon.');
    }
}
