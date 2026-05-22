@extends('layouts/contentNavbarLayout')

@section('title', 'StaffTask — ' . $task->title)

@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
@endphp

<div class="row">
    <div class="col-lg-10 mx-auto">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="bx bx-task me-2 text-primary"></i>{{ $task->title }}
            </h4>
            <a href="{{ route('stafftask.board') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>{{ __('Back to Board') }}
            </a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">{{ __('Status') }}</small>
                        <span class="badge bg-{{ ['pending' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success'][$task->status] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">{{ __('Priority') }}</small>
                        <span class="badge bg-{{ ['low' => 'info', 'medium' => 'warning', 'high' => 'danger'][$task->priority] ?? 'secondary' }}">
                            {{ ucfirst($task->priority ?? 'medium') }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">{{ __('Due Date') }}</small>
                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                    </div>
                </div>

                @if ($task->description)
                    <h6 class="mt-3">{{ __('Description') }}</h6>
                    <p class="mb-3">{{ $task->description }}</p>
                @endif

                @if ($task->assignees->count())
                    <h6>{{ __('Assignees') }}</h6>
                    <p>
                        @foreach ($task->assignees as $u)
                            <span class="badge bg-label-primary">{{ $u->name }}</span>
                        @endforeach
                    </p>
                @endif

                @if (!empty($task->start_location['address']))
                    <h6>{{ __('Start Location') }}</h6>
                    <p>📍 {{ $task->start_location['address'] }}</p>
                @endif

                @if ($task->files->count())
                    <h6 class="mt-3">{{ __('Proof Files') }}</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ($task->files as $file)
                            @if ($file->type === 'image')
                                <a href="{{ $file->url }}" target="_blank">
                                    <img src="{{ $file->url }}" alt="" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                                </a>
                            @else
                                <video src="{{ $file->url }}" controls style="width:200px;height:120px;border-radius:8px;border:1px solid #ddd;"></video>
                            @endif
                        @endforeach
                    </div>
                @endif

                <h6 class="mt-3">{{ __('Activity Timeline') }}</h6>
                <ul class="list-group list-group-flush">
                    @forelse ($task->activities as $act)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                <i class="bx bx-circle me-1 text-primary"></i>
                                {{ $act->action }}
                                @if ($act->user) <small class="text-muted">— {{ $act->user->name }}</small> @endif
                            </span>
                            <small class="text-muted">{{ $act->created_at->diffForHumans() }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('No activity yet.') }}</li>
                    @endforelse
                </ul>

                @if ($isAdmin)
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('stafftask.edit', $task->id) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-1"></i>{{ __('Edit') }}
                        </a>
                        <form action="{{ route('stafftask.destroy', $task->id) }}" method="POST"
                              onsubmit="return confirm('Delete this task?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger">
                                <i class="bx bx-trash me-1"></i>{{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
