@extends('layouts/contentNavbarLayout')

@section('title', 'New StaffTask')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
<style>
    .choices__inner { background:#fff; border:1px solid #d9dee3; border-radius:.375rem; min-height:38px; padding:.25rem .375rem; }
    .choices__list--multiple .choices__item { background:#696cff; border:0; border-radius:4px; }
    .choices__input { background:#fff; }
    .choices[data-type*="select-multiple"] .choices__button { filter:invert(1) brightness(2); }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="bx bx-task me-2 text-primary"></i>
                {{ __('Create New StaffTask') }}
            </h4>
            <a href="{{ route('stafftask.board') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>{{ __('Back to Board') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('stafftask.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">
                            {{ __('Task Title') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="e.g. Inspect water pump" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Optional details about the task...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">{{ __('Priority') }}</label>
                            <select id="priority" name="priority"
                                    class="form-select @error('priority') is-invalid @enderror">
                                <option value="low"    {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high"   {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                            <input type="date" id="due_date" name="due_date"
                                   class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date') }}" min="{{ date('Y-m-d') }}">
                            @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="assignees" class="form-label">
                            {{ __('Assign to Staff') }}
                            <small class="text-muted">({{ __('search and select multiple staff') }})</small>
                        </label>
                        <select id="assignees" name="assignees[]" multiple
                                class="form-select @error('assignees') is-invalid @enderror"
                                data-placeholder="{{ __('Search staff by name...') }}">
                            @forelse ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ collect(old('assignees'))->contains($user->id) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @empty
                                <option disabled>{{ __('No staff users available') }}</option>
                            @endforelse
                        </select>
                        @error('assignees') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">
                            {{ __('Type to filter the list. Click a name to assign; click the × on a tag to remove.') }}
                        </small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('stafftask.board') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>{{ __('Create Task') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const assignees = document.getElementById('assignees');
    if (assignees && window.Choices) {
        new Choices(assignees, {
            removeItemButton: true,
            shouldSort:       false,
            searchEnabled:    true,
            searchPlaceholderValue: assignees.dataset.placeholder || @json(__('Search staff by name...')),
            placeholder:      true,
            placeholderValue: assignees.dataset.placeholder || @json(__('Search staff by name...')),
            noResultsText:    @json(__('No staff match your search')),
            noChoicesText:    @json(__('No staff users available')),
            itemSelectText:   '',
        });
    }
});
</script>
@endpush

@endsection
