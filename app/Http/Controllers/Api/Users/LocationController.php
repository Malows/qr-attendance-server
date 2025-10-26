<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\Locations\IndexRequest;
use App\Http\Requests\Users\Locations\StoreRequest;
use App\Http\Requests\Users\Locations\UpdateRequest;
use App\Models\Location;

class LocationController extends Controller
{
    /**
     * Display a listing of the user's locations.
     */
    public function index(IndexRequest $request)
    {
        $locations = $request->user()
            ->locations()
            ->withCount('attendances')
            ->get();

        return response()->json($locations);
    }

    /**
     * Store a newly created location.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $location = $request->user()->locations()->create($validated);

        return response()->json($location, 201);
    }

    /**
     * Display the specified location.
     */
    public function show(Location $location)
    {
        $this->authorize('view', $location);

        $location->load('attendances');

        return response()->json($location);
    }

    /**
     * Update the specified location.
     */
    public function update(UpdateRequest $request, Location $location)
    {
        $validated = $request->validated();

        $location->update($validated);

        return response()->json($location);
    }

    /**
     * Remove the specified location.
     */
    public function destroy(Location $location)
    {
        $this->authorize('delete', $location);

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully'], 200);
    }
}
