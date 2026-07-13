<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('department')->where('role', 'user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('id_staff', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $employees = $query->latest()->paginate(10)->appends(request()->query());

        return view('support.employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('support.employees.create', compact('departments'));
    }

    public function store(Request $request, UsernameService $usernameService)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('users', 'username')->whereNull('deleted_at'),
            ],
            'email'         => [
                'nullable', 'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password'      => 'required|min:8|confirmed',
            'id_staff'      => [
                'required', 'string',
                Rule::unique('users', 'id_staff')->whereNull('deleted_at'),
            ],
            'department_id' => [
                                'required',
                                Rule::exists('departments', 'id')->whereNull('deleted_at'),
                                ],
        ]);

        // Cek tong sampah by id_staff
        $existing = User::withTrashed()
            ->where('id_staff', $request->id_staff)
            ->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'name'          => $request->name,
                'username'      => strtolower($request->username),
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'department_id' => $request->department_id,
                'is_active'     => true,
                'role'          => 'user',
            ]);
            return back()->with('success', 'Employee account restored successfully!');
        }

        User::create([
            'name'          => $request->name,
            'username'      => strtolower($request->username),
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'id_staff'      => $request->id_staff,
            'department_id' => $request->department_id,
            'role'          => 'user',
            'is_active'     => true,
        ]);

        return back()->with('success', 'Employee account created successfully!');
    }

    public function edit(User $employee)
    {
        $departments = Department::all();
        return view('support.employees.edit', compact('employee', 'departments'));
    }

    // Perbaikan utama ada di sini
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => [
                                    'required', 'string', 'alpha_dash', 'max:50',
                                    Rule::unique('users', 'username')->ignore($employee->id)->whereNull('deleted_at'),
                                ],
            'email'         => [
                                    'nullable', 'email',
                                    Rule::unique('users', 'email')->ignore($employee->id)->whereNull('deleted_at'),
                                ],
            'password'      => 'nullable|min:8|confirmed',
            'id_staff'      => [
                'required', 'string',
                Rule::unique('users', 'id_staff')->whereNull('deleted_at')->ignore($employee->id)->whereNull('deleted_at'),
            ],
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->whereNull('deleted_at'),
                                ],
        ]);

        // 3. Update data
        $updateData = [
            'name'          => $request->name,
            'username'      => strtolower($request->username),
            'email'         => $request->email,
            'id_staff'      => $request->id_staff,
            'department_id' => $request->department_id,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $employee->update($updateData);

        return back()->with('success', 'Employee data updated successfully!');
    }

    public function toggle(User $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        $status = $employee->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Account {$employee->name} has been {$status}!");
    }

    public function destroy(User $employee)
    {
        if ($employee->tickets()->whereNotIn('status', ['closed'])->count() > 0) {
            return back()->with('error', 'Cannot delete employee account that still has active tickets!');
        }

        $employee->delete(); // ← otomatis soft delete
        return back()->with('success', 'Employee account deleted successfully!');
    }
}
