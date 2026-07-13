<x-layout.app title="SLA Management" pageTitle="SLA Management">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('supervisor.sla.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div>
                <h1 class="page-title">Edit SLA</h1>
                <p class="page-subtitle">Priority: <x-ui.badge-priority :priority="$sla->priority->level" /></p>
            </div>
        </div>
    </div>

    <div style="max-width: 520px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">SLA Management — {{ $sla->priority->name }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('supervisor.sla.update', $sla) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label required">Response Time Target (minutes)</label>
                        <input type="number" name="response_time" min="1"
                               class="form-control {{ $errors->has('response_time') ? 'is-invalid' : '' }}"
                               value="{{ old('response_time', $sla->response_time) }}">
                        <span class="form-hint">
                            Current: {{ $sla->response_time }} minutes ({{ round($sla->response_time / 60, 1) }} hours)
                        </span>
                        @error('response_time') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Resolution Time Target (minutes)</label>
                        <input type="number" name="resolution_time" min="1"
                               class="form-control {{ $errors->has('resolution_time') ? 'is-invalid' : '' }}"
                               value="{{ old('resolution_time', $sla->resolution_time) }}">
                        <span class="form-hint">
                            Current: {{ $sla->resolution_time }} minutes ({{ round($sla->resolution_time / 60, 1) }} hours)
                        </span>
                        @error('resolution_time') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Calculate Working Hours Only</label>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:6px;">
                            <input type="hidden" name="working_hours_only" value="0">
                            <input type="checkbox" name="working_hours_only" value="1" id="working_hours"
                                   {{ old('working_hours_only', $sla->working_hours_only) ? 'checked' : '' }}
                                   style="width:16px; height:16px; cursor:pointer;">
                            <label for="working_hours" style="font-size:13px; color:var(--gray-700); cursor:pointer;">
                                SLA only calculated during working hours
                            </label>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
                        <a href="{{ route('supervisor.sla.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout.app>
