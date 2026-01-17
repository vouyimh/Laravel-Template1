<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - Laravel CRUD App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-tasks me-2"></i>Task Details</h4>
                        <a href="{{ route('tasks.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Tasks
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Task Title -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h2 class="text-primary mb-3">
                                    <i class="fas fa-bookmark me-2"></i>{{ $task->title }}
                                </h2>
                            </div>
                        </div>

                        <!-- Task Info Grid -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-info-circle me-2"></i>Status
                                        </h5>
                                        <span class="badge fs-6 {{ 
                                            $task->status == 'completed' ? 'bg-success' : 
                                            ($task->status == 'in_progress' ? 'bg-warning' : 'bg-secondary') 
                                        }}">
                                            <i class="fas {{ 
                                                $task->status == 'completed' ? 'fa-check-circle' : 
                                                ($task->status == 'in_progress' ? 'fa-clock' : 'fa-hourglass-start') 
                                            }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Priority
                                        </h5>
                                        <span class="badge fs-6 {{ 
                                            ($task->priority ?? 'medium') == 'high' ? 'bg-danger' : 
                                            (($task->priority ?? 'medium') == 'medium' ? 'bg-warning' : 'bg-info') 
                                        }}">
                                            <i class="fas {{ 
                                                ($task->priority ?? 'medium') == 'high' ? 'fa-arrow-up' : 
                                                (($task->priority ?? 'medium') == 'medium' ? 'fa-minus' : 'fa-arrow-down') 
                                            }} me-1"></i>
                                            {{ ucfirst($task->priority ?? 'Medium') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-align-left me-2"></i>Description
                                        </h5>
                                        @if($task->description)
                                            <p class="card-text">{{ $task->description }}</p>
                                        @else
                                            <p class="card-text text-muted fst-italic">
                                                <i class="fas fa-info-circle me-1"></i>No description provided
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-calendar-alt me-2"></i>Due Date
                                        </h5>
                                        @if($task->due_date)
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($task->due_date);
                                                $isOverdue = $dueDate->isPast() && $task->status !== 'completed';
                                                $isDueSoon = $dueDate->diffInDays(now()) <= 3 && $dueDate->isFuture();
                                            @endphp
                                            <p class="mb-1">
                                                <span class="badge fs-6 {{ 
                                                    $isOverdue ? 'bg-danger' : 
                                                    ($isDueSoon ? 'bg-warning' : 'bg-success') 
                                                }}">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $dueDate->format('M d, Y') }}
                                                </span>
                                            </p>
                                            <small class="text-muted">
                                                @if($isOverdue)
                                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                                    Overdue by {{ $dueDate->diffForHumans() }}
                                                @elseif($isDueSoon)
                                                    <i class="fas fa-clock text-warning me-1"></i>
                                                    Due {{ $dueDate->diffForHumans() }}
                                                @else
                                                    <i class="fas fa-check text-success me-1"></i>
                                                    {{ $dueDate->diffForHumans() }}
                                                @endif
                                            </small>
                                        @else
                                            <p class="text-muted fst-italic">
                                                <i class="fas fa-info-circle me-1"></i>No due date set
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-hashtag me-2"></i>Task ID
                                        </h5>
                                        <p class="mb-0">
                                            <span class="badge bg-dark fs-6">
                                                <i class="fas fa-tag me-1"></i>#{{ $task->id }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-plus-circle me-2"></i>Created
                                        </h5>
                                        <p class="mb-1">
                                            {{ $task->created_at ? $task->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                                        </p>
                                        @if($task->created_at)
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $task->created_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-secondary">
                                            <i class="fas fa-edit me-2"></i>Last Updated
                                        </h5>
                                        <p class="mb-1">
                                            {{ $task->updated_at ? $task->updated_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                                        </p>
                                        @if($task->updated_at)
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $task->updated_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-list me-1"></i>All Tasks
                                    </a>
                                    <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i>Edit Task
                                    </a>
                                    @if($task->status !== 'completed')
                                        <form action="{{ route('tasks.update', $task->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="title" value="{{ $task->title }}">
                                            <input type="hidden" name="description" value="{{ $task->description }}">
                                            <input type="hidden" name="status" value="completed">
                                            <input type="hidden" name="priority" value="{{ $task->priority ?? 'medium' }}">
                                            <input type="hidden" name="due_date" value="{{ $task->due_date }}">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check me-1"></i>Mark Complete
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this task? This action cannot be undone.')">
                                            <i class="fas fa-trash me-1"></i>Delete Task
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>