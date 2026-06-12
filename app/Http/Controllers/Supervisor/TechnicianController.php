<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = User::with('department')
            ->where('role', 'it_support')
            ->latest()
            ->paginate(10);

        return view('supervisor.technicians.index', compact('technicians'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('supervisor.technicians.create', compact('departments'));
    }

    public function store(Request $request, UsernameService $usernameService)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
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
            'role'          => 'it_support',
            'is_active'     => true,
        ]);

        return redirect()->route('supervisor.technicians.index')
            ->with('success', 'Akun teknisi berhasil dibuat!');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        return view('supervisor.technicians.edit', compact('user', 'departments'));
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

        return redirect()->route('supervisor.technicians.index')
            ->with('success', 'Data teknisi berhasil diperbarui!');
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}!");
    }
}
