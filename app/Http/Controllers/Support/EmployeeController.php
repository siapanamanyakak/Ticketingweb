<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'email'         => 'nullable|email|unique:users,email',
            'password'      => 'required|min:8|confirmed',
            'id_staff'      => 'required|string|unique:users,id_staff',
            'department_id' => 'required|exists:departments,id',
        ]);

        User::create([
            'name'          => $request->name,
            'username'      => $usernameService->generate($request->name),
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'id_staff'      => $request->id_staff,
            'department_id' => $request->department_id,
            'role'          => 'user',
            'is_active'     => true,
        ]);

        return redirect()->route('support.employees.index')
            ->with('success', 'Akun karyawan berhasil dibuat!');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        return view('support.employees.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user, UsernameService $usernameService)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'id_staff'      => 'required|string|unique:users,id_staff,' . $user->id,
            'department_id' => 'required|exists:departments,id',
            'password'      => 'nullable|min:8|confirmed',
        ]);

        $updateData = [
            'name'          => $request->name,
            'email'         => $request->email,
            'id_staff'      => $request->id_staff,
            'department_id' => $request->department_id,
            'username'      => $usernameService->generate($request->name),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('support.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}!");
    }
}
