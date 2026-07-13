<x-layout.app title="My Profile" pageTitle="My Profile">

    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information and security settings</p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 450px), 1fr)); gap:24px; width:100%;">

        {{-- Info Akun --}}
        <div class="card" style="height:fit-content;">
            <div class="card-header">
                <span class="card-title">Account Information</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    @php $isSupervisor = auth()->user()->role === 'it_supervisor'; @endphp

                    {{-- Username --}}
                    <div class="form-group">
                        <label class="form-label {{ !$isSupervisor ? '' : 'required' }}">Username</label>
                        @if($isSupervisor)
                            <input type="text" name="username" class="form-control"
                                   value="{{ old('username', auth()->user()->username) }}" required>
                            <span class="form-hint">Supervisor can change the username.</span>
                        @else
                            <input type="text" name="username" class="form-control"
                                   value="{{ old('username', auth()->user()->username) }}" required>
                            <span class="form-hint">Username can be changed by the user.</span>
                        @endif
                        @error('username') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nama (read only untuk semua kecuali supervisor) --}}
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        @if($isSupervisor)
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', auth()->user()->name) }}" required>
                        @else
                            <input type="text" class="form-control"
                                   value="{{ auth()->user()->name }}" disabled
                                   style="background:var(--gray-50); color:var(--gray-500);">
                            <span class="form-hint">Full name can't be changed.</span>
                        @endif
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label">
                            Email
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                        </label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', auth()->user()->email) }}"
                               placeholder="email@ktushipyard.com">
                        <span class="form-hint">Email is used as an alternative contact method.</span>
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- NIK & Department (read only semua kecuali supervisor) --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">NIK</label>
                            @if($isSupervisor)
                                <input type="text" name="id_staff" class="form-control"
                                       value="{{ old('id_staff', auth()->user()->id_staff) }}">
                            @else
                                <input type="text" class="form-control"
                                       value="{{ auth()->user()->id_staff ?? '—' }}" disabled
                                       style="background:var(--gray-50); color:var(--gray-500);">
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            @if($isSupervisor)
                                <select name="department_id" class="form-control">
                                    <option value="">Select Department</option>
                                    @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ auth()->user()->department_id == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control"
                                       value="{{ auth()->user()->department?->name ?? '—' }}" disabled
                                       style="background:var(--gray-50); color:var(--gray-500);">
                            @endif
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="card" style="height:fit-content;">
            <div class="card-header">
                <span class="card-title">Update Password</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label required">Current Password</label>
                        <input type="password" name="current_password" class="form-control"
                               placeholder="Enter current password">
                        @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">New Password</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Min. 8 characters">
                        @error('password') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Re-enter new password">
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</x-layout.app>
