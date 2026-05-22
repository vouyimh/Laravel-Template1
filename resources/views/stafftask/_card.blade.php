@php
    $priority   = $task->priority ?? 'medium';
    $assignees  = $task->assignees ?? collect();
    $assignee   = $assignees->first();
    $assigneeNm = $assignee?->name ?? '';
    $files      = $task->files ?? collect();
    // Both admin and staff can drive the workflow on this board.
    $canAct     = $isStaff || $isAdmin;
    $canStart   = $canAct && $task->status === 'pending';
    // Allow uploading proof at any time before the task is completed,
    // so users can attach images/videos in either "pending" or "in_progress".
    $canUpload  = $canAct && $task->status !== 'completed';
    $canComplete= $canAct && $task->status === 'in_progress' && $files->count() > 0;

    $initials = $assigneeNm
        ? strtoupper(collect(explode(' ', $assigneeNm))->map(fn($w)=>substr($w,0,1))->take(2)->implode(''))
        : '?';
    $hash = $assigneeNm ? crc32($assigneeNm) : 0;
    $colors = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
    $avatarColor = $colors[$hash % count($colors)];
@endphp

<div class="st-card prio-{{ $priority }}"
     data-task="{{ $task->id }}"
     data-task-id="{{ $task->id }}"
     data-title="{{ $task->title }}"
     data-prio="{{ $priority }}"
     data-status="{{ $task->status }}"
     data-assignee="{{ $assigneeNm }}"
     @if ($isAdmin) draggable="true" data-st-draggable @endif>

    <div class="st-card-head">
        <div class="st-card-title">{{ $task->title }}</div>
        <span class="st-badge st-badge-{{ $priority }}">{{ strtoupper($priority) }}</span>
    </div>

    @if ($task->description)
        <div class="st-card-desc">{{ Str::limit($task->description, 120) }}</div>
    @endif

    @if ($assignee)
        <div class="st-assignee">
            <span class="st-avatar" style="background: {{ $avatarColor }}">{{ $initials }}</span>
            <span style="font-size:.85rem; color: var(--st-text);">{{ $assigneeNm }}</span>
        </div>
    @endif

    <div class="st-meta">
        @if ($task->due_date)
            <span>📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
        @endif
        @if ($task->started_at)
            <span style="color:#4ddca6;">🎉 {{ __('Started') }}: {{ $task->started_at->format('n/j/Y, g:i:s A') }}</span>
        @endif
        @if ($task->completed_at)
            <span style="color:#4ddca6;">✅ {{ __('Done') }}: {{ $task->completed_at->format('n/j/Y, g:i:s A') }}</span>
        @endif
    </div>

    @if (!empty($task->start_location['address']))
        <div class="st-location">
            📍 <strong>{{ __('Start location') }}:</strong> {{ $task->start_location['address'] }}
        </div>
    @endif

    @if ($files->count())
        <div class="st-files">
            @foreach ($files as $file)
                <div class="st-file">
                    @if ($file->type === 'image')
                        <img src="{{ $file->url }}" alt="{{ $file->original_name }}"
                             data-st-preview="image"
                             data-url="{{ $file->url }}"
                             data-download="{{ $file->download_url }}"
                             data-name="{{ $file->original_name }}"
                             style="cursor:zoom-in;">
                    @else
                        <button type="button" class="vid"
                                data-st-preview="video"
                                data-url="{{ $file->url }}"
                                data-download="{{ $file->download_url }}"
                                data-name="{{ $file->original_name }}"
                                style="background:none;border:0;cursor:zoom-in;">
                            <span style="font-size:1.4rem;">🎥</span>
                            <span>video</span>
                        </button>
                    @endif
                    <a class="dl" href="{{ $file->download_url }}"
                       title="{{ __('Download') }}: {{ $file->original_name }}"
                       download="{{ $file->original_name }}">⬇</a>
                    @if ($canAct && $task->status !== 'completed')
                        <button class="rm" type="button" data-st-remove
                                data-url="{{ route('stafftask.files.destroy', [$task->id, $file->id]) }}"
                                title="{{ __('Remove') }}">×</button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="st-actions">
        @if ($canStart)
            <button type="button" class="st-btn st-btn-primary"
                    data-st-start data-url="{{ route('stafftask.start', $task->id) }}">
                🚀 {{ __('Start Task') }}
            </button>
        @endif

        @if ($canUpload)
            <div class="st-upload-wrap" style="flex:1 1 100%;">
                <label class="st-btn st-btn-success" style="width:100%; justify-content:center; margin:0;">
                    📷 {{ __('Upload Image') }}
                    <input type="file" class="st-hide" accept="image/*"
                           data-st-upload data-url="{{ route('stafftask.upload', $task->id) }}">
                </label>
                <div class="st-progress" style="display:none;"><div></div></div>
            </div>
            <div class="st-upload-wrap" style="flex:1 1 100%;">
                <label class="st-btn" style="width:100%; justify-content:center; margin:0;">
                    🎥 {{ __('Upload Video') }}
                    <input type="file" class="st-hide" accept="video/*"
                           data-st-upload data-url="{{ route('stafftask.upload', $task->id) }}">
                </label>
                <div class="st-progress" style="display:none;"><div></div></div>
            </div>
        @endif

        @if ($canComplete)
            <button type="button" class="st-btn st-btn-success" style="flex:1 1 100%;"
                    data-st-complete data-url="{{ route('stafftask.complete', $task->id) }}">
                ✅ {{ __('Complete Task') }}
            </button>
        @elseif ($canAct && $task->status === 'in_progress' && $files->count() === 0)
            <small style="flex:1 1 100%; color:var(--st-muted); font-size:.75rem; text-align:center;">
                {{ __('Upload at least one proof file to complete') }}
            </small>
        @endif

        @if ($isAdmin)
            <a href="{{ route('stafftask.edit', $task->id) }}" class="st-btn">✏️ {{ __('Edit') }}</a>
            <form method="POST" action="{{ route('stafftask.destroy', $task->id) }}" style="margin:0;flex:1 1 auto;"
                  onsubmit="return confirm('{{ __('Delete this task?') }}');">
                @csrf @method('DELETE')
                <button class="st-btn st-btn-danger" type="submit" style="width:100%;">🗑 {{ __('Delete') }}</button>
            </form>
        @endif

        <a href="{{ route('stafftask.show', $task->id) }}" class="st-btn" style="flex:1 1 100%; justify-content:center;">
            👁 {{ __('View details & timeline') }}
        </a>
    </div>
</div>
