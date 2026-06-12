@props(['priority' => 'low'])

@php
$labels = [
    'low'      => 'Low Priority',
    'medium'   => 'Medium Priority',
    'high'     => 'High Priority',
    'critical' => 'Critical',
];
@endphp

<span class="badge badge-{{ $priority }}">
    {{ $labels[$priority] ?? ucfirst($priority) }}
</span>
