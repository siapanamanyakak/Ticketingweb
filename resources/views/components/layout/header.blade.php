<header class="app-header">
    <div class="header-left">
        <button class="btn-toggle-sidebar" id="btnToggleSidebar" title="Toggle Sidebar">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <span class="header-page-title">
            {{ $pageTitle ?? config('app.name') }}
        </span>
    </div>

    <div class="header-right">

        {{-- Notification Bell + Dropdown --}}
        @php
            $unreadCount    = auth()->user()->unreadNotifications->count();
            $recentNotifs   = auth()->user()->notifications()->latest()->take(5)->get();
            $notifRoute     = match(auth()->user()->role) {
                'it_support'    => route('support.notifications.index'),
                'it_supervisor' => route('supervisor.notifications.index'),
                default         => route('user.notifications.index'),
            };
            $markAllRoute   = match(auth()->user()->role) {
                'it_support'    => route('support.notifications.markAllRead'),
                'it_supervisor' => route('supervisor.notifications.markAllRead'),
                default         => route('user.notifications.markAllRead'),
            };
            $readRoute = match(auth()->user()->role) {
                'it_support'    => 'support.notifications.readRedirect',
                'it_supervisor' => 'supervisor.notifications.readRedirect',
                default         => 'user.notifications.readRedirect',
            };
        @endphp

        <div class="notif-dropdown-wrapper" id="notifDropdownWrapper">
            {{-- Bell Button --}}
            <button class="btn-notification" id="btnNotification" title="Notifications">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </button>

            {{-- Dropdown --}}
            <div class="notif-dropdown" id="notifDropdown">
                {{-- Header --}}
                <div class="notif-dropdown-header">
                    <span class="notif-dropdown-title">Notifications</span>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ $markAllRoute }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="notif-mark-all-btn">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>

                {{-- List --}}
                <div class="notif-dropdown-list">
                    @forelse($recentNotifs as $notif)
                        @php
                            $isUnread = is_null($notif->read_at);
                        @endphp

                        <a href="{{ route($readRoute, $notif->id) }}"
                        class="notif-dropdown-item {{ $isUnread ? 'unread' : '' }}">
                            <span class="notif-dot {{ $isUnread ? 'unread' : 'read' }}"></span>
                            <div class="notif-dropdown-content">
                                <p class="notif-dropdown-msg">{{ $notif->data['message'] }}</p>
                                <span class="notif-dropdown-time">
                                    {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="notif-dropdown-empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p>No notifications</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer --}}
                <div class="notif-dropdown-footer">
                    <a href="{{ $notifRoute }}" class="notif-see-all">
                        See All Messages
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- User Dropdown --}}
        <div class="user-dropdown">
            <button class="user-dropdown-toggle" id="userDropdownToggle">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="user-dropdown-name">{{ auth()->user()->name }}</span>
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="user-dropdown-menu" id="userDropdownMenu">
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    My Profile
                </a>
                <div class="dropdown-divider"></div>
                <button type="button" class="dropdown-item danger"
                        style="width:100%;text-align:left;background:none;border:none;cursor:pointer;
                            font-family:'Montserrat',sans-serif;"
                        onclick="closeUserDropdown(); openLogoutModal();">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
                </form>
            </div>
        </div>
    </div>
</header>
