@props(['title' => 'Tidak ada data', 'description' => '', 'actionLabel' => null, 'actionRoute' => null])

<div class="empty-state">
    <div class="empty-state-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
    </div>
    <p class="empty-state-title">{{ $title }}</p>
    @if($description)
        <p class="empty-state-desc">{{ $description }}</p>
    @endif

    {{-- Menampilkan slot jika ada (untuk tombol custom/modal) --}}
    @if($slot->isNotEmpty())
        {{ $slot }}
    {{-- Menampilkan link bawaan jika tidak ada slot (tujuan awal dipertahankan) --}}
    @elseif($actionLabel && $actionRoute)
        <a href="{{ $actionRoute }}" class="btn btn-primary">
            {{ $actionLabel }}
        </a>
    @endif
</div>
