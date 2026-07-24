<x-layout.app title="Notification" pageTitle="Notification">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Notification</h1>
                <p class="page-subtitle">All notifications related to your ticket activities</p>
            </div>
            @if(auth()->user()->readNotifications()->count() > 0)
                <form method="POST" action="{{ route('supervisor.notifications.deleteRead') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete all read notifications?')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete All Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Mark All Read --}}
    @if(auth()->user()->unreadNotifications->count() > 0)
        <div style="margin-bottom:16px;">
            <form method="POST" action="{{ route('supervisor.notifications.markAllRead') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-secondary btn-sm">
                    ✓ Mark All as Read
                </button>
            </form>
        </div>
    @endif

    <div class="card">
        @forelse($notifications as $notif)
        @php
            $isUnread  = is_null($notif->read_at);
            $type      = $notif->data['type'] ?? 'general';
            $oldStatus = $notif->data['old_status'] ?? null;
            $newStatus = $notif->data['new_status'] ?? null;

            $typeConfig = match(true) {
                $type === 'new_ticket'                  => ['icon' => '🎫', 'label' => 'New Ticket',      'bg' => '#f0fdf4', 'color' => '#15803d'],
                str_contains($type, 'sla_warning')         => ['icon' => '⚠️', 'label' => 'SLA Warning',     'bg' => '#fef3c7', 'color' => '#b45309'],
                $type === 'critical_ticket'                 => ['icon' => '🚨', 'label' => 'Ticket Critical',  'bg' => '#fce7f3', 'color' => '#9d174d'],
                $type === 'outside_working_hours'           => ['icon' => '🌙', 'label' => 'Outside Working Hours','bg' => '#eff6ff', 'color' => '#1d4ed8'],
                $type === 'ticket_created'                  => ['icon' => '🎫', 'label' => 'Ticket Created',    'bg' => '#f0fdf4', 'color' => '#15803d'],
                $type === 'status_updated'                  => ['icon' => '🔄', 'label' => 'Status Updated','bg' => '#eff6ff', 'color' => '#1d4ed8'],
                $type === 'new_comment'                     => ['icon' => '💬', 'label' => 'New Comment',   'bg' => '#f5f3ff', 'color' => '#6d28d9'],
                default                                     => ['icon' => '📢', 'label' => 'Notification',      'bg' => '#f9fafb', 'color' => '#374151'],
            };

            $statusColors = [
                'open'        => ['bg' => '#f3f4f6', 'color' => '#4b5563'],
                'in_progress' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
                'pending'     => ['bg' => '#fef3c7', 'color' => '#b45309'],
                'resolved'    => ['bg' => '#dcfce7', 'color' => '#15803d'],
                'closed'      => ['bg' => '#f3f4f6', 'color' => '#374151'],
            ];
        @endphp

        <div style="display:flex; align-items:flex-start; gap:14px; padding:16px 20px;
                    border-bottom:1px solid var(--gray-100);
                    background:{{ $isUnread ? '#fef2f2' : 'transparent' }};">

            {{-- Type Icon --}}
            <div style="width:38px; height:38px; border-radius:10px; flex-shrink:0;
                        background:{{ $typeConfig['bg'] }}; display:flex; align-items:center;
                        justify-content:center; font-size:18px;">
                {{ $typeConfig['icon'] }}
            </div>

            {{-- Content --}}
            <div style="flex:1; min-width:0;">

                {{-- Header --}}
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px; flex-wrap:wrap;">
                    <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;
                                background:{{ $typeConfig['bg'] }}; color:{{ $typeConfig['color'] }};">
                        {{ $typeConfig['label'] }}
                    </span>

                    {{-- Status badges kalau ada --}}
                    @if($oldStatus && $newStatus)
                        <span style="background:{{ $statusColors[$oldStatus]['bg'] ?? '#f3f4f6' }};
                                    color:{{ $statusColors[$oldStatus]['color'] ?? '#374151' }};
                                    padding:1px 8px; border-radius:20px; font-size:10px; font-weight:700;">
                            {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}
                        </span>
                        <span style="font-size:10px; color:var(--gray-400);">→</span>
                        <span style="background:{{ $statusColors[$newStatus]['bg'] ?? '#f3f4f6' }};
                                    color:{{ $statusColors[$newStatus]['color'] ?? '#374151' }};
                                    padding:1px 8px; border-radius:20px; font-size:10px; font-weight:700;">
                            {{ ucfirst(str_replace('_', ' ', $newStatus)) }}
                        </span>
                    @endif

                    {{-- Unread dot --}}
                    @if($isUnread)
                        <span style="width:8px; height:8px; border-radius:50%;
                                    background:#ef4444; display:inline-block; margin-left:auto;"></span>
                    @endif
                </div>

                {{-- Message --}}
                <p style="font-size:13px;
                        font-weight:{{ $isUnread ? '600' : '400' }};
                        color:{{ $isUnread ? 'var(--gray-900)' : 'var(--gray-600)' }};
                        margin-bottom:4px; line-height:1.5;">
                    {{ $notif->data['message'] }}
                </p>

                <p style="font-size:11px; color:var(--gray-400);">
                    {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                    @if(!$isUnread)
                        · <span style="color:#16a34a;">Read</span>
                    @endif
                </p>
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:6px; flex-shrink:0;">
                @if($isUnread)
                    <form method="POST" action="{{ route('supervisor.notifications.read', $notif->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-secondary btn-sm">Mark as Read</button>
                    </form>
                @else
                    <span class="btn btn-secondary btn-sm" style="opacity:0.5; cursor:default;">
                        Read
                    </span>
                @endif

                @if(isset($notif->data['url']))
                    <a href="{{ $notif->data['url'] }}" class="btn btn-primary btn-sm">
                        View Ticket
                    </a>
                @endif
            </div>
        </div>
    @empty
        <x-ui.empty-state
            title="There's no notification"
            description="You don't have any notifications yet."
        />
    @endforelse
    </div>

    @if($notifications->hasPages())
        <x-ui.pagination :paginator="$notifications" />
    @endif

</x-layout.app>
