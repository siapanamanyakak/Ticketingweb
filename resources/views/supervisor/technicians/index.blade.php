<x-layout.app title="Manajemen Teknisi" pageTitle="Manajemen Teknisi">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Manajemen Teknisi</h1>
                <p class="page-subtitle">Kelola akun IT Support yang menangani tiket</p>
            </div>
            <a href="{{ route('supervisor.technicians.create') }}" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Teknisi
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Staff</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Departemen</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
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
                            <td style="color:var(--gray-500);">{{ $tech->email }}</td>
                            <td>{{ $tech->department?->name ?? '—' }}</td>
                            <td>
                                @if($tech->is_active)
                                    <span class="badge badge-resolved">Aktif</span>
                                @else
                                    <span class="badge badge-closed">Nonaktif</span>
                                @endif
                            </td>
                            <td style="color:var(--gray-500); font-size:12px;">
                                {{ $tech->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="{{ route('supervisor.technicians.edit', $tech) }}"
                                       class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST"
                                          action="{{ route('supervisor.technicians.toggle', $tech) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $tech->is_active ? 'btn-warning' : 'btn-success' }}"
                                                onclick="return confirm('{{ $tech->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                            {{ $tech->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state
                                    title="Belum ada teknisi"
                                    description="Tambahkan akun IT Support pertama."
                                    actionLabel="Tambah Teknisi"
                                    :actionRoute="route('supervisor.technicians.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($technicians->hasPages())
            <div style="padding: 0 20px;">
                <x-ui.pagination :paginator="$technicians" />
            </div>
        @endif
    </div>

</x-layout.app>
