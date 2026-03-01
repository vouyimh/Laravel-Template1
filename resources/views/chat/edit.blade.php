@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-6">
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4>Edit Task</h4>
                                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to Tasks</a>
                                </div>
                                <div class="card-body">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label for="title" class="form-label">Task Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                id="title" name="title" value="{{ old('title', $task->title) }}"
                                                placeholder="Enter task title" required>
                                            @error('title')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Task Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                rows="4" placeholder="Enter task description (optional)">{{ old('description', $task->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select @error('status') is-invalid @enderror" id="status"
                                                name="status">
                                                <option value="pending"
                                                    {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="in_progress"
                                                    {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In
                                                    Progress</option>
                                                <option value="completed"
                                                    {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>
                                                    Completed</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="priority" class="form-label">Priority</label>
                                            <select class="form-select @error('priority') is-invalid @enderror"
                                                id="priority" name="priority">
                                                <option value="low"
                                                    {{ old('priority', $task->priority ?? 'medium') == 'low' ? 'selected' : '' }}>
                                                    Low</option>
                                                <option value="medium"
                                                    {{ old('priority', $task->priority ?? 'medium') == 'medium' ? 'selected' : '' }}>
                                                    Medium</option>
                                                <option value="high"
                                                    {{ old('priority', $task->priority ?? 'medium') == 'high' ? 'selected' : '' }}>
                                                    High</option>
                                            </select>
                                            @error('priority')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="due_date" class="form-label">Due Date</label>
                                            <input type="date"
                                                class="form-control @error('due_date') is-invalid @enderror" id="due_date"
                                                name="due_date"
                                                value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                                            @error('due_date')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Created At</label>
                                                    <div class="form-control-plaintext">
                                                        {{ $task->created_at ? $task->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Last Updated</label>
                                                    <div class="form-control-plaintext">
                                                        {{ $task->updated_at ? $task->updated_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="assignees" class="form-label">Assign Users</label>

                                            <select name="assignees[]" id="assignees"
                                                class="form-select @error('assignees') is-invalid @enderror" multiple>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}"
                                                        {{ in_array($user->id, old('assignees', $task->assignees->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error('assignees')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <a href="{{ route('tasks.index') }}"
                                                class="btn btn-secondary me-md-2">Cancel</a>
                                            <a href="{{ route('tasks.show', $task->id) }}"
                                                class="btn btn-info me-md-2">View
                                                Task</a>
                                            <button type="submit" class="btn btn-primary">Update Task</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endsection

            @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if ($('#assignees').length) {
        $('#assignees').select2({
            placeholder: 'Select users',
            width: '100%'
        });
    }
});
</script>
@endpush
