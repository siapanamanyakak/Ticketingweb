<x-layout.app title="SLA Management" pageTitle="SLA Management">

    <div class="page-header">
        <div>
            <h1 class="page-title">SLA Management</h1>
            <p class="page-subtitle">Manage response and resolution time targets based on priority</p>
        </div>
    </div>

    <div class="alert alert-info">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>SLA time is calculated in <strong>minutes</strong>. Example: 60 = 1 hour, 480 = 8 hours, 1440 = 24 hours. SLA Warning will be triggered at <strong>10%</strong> of remaining time.</span>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Response Time</th>
                        <th>Resolution Time</th>
                        <th>Working Hours Only</th>
                        <th>Warning Trigger</th>
                        <th>Actions</th>
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
                                <span style="color:var(--gray-400); font-size:12px;"> min</span>
                                <span style="color:var(--gray-400); font-size:11px;">
                                    ({{ round($sla->response_time / 60, 1) }} hrs)
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:700;">{{ $sla->resolution_time }}</span>
                                <span style="color:var(--gray-400); font-size:12px;"> min</span>
                                <span style="color:var(--gray-400); font-size:11px;">
                                    ({{ round($sla->resolution_time / 60, 1) }} hrs)
                                </span>
                            </td>
                            <td>
                                @if($sla->working_hours_only)
                                    <span class="badge badge-resolved">Yes</span>
                                @else
                                    <span class="badge badge-closed">No</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:12px; color:var(--gray-500);">
                                    Response: <strong>{{ round($sla->response_time * 0.1) }} min</strong><br>
                                    Resolution: <strong>{{ round($sla->resolution_time * 0.1) }} min</strong>
                                </span>
                            </td>
                            <td>
                                <button onclick="openEditSlaModal(
                                            {{ $sla->id }},
                                            '{{ $sla->priority->name }}',
                                            '{{ $sla->priority->level }}',
                                            {{ $sla->response_time }},
                                            {{ $sla->resolution_time }},
                                            {{ $sla->working_hours_only ? 'true' : 'false' }}
                                        )"
                                        class="btn btn-secondary btn-sm">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit SLA --}}
    <div class="quick-modal-overlay" id="editSlaOverlay">
        <div class="quick-modal" style="max-width:480px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title" id="editSlaTitle">Edit SLA Configuration</span>
                <button type="button" class="quick-modal-close" onclick="closeEditSlaModal()">✕</button>
            </div>
            <form method="POST" id="editSlaForm">
                @csrf @method('PATCH')

                {{-- Hidden input for SLA ID (mencegah error route binding jika ada validasi gagal) --}}
                <input type="hidden" name="edit_sla_id" id="editSlaIdHidden">

                <div class="quick-modal-body" style="padding-top: 16px;">

                    {{-- Priority (read only) --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" style="font-weight: 600; color: var(--gray-700);">Priority Level</label>
                        <div style="position: relative;">
                            <input type="text" id="editSlaPriority" class="form-control"
                                   disabled style="background:var(--gray-50); color:var(--navy-700); font-weight: 600; padding-left: 36px;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px;">
                            </span>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom: 20px;">
                        {{-- Response Time --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-weight: 600;">Response Time (min)</label>
                            <input type="number" name="response_time" id="editSlaResponse"
                                   class="form-control" min="1" required
                                   oninput="updateWarning()" style="text-align: left;">
                            @error('response_time') <span class="form-error" style="color:red; font-size:12px; display:block; margin-top:4px;">{{ $message }}</span> @enderror

                            {{-- Warning Message dipindah ke bawah input --}}
                            <div style="margin-top: 6px; font-size: 11px; color: var(--amber-600); display: flex; align-items: center; gap: 4px;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Warns at: <strong id="responseWarning">—</strong> min</span>
                            </div>
                        </div>

                        {{-- Resolution Time --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" style="font-weight: 600;">Resolution Time (min)</label>
                            <input type="number" name="resolution_time" id="editSlaResolution"
                                   class="form-control" min="1" required
                                   oninput="updateWarning()" style="text-align: left;">
                            @error('resolution_time') <span class="form-error" style="color:red; font-size:12px; display:block; margin-top:4px;">{{ $message }}</span> @enderror

                            {{-- Warning Message dipindah ke bawah input --}}
                            <div style="margin-top: 6px; font-size: 11px; color: var(--amber-600); display: flex; align-items: center; gap: 4px;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Warns at: <strong id="resolutionWarning">—</strong> min</span>
                            </div>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--gray-200); margin: 0 0 20px 0;">

                    {{-- Working Hours Only (Slider Toggle) --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <label class="form-label" style="font-weight: 600; margin-bottom: 2px;">Calculate Working Hours Only</label>
                                <span class="form-hint" style="font-size: 11px; margin-top: 0;">Pause SLA calculation outside 08:00 - 17:00</span>
                            </div>

                            {{-- CSS untuk Switch Slider --}}
                            <style>
                                .switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
                                .switch input { opacity: 0; width: 0; height: 0; }
                                .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--gray-300); transition: .3s; border-radius: 24px; }
                                .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
                                input:checked + .slider { background-color: var(--navy-600); }
                                input:checked + .slider:before { transform: translateX(20px); }
                            </style>

                            <label class="switch">
                                {{-- Menggunakan hidden input agar nilai '0' tetap terkirim saat toggle dimatikan --}}
                                <input type="hidden" name="working_hours_only" value="0">
                                <input type="checkbox" name="working_hours_only" value="1" id="workingToggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                </div>
                <div class="quick-modal-footer" style="background: var(--gray-50); border-top: 1px solid var(--gray-200);">
                    <button type="button" class="btn btn-secondary" onclick="closeEditSlaModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
                // ── Script SLA Modal ────────────────────────

        function openEditSlaModal(id, priorityName, priorityLevel, responseTime, resolutionTime, workingHours) {
            // 1. Set ID ke hidden input (penting buat error handling Laravel)
            const hiddenIdInput = document.getElementById('editSlaIdHidden');
            if (hiddenIdInput) hiddenIdInput.value = id;

            // 2. Set judul dan isi form
            document.getElementById('editSlaTitle').textContent = `Edit SLA — ${priorityName}`;
            document.getElementById('editSlaPriority').value    = priorityName.toUpperCase();
            document.getElementById('editSlaResponse').value    = responseTime;
            document.getElementById('editSlaResolution').value  = resolutionTime;
            document.getElementById('editSlaForm').action       = `/supervisor/sla/${id}`;

            // 3. Set working hours slider (menggantikan logic radio button)
            // Pastikan nilainya dibaca sebagai boolean (true jika 1 atau true)
            document.getElementById('workingToggle').checked = (workingHours == 1 || workingHours === true);

            updateWarning();
            document.getElementById('editSlaOverlay').classList.add('open');
        }

        function closeEditSlaModal() {
            document.getElementById('editSlaOverlay').classList.remove('open');
        }

        function updateWarning() {
            const response   = parseInt(document.getElementById('editSlaResponse').value) || 0;
            const resolution = parseInt(document.getElementById('editSlaResolution').value) || 0;

            // Menghitung trigger 10%
            const responseWarn   = Math.round(response * 0.1);
            const resolutionWarn = Math.round(resolution * 0.1);

            // Update tulisan warning di bawah input
            document.getElementById('responseWarning').textContent =
                responseWarn > 0 ? `${responseWarn} min (${(responseWarn/60).toFixed(1)} hrs)` : '—';

            document.getElementById('resolutionWarning').textContent =
                resolutionWarn > 0 ? `${resolutionWarn} min (${(resolutionWarn/60).toFixed(1)} hrs)` : '—';
        }

        document.getElementById('editSlaOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeEditSlaModal();
        });
    </script>
    @endpush

</x-layout.app>
