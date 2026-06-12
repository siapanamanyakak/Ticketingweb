<x-layout.app title="Profil Saya" pageTitle="Profil Saya">

    <div class="page-header">
        <h1 class="page-title">Profil Saya</h1>
        <p class="page-subtitle">Kelola informasi akun dan keamanan kamu</p>
    </div>

    <div style="max-width:640px; display:flex; flex-direction:column; gap:20px;">

        {{-- Info Akun --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Informasi Akun</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    {{-- Username (read only) --}}
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control"
                               value="{{ auth()->user()->username }}" disabled
                               style="background:var(--gray-50); color:var(--gray-500);">
                        <span class="form-hint">Username tidak dapat diubah sendiri.</span>
                    </div>

                    {{-- Nama --}}
                    <div class="form-group">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email (opsional) --}}
                    <div class="form-group">
                        <label class="form-label">
                            Email
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(opsional)</span>
                        </label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', auth()->user()->email) }}"
                               placeholder="email@ktushipyard.com">
                        <span class="form-hint">Email digunakan sebagai kontak alternatif.</span>
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- ID Staff & Department (read only) --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">ID Staff</label>
                            <input type="text" class="form-control"
                                   value="{{ auth()->user()->id_staff ?? '—' }}" disabled
                                   style="background:var(--gray-50); color:var(--gray-500);">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Departemen</label>
                            <input type="text" class="form-control"
                                   value="{{ auth()->user()->department?->name ?? '—' }}" disabled
                                   style="background:var(--gray-50); color:var(--gray-500);">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Ubah Password</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label required">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control"
                               placeholder="Masukkan password saat ini">
                        @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Password Baru</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Min. 8 karakter">
                        @error('password') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Ulangi password baru">
                    </div>

                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</x-layout.app>
