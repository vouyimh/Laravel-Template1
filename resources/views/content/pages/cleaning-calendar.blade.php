@extends('layouts/contentNavbarLayout')

@section('title', __('Cleaning Calendar'))

@php
    $ccClientsData = $clients->map(function ($c) {
        return [
            'id'     => $c->client_id,
            'name'   => $c->company_name,
            'houses' => $c->houses->map(fn ($h) => ['id' => $h->id, 'address' => $h->house_address])->values(),
        ];
    })->filter(fn ($c) => count($c['houses']))->values();
@endphp

@section('content')
<style>
    .cc-header { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .cc-title-block { margin-right:auto; }
    .cc-eyebrow { font-size:.75rem; letter-spacing:.06em; text-transform:uppercase; color:#8a8d93; margin:0; }
    .cc-title { font-size:1.6rem; font-weight:600; color:#2b2c40; margin:0; }

    .cc-nav-btn { background:#fff; border:1px solid #e7e7eb; border-radius:.4rem;
        width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;
        cursor:pointer; color:#2b2c40; }
    .cc-nav-btn:hover { background:#eef0f5; }

    .cc-stats { display:flex; gap:2rem; flex-wrap:wrap; margin-bottom:1.5rem; }
    .cc-stat { display:flex; align-items:center; gap:.75rem; }
    .cc-stat-icon { width:44px; height:44px; border-radius:50%; display:flex; align-items:center;
        justify-content:center; color:#fff; font-size:1.1rem; flex-shrink:0; }
    .cc-stat-num { font-size:1.3rem; font-weight:600; color:#2b2c40; }
    .cc-stat-label { font-size:.78rem; letter-spacing:.04em; text-transform:uppercase; color:#8a8d93; }

    .cc-scroll { overflow-x:auto; border:1px solid #e7e7eb; border-radius:.5rem; background:#fff;
        box-shadow:0 1px 3px rgba(0,0,0,.04); }

    .cc-grid { display:grid; grid-auto-rows:52px; min-width:max-content; }

    .cc-cell-house { grid-column:1; position:sticky; left:0; background:#fff; z-index:2;
        border-right:1px solid #e7e7eb; border-bottom:1px solid #f0f0f2;
        display:flex; flex-direction:column; justify-content:center; padding:.35rem .9rem; }
    .cc-cell-house .cc-house-address { font-size:.85rem; font-weight:600; color:#2b2c40; }
    .cc-cell-house .cc-house-client { font-size:.72rem; color:#8a8d93; }

    .cc-group-header { grid-column:1/-1; background:#eef0ff; color:#4b4dab; font-weight:600;
        font-size:.8rem; padding:.5rem .9rem; display:flex; align-items:center; gap:.4rem;
        position:sticky; left:0; }

    .cc-head-house { grid-row:1; grid-column:1; position:sticky; left:0; top:0; background:#fff;
        z-index:3; border-right:1px solid #e7e7eb; border-bottom:2px solid #e7e7eb; }
    .cc-head-day { grid-row:1; position:sticky; top:0; background:#fdf6e3; z-index:1;
        border-bottom:2px solid #e7e7eb; border-right:1px solid #f0f0f2;
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        font-size:.72rem; color:#6f7180; }
    .cc-head-day.cc-weekend { background:#f5eefc; }
    .cc-head-day .cc-dow { text-transform:uppercase; letter-spacing:.04em; font-weight:600; }
    .cc-head-day .cc-dom { color:#2b2c40; font-size:.85rem; }

    .cc-day-cell { border-right:1px solid #f0f0f2; border-bottom:1px solid #f0f0f2; cursor:pointer; }
    .cc-day-cell:hover { background:#f7f8fb; }
    .cc-day-cell.cc-weekend { background:rgba(105,108,255,.04); }

    .cc-bar { display:flex; align-items:center; padding:0 .6rem; border-radius:.35rem; color:#fff;
        font-size:.78rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        margin:6px 3px; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,.15); }
    .cc-bar.status-pending     { background:#FF3E1D; }
    .cc-bar.status-in_progress { background:#FFAB00; }
    .cc-bar.status-completed   { background:#71DD37; }

    .cc-legend { display:flex; gap:1.25rem; margin-top:1rem; flex-wrap:wrap; }
    .cc-legend-item { display:flex; align-items:center; gap:.4rem; font-size:.8rem; color:#6f7180; }
    .cc-legend-dot { width:12px; height:12px; border-radius:3px; display:inline-block; }
</style>
<div class="cc-header">
    <div class="cc-title-block">
        <p class="cc-eyebrow">{{ __('Cleaning Schedule') }}</p>
        <h1 class="cc-title">{{ __('Cleaning Calendar') }}</h1>
    </div>

    <select id="cc-client-filter" class="form-select" style="width:auto;">
        <option value="">{{ __('All Clients') }}</option>
        @foreach($clients as $client)
            @if($client->houses->count())
                <option value="{{ $client->client_id }}">{{ $client->company_name }}</option>
            @endif
        @endforeach
    </select>

    <button type="button" class="cc-nav-btn" id="cc-prev"><i class="bx bx-chevron-left"></i></button>

    <input type="date" id="cc-start-date" class="form-control" style="width:auto;">

    <select id="cc-weeks" class="form-select" style="width:auto;">
        <option value="1">{{ __('1 week') }}</option>
        <option value="2" selected>{{ __('2 weeks') }}</option>
        <option value="4">{{ __('4 weeks') }}</option>
    </select>

    <button type="button" class="cc-nav-btn" id="cc-next"><i class="bx bx-chevron-right"></i></button>

    <button type="button" class="btn btn-primary" id="cc-add-task">
        <i class="bx bx-plus"></i> {{ __('Add Cleaning Task') }}
    </button>
</div>

<div class="cc-stats">
    <div class="cc-stat">
        <div class="cc-stat-icon" style="background:#FF3E1D;"><i class="bx bx-brush"></i></div>
        <div>
            <div class="cc-stat-num" id="cc-stat-pending">0</div>
            <div class="cc-stat-label">{{ __('Needs Cleaning') }}</div>
        </div>
    </div>
    <div class="cc-stat">
        <div class="cc-stat-icon" style="background:#FFAB00;"><i class="bx bx-loader-circle"></i></div>
        <div>
            <div class="cc-stat-num" id="cc-stat-in_progress">0</div>
            <div class="cc-stat-label">{{ __('In Progress') }}</div>
        </div>
    </div>
    <div class="cc-stat">
        <div class="cc-stat-icon" style="background:#71DD37;"><i class="bx bx-check"></i></div>
        <div>
            <div class="cc-stat-num" id="cc-stat-completed">0</div>
            <div class="cc-stat-label">{{ __('Done') }}</div>
        </div>
    </div>
    <div class="cc-stat">
        <div class="cc-stat-icon" style="background:#696cff;"><i class="bx bx-home"></i></div>
        <div>
            <div class="cc-stat-num" id="cc-stat-houses">0</div>
            <div class="cc-stat-label">{{ __('Houses') }}</div>
        </div>
    </div>
</div>

<div class="cc-scroll">
    <div class="cc-grid" id="cc-grid"></div>
</div>

<div class="cc-legend">
    <div class="cc-legend-item"><span class="cc-legend-dot" style="background:#FF3E1D;"></span> {{ __('Needs Cleaning') }}</div>
    <div class="cc-legend-item"><span class="cc-legend-dot" style="background:#FFAB00;"></span> {{ __('In Progress') }}</div>
    <div class="cc-legend-item"><span class="cc-legend-dot" style="background:#71DD37;"></span> {{ __('Done') }}</div>
</div>

{{-- ============================ MODAL ============================ --}}
<div class="modal fade" id="cc-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cc-modal-title">{{ __('New Cleaning Task') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cc-modal-error" class="alert alert-danger d-none"></div>

                <input type="hidden" id="cc-task-id">

                <div class="mb-3">
                    <label class="form-label">{{ __('House') }}</label>
                    <select id="cc-house-id" class="form-select"></select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Title') }}</label>
                    <input type="text" id="cc-task-title" class="form-control" value="{{ __('Clean house') }}">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('Start Date') }}</label>
                        <input type="date" id="cc-task-start" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">{{ __('End Date') }}</label>
                        <input type="date" id="cc-task-end" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Assign Staff') }}</label>
                    <select id="cc-task-assignee" class="form-select">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select id="cc-task-status" class="form-select">
                        <option value="pending">{{ __('Needs Cleaning') }}</option>
                        <option value="in_progress">{{ __('In Progress') }}</option>
                        <option value="completed">{{ __('Done') }}</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea id="cc-task-description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger me-auto d-none" id="cc-delete-task">{{ __('Delete') }}</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="cc-save-task">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* Bootstrap's JS bundle loads as an async Vite module; without this guard
   the first bootstrap.Modal lookup can run before it's ready. */
(function boot(retries) {
    if (!window.bootstrap || !window.bootstrap.Modal) {
        if (retries > 0) return setTimeout(() => boot(retries - 1), 50);
        console.error('[CleaningCalendar] bootstrap failed to load');
        return;
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init(), { once: true });
    } else {
        init();
    }
})(40);

function init() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const CLIENTS = @json($ccClientsData);

    const DOW = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    const grid          = document.getElementById('cc-grid');
    const clientFilter   = document.getElementById('cc-client-filter');
    const startInput     = document.getElementById('cc-start-date');
    const weeksSelect     = document.getElementById('cc-weeks');
    const prevBtn         = document.getElementById('cc-prev');
    const nextBtn         = document.getElementById('cc-next');
    const addTaskBtn      = document.getElementById('cc-add-task');

    const modalEl   = document.getElementById('cc-modal');
    const modal     = new bootstrap.Modal(modalEl);
    const houseSelect     = document.getElementById('cc-house-id');
    const taskIdInput      = document.getElementById('cc-task-id');
    const taskTitleInput   = document.getElementById('cc-task-title');
    const taskStartInput   = document.getElementById('cc-task-start');
    const taskEndInput     = document.getElementById('cc-task-end');
    const taskAssigneeSel  = document.getElementById('cc-task-assignee');
    const taskStatusSel    = document.getElementById('cc-task-status');
    const taskDescInput    = document.getElementById('cc-task-description');
    const modalTitle       = document.getElementById('cc-modal-title');
    const modalError       = document.getElementById('cc-modal-error');
    const deleteBtn        = document.getElementById('cc-delete-task');
    const saveBtn          = document.getElementById('cc-save-task');

    // All dates are treated as plain UTC calendar days (matching the backend's
    // timezone-less DATE columns) so the grid renders identically regardless
    // of the viewer's local timezone.
    function toDateStr(d) {
        return d.toISOString().slice(0, 10);
    }

    function addDays(dateStr, n) {
        const d = new Date(dateStr + 'T00:00:00Z');
        d.setUTCDate(d.getUTCDate() + n);
        return d;
    }

    function currentClients() {
        const clientId = clientFilter.value;
        if (!clientId) return CLIENTS;
        return CLIENTS.filter(c => String(c.id) === clientId);
    }

    function houseRows() {
        const rows = [];
        currentClients().forEach(c => {
            c.houses.forEach(h => rows.push({ ...h, clientName: c.name }));
        });
        return rows;
    }

    function populateHouseSelect() {
        houseSelect.innerHTML = '';
        currentClients().forEach(c => {
            const group = document.createElement('optgroup');
            group.label = c.name;
            c.houses.forEach(h => {
                const opt = document.createElement('option');
                opt.value = h.id;
                opt.textContent = h.address;
                group.appendChild(opt);
            });
            houseSelect.appendChild(group);
        });
    }

    function statusLabelCounts(tasks) {
        return {
            pending:     tasks.filter(t => t.status === 'pending').length,
            in_progress: tasks.filter(t => t.status === 'in_progress').length,
            completed:   tasks.filter(t => t.status === 'completed').length,
        };
    }

    async function fetchEvents(start, end) {
        const params = new URLSearchParams({ start, end });
        const clientId = clientFilter.value;
        if (clientId) params.set('client_id', clientId);

        const res = await fetch(`{{ route('admin.cleaning-calendar.events') }}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) return [];
        return res.json();
    }

    async function render() {
        const start = startInput.value;
        const weeks = parseInt(weeksSelect.value, 10);
        const dayCount = weeks * 7;
        const end = toDateStr(addDays(start, dayCount - 1));

        const rows  = houseRows();
        const tasks = await fetchEvents(start, end);

        document.getElementById('cc-stat-houses').textContent = rows.length;
        const counts = statusLabelCounts(tasks);
        document.getElementById('cc-stat-pending').textContent     = counts.pending;
        document.getElementById('cc-stat-in_progress').textContent = counts.in_progress;
        document.getElementById('cc-stat-completed').textContent   = counts.completed;

        grid.innerHTML = '';
        grid.style.gridTemplateColumns = `240px repeat(${dayCount}, minmax(80px, 1fr))`;

        // Header row
        const headHouse = document.createElement('div');
        headHouse.className = 'cc-head-house';
        grid.appendChild(headHouse);

        for (let i = 0; i < dayCount; i++) {
            const d = addDays(start, i);
            const cell = document.createElement('div');
            const isWeekend = d.getUTCDay() === 0 || d.getUTCDay() === 6;
            cell.className = 'cc-head-day' + (isWeekend ? ' cc-weekend' : '');
            cell.style.gridColumn = (i + 2);
            cell.innerHTML = `<span class="cc-dow">${DOW[d.getUTCDay()]}</span><span class="cc-dom">${d.getUTCDate()}</span>`;
            grid.appendChild(cell);
        }

        let rowIndex = 2; // row 1 is header
        let lastClient = null;

        rows.forEach(house => {
            if (house.clientName !== lastClient && !clientFilter.value) {
                const groupRow = document.createElement('div');
                groupRow.className = 'cc-group-header';
                groupRow.style.gridRow = rowIndex;
                groupRow.innerHTML = `<i class='bx bx-buildings'></i> ${house.clientName}`;
                grid.appendChild(groupRow);
                rowIndex++;
                lastClient = house.clientName;
            }

            const houseCell = document.createElement('div');
            houseCell.className = 'cc-cell-house';
            houseCell.style.gridRow = rowIndex;
            houseCell.innerHTML = `<span class="cc-house-address">${house.address}</span>
                                    <span class="cc-house-client">${house.clientName}</span>`;
            grid.appendChild(houseCell);

            for (let i = 0; i < dayCount; i++) {
                const d = addDays(start, i);
                const isWeekend = d.getUTCDay() === 0 || d.getUTCDay() === 6;
                const dayCell = document.createElement('div');
                dayCell.className = 'cc-day-cell' + (isWeekend ? ' cc-weekend' : '');
                dayCell.style.gridRow = rowIndex;
                dayCell.style.gridColumn = (i + 2);
                dayCell.dataset.houseId = house.id;
                dayCell.dataset.date = toDateStr(d);
                dayCell.addEventListener('click', () => openCreateModal(house.id, dayCell.dataset.date));
                grid.appendChild(dayCell);
            }

            tasks.filter(t => String(t.client_house_id) === String(house.id)).forEach(task => {
                const taskStartIdx = Math.round((new Date(task.due_date) - new Date(start)) / 86400000);
                const taskEndIdx   = Math.round((new Date(task.end_date) - new Date(start)) / 86400000);
                const from = Math.max(taskStartIdx, 0);
                const to   = Math.min(taskEndIdx, dayCount - 1);
                if (to < 0 || from > dayCount - 1) return;

                const bar = document.createElement('div');
                bar.className = `cc-bar status-${task.status}`;
                bar.style.gridRow = rowIndex;
                bar.style.gridColumn = `${from + 2} / ${to + 3}`;
                bar.textContent = task.assignee_name ? `${task.title} — ${task.assignee_name}` : task.title;
                bar.addEventListener('click', (e) => { e.stopPropagation(); openEditModal(task); });
                grid.appendChild(bar);
            });

            rowIndex++;
        });
    }

    function resetModal() {
        modalError.classList.add('d-none');
        modalError.textContent = '';
        taskIdInput.value = '';
        taskTitleInput.value = '{{ __('Clean house') }}';
        taskAssigneeSel.value = '';
        taskStatusSel.value = 'pending';
        taskDescInput.value = '';
        deleteBtn.classList.add('d-none');
    }

    function openCreateModal(houseId, dateStr) {
        resetModal();
        modalTitle.textContent = '{{ __('New Cleaning Task') }}';
        populateHouseSelect();
        houseSelect.value = houseId;
        taskStartInput.value = dateStr;
        taskEndInput.value = dateStr;
        modal.show();
    }

    function openEditModal(task) {
        resetModal();
        modalTitle.textContent = '{{ __('Edit Cleaning Task') }}';
        populateHouseSelect();
        taskIdInput.value = task.id;
        houseSelect.value = task.client_house_id;
        taskTitleInput.value = task.title;
        taskStartInput.value = task.due_date;
        taskEndInput.value = task.end_date;
        taskAssigneeSel.value = task.assignee_id ?? '';
        taskStatusSel.value = task.status;
        taskDescInput.value = task.description ?? '';
        deleteBtn.classList.remove('d-none');
        modal.show();
    }

    addTaskBtn.addEventListener('click', () => openCreateModal(null, startInput.value));

    saveBtn.addEventListener('click', async () => {
        modalError.classList.add('d-none');

        const payload = {
            client_house_id: houseSelect.value,
            title:           taskTitleInput.value,
            description:     taskDescInput.value,
            status:          taskStatusSel.value,
            due_date:        taskStartInput.value,
            end_date:        taskEndInput.value || taskStartInput.value,
            assignee_id:     taskAssigneeSel.value || null,
        };

        const id = taskIdInput.value;
        const url = id
            ? `{{ url('admin/cleaning-calendar/tasks') }}/${id}`
            : `{{ route('admin.cleaning-calendar.store') }}`;

        const res = await fetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     CSRF,
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            const body = await res.json().catch(() => ({}));
            const firstError = body.errors ? Object.values(body.errors)[0][0] : (body.message || 'Something went wrong.');
            modalError.textContent = firstError;
            modalError.classList.remove('d-none');
            return;
        }

        modal.hide();
        render();
    });

    deleteBtn.addEventListener('click', async () => {
        const id = taskIdInput.value;
        if (!id || !confirm('{{ __('Delete this cleaning task?') }}')) return;

        await fetch(`{{ url('admin/cleaning-calendar/tasks') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        modal.hide();
        render();
    });

    prevBtn.addEventListener('click', () => {
        const weeks = parseInt(weeksSelect.value, 10);
        startInput.value = toDateStr(addDays(startInput.value, -weeks * 7));
        render();
    });

    nextBtn.addEventListener('click', () => {
        const weeks = parseInt(weeksSelect.value, 10);
        startInput.value = toDateStr(addDays(startInput.value, weeks * 7));
        render();
    });

    weeksSelect.addEventListener('change', render);
    clientFilter.addEventListener('change', render);

    // Use the viewer's local calendar day for "today" (not UTC), then treat
    // it as a UTC-normalized date for all grid math from here on.
    const now = new Date();
    startInput.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    render();
}
</script>
@endpush
