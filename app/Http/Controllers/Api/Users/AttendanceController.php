<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\Attendances\IndexRequest;
use App\Http\Requests\Users\Attendances\StoreRequest;
use App\Http\Requests\Users\Attendances\UpdateRequest;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendances.
     */
    public function index(IndexRequest $request)
    {
        $query = Attendance::query()
            ->whereHas('employee', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        // Filter by location
        if ($request->has('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('check_in', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('check_in', '<=', $request->input('end_date'));
        }

        $attendances = $query->with(['employee', 'location'])
            ->orderBy('check_in', 'desc')
            ->get();

        return response()->json($attendances);
    }

    /**
     * Store a newly created attendance.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $attendance = Attendance::create($validated);

        return response()->json($attendance->load(['employee', 'location']), 201);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);

        return response()->json($attendance->load(['employee', 'location']));
    }

    /**
     * Update the specified attendance.
     */
    public function update(UpdateRequest $request, Attendance $attendance)
    {
        $validated = $request->validated();

        $attendance->update($validated);

        return response()->json($attendance->load(['employee', 'location']));
    }

    /**
     * Remove the specified attendance.
     */
    public function destroy(Attendance $attendance)
    {
        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response()->json(['message' => 'Attendance deleted successfully'], 200);
    }
}
