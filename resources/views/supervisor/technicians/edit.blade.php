<x-layout.app title="Edit Teknisi" pageTitle="Edit Teknisi">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('supervisor.technicians.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <div>
                <h1 class="page-title">Edit Teknisi</h1>
                <p class="page-subtitle">{{ $user->name }}</p>
            </div>
        </div>
    </div>

    <div style="max-width: 640px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Edit Data Teknisi</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('supervisor.technicians.update', $user) }}">
                    @csrf
                    @method('PATCH')

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" name="name"
                                   class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   value="{{ old('name', $user->name) }}">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label required">ID Staff</label>
                            <input type="text" name="id_staff"
                                   class="form-control {{ $errors->has('id_staff') ? 'is-invalid' : '' }}"
                                   value="{{ old('id_staff', $user->id_staff) }}">
                            @error('id_staff') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email"
                               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email', $user->email) }}">
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Departemen</label>
                        <select name="department_id" class="form-control">
                            <option value="">Pilih Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Password Baru <span style="color:var(--gray-400); font-weight:400;">(opsional)</span></label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Kosongkan jika tidak diubah">
                            @error('password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
                        <a href="{{ route('supervisor.technicians.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout.app>
