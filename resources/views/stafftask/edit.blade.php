@extends('layouts/contentNavbarLayout')

@section('title', 'Edit StaffTask')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
<style>
    /* Make Choices.js theme blend with this template's look. */
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
                <i class="bx bx-edit me-2 text-primary"></i>
                {{ __('Edit StaffTask') }}
            </h4>
            <a href="{{ route('stafftask.board') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>{{ __('Back to Board') }}
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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

                {{-- Delete form sits separately so the main form's Save button isn't nested inside it. --}}
                <form id="stafftask-delete-form" action="{{ route('stafftask.destroy', $task->id) }}" method="POST"
                      onsubmit="return confirm('{{ __('Delete this task?') }}');">
                    @csrf @method('DELETE')
                </form>

                {{-- ============================ MAIN EDIT FORM ============================ --}}
                <form action="{{ route('stafftask.update', $task->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label">
                            {{ __('Task Title') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $task->title) }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select id="status" name="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                @foreach (['pending' => __('To Do'), 'in_progress' => __('In Progress'), 'completed' => __('Complete')] as $k => $label)
                                    <option value="{{ $k }}" {{ old('status', $task->status) === $k ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">{{ __('Priority') }}</label>
                            <select id="priority" name="priority"
                                    class="form-select @error('priority') is-invalid @enderror">
                                @foreach (['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High')] as $p => $label)
                                    <option value="{{ $p }}" {{ old('priority', $task->priority) === $p ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                            <input type="date" id="due_date" name="due_date"
                                   class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="assignees" class="form-label">
                            {{ __('Assign to Staff') }}
                            <small class="text-muted">({{ __('search and select multiple staff') }})</small>
                        </label>
                        @php $selectedIds = old('assignees', $task->assignees->pluck('id')->toArray()); @endphp
                        <select id="assignees" name="assignees[]" multiple
                                class="form-select @error('assignees') is-invalid @enderror"
                                data-placeholder="{{ __('Search staff by name...') }}">
                            @forelse ($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ in_array($u->id, $selectedIds) ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @empty
                                <option disabled>{{ __('No staff users available') }}</option>
                            @endforelse
                        </select>
                        <small class="text-muted">
                            {{ __('Type to filter the list. Click a name to assign; click the × on a tag to remove.') }}
                        </small>
                    </div>

                    {{-- ============================ ATTACHMENTS ============================ --}}
                    <hr class="my-4">
                    <h6 class="mb-3">
                        <i class="bx bx-paperclip me-1"></i>
                        {{ __('Attachments') }}
                        <span class="badge bg-label-primary ms-1" id="edit-files-count">{{ $task->files->count() }}</span>
                    </h6>

                    <div class="row g-3 mb-3" id="edit-files-grid">
                        @forelse ($task->files as $file)
                            <div class="col-md-3 col-sm-4 col-6" data-file-id="{{ $file->id }}">
                                <div class="border rounded p-2 h-100">
                                    @if ($file->type === 'image')
                                        <img src="{{ $file->url }}"
                                             alt="{{ $file->original_name }}"
                                             class="img-fluid rounded"
                                             style="width:100%; height:120px; object-fit:cover; cursor:zoom-in;"
                                             onclick="window.open(this.src,'_blank')">
                                    @else
                                        <video src="{{ $file->url }}" controls
                                               style="width:100%; height:120px; background:#000; border-radius:4px;"></video>
                                    @endif
                                    <div class="small text-truncate mt-2" title="{{ $file->original_name }}">
                                        {{ $file->original_name }}
                                    </div>
                                    <div class="d-flex gap-1 mt-2">
                                        <a href="{{ $file->download_url }}"
                                           class="btn btn-sm btn-outline-primary flex-fill"
                                           title="{{ __('Download') }}"
                                           download="{{ $file->original_name }}">
                                            <i class="bx bx-download"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger flex-fill"
                                                data-edit-remove
                                                data-url="{{ route('stafftask.files.destroy', [$task->id, $file->id]) }}"
                                                title="{{ __('Remove') }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12" data-empty-files>
                                <div class="alert alert-light text-center mb-0">
                                    <i class="bx bx-info-circle me-1"></i>
                                    {{ __('No attachments yet.') }}
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex gap-2 flex-wrap mb-1">
                        <label class="btn btn-success mb-0">
                            <i class="bx bx-image-add me-1"></i>{{ __('Add Image') }}
                            <input type="file" class="d-none" accept="image/*"
                                   data-edit-upload data-url="{{ route('stafftask.upload', $task->id) }}">
                        </label>
                        <label class="btn btn-info mb-0">
                            <i class="bx bx-video-plus me-1"></i>{{ __('Add Video') }}
                            <input type="file" class="d-none" accept="video/*"
                                   data-edit-upload data-url="{{ route('stafftask.upload', $task->id) }}">
                        </label>
                    </div>
                    <div class="progress d-none mt-2" id="edit-upload-progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar" style="width:0%"></div>
                    </div>

                    {{-- ============================ ACTION BAR (bottom) ============================ --}}
                    <hr class="my-4">
                    <div class="d-flex justify-content-between gap-2">
                        <button type="submit" form="stafftask-delete-form" class="btn btn-outline-danger">
                            <i class="bx bx-trash me-1"></i>{{ __('Delete') }}
                        </button>

                        <div class="d-flex gap-2">
                            <a href="{{ route('stafftask.board') }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>{{ __('Save Changes') }}
                            </button>
                        </div>
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

    // === Searchable multi-select for assignees ============================
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

    // === Attachments: AJAX remove/upload ==================================
    const CSRF       = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const grid       = document.getElementById('edit-files-grid');
    const countEl    = document.getElementById('edit-files-count');
    const progressEl = document.getElementById('edit-upload-progress');
    const progressBar= progressEl?.querySelector('.progress-bar');

    function refreshCount() {
        if (countEl) countEl.textContent = grid.querySelectorAll('[data-file-id]').length;
    }

    grid?.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-edit-remove]');
        if (!btn) return;
        if (!confirm(@json(__('Remove this file?')))) return;
        btn.disabled = true;
        try {
            const res = await fetch(btn.dataset.url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            btn.closest('[data-file-id]').remove();
            refreshCount();
            if (grid.querySelectorAll('[data-file-id]').length === 0 && !grid.querySelector('[data-empty-files]')) {
                grid.insertAdjacentHTML('beforeend',
                    '<div class="col-12" data-empty-files><div class="alert alert-light text-center mb-0">'
                    + '<i class="bx bx-info-circle me-1"></i>' + @json(__('No attachments yet.')) + '</div></div>');
            }
        } catch (err) {
            alert(@json(__('Remove failed')) + ': ' + err.message);
            btn.disabled = false;
        }
    });

    document.querySelectorAll('[data-edit-upload]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0]; if (!file) return;

            progressEl?.classList.remove('d-none');
            if (progressBar) progressBar.style.width = '0%';

            const xhr = new XMLHttpRequest();
            const fd  = new FormData(); fd.append('file', file);
            xhr.open('POST', input.dataset.url);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.onprogress = e => {
                if (e.lengthComputable && progressBar) {
                    progressBar.style.width = Math.round(e.loaded / e.total * 100) + '%';
                }
            };
            xhr.onload = () => {
                input.value = '';
                progressEl?.classList.add('d-none');
                if (xhr.status >= 200 && xhr.status < 300) {
                    location.reload();
                } else {
                    let m = 'HTTP ' + xhr.status;
                    try { m = JSON.parse(xhr.responseText).message || m; } catch (e) {}
                    alert(@json(__('Upload failed')) + ': ' + m);
                }
            };
            xhr.onerror = () => {
                progressEl?.classList.add('d-none');
                alert(@json(__('Upload failed')));
            };
            xhr.send(fd);
        });
    });
});
</script>
@endpush

@endsection
