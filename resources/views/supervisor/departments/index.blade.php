<x-layout.app title="Department Management" pageTitle="Department Management">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Department Management</h1>
                <p class="page-subtitle">Manage departments available in the system</p>
            </div>
            <button onclick="openAddModal()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Department
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>User Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td style="font-weight:600;">{{ $dept->name }}</td>
                            <td>{{ $dept->users_count }} users</td>
                            <td>
                                @if($dept->is_active)
                                    <span class="badge badge-resolved">Active</span>
                                @else
                                    <span class="badge badge-closed">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    {{-- Edit --}}
                                    <button onclick="openEditModal({{ $dept->id }}, '{{ $dept->name }}')"
                                            class="btn btn-secondary btn-sm">Edit</button>

                                    {{-- Toggle --}}
                                    <form method="POST" action="{{ route('supervisor.departments.toggle', $dept) }}"
                                        id="toggleDept{{ $dept->id }}">
                                        @csrf @method('PATCH')
                                        <button type="button"
                                                class="btn btn-sm {{ $dept->is_active ? 'btn-warning' : 'btn-success' }}"
                                                onclick="showConfirmModal({
                                                    title: '{{ $dept->is_active ? 'Deactivate' : 'Activate' }} Department',
                                                    desc: 'Are you sure you want to {{ $dept->is_active ? 'deactivate' : 'activate' }} {{ addslashes($dept->name) }}?',
                                                    btnText: '{{ $dept->is_active ? 'Deactivate' : 'Activate' }}',
                                                    btnClass: '{{ $dept->is_active ? 'btn-warning' : 'btn-success' }}',
                                                    icon: '{{ $dept->is_active ? '⚠️' : '✅' }}',
                                                    type: '{{ $dept->is_active ? 'warning' : 'info' }}',
                                                    action: () => document.getElementById('toggleDept{{ $dept->id }}').submit()
                                                })">
                                            {{ $dept->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form method="POST"
                                        action="{{ route('supervisor.departments.destroy', $dept) }}"
                                        id="deleteDept{{ $dept->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDelete('deleteDept{{ $dept->id }}', '{{ addslashes($dept->name) }}')">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state title="No departments available" description="Add your first department." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
            <div style="padding:0 20px;">
                <x-ui.pagination :paginator="$departments" />
            </div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div class="quick-modal-overlay" id="addModalOverlay">
        <div class="quick-modal" style="max-width:420px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">Add Department</span>
                <button class="quick-modal-close" onclick="closeAddModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('supervisor.departments.store') }}">
                @csrf
                <div class="quick-modal-body">
                    <div class="form-group">
                        <label class="form-label required">Department Name</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Example: HR" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="quick-modal-overlay" id="editModalOverlay">
        <div class="quick-modal" style="max-width:420px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">Edit Department</span>
                <button class="quick-modal-close" onclick="closeEditModal()">✕</button>
            </div>
            <form method="POST" id="editDeptForm">
                @csrf
                @method('PATCH')
                <div class="quick-modal-body">
                    <div class="form-group">
                        <label class="form-label required">Department Name</label>
                        <input type="text" name="name" id="editDeptName"
                               class="form-control" required>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openAddModal() {
            document.getElementById('addModalOverlay').classList.add('open');
        }
        function closeAddModal() {
            document.getElementById('addModalOverlay').classList.remove('open');
        }
        function openEditModal(id, name) {
            document.getElementById('editDeptName').value = name;
            document.getElementById('editDeptForm').action = `/supervisor/departments/${id}`;
            document.getElementById('editModalOverlay').classList.add('open');
        }
        function closeEditModal() {
            document.getElementById('editModalOverlay').classList.remove('open');
        }

        document.getElementById('addModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeAddModal();
        });
        document.getElementById('editModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        @if($errors->any())
            openAddModal();
        @endif
    </script>
    @endpush

</x-layout.app>
