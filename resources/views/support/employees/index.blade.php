<x-layout.app title="Employees Management" pageTitle="Employees Management">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Employees Management</h1>
                <p class="page-subtitle">Manage employee accounts that can submit tickets</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('support.employees.import.template') }}" class="btn btn-secondary">
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
                <button onclick="openAddEmployeeModal()" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Employee
                </button>
            </div>
        </div>
    </div>

    {{-- Import errors --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="card" style="margin-bottom:16px; border-left:4px solid #d97706;">
            <div class="card-body">
                <p style="font-size:13px; font-weight:700; color:#b45309; margin-bottom:8px;">
                    ⚠️ {{ count(session('import_errors')) }} rows skipped due to errors:
                </p>
                @foreach(session('import_errors') as $error)
                    <p style="font-size:12px; color:#b45309; margin-bottom:4px;">• {{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET">
                <div class="filters-bar">
                    <div class="search-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" placeholder="Search by name or NIK..."
                               value="{{ request('search') }}">
                    </div>
                    <select name="department" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @if(request()->hasAny(['search', 'status', 'department']))
                        <a href="{{ route('support.employees.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
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
                        <th>NIK</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
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
                                    <span style="font-weight:600;">{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:12px; font-weight:600; color:var(--navy-600);
                                             background:var(--navy-50); padding:2px 8px; border-radius:6px;">
                                    {{ $employee->username ?? '—' }}
                                </span>
                            </td>
                            <td style="color:var(--gray-500);">{{ $employee->email ?? '—' }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>
                                @if($employee->is_active)
                                    <span class="badge badge-resolved">Active</span>
                                @else
                                    <span class="badge badge-closed">Inactive</span>
                                @endif
                            </td>
                            <td style="color:var(--gray-500); font-size:12px;">
                                {{ $employee->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button onclick="openEditEmployeeModal(
                                                {{ $employee->id }},
                                                '{{ addslashes($employee->name) }}',
                                                '{{ $employee->username }}',
                                                '{{ $employee->id_staff }}',
                                                '{{ $employee->email }}',
                                                {{ $employee->department_id ?? 'null' }}
                                            )"
                                            class="btn btn-secondary btn-sm">Edit</button>

                                    <form method="POST"
                                          action="{{ route('support.employees.toggle', $employee) }}"
                                          id="toggleEmp{{ $employee->id }}">
                                        @csrf @method('PATCH')
                                        <button type="button"
                                                class="btn btn-sm {{ $employee->is_active ? 'btn-warning' : 'btn-success' }}"
                                                onclick="showConfirmModal({
                                                    title: '{{ $employee->is_active ? 'Deactivate' : 'Activate' }} Employee',
                                                    desc: 'Are you sure you want to {{ $employee->is_active ? 'deactivate' : 'activate' }} the account of {{ addslashes($employee->name) }}?',
                                                    btnText: '{{ $employee->is_active ? 'Deactivate' : 'Activate' }}',
                                                    btnClass: '{{ $employee->is_active ? 'btn-warning' : 'btn-success' }}',
                                                    icon: '{{ $employee->is_active ? '⚠️' : '✅' }}',
                                                    type: '{{ $employee->is_active ? 'warning' : 'info' }}',
                                                    action: () => document.getElementById('toggleEmp{{ $employee->id }}').submit()
                                                })">
                                            {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST"
                                    action="{{ route('support.employees.destroy', $employee) }}"
                                    id="deleteEmp{{ $employee->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDelete('deleteEmp{{ $employee->id }}', '{{ addslashes($employee->name) }}')">
                                        Hapus
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-ui.empty-state
                                    title="There are no Employee"
                                    description="Add the first employee."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div style="padding:0 20px;">
                <x-ui.pagination :paginator="$employees" />
            </div>
        @endif
    </div>

    {{-- Modal Tambah Karyawan --}}
    <div class="quick-modal-overlay" id="addEmployeeOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">👤 Add Employee</span>
                <button class="quick-modal-close" onclick="closeAddEmployeeModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('support.employees.store') }}"
                  id="addEmployeeForm">
                @csrf
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Full name" required
                                   oninput="previewEmpUsername(this.value)">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">NIK</label>
                            <input type="text" name="id_staff" class="form-control"
                                   placeholder="STF-100" required>
                            @error('id_staff') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Username</label>
                        <input type="text" name="username" id="addEmpUsername" class="form-control"
                               placeholder="Automatically generated from name / fill manually" required>
                        <span class="form-hint">Username for logging into the system.</span>
                        @error('username') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Email
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                        </label>
                        <input type="email" name="email" class="form-control"
                               placeholder="email@ktushipyard.com">
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label required">Password</label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Min. 8 karakter" required>
                            @error('password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" placeholder="Re-enter password" required>
                        </div>
                    </div>

                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddEmployeeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Karyawan --}}
    <div class="quick-modal-overlay" id="editEmployeeOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">✏️ Edit Employee</span>
                <button class="quick-modal-close" onclick="closeEditEmployeeModal()">✕</button>
            </div>
            <form method="POST" id="editEmployeeForm">
                @csrf @method('PATCH')
                <input type="hidden" name="edit_user_id" id="editUserIdHidden">
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" id="editEmpName"
                                   class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">NIK</label>
                            <input type="text" name="id_staff" id="editEmpIdStaff"
                                   class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Username</label>
                        <input type="text" name="username" id="editEmpUsername"
                               class="form-control" required>
                        <span class="form-hint">Username for logging into the system.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Email
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                        </label>
                        <input type="email" name="email" id="editEmpEmail"
                               class="form-control" placeholder="email@ktushipyard.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Department</label>
                        <select name="department_id" id="editEmpDept" class="form-control" required>
                            <option value="">Select Department</option>
                            @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">
                                New Password
                                <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                            </label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Leave blank if not changing">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" placeholder="Re-enter new password">
                        </div>
                    </div>

                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditEmployeeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Import --}}
    <div class="quick-modal-overlay" id="importModalOverlay">
        <div class="quick-modal" style="max-width:460px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">📥 Import Employees from Excel</span>
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
                        <span>Download the template first, fill in the data, then upload.
                            Default password: <strong>password123</strong></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Excel File</label>
                        <input type="file" name="file" class="form-control"
                               accept=".xlsx,.xls" required>
                        <span class="form-hint">Format: .xlsx or .xls. Maximum 2MB.</span>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // ── Add Employee ──────────────────────────
        function openAddEmployeeModal()  { document.getElementById('addEmployeeOverlay').classList.add('open'); }
        function closeAddEmployeeModal() {
            document.getElementById('addEmployeeOverlay').classList.remove('open');
            document.getElementById('addEmployeeForm').reset();
            document.getElementById('addEmpUsername').dataset.manual = '';
        }

        // ── Edit Employee ─────────────────────────
        function openEditEmployeeModal(id, name, username, idStaff, email, deptId) {
            document.getElementById('editUserIdHidden').value = id;
            document.getElementById('editEmpName').value     = name;
            document.getElementById('editEmpUsername').value = username || '';
            document.getElementById('editEmpIdStaff').value  = idStaff || '';
            document.getElementById('editEmpEmail').value    = email || '';
            document.getElementById('editEmpDept').value     = deptId || '';
            document.getElementById('editEmployeeForm').action = `/support/employees/${id}`;
            document.getElementById('editEmployeeOverlay').classList.add('open');
        }
        function closeEditEmployeeModal() {
            document.getElementById('editEmployeeOverlay').classList.remove('open');
        }

        // ── Import ────────────────────────────────
        function openImportModal()  { document.getElementById('importModalOverlay').classList.add('open'); }
        function closeImportModal() { document.getElementById('importModalOverlay').classList.remove('open'); }

        // ── Username Preview ──────────────────────
        function previewEmpUsername(name) {
            const parts = name.trim().toLowerCase().split(' ');
            const base  = parts[0].replace(/[^a-z0-9]/g, '');
            const field = document.getElementById('addEmpUsername');
            if (base && !field.dataset.manual) {
                const second = parts[1] ? parts[1].replace(/[^a-z0-9]/g, '') : '';
                field.value  = base + (second ? '_' + second : '');
            }
        }

        document.getElementById('addEmpUsername')?.addEventListener('input', function() {
            this.dataset.manual = this.value ? 'true' : '';
        });

        // ── Close on overlay click ────────────────
        ['addEmployeeOverlay', 'editEmployeeOverlay', 'importModalOverlay'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('open');
            });
        });

        @if($errors->any())
    // Cek apakah error ini berasal dari proses UPDATE (PATCH) atau CREATE (POST)
            @if(old('_method') === 'PATCH')
                // Buka kembali modal Edit dengan data yang barusan diketik
                openEditEmployeeModal(
                    '{{ old("edit_user_id") }}',
                    '{{ addslashes(old("name")) }}',
                    '{{ old("username") }}',
                    '{{ old("id_staff") }}',
                    '{{ old("email") }}',
                    '{{ old("department_id") }}'
                );
            @else
                // Kalau error dari fungsi Store biasa, baru buka modal Add
                openAddEmployeeModal();
            @endif
        @endif
    </script>
    @endpush

</x-layout.app>
