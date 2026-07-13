@props(['news'])

@if($news->count() > 0)
    <div style="margin-bottom:16px;">
        @foreach($news as $item)
            @php $style = $item->type_color; @endphp
            <div style="background:{{ $style['bg'] }}; border:1px solid {{ $style['border'] }};
                        border-radius:12px; padding:14px 18px; margin-bottom:8px;
                        display:flex; align-items:flex-start; gap:12px;">

                <span style="font-size:18px; flex-shrink:0; margin-top:1px;">{{ $style['icon'] }}</span>

                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px; flex-wrap:wrap;">
                        <span style="font-size:13px; font-weight:700; color:{{ $style['color'] }};">
                            {{ $item->title }}
                        </span>
                        <span style="font-size:10px; font-weight:600; padding:2px 8px;
                                     border-radius:10px; background:{{ $style['color'] }};
                                     color:white; text-transform:uppercase; letter-spacing:0.5px;">
                            {{ $item->type }}
                        </span>
                    </div>
                    <p style="font-size:12px; color:{{ $style['color'] }}; opacity:0.85;
                              line-height:1.5; margin-bottom:6px;">
                        {{ $item->description }}
                    </p>
                    <div style="display:flex; align-items:center; gap:8px; font-size:11px;
                                color:{{ $style['color'] }}; opacity:0.7;">
                        <span>{{ $item->creator->name }}</span>
                        <span>·</span>
                        <span>
                            {{ match($item->creator->role) {
                                'it_support'    => 'IT Support',
                                'it_supervisor' => 'IT Supervisor',
                                default         => 'Staff'
                            } }}
                        </span>
                        <span>·</span>
                        <span>{{ $item->created_at->format('d M Y, H:i') }}</span>
                        @if($item->ends_at)
                            <span>·</span>
                            <span>Sampai: {{ $item->ends_at->format('d M Y, H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
