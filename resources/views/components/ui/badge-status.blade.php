@props(['status' => 'open'])

@php
$labels = [
    'open'        => 'Open',
    'in_progress' => 'In Progress',
    'pending'     => 'Pending',
    'resolved'    => 'Resolved',
    'closed'      => 'Closed',
];
@endphp

<span class="badge badge-{{ str_replace('_', '-', $status) }}">
    <span class="badge-dot"></span>
    {{ $labels[$status] ?? ucfirst($status) }}
</span>
