<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::query()
            ->with('agent:id,name,company_name,phone')
            ->active()
            ->when($request->filled('type'), fn ($q) => $q->where('property_type', $request->type))
            ->when($request->filled('listing'), fn ($q) => $q->where('listing_type', $request->listing))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->filled('bedrooms'), fn ($q) => $q->where('bedrooms', '>=', $request->bedrooms))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', '%'.$request->q.'%')
                        ->orWhere('description', 'like', '%'.$request->q.'%')
                        ->orWhere('city', 'like', '%'.$request->q.'%')
                        ->orWhere('region', 'like', '%'.$request->q.'%');
                });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('listed_at');

        return Inertia::render('Properties/Index', [
            'properties' => $query->paginate(12)->withQueryString(),
            'filters' => $request->only(['q', 'type', 'listing', 'region', 'city', 'min_price', 'max_price', 'bedrooms']),
        ]);
    }

    public function show(Property $property)
    {
        $property->load('agent:id,name,company_name,phone', 'media');

        // Track view (demand analytics)
        $property->increment('views_count');

        return Inertia::render('Properties/Show', [
            'property' => $property,
        ]);
    }
}
