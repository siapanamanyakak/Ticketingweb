<x-layout.app title="News Management" pageTitle="News Management">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">News Management</h1>
                <p class="page-subtitle">Manage announcements and information for all users</p>
            </div>
            <button onclick="openAddNewsModal()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create News
            </button>
        </div>
    </div>

    {{-- Active News Preview --}}
    @php $activeNews = \App\Models\News::active()->latest()->get(); @endphp
    @if($activeNews->count() > 0)
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <span class="card-title">📢 Active News</span>
            </div>
            <div class="card-body">
                <x-ui.news-banner :news="$activeNews" />
            </div>
        </div>
    @endif

    {{-- All News Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created By</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($news as $item)
                        <tr>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $item->title }}</div>
                                <div style="font-size:11px; color:var(--gray-400);">
                                    {{ Str::limit($item->description, 60) }}
                                </div>
                            </td>
                            <td>
                                @php $style = $item->type_color; @endphp
                                <span style="display:inline-flex; align-items:center; gap:4px;
                                             background:{{ $style['bg'] }}; color:{{ $style['color'] }};
                                             padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                                    {{ $style['icon'] }} {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td style="font-size:12px;">{{ $item->creator->name }}</td>
                            <td style="font-size:12px; color:var(--gray-500);">
                                {{ $item->starts_at?->format('d M Y, H:i') ?? '—' }}
                            </td>
                            <td style="font-size:12px; color:var(--gray-500);">
                                {{ $item->ends_at?->format('d M Y, H:i') ?? 'None' }}
                            </td>
                            <td>
                                @if($item->is_active && (!$item->ends_at || $item->ends_at >= now()))
                                    <span class="badge badge-resolved">Active</span>
                                @elseif(!$item->is_active)
                                    <span class="badge badge-closed">Inactive</span>
                                @else
                                    <span class="badge badge-pending">Expired</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button onclick="openEditNewsModal(
                                                {{ $item->id }},
                                                '{{ addslashes($item->title) }}',
                                                '{{ addslashes($item->description) }}',
                                                '{{ $item->type }}',
                                                '{{ $item->starts_at?->format('Y-m-d\TH:i') ?? '' }}',
                                                '{{ $item->ends_at?->format('Y-m-d\TH:i') ?? '' }}'
                                            )"
                                            class="btn btn-secondary btn-sm">Edit</button>

                                    <form method="POST" action="{{ route('supervisor.news.toggle', $item) }}"
                                    id="toggleNews{{ $item->id }}">
                                    @csrf @method('PATCH')
                                    <button type="button"
                                            class="btn btn-sm {{ $item->is_active ? 'btn-warning' : 'btn-success' }}"
                                            onclick="showConfirmModal({
                                                title: '{{ $item->is_active ? 'Deactivate' : 'Activate' }} News',
                                                desc: 'Are you sure you want to {{ $item->is_active ? 'deactivate' : 'activate' }} this news?',
                                                btnText: '{{ $item->is_active ? 'Deactivate' : 'Activate' }}',
                                                btnClass: '{{ $item->is_active ? 'btn-warning' : 'btn-success' }}',
                                                icon: '{{ $item->is_active ? '⚠️' : '✅' }}',
                                                type: '{{ $item->is_active ? 'warning' : 'info' }}',
                                                action: () => document.getElementById('toggleNews{{ $item->id }}').submit()
                                            })">
                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('supervisor.news.destroy', $item) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDelete('delete-form-{{ $item->id }}', '{{ $item->title }}')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state title="No news available" description="Create your first news item." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($news->hasPages())
            <div style="padding:0 20px;">
                <x-ui.pagination :paginator="$news" />
            </div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div class="quick-modal-overlay" id="addNewsOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">📢 Create New News</span>
                <button class="quick-modal-close" onclick="closeAddNewsModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('supervisor.news.store') }}">
                @csrf
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control"
                               placeholder="Example: Monthly server maintenance" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Explain the details..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="info">📢 Info</option>
                            <option value="warning">⚠️ Warning</option>
                            <option value="maintenance">🔧 Maintenance</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Start Showing</label>
                            <input type="datetime-local" name="starts_at" class="form-control">
                            <span class="form-hint">Empty = show immediately</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="datetime-local" name="ends_at" class="form-control">
                            <span class="form-hint">Empty = no expiration</span>
                        </div>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddNewsModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="quick-modal-overlay" id="editNewsOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">✏️ Edit News</span>
                <button class="quick-modal-close" onclick="closeEditNewsModal()">✕</button>
            </div>
            <form method="POST" id="editNewsForm">
                @csrf @method('PATCH')
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" id="editNewsTitle" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Description</label>
                        <textarea name="description" id="editNewsDesc" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Type</label>
                        <select name="type" id="editNewsType" class="form-control" required>
                            <option value="info">📢 Info</option>
                            <option value="warning">⚠️ Warning</option>
                            <option value="maintenance">🔧 Maintenance</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Start Showing</label>
                            <input type="datetime-local" name="starts_at" id="editNewsStart" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="datetime-local" name="ends_at" id="editNewsEnd" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditNewsModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openAddNewsModal()  { document.getElementById('addNewsOverlay').classList.add('open'); }
        function closeAddNewsModal() { document.getElementById('addNewsOverlay').classList.remove('open'); }

        function openEditNewsModal(id, title, desc, type, startsAt, endsAt) {
            document.getElementById('editNewsTitle').value = title;
            document.getElementById('editNewsDesc').value  = desc;
            document.getElementById('editNewsType').value  = type;
            document.getElementById('editNewsStart').value = startsAt;
            document.getElementById('editNewsEnd').value   = endsAt;
            document.getElementById('editNewsForm').action = `/supervisor/news/${id}`;
            document.getElementById('editNewsOverlay').classList.add('open');
        }
        function closeEditNewsModal() { document.getElementById('editNewsOverlay').classList.remove('open'); }

        document.getElementById('addNewsOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeAddNewsModal();
        });
        document.getElementById('editNewsOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeEditNewsModal();
        });
    </script>
    @endpush

</x-layout.app>
