<x-layout.app title="Tambah Karyawan" pageTitle="Tambah Karyawan">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('support.employees.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <div>
                <h1 class="page-title">Tambah Karyawan</h1>
                <p class="page-subtitle">Buat akun baru untuk karyawan</p>
            </div>
        </div>
    </div>

    <div style="max-width: 640px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Form Karyawan Baru</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('support.employees.store') }}">
                    @csrf

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   value="{{ old('name') }}" placeholder="Nama lengkap">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label required">ID Staff</label>
                            <input type="text" name="id_staff" class="form-control {{ $errors->has('id_staff') ? 'is-invalid' : '' }}"
                                   value="{{ old('id_staff') }}" placeholder="STF-001">
                            @error('id_staff') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email') }}" placeholder="email@perusahaan.com">
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Departemen</label>
                        <select name="department_id" class="form-control {{ $errors->has('department_id') ? 'is-invalid' : '' }}">
                            <option value="">Pilih Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label required">Password</label>
                            <input type="password" name="password"
                                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   placeholder="Min. 8 karakter">
                            @error('password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" placeholder="Ulangi password">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
                        <a href="{{ route('support.employees.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout.app>
