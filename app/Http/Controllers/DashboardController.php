<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Property;
use App\Models\Task;
use App\Models\Tour;
use App\Mail\TourStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ---------- Non-admin users get their own dashboard ----------
        if ($user->role !== 'admin') {
            return $this->userDashboard($request);
        }

        // ---------- Key metrics ----------
        $metrics = [
            'total_properties' => Property::count(),
            'active_listings' => Property::where('status', 'active')->count(),
            'pending_sales' => Property::where('status', 'pending')->count(),
            'sold' => Property::whereIn('status', ['sold', 'rented'])->count(),
            'total_value' => Property::where('status', '!=', 'off_market')
                ->where('currency', 'TZS')
                ->sum('price'),
            'total_views' => Property::sum('views_count'),
            'total_enquiries' => Enquiry::count(),
            'new_enquiries' => Enquiry::where('created_at', '>=', now()->subDays(7))->count(),
            'total_tours' => Tour::count(),
            'new_tours' => Tour::where('created_at', '>=', now()->subDays(7))->count(),
            'open_tasks' => Task::where('user_id', $user->id)->where('status', '!=', 'done')->count(),
        ];

        // ---------- Avg time from listing to closing (sale/rent) ----------
        $closedProperties = Property::whereIn('status', ['sold', 'rented'])
            ->whereNotNull('sold_at')
            ->get()
            ->map(fn ($p) => abs($p->sold_at->diffInDays($p->listed_at ?? $p->created_at)));
        $metrics['avg_days_to_close'] = $closedProperties->isNotEmpty()
            ? round($closedProperties->avg())
            : null;

        // ---------- Recent activity feed ----------
        $activity = collect();

        Enquiry::with('property:id,title')
            ->latest()
            ->limit(6)
            ->get()
            ->each(function ($e) use ($activity) {
                $activity->push([
                    'type' => 'enquiry',
                    'label' => 'New enquiry',
                    'detail' => ($e->name ?: 'Someone') . ' asked about "'.($e->property?->title ?: 'a property').'"',
                    'time' => $e->created_at?->diffForHumans(),
                    'created_at' => $e->created_at,
                ]);
            });

        Tour::with('property:id,title')
            ->latest()
            ->limit(6)
            ->get()
            ->each(function ($t) use ($activity) {
                $activity->push([
                    'type' => 'tour',
                    'label' => 'Tour request',
                    'detail' => ($t->name ?: 'Someone') . ' wants to view "'.($t->property?->title ?: 'a property').'"',
                    'time' => $t->created_at?->diffForHumans(),
                    'created_at' => $t->created_at,
                ]);
            });

        Property::latest()
            ->limit(4)
            ->get()
            ->each(function ($p) use ($activity) {
                $activity->push([
                    'type' => 'listing',
                    'label' => ucfirst($p->status).' listing',
                    'detail' => $p->title.' — '.$p->city,
                    'time' => $p->created_at?->diffForHumans(),
                    'created_at' => $p->created_at,
                ]);
            });

        $activity = $activity->sortByDesc('created_at')->take(8)->values();

        // ---------- Quick alerts ----------
        $alerts = [];
        $stale = Property::where('status', 'active')->where('listed_at', '<', now()->subDays(60))->count();
        if ($stale > 0) {
            $alerts[] = ['level' => 'warning', 'text' => "$stale active listing(s) older than 60 days — consider refreshing."];
        }
        if ($metrics['new_enquiries'] > 0) {
            $alerts[] = ['level' => 'info', 'text' => $metrics['new_enquiries'].' new enquiry/enquiries in the last 7 days.'];
        }
        if ($metrics['new_tours'] > 0) {
            $alerts[] = ['level' => 'info', 'text' => $metrics['new_tours'].' new tour request(s) in the last 7 days.'];
        }
        $pendingTours = Tour::where('status', 'pending')->count();
        if ($pendingTours > 0) {
            $alerts[] = ['level' => 'warning', 'text' => "$pendingTours tour request(s) awaiting confirmation."];
        }
        $pending = Property::where('status', 'pending')->count();
        if ($pending > 0) {
            $alerts[] = ['level' => 'info', 'text' => "$pending property/properties pending review."];
        }
        if ($metrics['open_tasks'] > 0) {
            $alerts[] = ['level' => 'warning', 'text' => $metrics['open_tasks'].' open task(s) on your list.'];
        }

        // ---------- Property snapshot with filters ----------
        $propertyQuery = Property::query()
            ->with('media:id,property_id,path,is_primary')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('property_type', $request->type))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(fn ($sub) => $sub->where('title', 'like', '%'.$request->q.'%')
                    ->orWhere('city', 'like', '%'.$request->q.'%')
                    ->orWhere('region', 'like', '%'.$request->q.'%'));
            })
            ->orderByDesc('updated_at');

        $properties = $propertyQuery->paginate(8)->withQueryString();

        // ---------- Leads ----------
        $recentEnquiries = Enquiry::with('property:id,title')
            ->latest()->limit(5)->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name ?: 'Anonymous',
                'phone' => $e->phone,
                'channel' => $e->channel,
                'property' => $e->property?->title,
                'message' => \Illuminate\Support\Str::limit($e->message, 60),
                'created_at' => $e->created_at?->diffForHumans(),
            ]);

        // ---------- Financial snapshot ----------
        $financial = [
            'total_listing_value_tzs' => Property::where('currency', 'TZS')->where('status', '!=', 'off_market')->sum('price'),
            'avg_price_tzs' => round(Property::where('currency', 'TZS')->avg('price') ?? 0),
            'rental_income_tzs' => Property::where('listing_type', 'rent')->where('currency', 'TZS')->sum('rental_income') ?: Property::where('listing_type', 'rent')->where('currency', 'TZS')->sum('price'),
            'usd_listings' => Property::where('currency', 'USD')->count(),
        ];

        // ---------- Tasks ----------
        $tasks = Task::where('user_id', $user->id)
            ->with('property:id,title')
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'done')")
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $upcoming = Task::where('user_id', $user->id)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now()->toDateString())
            ->orderBy('due_date')
            ->limit(6)
            ->get()
            ->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'due_date' => $t->due_date?->format('M d')]);

        // ---------- Analytics ----------
        $topListings = Property::orderByDesc('views_count')
            ->limit(5)
            ->get(['id', 'title', 'views_count', 'enquiries_count'])
            ->map(fn ($p) => [
                'title' => \Illuminate\Support\Str::limit($p->title, 28),
                'views' => $p->views_count,
                'enquiries' => $p->enquiries_count,
                'url' => $p->public_url,
            ]);

        $viewsByStatus = Property::select('status', DB::raw('SUM(views_count) as views'))
            ->groupBy('status')->get();

        // ---------- Filters for the bar ----------
        $filterOptions = [
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
            'regions' => Property::distinct()->pluck('region')->filter()->values(),
        ];

        // ---------- Admin KPIs (merged) ----------
        $adminKpis = [
            'off_market' => Property::where('status', 'off_market')->count(),
            'usd_value' => Property::where('currency', 'USD')->where('status', '!=', 'off_market')->sum('price'),
            'total_users' => \App\Models\User::count(),
            'agents' => \App\Models\User::where('role', 'agent')->count(),
            'buyers' => \App\Models\User::where('role', 'buyer')->count(),
        ];
        $byType = Property::select('property_type', DB::raw('count(*) as c'))
            ->groupBy('property_type')->orderByDesc('c')->get();
        $byRegion = Property::select('region', DB::raw('count(*) as c'))
            ->whereNotNull('region')->groupBy('region')->orderByDesc('c')->limit(6)->get();
        $byStatus = Property::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')->get();

        // ---------- All enquiries (for merged tab) ----------
        $allEnquiries = Enquiry::with('property:id,title')
            ->when($request->filled('eq'), fn ($q) => $q->where(fn ($s) => $s
                ->where('name', 'like', '%'.$request->eq.'%')
                ->orWhere('email', 'like', '%'.$request->eq.'%')
                ->orWhere('phone', 'like', '%'.$request->eq.'%')))
            ->when($request->filled('estatus'), fn ($q) => $q->where('status', $request->estatus))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // ---------- All tours (for merged tab) ----------
        $allTours = Tour::with('property:id,title,price,currency')
            ->when($request->filled('tq'), fn ($q) => $q->where(fn ($s) => $s
                ->where('name', 'like', '%'.$request->tq.'%')
                ->orWhere('email', 'like', '%'.$request->tq.'%')
                ->orWhere('phone', 'like', '%'.$request->tq.'%')))
            ->when($request->filled('tstatus'), fn ($q) => $q->where('status', $request->tstatus))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // ---------- All users (for merged admin tab) ----------
        $allUsers = \App\Models\User::query()
            ->withCount(['tours', 'favorites'])
            ->when($request->filled('uq'), fn ($q) => $q->where(fn ($s) => $s
                ->where('name', 'like', '%'.$request->uq.'%')
                ->orWhere('email', 'like', '%'.$request->uq.'%')
                ->orWhere('phone', 'like', '%'.$request->uq.'%')))
            ->when($request->filled('urole'), fn ($q) => $q->where('role', $request->urole))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->role,
                'provider' => $u->provider,
                'email_verified_at' => $u->email_verified_at?->toDateTimeString(),
                'created_at' => $u->created_at?->diffForHumans(),
                'tours_count' => $u->tours_count,
                'favorites_count' => $u->favorites_count,
            ]);

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'activity' => $activity,
            'alerts' => $alerts,
            'properties' => $properties,
            'filters' => $request->only(['q', 'status', 'type', 'region', 'min_price', 'max_price']),
            'filterOptions' => $filterOptions,
            'leads' => $recentEnquiries,
            'financial' => $financial,
            'tasks' => $tasks,
            'upcoming' => $upcoming,
            'topListings' => $topListings,
            'viewsByStatus' => $viewsByStatus,
            'adminKpis' => $adminKpis,
            'byType' => $byType,
            'byRegion' => $byRegion,
            'byStatus' => $byStatus,
            'allEnquiries' => $allEnquiries,
            'enquiryFilters' => $request->only(['eq', 'estatus']),
            'allTours' => $allTours,
            'tourFilters' => $request->only(['tq', 'tstatus']),
            'allUsers' => $allUsers,
            'userFilters' => $request->only(['uq', 'urole']),
        ]);
    }

    /**
     * User dashboard: only data related to the logged-in buyer/seller/tenant/agent.
     */
    protected function userDashboard(Request $request)
    {
        $user = $request->user();

        $myTours = Tour::with('property:id,title,city,region,price,currency')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'property' => $t->property,
                'preferred_date' => $t->preferred_date?->format('Y-m-d'),
                'preferred_time' => $t->preferred_time,
                'status' => $t->status,
                'created_at' => $t->created_at?->diffForHumans(),
            ]);

        $favorites = $user->favorites()
            ->with('media:id,property_id,path,is_primary')
            ->active()
            ->latest('favorites.created_at')
            ->get();

        $savedSearches = $user->savedSearches()
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'label' => $s->label(),
                'url' => $s->url(),
            ]);

        return Inertia::render('UserDashboard', [
            'myTours' => $myTours,
            'favorites' => $favorites,
            'savedSearches' => $savedSearches,
        ]);
    }

    // ---------- Merged admin: property management ----------

    public function createProperty()
    {
        return Inertia::render('Admin/PropertyForm', [
            'property' => null,
            'agents' => \App\Models\User::whereIn('role', ['agent', 'admin'])->get(['id', 'name', 'company_name', 'email']),
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
        ]);
    }

    public function editProperty(Property $property)
    {
        $property->load('media');

        return Inertia::render('Admin/PropertyForm', [
            'property' => $property,
            'agents' => \App\Models\User::whereIn('role', ['agent', 'admin'])->get(['id', 'name', 'company_name', 'email']),
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
        ]);
    }

    public function storeProperty(Request $request)
    {
        $data = $this->validateProperty($request);
        $property = Property::create($data);
        $this->handleUploads($request, $property);

        return redirect(route('dashboard'))->with('success', 'Property created.');
    }

    public function updateProperty(Request $request, Property $property)
    {
        $data = $this->validateProperty($request);
        $property->update($data);
        $this->handleUploads($request, $property);

        return redirect(route('dashboard'))->with('success', 'Property updated.');
    }

    public function destroyProperty(Property $property)
    {
        $property->delete();

        return back()->with('success', 'Property deleted.');
    }

    /**
     * Quick status update from the dashboard table (e.g. mark as sold).
     */
    public function updatePropertyStatus(Request $request, Property $property)
    {
        $data = $request->validate([
            'status' => 'required|in:active,pending,sold,rented,off_market',
        ]);

        $property->status = $data['status'];

        // Track when it sold/rented (used by analytics and listings)
        if (in_array($data['status'], ['sold', 'rented'])) {
            $property->sold_at = $property->sold_at ?? now();
        } else {
            $property->sold_at = null;
        }

        $property->save();

        return back()->with('success', 'Property marked as '.str_replace('_', ' ', $data['status']).'.');
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

    // ---------- Tour management (merged admin) ----------

    public function updateTour(Request $request, Tour $tour)
    {
        $tour->update($request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]));

        // Transactional email: notify the requester about the status change
        if ($tour->email) {
            try {
                Mail::to($tour->email)->send(new TourStatusChanged($tour));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Tour updated.');
    }

    public function destroyTour(Tour $tour)
    {
        $tour->delete();

        return back()->with('success', 'Tour deleted.');
    }

    // ---------- User management (merged admin) ----------

    public function updateUser(Request $request, \App\Models\User $user)
    {
        $data = $request->validate([
            'role' => 'nullable|in:admin,agent,buyer,seller,tenant',
            'verify' => 'nullable|boolean',
        ]);

        if (array_key_exists('role', $data) && $data['role']) {
            // Don't let an admin demote themselves (keeps at least one admin)
            if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
                return back()->with('error', 'You cannot change your own admin role.');
            }
            $user->role = $data['role'];
        }

        if (!empty($data['verify'])) {
            $user->email_verified_at = $user->email_verified_at ?? now();
        }

        $user->save();

        return back()->with('success', 'User updated.');
    }

    public function destroyUser(Request $request, \App\Models\User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted.');
    }

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

    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'type' => 'nullable|in:follow_up,document,inspection,showing,general',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $task = $request->user()->tasks()->create($data);

        return back()->with('success', 'Task added.');
    }

    public function updateTask(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => 'nullable|in:pending,in_progress,done',
            'title' => 'nullable|string|max:255',
        ]);

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = now();
        } elseif (isset($data['status'])) {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return back()->with('success', 'Task updated.');
    }

    public function destroyTask(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        $task->delete();

        return back()->with('success', 'Task removed.');
    }

    public function export(Request $request)
    {
        $properties = Property::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->get();

        $filename = 'edenire-properties-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($properties) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['ID', 'Title', 'Type', 'Listing', 'City', 'Region', 'Price', 'Currency', 'Status', 'Bedrooms', 'Bathrooms', 'Area (m²)', 'Views', 'Enquiries', 'Listed']);
            foreach ($properties as $p) {
                fputcsv($fh, [
                    $p->id, $p->title, $p->property_type, $p->listing_type,
                    $p->city, $p->region, $p->price, $p->currency, $p->status,
                    $p->bedrooms, $p->bathrooms, $p->building_area,
                    $p->views_count, $p->enquiries_count,
                    $p->listed_at?->toDateString(),
                ]);
            }
            fclose($fh);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Site content manager — edit hero, stats, contact, footer and SEO text.
     */
    public function content()
    {
        $contents = \App\Models\SiteContent::orderBy('group')->orderBy('key')->get();

        return Inertia::render('Admin/Content', [
            'contents' => $contents,
            'groups' => \App\Models\SiteContent::query()
                ->select('group')
                ->distinct()
                ->orderBy('group')
                ->pluck('group'),
        ]);
    }

    public function updateContent(Request $request)
    {
        $data = $request->validate([
            'contents' => 'required|array',
            'contents.*.key' => 'required|string|max:191',
            'contents.*.value' => 'nullable|string|max:5000',
            'contents.*.group' => 'nullable|string|max:50',
        ]);

        foreach ($data['contents'] as $row) {
            \App\Models\SiteContent::updateOrCreate(
                ['key' => $row['key']],
                ['value' => $row['value'] ?? null, 'group' => $row['group'] ?? 'general']
            );
        }

        return back()->with('success', 'Site content saved.');
    }
}
