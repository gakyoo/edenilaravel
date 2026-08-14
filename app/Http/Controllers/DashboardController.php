<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Property;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

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
            'open_tasks' => Task::where('user_id', $user->id)->where('status', '!=', 'done')->count(),
        ];

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
            ->latest();

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
            ]);

        $viewsByStatus = Property::select('status', DB::raw('SUM(views_count) as views'))
            ->groupBy('status')->get();

        // ---------- Filters for the bar ----------
        $filterOptions = [
            'statuses' => ['active', 'pending', 'sold', 'rented', 'off_market'],
            'types' => ['residential', 'commercial', 'industrial', 'land', 'mixed_use'],
            'regions' => Property::distinct()->pluck('region')->filter()->values(),
        ];

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
        ]);
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
}
