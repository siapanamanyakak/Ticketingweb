<x-layout.app title="Work Schedules" pageTitle="Work Schedules">

    <div class="page-header">
        <div>
            <h1 class="page-title">Work Schedules</h1>
            <p class="page-subtitle">Manage working days and hours for SLA calculation</p>
        </div>
    </div>

    <div class="alert alert-info">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Tickets that come in outside working hours will automatically have their SLA paused and resumed when working hours begin.</span>
    </div>

    @php
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    @endphp

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Working Day</th>
                        <th>Actions</th>
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
                                    <span class="badge badge-resolved">Working Day</span>
                                @else
                                    <span class="badge badge-closed">Off Day</span>
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
                <span class="modal-title">Edit Schedule — <span id="modalDayName"></span></span>
                <button type="button" class="modal-close" onclick="closeEditModal()">✕</button>
            </div>
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PATCH')

                {{-- WAJIB DITAMBAHKAN: Hidden input agar kita ingat ID jadwal mana yang sedang diedit --}}
                <input type="hidden" name="edit_schedule_id" id="modalScheduleId">
                <input type="hidden" name="day_name" id="modalDayNameHidden">

                <div class="modal-body">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label required">Start Time</label>
                            <input type="time" name="start_time" id="modalStartTime" class="form-control">
                            {{-- Tampilkan error dari Laravel di sini --}}
                            @error('start_time') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">End Time</label>
                            <input type="time" name="end_time" id="modalEndTime" class="form-control">
                            {{-- Tampilkan error dari Laravel di sini --}}
                            @error('end_time') <span class="form-error" style="color:red; font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Bagian Day Status Toggle --}}
                    <div class="form-group">
                        <label class="form-label">Day Status</label>
                        <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                            <input type="hidden" name="is_working_day" value="0">

                            <label class="toggle-switch">
                                <input type="checkbox" name="is_working_day" value="1" id="modalIsWorking">
                                <span class="slider"></span>
                            </label>

                            <label for="modalIsWorking" style="font-size:14px; color:var(--gray-700); cursor:pointer; font-weight:500;">
                                Working Day
                            </label>
                        </div>
                    </div>

                    {{-- TEMPAT MUNCULNYA ERROR JS --}}
                    <div id="scheduleError" style="display:none; margin-top:16px; padding:10px; background:#fef2f2; border:1px solid #f87171; border-radius:6px; color:#b91c1c; font-size:13px; font-weight:600;">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(id, dayName, startTime, endTime, isWorking) {
            const errorContainer = document.getElementById('scheduleError');
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }

            // Set Title & Hidden Inputs
            document.getElementById('modalDayName').textContent = dayName;
            document.getElementById('modalDayNameHidden').value = dayName;
            document.getElementById('modalScheduleId').value    = id;

            // Set Form Values
            document.getElementById('modalStartTime').value   = startTime;
            document.getElementById('modalEndTime').value     = endTime;

            // Perbaikan logika isWorking: true jika value adalah 1 atau "true"
            document.getElementById('modalIsWorking').checked = (isWorking == 1 || isWorking === true);

            // Set Form Action
            document.getElementById('editScheduleForm').action = `/supervisor/work-schedules/${id}`;
            document.getElementById('editModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('open');
        }

        document.addEventListener('DOMContentLoaded', function() {

            // 1. Tutup modal saat klik overlay
            const modalOverlay = document.getElementById('editModal');
            if (modalOverlay) {
                modalOverlay.addEventListener('click', function(e) {
                    if (e.target === this) closeEditModal();
                });
            }

            // 2. Logika Validasi Frontend (JS) Waktu Start dan End
            const scheduleForm = document.getElementById('editScheduleForm');
            if (scheduleForm) {
                scheduleForm.addEventListener('submit', function(e) {
                    const errorContainer = document.getElementById('scheduleError');
                    const startTimeValue = document.getElementById('modalStartTime').value;
                    const endTimeValue   = document.getElementById('modalEndTime').value;
                    const isWorking      = document.getElementById('modalIsWorking').checked;

                    // Cegah submit jika waktu Start >= End
                    if (isWorking && startTimeValue && endTimeValue) {
                        if (startTimeValue >= endTimeValue) {
                            e.preventDefault(); // TAHAN form!

                            if (errorContainer) {
                                errorContainer.style.display = 'block';
                                errorContainer.textContent = '⚠️ Validasi Gagal: Waktu Start harus lebih awal daripada Waktu End.';
                            }
                            return false;
                        }
                    }

                    // Jika aman, biarkan lanjut ke Laravel
                    if (errorContainer) {
                        errorContainer.style.display = 'none';
                    }
                });
            }

            // 3. AUTO-REOPEN JIKA ADA ERROR DARI LARAVEL BACKEND
            @if($errors->any() && old('edit_schedule_id'))
                openEditModal(
                    '{{ old("edit_schedule_id") }}',
                    '{{ old("day_name", "Hari") }}',
                    '{{ old("start_time") }}',
                    '{{ old("end_time") }}',
                    {{ old("is_working_day", 0) == 1 ? 'true' : 'false' }}
                );
            @endif

        });
    </script>
    @endpush

</x-layout.app>
