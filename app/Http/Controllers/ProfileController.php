<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user        = auth()->user();
        $isSupervisor = $user->role === 'it_supervisor';

        $rules = [
            'username' => 'required|string|alpha_dash|max:50|unique:users,username,' . $user->id,
            'email'    => 'nullable|email|unique:users,email,' . $user->id,
        ];

        // Supervisor bisa ubah semua
        if ($isSupervisor) {
            $rules['name']          = 'required|string|max:255';
            $rules['id_staff']      = 'nullable|string|unique:users,id_staff,' . $user->id;
            $rules['department_id'] = 'nullable|exists:departments,id';
        }

        $request->validate($rules);

        $updateData = [
            'username' => strtolower($request->username),
            'email'    => $request->email,
        ];

        if ($isSupervisor) {
            $updateData['name']          = $request->name;
            $updateData['id_staff']      = $request->id_staff;
            $updateData['department_id'] = $request->department_id;
        }

        $user->update($updateData);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'      => 'required|current_password',
            'password'              => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}
