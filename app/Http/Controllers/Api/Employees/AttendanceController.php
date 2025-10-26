<?php

namespace App\Http\Controllers\Api\Employees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\CheckInRequest;
use App\Http\Requests\Employees\CheckOutRequest;
use App\Http\Requests\Employees\IndexEmployeeAttendanceRequest;
use App\Models\Attendance;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Get employee's own attendances
     */
    public function index(IndexEmployeeAttendanceRequest $request)
    {
        // Get authenticated employee from employee guard
        $employee = $request->user();

        $query = Attendance::where('employee_id', $employee->id);

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('check_in', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('check_in', '<=', $request->input('end_date'));
        }

        // Filter by location
        if ($request->has('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        $attendances = $query->with('location')
            ->orderBy('check_in', 'desc')
            ->get();

        return response()->json($attendances);
    }

    /**
     * Check-in: Register employee arrival
     */
    public function checkIn(CheckInRequest $request)
    {
        // Get authenticated employee from employee guard
        $employee = $request->user();

        $validated = $request->validated();

        // Check if employee already has an open attendance (no check_out)
        $openAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->first();

        if ($openAttendance) {
            return response()->json([
                'error' => __('messages.attendance.already_checked_in'),
                'attendance' => $openAttendance,
            ], 422);
        }

        // Create new attendance
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'location_id' => $validated['location_id'],
            'check_in' => Carbon::now(),
            'check_in_latitude' => $validated['latitude'],
            'check_in_longitude' => $validated['longitude'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($attendance->load('location'), 201);
    }

    /**
     * Check-out: Register employee departure
     */
    public function checkOut(CheckOutRequest $request)
    {
        // Get authenticated employee from employee guard
        $employee = $request->user();

        $validated = $request->validated();

        // Find the open attendance
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (! $attendance) {
            return response()->json([
                'error' => __('messages.attendance.no_open_attendance'),
            ], 422);
        }

        // Update with check-out time
        $attendance->update([
            'check_out' => Carbon::now(),
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'notes' => $validated['notes'] ?? $attendance->notes,
        ]);

        return response()->json($attendance->load('location'));
    }

    /**
     * Get current open attendance if exists
     */
    public function current(Request $request)
    {
        // Get authenticated employee from employee guard
        $employee = $request->user();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->with('location')
            ->first();

        if (! $attendance) {
            return response()->json([
                'attendance' => null,
            ]);
        }

        return response()->json($attendance);
    }

    /**
     * Get available locations for check-in
     */
    public function locations(Request $request)
    {
        // Get authenticated employee from employee guard
        $employee = $request->user();

        // Get all active locations belonging to the employee's user
        $locations = Location::where('user_id', $employee->user_id)
            ->where('is_active', true)
            ->get();

        return response()->json($locations);
    }
}
