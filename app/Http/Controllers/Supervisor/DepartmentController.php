<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->latest()->paginate(15);
        return view('supervisor.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments', 'name')->whereNull('deleted_at'),
            ],
        ]);

        // Cek apakah ada di tong sampah
        $existing = Department::withTrashed()
            ->where('name', $request->name)
            ->first();

        if ($existing && $existing->trashed()) {
            // Restore data lama
            $existing->restore();
            $existing->update(['is_active' => true]);
            return back()->with('success', 'Department restored successfully!');
        }

        // Buat baru
        Department::create([
            'name' => $request->name,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Department added successfully!');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments', 'name')
                    ->ignore($department->id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $department->update(['name' => $request->name]);

        return back()->with('success', 'Department updated successfully!');
    }

    public function toggle(Department $department)
    {
        
        if ($department->is_active && $department->users()->where('is_active', true)->count() > 0) {
            return back()->with('error', 'Cannot deactivate department that still has active users!');
        }

        $department->update(['is_active' => !$department->is_active]);
        $status = $department->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Department {$department->name} successfully {$status}!");
    }

    public function destroy(Department $department)
    {
        // Cegah hapus kalau masih ada user
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Cannot delete department that still has users!');
        }

        $department->delete();
        return back()->with('success', 'Department successfully deleted!');
    }
}
