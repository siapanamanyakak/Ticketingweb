<x-layout.app title="Manajemen Karyawan" pageTitle="Manajemen Karyawan">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Manajemen Karyawan</h1>
                <p class="page-subtitle">Kelola akun karyawan yang dapat mengajukan tiket</p>
            </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('support.employees.import.template') }}"
        class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Download Template
        </a>
        <button onclick="openImportModal()" class="btn btn-warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Import Excel
        </button>
        <a href="{{ route('support.employees.create') }}" class="btn btn-primary">
            + Tambah Karyawan
        </a>
    </div>
    
    </div>
    </div>

    {{-- alert --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="card" style="margin-bottom:16px; border-left:4px solid #d97706;">
        <div class="card-body">
            <p style="font-size:13px; font-weight:700; color:#b45309; margin-bottom:8px;">
                ⚠️ {{ count(session('import_errors')) }} baris dilewati:
            </p>
            @foreach(session('import_errors') as $error)
                <p style="font-size:12px; color:#b45309; margin-bottom:4px;">• {{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif



    {{-- Filter --}}
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET">
                <div class="filters-bar">
                    <div class="search-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" placeholder="Cari nama atau ID staff..."
                               value="{{ request('search') }}">
                    </div>
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('support.employees.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Staff</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Departemen</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <span style="font-weight:700; color:var(--navy-600);">
                                    {{ $employee->id_staff ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="reporter-avatar">
                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                    </div>
                                    <span style="font-weight:600;">{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td style="font-size:12px; color:var(--navy-600); font-weight:600;">
                                {{ $employee->username ?? '—' }}
                            </td>
                            <td style="color:var(--gray-500);">{{ $employee->email }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>
                                @if($employee->is_active)
                                    <span class="badge badge-resolved">Aktif</span>
                                @else
                                    <span class="badge badge-closed">Nonaktif</span>
                                @endif
                            </td>
                            <td style="color:var(--gray-500); font-size:12px;">
                                {{ $employee->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="{{ route('support.employees.edit', $employee) }}"
                                       class="btn btn-secondary btn-sm">Edit</a>

                                    <form method="POST"
                                          action="{{ route('support.employees.toggle', $employee) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $employee->is_active ? 'btn-warning' : 'btn-success' }}"
                                                onclick="return confirm('{{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                            {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state
                                    title="Belum ada karyawan"
                                    description="Tambahkan akun karyawan pertama."
                                    actionLabel="Tambah Karyawan"
                                    :actionRoute="route('support.employees.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div style="padding: 0 20px;">
                <x-ui.pagination :paginator="$employees" />
            </div>
        @endif
    </div>

    {{-- Modal Import --}}
    <div class="quick-modal-overlay" id="importModalOverlay">
        <div class="quick-modal" style="max-width:460px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">📥 Import Karyawan dari Excel</span>
                <button class="quick-modal-close" onclick="closeImportModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('support.employees.import') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="quick-modal-body">
                    <div class="alert alert-info" style="margin-bottom:16px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>
                            Download template terlebih dahulu, isi data, lalu upload.
                            Password default: <strong>password123</strong>
                        </span>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">File Excel</label>
                        <input type="file" name="file" class="form-control"
                            accept=".xlsx,.xls" required>
                        <span class="form-hint">Format: .xlsx atau .xls. Maksimal 2MB.</span>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openImportModal()  { document.getElementById('importModalOverlay').classList.add('open'); }
        function closeImportModal() { document.getElementById('importModalOverlay').classList.remove('open'); }
        document.getElementById('importModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeImportModal();
        });
    </script>
    @endpush

</x-layout.app>
