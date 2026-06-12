<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create([
            'name' => $request->name,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Departemen berhasil ditambahkan!');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update(['name' => $request->name]);

        return back()->with('success', 'Departemen berhasil diperbarui!');
    }

    public function toggle(Department $department)
    {
        // Cegah nonaktifkan kalau masih ada user aktif
        if ($department->is_active && $department->users()->where('is_active', true)->count() > 0) {
            return back()->with('error', 'Tidak dapat menonaktifkan departemen yang masih memiliki pengguna aktif!');
        }

        $department->update(['is_active' => !$department->is_active]);
        $status = $department->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Departemen {$department->name} berhasil {$status}!");
    }

    public function destroy(Department $department)
    {
        // Cegah hapus kalau masih ada user
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus departemen yang masih memiliki pengguna!');
        }

        $department->delete();
        return back()->with('success', 'Departemen berhasil dihapus!');
    }
}
