@php
    $priority = $task->priority ?? 'medium';
    $priorityClass = ['high' => 'bg-danger', 'medium' => 'bg-warning', 'low' => 'bg-info'][$priority] ?? 'bg-secondary';

    $files = $task->files ?? collect();
    $canStart    = $task->status === 'pending';
    $canUpload   = $task->status === 'in_progress';
    $canComplete = $task->status === 'in_progress' && $files->count() > 0;
@endphp

<div class="card mb-2 shadow-sm" data-task-id="{{ $task->id }}">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0">{{ $task->title }}</h6>
            <span class="badge {{ $priorityClass }} ms-2">{{ ucfirst($priority) }}</span>
        </div>

        @if ($task->description)
            <p class="small text-muted mb-2">{{ Str::limit($task->description, 100) }}</p>
        @endif

        <div class="d-flex flex-wrap gap-2 small text-muted mb-2">
            @if ($task->due_date)
                <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
            @endif
            @if ($task->started_at)
                <span><i class="bx bx-play-circle"></i> {{ $task->started_at->diffForHumans() }}</span>
            @endif
            @if ($task->completed_at)
                <span><i class="bx bx-check-circle text-success"></i> {{ $task->completed_at->diffForHumans() }}</span>
            @endif
        </div>

        @if (!empty($task->start_location['address']))
            <div class="small mb-2">
                <i class="bx bx-map text-primary"></i>
                <strong>{{ __('Start location:') }}</strong>
                {{ $task->start_location['address'] }}
            </div>
        @endif

        {{-- PROOF FILES --}}
        @if ($files->count() > 0)
            <div class="mb-2">
                <div class="small text-muted mb-1">{{ __('Proof files') }} ({{ $files->count() }})</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($files as $file)
                        <div class="position-relative" data-file-row style="width: 64px;">
                            @if ($file->type === 'image')
                                <a href="{{ $file->url }}" target="_blank">
                                    <img src="{{ $file->url }}" alt="{{ $file->original_name }}"
                                         class="rounded border" style="width: 64px; height: 64px; object-fit: cover;">
                                </a>
                            @else
                                <a href="{{ $file->url }}" target="_blank"
                                   class="d-flex flex-column align-items-center justify-content-center rounded border bg-light text-decoration-none"
                                   style="width: 64px; height: 64px;">
                                    <i class="bx bx-video fs-3 text-primary"></i>
                                    <span class="small">video</span>
                                </a>
                            @endif
                            @if ($task->status !== 'completed')
                                <button type="button"
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0"
                                        style="width: 18px; height: 18px; line-height: 1; font-size: 10px;"
                                        data-staff-remove-file
                                        data-url="{{ route('tasks.files.destroy', [$task->id, $file->id]) }}"
                                        title="Remove">×</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ACTION BUTTONS --}}
        <div class="d-flex flex-wrap gap-2 mt-2">
            @if ($canStart)
                <button type="button" class="btn btn-primary btn-sm flex-grow-1"
                        data-staff-start="{{ $task->id }}"
                        data-url="{{ route('tasks.start', $task->id) }}">
                    🚀 {{ __('Start Task') }}
                </button>
            @endif

            @if ($canUpload)
                <div class="w-100">
                    <label class="btn btn-outline-primary btn-sm w-100 mb-0">
                        📷 {{ __('Upload Proof') }}
                        <input type="file" class="d-none"
                               accept="image/*,video/*"
                               data-staff-upload
                               data-url="{{ route('tasks.upload', $task->id) }}">
                    </label>
                    <div class="progress mt-1 upload-progress d-none" style="height: 4px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            @endif

            @if ($canComplete)
                <button type="button" class="btn btn-success btn-sm w-100"
                        data-staff-complete
                        data-url="{{ route('tasks.complete', $task->id) }}">
                    ✅ {{ __('Complete Task') }}
                </button>
            @elseif ($task->status === 'in_progress' && $files->count() === 0)
                <small class="text-muted w-100">
                    {{ __('Upload at least one proof file to complete.') }}
                </small>
            @endif

            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-link btn-sm p-0 w-100">
                {{ __('View details & timeline') }}
            </a>
        </div>
    </div>
</div>
