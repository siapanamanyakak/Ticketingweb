<x-layout.app title="Technician Management" pageTitle="Technician Management">


    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">

        {{-- Bagian Kiri: Judul --}}
        <div>
            <h1 class="page-title">Technician Management</h1>
            <p class="page-subtitle">Manage technician data in the system</p>
        </div>

        {{-- Bagian Kanan: Tombol-tombol --}}
        <div style="display:flex; gap:8px;">

            <a href="{{ route('supervisor.technicians.import.template') }}" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Download Template
            </a>
            <button onclick="openImportTechModal()" class="btn btn-warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Excel
            </button>
            <button onclick="openAddTechModal()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Technician
            </button>
        </div>
    </div>

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
                    @forelse($technicians as $tech)
                        <tr>
                            <td>
                                <span style="font-weight:700; color:var(--navy-600);">
                                    {{ $tech->id_staff ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="reporter-avatar" style="background:#dbeafe; color:#1d4ed8;">
                                        {{ strtoupper(substr($tech->name, 0, 1)) }}
                                    </div>
                                    <span style="font-weight:600;">{{ $tech->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:12px; font-weight:600; color:var(--navy-600);
                                             background:var(--navy-50); padding:2px 8px; border-radius:6px;">
                                    {{ $tech->username ?? '—' }}
                                </span>
                            </td>
                            <td style="color:var(--gray-500);">{{ $tech->email ?? '—' }}</td>
                            <td>{{ $tech->department?->name ?? '—' }}</td>
                            <td>
                                @if($tech->is_active)
                                    <span class="badge badge-resolved">Active</span>
                                @else
                                    <span class="badge badge-closed">Inactive</span>
                                @endif
                            </td>
                            <td style="color:var(--gray-500); font-size:12px;">
                                {{ $tech->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button onclick="openEditTechModal(
                                                {{ $tech->id }},
                                                '{{ addslashes($tech->name) }}',
                                                '{{ $tech->username }}',
                                                '{{ $tech->id_staff }}',
                                                '{{ $tech->email }}',
                                                {{ $tech->department_id ?? 'null' }}
                                            )"
                                            class="btn btn-secondary btn-sm">Edit</button>

                                    <form method="POST"
                                          action="{{ route('supervisor.technicians.toggle', $tech) }}"
                                          id="toggleTech{{ $tech->id }}">
                                        @csrf @method('PATCH')
                                        <button type="button"
                                                class="btn btn-sm {{ $tech->is_active ? 'btn-warning' : 'btn-success' }}"
                                                onclick="showConfirmModal({
                                                    title: '{{ $tech->is_active ? 'Deactivate' : 'Activate' }} Technician',
                                                    desc: 'Are you sure you want to {{ $tech->is_active ? 'deactivate' : 'activate' }} the account for {{ addslashes($tech->name) }}?',
                                                    btnText: '{{ $tech->is_active ? 'Deactivate' : 'Activate' }}',
                                                    btnClass: '{{ $tech->is_active ? 'btn-warning' : 'btn-success' }}',
                                                    icon: '{{ $tech->is_active ? '⚠️' : '✅' }}',
                                                    type: '{{ $tech->is_active ? 'warning' : 'info' }}',
                                                    action: () => document.getElementById('toggleTech{{ $tech->id }}').submit()
                                                })">
                                            {{ $tech->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST"
                                    action="{{ route('supervisor.technicians.destroy', $tech) }}"
                                    id="deleteTech{{ $tech->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDelete('deleteTech{{ $tech->id }}', '{{ addslashes($tech->name) }}')">
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
                                    title="No technicians found"
                                    description="Add the first IT Support account."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($technicians->hasPages())
            <div style="padding:0 20px;">
                <x-ui.pagination :paginator="$technicians" />
            </div>
        @endif
    </div>

    {{-- Modal Tambah Teknisi --}}
    <div class="quick-modal-overlay" id="addTechOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">👨‍💻 Add Technician</span>
                <button class="quick-modal-close" onclick="closeAddTechModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('supervisor.technicians.store') }}"
                  id="addTechForm">
                @csrf
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label required">Name</label>
                            <input type="text" name="name" id="addTechName" class="form-control"
                                   placeholder="Full name" required
                                   oninput="previewUsername(this.value, 'addTechUsername')">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">NIK</label>
                            <input type="text" name="id_staff" class="form-control"
                                   placeholder="STF-010" required>
                            @error('id_staff') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Username</label>
                        <input type="text" name="username" id="addTechUsername" class="form-control"
                               placeholder="Automatically generated from name / fill manually" required>
                        <span class="form-hint">Username for login to the system.</span>
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
                        @php
                            // Mengambil data departemen IT dari database.
                            // NOTE: Sesuaikan string 'IT' dengan nama persis departemen IT di database kamu.
                            $itDept = \App\Models\Department::where('name', 'IT')->first();
                        @endphp
                        <select name="department_id" class="form-control" required disabled>
                            <option value="">Select Department</option>
                            @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}" {{ $dept->id == $itDept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="department_id" value="{{ $itDept->id ?? '' }}">
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

                    <div class="alert alert-info" style="margin-bottom:0;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>New account will have the role <strong>IT Support</strong>.</span>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddTechModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Teknisi --}}
    <div class="quick-modal-overlay" id="editTechOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">✏️ Edit Technician</span>
                <button class="quick-modal-close" onclick="closeEditTechModal()">✕</button>
            </div>
            <form method="POST" id="editTechForm">
                @csrf @method('PATCH')
                <input type="hidden" name="edit_tech_id" id="editTechIdHidden">
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" id="editTechName" class="form-control" required>
                            @error('name') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">NIK</label>
                            <input type="text" name="id_staff" id="editTechIdStaff" class="form-control" required>
                            @error('id_staff') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Username</label>
                        <input type="text" name="username" id="editTechUsername" class="form-control" required>
                        <span class="form-hint">Username for login to the system.</span>
                        @error('username') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Email
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                        </label>
                        <input type="email" name="email" id="editTechEmail" class="form-control" placeholder="email@ktushipyard.com">
                        @error('email') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Department</label>
                        @php
                            $itDept = \App\Models\Department::where('name', 'IT')->first();
                        @endphp
                        <select name="department_id" id="editTechDept" class="form-control" required disabled>
                            <option value="">Select Department</option>
                            @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}" {{ ($itDept && $dept->id == $itDept->id) ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="department_id" value="{{ $itDept->id ?? '' }}">
                        @error('department_id') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">
                                New Password
                                <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank if not changing">
                            @error('password') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter new password">
                        </div>
                    </div>

                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditTechModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
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

    {{-- Modal Import Teknisi --}}
    <div class="quick-modal-overlay" id="importTechOverlay">
        <div class="quick-modal" style="max-width:460px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">📥 Import Technicians from Excel</span>
                <button class="quick-modal-close" onclick="closeImportTechModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('supervisor.technicians.import') }}"
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
                    <button type="button" class="btn btn-secondary" onclick="closeImportTechModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // ── Add Tech Modal ────────────────────────
        function openAddTechModal()  {
            document.getElementById('addTechOverlay').classList.add('open');
        }
        function closeAddTechModal() {
            document.getElementById('addTechOverlay').classList.remove('open');
            document.getElementById('addTechForm').reset();
            document.getElementById('addTechUsername').dataset.manual = '';
        }

        // ── Edit Tech Modal ───────────────────────
        function openEditTechModal(id, name, username, idStaff, email, deptId) {
            document.getElementById('editTechIdHidden').value = id;
            document.getElementById('editTechName').value     = name;
            document.getElementById('editTechUsername').value = username || '';
            document.getElementById('editTechIdStaff').value  = idStaff || '';
            document.getElementById('editTechEmail').value    = email || '';
            document.getElementById('editTechDept').value     = deptId || '';
            document.getElementById('editTechForm').action    = `/supervisor/technicians/${id}`;
            document.getElementById('editTechOverlay').classList.add('open');
        }
        function closeEditTechModal() {
            document.getElementById('editTechOverlay').classList.remove('open');
        }

        // ── Username Preview ──────────────────────
        function previewUsername(name, fieldId) {
            const parts    = name.trim().toLowerCase().split(' ');
            const base     = parts[0].replace(/[^a-z0-9]/g, '');
            const field    = document.getElementById(fieldId);

            if (base && !field.dataset.manual) {
                const second = parts[1] ? parts[1].replace(/[^a-z0-9]/g, '') : '';
                field.value  = base + (second ? '_' + second : '');
            }
        }

        document.getElementById('addTechUsername')?.addEventListener('input', function() {
            this.dataset.manual = this.value ? 'true' : '';
        });

        // ── Close on overlay click ────────────────
        document.getElementById('addTechOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeAddTechModal();
        });
        document.getElementById('editTechOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeEditTechModal();
        });

        function openImportTechModal()  { document.getElementById('importTechOverlay').classList.add('open'); }
        function closeImportTechModal() { document.getElementById('importTechOverlay').classList.remove('open'); }
        document.getElementById('importTechOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeImportTechModal();
        });

        @if($errors->any())
            @if(old('_method') === 'PATCH')
                openEditTechModal(
                    '{{ old("edit_tech_id") }}',
                    '{{ addslashes(old("name")) }}',
                    '{{ old("username") }}',
                    '{{ old("id_staff") }}',
                    '{{ old("email") }}',
                    '{{ old("department_id") }}'
                );
            @else
                openAddTechModal();
            @endif
        @endif
    </script>
    @endpush

</x-layout.app>
