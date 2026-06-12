<x-layout.app title="Jadwal Kerja" pageTitle="Jadwal Kerja">

    <div class="page-header">
        <div>
            <h1 class="page-title">Jadwal Kerja</h1>
            <p class="page-subtitle">Atur hari dan jam operasional untuk perhitungan SLA</p>
        </div>
    </div>

    <div class="alert alert-info">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Tiket yang masuk diluar jam kerja akan otomatis dijeda SLA-nya dan dilanjutkan saat jam kerja dimulai.</span>
    </div>

    @php
        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    @endphp

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Hari Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                        <tr id="row-{{ $schedule->id }}">
                            <td style="font-weight:700;">
                                {{ $dayNames[$schedule->day_of_week] }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                            <td>
                                @if($schedule->is_working_day)
                                    <span class="badge badge-resolved">Hari Kerja</span>
                                @else
                                    <span class="badge badge-closed">Libur</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-secondary btn-sm"
                                        onclick="openEditModal(
                                            {{ $schedule->id }},
                                            '{{ $dayNames[$schedule->day_of_week] }}',
                                            '{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}',
                                            '{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}',
                                            {{ $schedule->is_working_day ? 'true' : 'false' }}
                                        )">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Jadwal — <span id="modalDayName"></span></span>
                <button class="modal-close" onclick="closeEditModal()">✕</button>
            </div>
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label required">Jam Mulai</label>
                            <input type="time" name="start_time" id="modalStartTime" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Jam Selesai</label>
                            <input type="time" name="end_time" id="modalEndTime" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Hari</label>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:6px;">
                            <input type="hidden" name="is_working_day" value="0">
                            <input type="checkbox" name="is_working_day" value="1"
                                   id="modalIsWorking" style="width:16px; height:16px; cursor:pointer;">
                            <label for="modalIsWorking" style="font-size:13px; color:var(--gray-700); cursor:pointer;">
                                Tandai sebagai hari kerja
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(id, dayName, startTime, endTime, isWorking) {
            document.getElementById('modalDayName').textContent   = dayName;
            document.getElementById('modalStartTime').value       = startTime;
            document.getElementById('modalEndTime').value         = endTime;
            document.getElementById('modalIsWorking').checked     = isWorking;
            document.getElementById('editScheduleForm').action    = `/supervisor/work-schedules/${id}`;
            document.getElementById('editModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('open');
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
    @endpush

</x-layout.app>
