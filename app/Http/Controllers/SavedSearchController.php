<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'filters' => 'nullable|array',
        ]);

        SavedSearch::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'] ?? null,
            'filters' => $data['filters'] ?? [],
        ]);

        return back()->with('success', 'Search saved.');
    }

    public function destroy(Request $request, SavedSearch $savedSearch)
    {
        abort_unless($savedSearch->user_id === $request->user()->id, 403);

        $savedSearch->delete();

        return back()->with('success', 'Saved search removed.');
    }
}
