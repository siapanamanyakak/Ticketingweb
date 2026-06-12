<x-layout.app title="Manajemen Kategori" pageTitle="Manajemen Kategori">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Manajemen Kategori</h1>
                <p class="page-subtitle">Kelola kategori tiket dan keyword auto-detect</p>
            </div>
            <button onclick="openAddCategoryModal()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kategori
            </button>
        </div>
    </div>

    {{-- Category Cards --}}
    @forelse($categories as $category)
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="card-title">{{ $category->name }}</span>
                    @if($category->is_active)
                        <span class="badge badge-resolved">Aktif</span>
                    @else
                        <span class="badge badge-closed">Nonaktif</span>
                    @endif
                </div>
                <div style="display:flex; gap:6px;">
                    <button onclick="openEditCategoryModal(
                    {{ $category->id }},
                    '{{ $category->name }}',
                    '{{ $category->description }}',
                    '{{ $category->base_priority }}',
                    '{{ $category->max_priority }}'
                    )"
                    class="btn btn-secondary btn-sm">Edit</button>

                    <form method="POST" action="{{ route('supervisor.categories.toggle', $category) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="btn btn-sm {{ $category->is_active ? 'btn-warning' : 'btn-success' }}"
                                onclick="return confirm('{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} kategori ini?')">
                            {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>

                    @if($category->tickets()->count() === 0)
                        <form method="POST" action="{{ route('supervisor.categories.destroy', $category) }}">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmDelete('deleteCategory{{ $category->id }}', '{{ $category->name }}')">
                                Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($category->description)
                    <p style="font-size:13px; color:var(--gray-500); margin-bottom:14px;">
                        {{ $category->description }}
                    </p>
                @endif

                {{-- Keywords --}}
                <div style="margin-bottom:10px;">
                    <span style="font-size:11px; font-weight:700; color:var(--gray-400);
                                 text-transform:uppercase; letter-spacing:0.5px;">
                        Keywords Auto-Detect
                    </span>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px;">
                    @forelse($category->keywords as $kw)
                        <span style="display:inline-flex; align-items:center; gap:4px;
                                    background:var(--navy-50); color:var(--navy-600);
                                    padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                            {{ $kw->keyword }}
                            <span style="background:var(--navy-200); color:var(--navy-700);
                                        padding:0 5px; border-radius:10px; font-size:10px; font-weight:700;">
                                {{ $kw->weight }}
                            </span>
                            <form method="POST"
                                action="{{ route('supervisor.categories.keywords.destroy', $kw) }}"
                                style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="background:none; border:none; cursor:pointer;
                                            color:var(--navy-400); font-size:13px; line-height:1;
                                            padding:0 0 0 2px;"
                                        onclick="return confirm('Hapus keyword ini?')">×</button>
                            </form>
                        </span>
                    @empty
                        <span style="font-size:12px; color:var(--gray-400);">Belum ada keyword</span>
                    @endforelse
                </div>

                {{-- Tambah keyword --}}
                <form method="POST" action="{{ route('supervisor.categories.keywords.store', $category) }}"
                    style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; max-width:500px;">
                    @csrf
                    <div style="flex:1; min-width:160px;">
                        <label style="font-size:11px; font-weight:600; color:var(--gray-400);
                                    text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:4px;">
                            Keyword
                        </label>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tambah keyword baru..."
                            style="margin-bottom:0;" required>
                    </div>
                    <div style="width:100px;">
                        <label style="font-size:11px; font-weight:600; color:var(--gray-400);
                                    text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:4px;">
                            Bobot
                        </label>
                        <select name="weight" class="form-control" style="margin-bottom:0;" required>
                            <option value="">Pilih</option>
                            <option value="1">1 — Umum</option>
                            <option value="3">3 — Standar</option>
                            <option value="5">5 — Spesifik</option>
                            <option value="10">10 — Kritis</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap; height:38px;">
                        + Tambah
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="card">
            <x-ui.empty-state title="Belum ada kategori" description="Tambahkan kategori pertama." />
        </div>
    @endforelse

    @if($categories->hasPages())
        <x-ui.pagination :paginator="$categories" />
    @endif

    {{-- Modal Tambah Kategori --}}
    <div class="quick-modal-overlay" id="addCategoryOverlay">
        <div class="quick-modal" style="max-width:480px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">Tambah Kategori</span>
                <button class="quick-modal-close" onclick="closeAddCategoryModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('supervisor.categories.store') }}">
                @csrf
                <div class="quick-modal-body">
                    <div class="form-group">
                        <label class="form-label required">Nama Kategori</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Contoh: Database" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Deskripsi singkat kategori..."></textarea>
                    </div>
                </div>
                {{-- Di modal tambah, setelah field description --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label required">Base Priority</label>
                        <select name="base_priority" class="form-control" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                        <span class="form-hint">Priority default kategori ini</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Max Priority</label>
                        <select name="max_priority" class="form-control" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high" selected>High</option>
                            <option value="critical">Critical</option>
                        </select>
                        <span class="form-hint">Batas tertinggi priority</span>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddCategoryModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kategori --}}
    <div class="quick-modal-overlay" id="editCategoryOverlay">
        <div class="quick-modal" style="max-width:480px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">Edit Kategori</span>
                <button class="quick-modal-close" onclick="closeEditCategoryModal()">✕</button>
            </div>
            <form method="POST" id="editCategoryForm">
                @csrf @method('PATCH')
                <div class="quick-modal-body">
                    <div class="form-group">
                        <label class="form-label required">Nama Kategori</label>
                        <input type="text" name="name" id="editCategoryName"
                               class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="editCategoryDesc"
                                  class="form-control" rows="2"></textarea>
                    </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label required">Base Priority</label>
                    <select name="base_priority" id="editBasePriority" class="form-control" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Max Priority</label>
                    <select name="max_priority" id="editMaxPriority" class="form-control" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditCategoryModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openAddCategoryModal() {
            document.getElementById('addCategoryOverlay').classList.add('open');
        }
        function closeAddCategoryModal() {
            document.getElementById('addCategoryOverlay').classList.remove('open');
        }
        function openEditCategoryModal(id, name, desc, basePriority, maxPriority) {
        document.getElementById('editCategoryName').value       = name;
        document.getElementById('editCategoryDesc').value       = desc ?? '';
        document.getElementById('editBasePriority').value       = basePriority ?? 'low';
        document.getElementById('editMaxPriority').value        = maxPriority ?? 'high';
        document.getElementById('editCategoryForm').action      = `/supervisor/categories/${id}`;
        document.getElementById('editCategoryOverlay').classList.add('open');
        }
        function closeEditCategoryModal() {
            document.getElementById('editCategoryOverlay').classList.remove('open');
        }

        document.getElementById('addCategoryOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeAddCategoryModal();
        });
        document.getElementById('editCategoryOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeEditCategoryModal();
        });
    </script>
    @endpush

</x-layout.app>
