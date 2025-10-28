<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\Employees\IndexRequest;
use App\Http\Requests\Users\Employees\ResetPasswordRequest;
use App\Http\Requests\Users\Employees\StoreRequest;
use App\Http\Requests\Users\Employees\UpdateRequest;
use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the user's employees.
     */
    public function index(IndexRequest $request)
    {
        $user = $request->user();
        $query = Employee::query()->visible($user);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $employees = $query->get();

        return response()->json([
            'data' => $employees,
            'links' => [],
            'meta' => [],
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $employee = $request->user()->employees()->create($validated);

        return response()->json($employee, 201);
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);

        $employee->load('attendances');

        return response()->json(['data' => $employee]);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateRequest $request, Employee $employee)
    {
        $validated = $request->validated();

        $employee->update($validated);

        return response()->json($employee);
    }

    /**
     * Remove the specified employee (soft delete).
     */
    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }

    /**
     * Reset employee password - forces password change on next login
     */
    public function resetPassword(ResetPasswordRequest $request, Employee $employee)
    {
        $this->authorize('update', $employee);

        $employee->update([
            'password' => null,
            'force_password_change' => true,
        ]);

        return response()->json([
            'message' => 'Contraseña del empleado reseteada correctamente. El empleado deberá establecer una nueva contraseña en su próximo ingreso.',
            'data' => $employee,
        ]);
    }
}
