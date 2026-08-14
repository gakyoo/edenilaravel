<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Admin dashboard with complete KPIs.
     */
    public function index()
    {
        // Core counts
        $total = Property::count();
        $active = Property::where('status', 'active')->count();
        $pending = Property::where('status', 'pending')->count();
        $sold = Property::whereIn('status', ['sold', 'rented'])->count();
        $offMarket = Property::where('status', 'off_market')->count();

        // Value metrics (TZS + USD separately)
        $tzsValue = Property::where('currency', 'TZS')->where('status', '!=', 'off_market')->sum('price');
        $usdValue = Property::where('currency', 'USD')->where('status', '!=', 'off_market')->sum('price');

        // Engagement
        $totalViews = Property::sum('views_count');
        $totalFavorites = Property::sum('favorites_count');
        $totalEnquiries = Enquiry::count();
        $newEnquiries7d = Enquiry::where('created_at', '>=', now()->subDays(7))->count();

        // Conversion (enquiries per view)
        $conversionRate = $totalViews > 0 ? round(($totalEnquiries / $totalViews) * 100, 2) : 0;

        // Users
        $totalUsers = User::count();
        $agents = User::where('role', 'agent')->count();
        $buyers = User::where('role', 'buyer')->count();

        // Tasks open
        $openTasks = Task::where('status', '!=', 'done')->count();

        // Distribution by type and region
        $byType = Property::select('property_type', DB::raw('count(*) as c'))
            ->groupBy('property_type')->orderByDesc('c')->get();
        $byRegion = Property::select('region', DB::raw('count(*) as c'))
            ->whereNotNull('region')->groupBy('region')->orderByDesc('c')->limit(6)->get();
        $byStatus = Property::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')->get();

        // Recent items
        $recentProperties = Property::with('media:id,property_id,path,is_primary')
            ->latest()->limit(5)->get();
        $recentEnquiries = Enquiry::with('property:id,title')
            ->latest()->limit(5)->get();

        return Inertia::render('Admin/Index', [
            'kpis' => [
                'total' => $total, 'active' => $active, 'pending' => $pending,
                'sold' => $sold, 'off_market' => $offMarket,
                'tzs_value' => $tzsValue, 'usd_value' => $usdValue,
                'total_views' => $totalViews, 'total_favorites' => $totalFavorites,
                'total_enquiries' => $totalEnquiries, 'new_enquiries_7d' => $newEnquiries7d,
                'conversion_rate' => $conversionRate,
                'total_users' => $totalUsers, 'agents' => $agents, 'buyers' => $buyers,
                'open_tasks' => $openTasks,
            ],
            'byType' => $byType,
            'byRegion' => $byRegion,
            'byStatus' => $byStatus,
            'recentProperties' => $recentProperties,
            'recentEnquiries' => $recentEnquiries,
        ]);
    }

    /**
     * Property list with filters + pagination.
     */
    public function properties(Request $request)
    {
        $query = Property::query()
            ->with('media:id,property_id,path,is_primary')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('property_type', $request->type))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($s) => $s
                ->where('title', 'like', '%'.$request->q.'%')
                ->orWhere('city', 'like', '%'.$request->q.'%')
                ->orWhere('region', 'like', '%'.$request->q.'%')))
            ->latest();

        return Inertia::render('Admin/Properties', [
            'properties' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'type', 'region']),
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
            'regions' => Property::distinct()->pluck('region')->filter()->values(),
        ]);
    }

    /**
     * Show the create-property form.
     */
    public function create()
    {
        return Inertia::render('Admin/PropertyForm', [
            'property' => null,
            'agents' => User::whereIn('role', ['agent', 'admin'])->get(['id', 'name', 'company_name', 'email']),
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
        ]);
    }

    /**
     * Store a new property.
     */
    public function store(Request $request)
    {
        $data = $this->validateProperty($request);

        $property = Property::create($data);

        // Handle primary image upload
        $this->handleUploads($request, $property);

        return redirect(route('admin.properties'))->with('success', 'Property created.');
    }

    /**
     * Show the edit-property form.
     */
    public function edit(Property $property)
    {
        $property->load('media');

        return Inertia::render('Admin/PropertyForm', [
            'property' => $property,
            'agents' => User::whereIn('role', ['agent', 'admin'])->get(['id', 'name', 'company_name', 'email']),
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
        ]);
    }

    /**
     * Update a property.
     */
    public function update(Request $request, Property $property)
    {
        $data = $this->validateProperty($request);

        $property->update($data);

        $this->handleUploads($request, $property);

        return redirect(route('admin.properties'))->with('success', 'Property updated.');
    }

    /**
     * Delete a property (soft delete).
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return back()->with('success', 'Property deleted.');
    }

    /**
     * Enquiry management.
     */
    public function enquiries(Request $request)
    {
        $query = Enquiry::query()
            ->with('property:id,title')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($s) => $s
                ->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%')
                ->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->latest();

        return Inertia::render('Admin/Enquiries', [
            'enquiries' => $query->paginate(12)->withQueryString(),
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function updateEnquiry(Request $request, Enquiry $enquiry)
    {
        $enquiry->update($request->validate([
            'status' => 'required|in:new,contacted,qualified,closed',
        ]));

        return back()->with('success', 'Enquiry updated.');
    }

    public function destroyEnquiry(Enquiry $enquiry)
    {
        $enquiry->delete();

        return back()->with('success', 'Enquiry deleted.');
    }

    /**
     * Shared property validation.
     */
    protected function validateProperty(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'property_type' => 'required|in:residential,commercial,industrial,land,mixed_use',
            'listing_type' => 'required|in:sale,rent,both',
            'lot_size' => 'nullable|numeric',
            'building_area' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'stories' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'parking_spaces' => 'nullable|integer',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:TZS,USD',
            'is_negotiable' => 'nullable|boolean',
            'status' => 'required|in:active,pending,sold,rented,off_market',
            'is_featured' => 'nullable|boolean',
            'amenities' => 'nullable|array',
            'listed_at' => 'nullable|date',
        ]);
    }

    /**
     * Handle photo uploads (primary + gallery).
     */
    protected function handleUploads(Request $request, Property $property): void
    {
        if ($request->hasFile('primary_photo')) {
            $path = $request->file('primary_photo')->store('property-media/'.$property->id, 'public');
            PropertyMedia::create([
                'property_id' => $property->id,
                'type' => 'photo',
                'path' => $path,
                'is_primary' => true,
                'mime_type' => $request->file('primary_photo')->getMimeType(),
            ]);
        }

        if ($request->hasFile('gallery_photos')) {
            foreach ($request->file('gallery_photos') as $photo) {
                $path = $photo->store('property-media/'.$property->id, 'public');
                PropertyMedia::create([
                    'property_id' => $property->id,
                    'type' => 'photo',
                    'path' => $path,
                    'is_primary' => false,
                    'mime_type' => $photo->getMimeType(),
                ]);
            }
        }
    }
}
