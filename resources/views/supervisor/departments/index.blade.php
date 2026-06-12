<x-layout.app title="Manajemen Departemen" pageTitle="Manajemen Departemen">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Manajemen Departemen</h1>
                <p class="page-subtitle">Kelola departemen yang tersedia dalam sistem</p>
            </div>
            <button onclick="openAddModal()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Departemen
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Departemen</th>
                        <th>Jumlah Pengguna</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td style="font-weight:600;">{{ $dept->name }}</td>
                            <td>{{ $dept->users_count }} pengguna</td>
                            <td>
                                @if($dept->is_active)
                                    <span class="badge badge-resolved">Aktif</span>
                                @else
                                    <span class="badge badge-closed">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    {{-- Edit --}}
                                    <button onclick="openEditModal({{ $dept->id }}, '{{ $dept->name }}')"
                                            class="btn btn-secondary btn-sm">Edit</button>

                                    {{-- Toggle --}}
                                    <form method="POST" action="{{ route('supervisor.departments.toggle', $dept) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $dept->is_active ? 'btn-warning' : 'btn-success' }}"
                                                onclick="return confirm('{{ $dept->is_active ? 'Nonaktifkan' : 'Aktifkan' }} departemen ini?')">
                                            {{ $dept->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    @if($dept->users_count === 0)
                                        <form method="POST" action="{{ route('supervisor.departments.destroy', $dept) }}"
                                            id="deleteDept{{ $dept->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete('deleteDept{{ $dept->id }}', '{{ $dept->name }}')">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state title="Belum ada departemen" description="Tambahkan departemen pertama." />
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
                <span class="quick-modal-title">Tambah Departemen</span>
                <button class="quick-modal-close" onclick="closeAddModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('supervisor.departments.store') }}">
                @csrf
                <div class="quick-modal-body">
                    <div class="form-group">
                        <label class="form-label required">Nama Departemen</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Contoh: Finance" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="quick-modal-overlay" id="editModalOverlay">
        <div class="quick-modal" style="max-width:420px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">Edit Departemen</span>
                <button class="quick-modal-close" onclick="closeEditModal()">✕</button>
            </div>
            <form method="POST" id="editDeptForm">
                @csrf
                @method('PATCH')
                <div class="quick-modal-body">
                    <div class="form-group">
                        <label class="form-label required">Nama Departemen</label>
                        <input type="text" name="name" id="editDeptName"
                               class="form-control" required>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
