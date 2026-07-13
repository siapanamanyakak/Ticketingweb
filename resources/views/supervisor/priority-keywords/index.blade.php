<x-layout.app title="Keyword Priority" pageTitle="Keyword Priority">

    <div class="page-header">
        <div>
            <h1 class="page-title">Keyword Priority</h1>
            <p class="page-subtitle">Manage keywords for automatic ticket priority detection</p>
        </div>
    </div>

    <div class="alert alert-info">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Keyword this is used to detect the urgency level from the ticket description. The system checks from Critical → High → Medium. If no matching keyword is found, the priority is determined by the ticket category.</span>
    </div>

    @php
        $colorMap = [
            'low'      => ['color' => '#15803d', 'bg' => '#dcfce7'],
            'medium'   => ['color' => '#b45309', 'bg' => '#fef3c7'],
            'high'     => ['color' => '#b91c1c', 'bg' => '#fee2e2'],
            'critical' => ['color' => '#7c2d12', 'bg' => '#fce7f3'],
        ];
    @endphp

    @foreach($priorities as $priority)
        @php $c = $colorMap[$priority->level] ?? ['color' => '#374151', 'bg' => '#f3f4f6']; @endphp
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <span class="card-title">
                    <span style="display:inline-flex; align-items:center; gap:8px;">
                        <span style="width:10px; height:10px; border-radius:50%;
                                     background:{{ $c['color'] }}; display:inline-block;"></span>
                        {{ $priority->name }} Keywords
                    </span>
                </span>
                <span style="font-size:12px; color:var(--gray-400);">
                    {{ $priority->keywords->count() }} keyword
                </span>
            </div>
            <div class="card-body">
                {{-- Keywords --}}
                <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px;">
                    @forelse($priority->keywords as $kw)
                        <span style="display:inline-flex; align-items:center; gap:4px;
                                    background:{{ $c['bg'] }}; color:{{ $c['color'] }};
                                    padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                            {{ $kw->keyword }}
                            <span style="background:rgba(0,0,0,0.1); padding:0 5px;
                                        border-radius:10px; font-size:10px; font-weight:700;">
                                {{ $kw->weight }}
                            </span>
                            <form method="POST"
                                action="{{ route('supervisor.priority-keywords.destroy', $kw) }}"
                                style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="background:none; border:none; cursor:pointer;
                                            color:{{ $c['color'] }}; font-size:13px;
                                            line-height:1; padding:0 0 0 2px; opacity:0.7;"
                                        onclick="return confirm('Delete this keyword?')">×</button>
                            </form>
                        </span>
                    @empty
                        <span style="font-size:12px; color:var(--gray-400);">No keywords added yet</span>
                    @endforelse
                </div>

                {{-- Tambah keyword --}}
                <form method="POST" action="{{ route('supervisor.priority-keywords.store') }}"
                    style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; max-width:500px;">
                    @csrf
                    <input type="hidden" name="priority_id" value="{{ $priority->id }}">

                    <div style="flex:1; min-width:160px;">
                        <label style="font-size:11px; font-weight:600; color:var(--gray-400);
                                    text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:4px;">
                            Keyword
                        </label>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Add keyword {{ $priority->priority_name }}..."
                            style="margin-bottom:0;" required>
                    </div>

                    <div style="width:100px;">
                        <label style="font-size:11px; font-weight:600; color:var(--gray-400);
                                    text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:4px;">
                            Weight
                        </label>
                        <select name="weight" class="form-control" style="margin-bottom:0;" required>
                            <option value="">Select</option>
                            <option value="1">1 — General</option>
                            <option value="3">3 — Standard</option>
                            <option value="5">5 — Specific</option>
                            <option value="10">10 — Critical</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap; height:38px;">
                        + add
                    </button>
                </form>
            </div>
        </div>
    @endforeach

</x-layout.app>
