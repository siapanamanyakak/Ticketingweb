<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notifTitle }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Arial, sans-serif; background:#f3f4f6; color:#1f2937; }
        .wrapper { max-width:580px; margin:32px auto; }
        .header { background:#0f2044; padding:24px 32px; border-radius:12px 12px 0 0; }
        .header-top {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-logo {
            max-height: 50px;
            width: auto;
            object-fit: contain;
        }
        .header-name { color:white; font-size:15px; font-weight:700; letter-spacing:0.5px; }
        .header-sub  { color:rgba(255,255,255,0.5); font-size:11px; margin-top:2px; }
        .body { background:white; padding:28px 32px; }
        .badge { display:inline-block; padding:4px 12px; border-radius:20px;
                 font-size:11px; font-weight:700; margin-bottom:16px; }
        .badge-info     { background:#dbeafe; color:#1d4ed8; }
        .badge-warning  { background:#fef3c7; color:#b45309; }
        .badge-critical { background:#fce7f3; color:#9d174d; }
        .badge-sla      { background:#fee2e2; color:#b91c1c; }
        .badge-status   { background:#dcfce7; color:#15803d; }
        .notif-title   { font-size:18px; font-weight:700; color:#0f2044; margin-bottom:6px; }
        .notif-message { font-size:13px; color:#6b7280; line-height:1.6; margin-bottom:20px; }

        /* Tambahan CSS untuk Kotak Komentar */
        .comment-box {
            background: #f8fafc;
            border-left: 4px solid #1d4ed8;
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            color: #334155;
            font-style: italic;
            white-space: pre-line;
        }

        .divider { border:none; border-top:1px solid #e5e7eb; margin:20px 0; }
        .ticket-card { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px;
                       padding:20px; margin-bottom:20px; }
        .ticket-number { font-size:12px; font-weight:700; color:#0f2044;
                         letter-spacing:0.5px; margin-bottom:4px; }
        .ticket-title  { font-size:16px; font-weight:700; color:#111827; margin-bottom:14px; }
        .ticket-desc   { font-size:13px; color:#4b5563; line-height:1.6;
                         margin-bottom:14px; white-space:pre-line; }
        .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
        .meta-item { background:white; border:1px solid #e5e7eb; border-radius:8px; padding:10px 12px; }
        .meta-label { font-size:10px; font-weight:700; color:#9ca3af;
                      text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
        .meta-value { font-size:13px; font-weight:600; color:#111827; }
        .priority-low      { color:#15803d; }
        .priority-medium   { color:#b45309; }
        .priority-high     { color:#b91c1c; }
        .priority-critical { color:#9d174d; }
        .reporter-box { display:flex; align-items:center; gap:10px; padding:12px;
                        background:white; border:1px solid #e5e7eb; border-radius:8px;
                        margin-bottom:14px; }
        .reporter-avatar { width:36px; height:36px; background:#0f2044; color:white;
                           border-radius:50%; display:flex; align-items:center;
                           justify-content:center; font-size:14px; font-weight:700; }
        .reporter-name { font-size:13px; font-weight:700; color:#111827; }
        .reporter-dept { font-size:11px; color:#6b7280; margin-top:2px; }
        .attachment-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px;
                          padding:10px 14px; font-size:12px; color:#1d4ed8; font-weight:600;
                          margin-bottom:14px; }
        .btn { display:inline-block; background:#0f2044; color:white !important;
               padding:12px 28px; border-radius:8px; text-decoration:none;
               font-size:13px; font-weight:700; letter-spacing:0.5px; }
        .footer { background:#f9fafb; padding:16px 32px; border-radius:0 0 12px 12px;
                  border-top:1px solid #e5e7eb; text-align:center; }
        .footer p { font-size:11px; color:#9ca3af; line-height:1.8; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <img src="{{ $message->embed(public_path('img/Logo-KTU.jpg')) }}" alt="Logo KTU Shipyard" class="header-logo">
            <div>
                <div class="header-name">KTU Shipyard</div>
                <div class="header-sub">IT Support Ticketing System</div>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="body">
        @php
            $badgeClass = match($notifType ?? 'info') {
                'warning'     => 'badge-warning',
                'critical'    => 'badge-critical',
                'sla_warning' => 'badge-sla',
                'status'      => 'badge-status',
                default       => 'badge-info',
            };
            $badgeLabel = match($notifType ?? 'info') {
            'new_ticket'  => '🎫 New Ticket',
            'new_comment' => '💬 New Comment',
            'status'      => '🔄 Status Update',
            'warning'     => '🌙 Outside Hours',
            'sla_warning' => '⚠️ SLA Warning',
            'critical'    => '🚨 Critical Priority',
            default       => '🔔 Notification',
         };
        @endphp

        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        <div class="notif-title">{{ $notifTitle }}</div>
        <div class="notif-message">{{ $notifMessage }}</div>

        {{-- Blok Khusus Komentar (Hanya tampil jika $commentText dikirim) --}}
        @if(!empty($commentText))
            <div class="comment-box">
                <strong>{{ $commenterName ?? 'User' }} wrote:</strong><br>
                "{{ $commentText }}"
            </div>
        @endif

        {{-- Detail Tiket (hanya kalau ada $ticket) --}}
        @if(!empty($ticket))
            <hr class="divider">

            <div class="ticket-card">
                {{-- Ticket Number --}}
                <div class="ticket-number">{{ $ticket->ticket_number }}</div>
                <div class="ticket-title">{{ $ticket->title }}</div>

                {{-- Reporter --}}
                <div class="reporter-box">
                    <div class="reporter-avatar">
                        {{ strtoupper(substr($ticket->reporter->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="reporter-name">{{ $ticket->reporter->name ?? 'Unknown' }}</div>
                        <div class="reporter-dept">
                            {{ $ticket->reporter->department?->name ?? 'N/A' }}
                            · {{ $ticket->reporter->id_staff ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                @if($ticket->description)
                    <div class="ticket-desc">{{ $ticket->description }}</div>
                @endif

                {{-- Meta Grid --}}
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">Category</div>
                        <div class="meta-value">
                            {{ $ticket->category?->name ?? 'Uncategorized' }}
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Priority</div>
                        <div class="meta-value priority-{{ $ticket->priority?->level ?? 'low' }}">
                            {{ ucfirst($ticket->priority?->level ?? 'low') }}
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Status</div>
                        <div class="meta-value">{{ ucfirst($ticket->status ?? 'open') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Submitted</div>
                        <div class="meta-value" style="font-size:11px;">
                            {{ $ticket->created_at ? $ticket->created_at->format('d M Y, H:i') : 'N/A' }}
                        </div>
                    </div>
                </div>

                {{-- Attachment --}}
                @if($ticket->attachment)
                    <div class="attachment-box">
                        📎 This ticket has an attachment. View it in the system.
                    </div>
                @endif
            </div>
        @endif

        {{-- CTA Button --}}
        @if(!empty($notifUrl))
            <a href="{{ $notifUrl }}" class="btn">View Ticket →</a>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>© {{ date('Y') }} KTU Shipyard · IT Helpdesk System</p>
        <p>You received this email because you have an active account in the system.</p>
    </div>

</div>
</body>
</html>
