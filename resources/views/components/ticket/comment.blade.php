@props(['comment', 'ticketId'])

<div class="comment-item" id="comment-{{ $comment->id }}">
    <div class="comment-avatar {{ $comment->user->isItSupport() ? 'support' : '' }}">
        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
    </div>

    <div class="comment-body">
        <div class="comment-header">
            <span class="comment-author">{{ $comment->user->name }}</span>

            @if($comment->user->isItSupport())
                <span class="comment-role-badge support">IT Support</span>
            @elseif($comment->user->isItSupervisor())
                <span class="comment-role-badge support">Supervisor</span>
            @else
                <span class="comment-role-badge user">Staff</span>
            @endif

            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
        </div>

        <div class="comment-text">{{ $comment->comment }}</div>
    </div>
</div>
