@props(['log', 'canViewNote' => false])

@php
$actionLabels = [
    'status'   => 'Status Updated',
    'comment'  => 'Comment Added',
    'priority' => 'Priority Updated',
    'category' => 'Category Updated',
];

$actionIcons = [
    'status'   => '🔄',
    'comment'  => '💬',
    'priority' => '🎯',
    'category' => '📂'
];

$statusColors = [
    'open'        => ['bg' => '#f3f4f6', 'color' => '#4b5563'],
    'in_progress' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
    'pending'     => ['bg' => '#fef3c7', 'color' => '#b45309'],
    'resolved'    => ['bg' => '#dcfce7', 'color' => '#15803d'],
    'closed'      => ['bg' => '#f3f4f6', 'color' => '#374151'],
];

$priorityColors = [
    'low'      => ['bg' => '#dcfce7', 'color' => '#15803d'],
    'medium'   => ['bg' => '#fef3c7', 'color' => '#b45309'],
    'high'     => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
    'critical' => ['bg' => '#fce7f3', 'color' => '#9d174d'],
];

$hasNote = !empty($log->note) && ($canViewNote || $log->visibility === 'all');

// Tentukan warna badge
$colorMap = $log->field_changed === 'priority' ? $priorityColors : $statusColors;
$beforeStyle = $colorMap[$log->status_before] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
$afterStyle  = $colorMap[$log->status_after]  ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
@endphp

<div class="log-item"
     @if($hasNote || $log->field_changed !== 'comment')
         onclick="openLogModal(
             '{{ addslashes($actionIcons[$log->field_changed] ?? '📝') }} {{ addslashes($actionLabels[$log->field_changed] ?? ucfirst($log->field_changed)) }}',
             '{{ addslashes($log->updatedBy->name) }}',
             '{{ $log->created_at->format('d M Y, H:i') }}',
             '{{ addslashes($log->status_before ?? '') }}',
             '{{ addslashes($log->status_after ?? '') }}',
             '{{ addslashes($log->field_changed) }}',
             {{ $hasNote ? "'".addslashes($log->note)."'" : 'null' }},
             '{{ addslashes($log->status_after ?? '') }}'
         )"
         style="cursor:pointer;"
     @endif>

    <div class="log-item-content"
         onmouseover="{{ ($hasNote || $log->field_changed !== 'comment') ? 'this.style.background=\'var(--gray-100)\'' : '' }}"
         onmouseout="{{ ($hasNote || $log->field_changed !== 'comment') ? 'this.style.background=\'var(--gray-50)\'' : '' }}">

        <div class="log-item-action">
            {{ $actionIcons[$log->field_changed] ?? '📝' }}
            {{ $actionLabels[$log->field_changed] ?? ucfirst($log->field_changed) }}

            @if($log->status_before && $log->status_after)
                {{-- Badge before --}}
                <span style="display:inline-flex; align-items:center; gap:3px;
                             background:{{ $beforeStyle['bg'] }}; color:{{ $beforeStyle['color'] }};
                             padding:1px 8px; border-radius:20px; font-size:10px; font-weight:700;
                             margin-left:6px;">
                    {{ ucfirst(str_replace('_', ' ', $log->status_before)) }}
                </span>

                <span style="font-size:10px; color:var(--gray-400); margin:0 2px;">→</span>

                {{-- Badge after --}}
                <span style="display:inline-flex; align-items:center; gap:3px;
                             background:{{ $afterStyle['bg'] }}; color:{{ $afterStyle['color'] }};
                             padding:1px 8px; border-radius:20px; font-size:10px; font-weight:700;">
                    {{ ucfirst(str_replace('_', ' ', $log->status_after)) }}
                </span>
            @endif

            @if($hasNote)
                <span style="margin-left:4px; font-size:10px; color:var(--navy-500);">📝</span>
            @endif
        </div>

        <div class="log-item-time">
            {{ $log->created_at->format('d M Y, H:i') }} · {{ $log->updatedBy->name }}
            @if($hasNote || $log->field_changed !== 'comment')
                <span style="color:var(--navy-400); font-size:10px;"> · Click for details</span>
            @endif
        </div>
    </div>
</div>
