<x-layout.app title="Pengaturan SLA" pageTitle="Pengaturan SLA">

    <div class="page-header">
        <div>
            <h1 class="page-title">Pengaturan SLA</h1>
            <p class="page-subtitle">Atur target waktu respon dan penyelesaian berdasarkan prioritas</p>
        </div>
    </div>

    <div class="alert alert-info">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Waktu SLA dihitung dalam <strong>menit</strong>. Contoh: 60 = 1 jam, 480 = 8 jam, 1440 = 24 jam.</span>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Prioritas</th>
                        <th>Target Respon</th>
                        <th>Target Selesai</th>
                        <th>Hitung Jam Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($slas as $sla)
                        <tr>
                            <td>
                                <x-ui.badge-priority :priority="$sla->priority->level" />
                            </td>
                            <td>
                                <span style="font-weight:700;">{{ $sla->response_time }}</span>
                                <span style="color:var(--gray-400); font-size:12px;"> menit</span>
                                <span style="color:var(--gray-400); font-size:11px;">
                                    ({{ round($sla->response_time / 60, 1) }} jam)
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:700;">{{ $sla->resolution_time }}</span>
                                <span style="color:var(--gray-400); font-size:12px;"> menit</span>
                                <span style="color:var(--gray-400); font-size:11px;">
                                    ({{ round($sla->resolution_time / 60, 1) }} jam)
                                </span>
                            </td>
                            <td>
                                @if($sla->working_hours_only)
                                    <span class="badge badge-resolved">Ya</span>
                                @else
                                    <span class="badge badge-closed">Tidak</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('supervisor.sla.edit', $sla) }}"
                                   class="btn btn-secondary btn-sm">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-layout.app>
