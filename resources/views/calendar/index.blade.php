@extends('layouts/contentNavbarLayout')

@section('title', __('Calendar'))

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
    .cal-wrap { display:grid; grid-template-columns:280px 1fr; gap:1.25rem; }
    @media (max-width: 992px) { .cal-wrap { grid-template-columns:1fr; } }

    .cal-side-card { background:#fff; border:1px solid #e7e7eb; border-radius:.5rem;
        padding:1rem; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .cal-side-card + .cal-side-card { margin-top:1rem; }

    .btn-add-event { background:#696cff; color:#fff; border:0; padding:.7rem 1rem;
        border-radius:.4rem; font-weight:600; width:100%; display:inline-flex;
        align-items:center; justify-content:center; gap:.4rem; }
    .btn-add-event:hover { background:#5f62f5; color:#fff; }

    /* Mini-calendar */
    #mini-calendar .fc-toolbar-title { font-size:.95rem; }
    #mini-calendar .fc-toolbar { margin-bottom:.5rem; }
    #mini-calendar .fc-button { background:transparent; border:0; color:#6f7180;
        padding:.15rem .35rem; }
    #mini-calendar .fc-button:hover { background:#eef0f5; }
    #mini-calendar .fc-daygrid-day-frame { padding:.15rem; min-height:auto; }
    #mini-calendar .fc-daygrid-day-number { font-size:.8rem; color:#2b2c40; padding:.1rem; }
    #mini-calendar .fc-day-other .fc-daygrid-day-number { color:#c4c6d0; }
    #mini-calendar .fc-daygrid-day.fc-day-today { background:rgba(105,108,255,.1); }
    #mini-calendar .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background:#696cff; color:#fff; border-radius:4px; padding:.1rem .35rem; }
    #mini-calendar .fc-col-header-cell-cushion { font-size:.7rem; color:#6f7180;
        font-weight:600; text-decoration:none; }
    #mini-calendar .fc-daygrid-day-events { display:none; }
    #mini-calendar .fc-daygrid-day { cursor:pointer; }

    /* Filter list */
    .cal-filter-title { font-weight:600; margin-bottom:.5rem; color:#2b2c40; }
    .cal-filter-item { display:flex; align-items:center; gap:.6rem; padding:.4rem 0;
        cursor:pointer; user-select:none; }
    .cal-filter-item input { width:18px; height:18px; cursor:pointer; }
    .cal-filter-item label { cursor:pointer; margin:0; font-size:.9rem; color:#2b2c40; }
    .cal-filter-item.cat-personal input { accent-color:#FF3E1D; }
    .cal-filter-item.cat-business input { accent-color:#03C3EC; }
    .cal-filter-item.cat-family   input { accent-color:#FFAB00; }
    .cal-filter-item.cat-holiday  input { accent-color:#71DD37; }
    .cal-filter-item.cat-etc      input { accent-color:#696CFF; }
    .cal-filter-item.cat-view-all input { accent-color:#6f7180; }

    /* Custom header above main calendar */
    .cal-header { display:flex; align-items:center; gap:.5rem; margin-bottom:1rem;
        flex-wrap:wrap; }
    .cal-nav-btn { background:#fff; border:1px solid #e7e7eb; border-radius:.4rem;
        width:32px; height:32px; display:inline-flex; align-items:center;
        justify-content:center; cursor:pointer; color:#2b2c40; }
    .cal-nav-btn:hover { background:#eef0f5; }
    .cal-today-btn { background:#fff; border:1px solid #e7e7eb; border-radius:.4rem;
        padding:.4rem .9rem; cursor:pointer; color:#2b2c40; font-weight:500;
        margin-left:.25rem; transition:all .15s; }
    .cal-today-btn:hover { background:#696cff; color:#fff; border-color:#696cff; }
    .cal-title { font-size:1.6rem; font-weight:600; color:#2b2c40; margin:0 .5rem; }
    .cal-view-switch { display:inline-flex; background:#eef0ff; border-radius:.4rem;
        overflow:hidden; margin-left:auto; }
    .cal-view-switch button { background:transparent; border:0; padding:.5rem 1rem;
        color:#696cff; font-weight:500; cursor:pointer; }
    .cal-view-switch button.active { background:#696cff; color:#fff; }

    /* Main calendar styling tweaks */
    #calendar { background:#fff; border:1px solid #e7e7eb; border-radius:.5rem;
        padding:1rem; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    #calendar .fc-col-header-cell-cushion { color:#6f7180; font-weight:600;
        text-transform:capitalize; padding:.75rem 0; text-decoration:none; }
    #calendar .fc-daygrid-day-number { color:#2b2c40; padding:.5rem; text-decoration:none; }
    #calendar .fc-day-other .fc-daygrid-day-number { color:#c4c6d0; }
    #calendar .fc-day-today { background:rgba(105,108,255,.05) !important; }
    #calendar .fc-event { border-radius:.3rem; padding:.15rem .35rem; font-size:.78rem;
        border:0; }

    /* Hide events by category via toggle classes */
    .cal-hide-personal .fc-event[data-cat="personal"] { display:none; }
    .cal-hide-business .fc-event[data-cat="business"] { display:none; }
    .cal-hide-family   .fc-event[data-cat="family"]   { display:none; }
    .cal-hide-holiday  .fc-event[data-cat="holiday"]  { display:none; }
    .cal-hide-etc      .fc-event[data-cat="etc"]      { display:none; }
</style>
@endsection

@section('content')
<div class="cal-wrap">

    {{-- ============================ SIDEBAR ============================ --}}
    <aside>
        <div class="cal-side-card">
            <button type="button" class="btn-add-event" id="btn-add-event">
                <i class="bx bx-plus"></i> {{ __('Add Event') }}
            </button>
        </div>

        <div class="cal-side-card">
            <div id="mini-calendar"></div>
        </div>

        <div class="cal-side-card">
            <div class="cal-filter-title">{{ __('Event Filters') }}</div>

            <div class="cal-filter-item cat-view-all">
                <input type="checkbox" id="filter-view-all" checked>
                <label for="filter-view-all">{{ __('View All') }}</label>
            </div>
            <div class="cal-filter-item cat-personal">
                <input type="checkbox" id="filter-personal" data-filter-cat="personal" checked>
                <label for="filter-personal">{{ __('Personal') }}</label>
            </div>
            <div class="cal-filter-item cat-business">
                <input type="checkbox" id="filter-business" data-filter-cat="business" checked>
                <label for="filter-business">{{ __('Business') }}</label>
            </div>
            <div class="cal-filter-item cat-family">
                <input type="checkbox" id="filter-family" data-filter-cat="family" checked>
                <label for="filter-family">{{ __('Family') }}</label>
            </div>
            <div class="cal-filter-item cat-holiday">
                <input type="checkbox" id="filter-holiday" data-filter-cat="holiday" checked>
                <label for="filter-holiday">{{ __('Holiday') }}</label>
            </div>
            <div class="cal-filter-item cat-etc">
                <input type="checkbox" id="filter-etc" data-filter-cat="etc" checked>
                <label for="filter-etc">{{ __('Etc') }}</label>
            </div>
        </div>
    </aside>

    {{-- ============================ MAIN CALENDAR ============================ --}}
    <main>
        <div class="cal-header">
            <button type="button" class="cal-nav-btn" id="cal-prev" aria-label="Previous">
                <i class="bx bx-chevron-left"></i>
            </button>
            <button type="button" class="cal-nav-btn" id="cal-next" aria-label="Next">
                <i class="bx bx-chevron-right"></i>
            </button>
            <button type="button" class="cal-today-btn" id="cal-today">{{ __('Today') }}</button>
            <h2 class="cal-title" id="calendar-title">&nbsp;</h2>

            <div class="cal-view-switch" role="tablist">
                <button type="button" data-view="dayGridMonth" class="active">{{ __('Month') }}</button>
                <button type="button" data-view="timeGridWeek">{{ __('Week') }}</button>
                <button type="button" data-view="timeGridDay">{{ __('Day') }}</button>
                <button type="button" data-view="listMonth">{{ __('List') }}</button>
            </div>
        </div>

        <div id="calendar"></div>
    </main>
</div>

{{-- ============================ EVENT MODAL ============================ --}}
<div class="modal fade" id="event-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="event-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="event-modal-title">{{ __('Add Event') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="ev-id">

                    <div class="mb-3">
                        <label for="ev-title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ev-title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="ev-category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="ev-category" name="category" required>
                            <option value="personal">{{ __('Personal') }}</option>
                            <option value="business">{{ __('Business') }}</option>
                            <option value="family">{{ __('Family') }}</option>
                            <option value="holiday">{{ __('Holiday') }}</option>
                            <option value="etc" selected>{{ __('Etc') }}</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ev-start" class="form-label">{{ __('Start') }} <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="ev-start" name="start" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ev-end" class="form-label">{{ __('End') }}</label>
                            <input type="datetime-local" class="form-control" id="ev-end" name="end">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="ev-all-day" name="all_day">
                        <label for="ev-all-day" class="form-check-label">{{ __('All day') }}</label>
                    </div>

                    <div class="mb-3">
                        <label for="ev-url" class="form-label">
                            {{ __('URL') }}
                            <small class="text-muted">({{ __('optional — https:// added automatically') }})</small>
                        </label>
                        <input type="text" inputmode="url" class="form-control" id="ev-url" name="url"
                               placeholder="e.g. example.com or https://example.com/page">
                    </div>

                    <div class="mb-1">
                        <label for="ev-description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" id="ev-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-danger d-none" id="ev-delete">
                        <i class="bx bx-trash me-1"></i>{{ __('Delete') }}
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>{{ __('Save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $monthLongLabels = [
        __('January'), __('February'), __('March'),     __('April'),
        __('May'),     __('June'),     __('July'),      __('August'),
        __('September'), __('October'), __('November'), __('December'),
    ];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
/* ============================================================
 | Wait for both FullCalendar and Bootstrap before booting.
 | They both load asynchronously; without this guard the first
 | failed lookup (bootstrap.Modal) silently kills every handler.
 | ============================================================ */
(function bootCalendar(retries) {
    if (!window.FullCalendar || !window.bootstrap || !window.bootstrap.Modal) {
        if (retries > 0) return setTimeout(() => bootCalendar(retries - 1), 50);
        console.error('[Calendar] Dependencies failed to load:', {
            FullCalendar: !!window.FullCalendar,
            bootstrap:    !!window.bootstrap,
        });
        return;
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendar, { once: true });
    } else {
        initCalendar();
    }
})(40);  // ~2s of retries

function initCalendar() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const monthLabels = @json($monthLongLabels);
    const TXT = {
        addEvent:  @json(__('Add Event')),
        editEvent: @json(__('Edit Event')),
        deleteQ:   @json(__('Delete event?')),
        saved:     @json(__('Event saved')),
        deleted:   @json(__('Event deleted')),
        saveFail:  @json(__('Save failed')),
        delFail:   @json(__('Delete failed')),
    };

    /* ============================================================
     | Helpers
     | ============================================================ */
    function toast(message, kind = 'ok') {
        let wrap = document.getElementById('cal-toasts');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'cal-toasts';
            wrap.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;';
            document.body.appendChild(wrap);
        }
        const el = document.createElement('div');
        el.textContent = message;
        el.style.cssText = 'padding:.7rem 1rem;border-radius:8px;color:#fff;font-weight:500;'
            + 'box-shadow:0 10px 30px -10px rgba(0,0,0,.25);min-width:200px;'
            + 'background:' + (kind === 'err' ? '#FF3E1D' : '#71DD37') + ';';
        wrap.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

    function toLocalInput(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        const pad = n => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
             + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function formatRange(view) {
        const s = view.currentStart, e = new Date(view.currentEnd - 1);
        if (view.type === 'dayGridMonth') {
            return monthLabels[s.getMonth()] + ' ' + s.getFullYear();
        }
        if (view.type === 'timeGridDay') {
            return monthLabels[s.getMonth()] + ' ' + s.getDate() + ', ' + s.getFullYear();
        }
        // week or list — show range like "May 17 – 23, 2026"
        if (s.getMonth() === e.getMonth()) {
            return monthLabels[s.getMonth()] + ' ' + s.getDate() + ' – ' + e.getDate() + ', ' + s.getFullYear();
        }
        return monthLabels[s.getMonth()] + ' ' + s.getDate() + ' – '
             + monthLabels[e.getMonth()] + ' ' + e.getDate() + ', ' + s.getFullYear();
    }

    /* ============================================================
     | MAIN CALENDAR
     | ============================================================ */
    const calendarEl = document.getElementById('calendar');
    const titleEl    = document.getElementById('calendar-title');
    const viewSwitch = document.querySelectorAll('.cal-view-switch button');
    let miniCalendar = null;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: false,
        height: 'auto',
        firstDay: 0,
        nowIndicator: true,
        editable: true,
        selectable: true,
        dayMaxEvents: 3,
        navLinks: true,                          // click day-number to drill into Day view
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
        scrollTime: '08:00:00',                  // time-grid views start scrolled to 8am
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        events: @json(route('calendar.events')),

        eventDidMount(info) {
            info.el.dataset.cat = info.event.extendedProps.category || 'etc';
            const desc = info.event.extendedProps.description;
            if (desc) info.el.title = info.event.title + '\n' + desc;
        },

        datesSet(info) {
            titleEl.textContent = formatRange(info.view);
            if (miniCalendar) miniCalendar.gotoDate(info.view.currentStart);
        },

        select(info) {
            openModalForCreate(info.startStr, info.endStr, info.allDay);
        },

        eventClick(info) {
            info.jsEvent.preventDefault();
            openModalForEdit(info.event);
        },

        eventDrop:   info => saveEventChange(info.event),
        eventResize: info => saveEventChange(info.event),
    });
    calendar.render();

    /* ============================================================
     | MINI CALENDAR (date-picker only)
     | ============================================================ */
    miniCalendar = new FullCalendar.Calendar(document.getElementById('mini-calendar'), {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: { left: 'prev', center: 'title', right: 'next' },
        titleFormat: { year: 'numeric', month: 'long' },
        dateClick(info) {
            calendar.gotoDate(info.date);
            calendar.changeView('timeGridDay');
            setActiveView('timeGridDay');
        },
        selectable: false,
    });
    miniCalendar.render();

    /* ============================================================
     | HEADER BUTTONS
     | ============================================================ */
    document.getElementById('cal-prev').addEventListener('click', () => calendar.prev());
    document.getElementById('cal-next').addEventListener('click', () => calendar.next());

    const btnToday = document.getElementById('cal-today');
    if (btnToday) btnToday.addEventListener('click', () => calendar.today());

    function setActiveView(view) {
        viewSwitch.forEach(b => b.classList.toggle('active', b.dataset.view === view));
    }

    viewSwitch.forEach(btn => {
        btn.addEventListener('click', () => {
            const v = btn.dataset.view;
            calendar.changeView(v);
            setActiveView(v);
        });
    });

    /* ============================================================
     | CATEGORY FILTERS (pure CSS toggles, no refetch)
     | ============================================================ */
    const categoryBoxes = document.querySelectorAll('[data-filter-cat]');
    const viewAllBox    = document.getElementById('filter-view-all');

    function applyFilters() {
        categoryBoxes.forEach(box => {
            calendarEl.classList.toggle('cal-hide-' + box.dataset.filterCat, !box.checked);
        });
        viewAllBox.checked = Array.from(categoryBoxes).every(b => b.checked);
    }
    categoryBoxes.forEach(box => box.addEventListener('change', applyFilters));
    viewAllBox.addEventListener('change', () => {
        categoryBoxes.forEach(b => { b.checked = viewAllBox.checked; });
        applyFilters();
    });

    /* ============================================================
     | EVENT MODAL
     | ============================================================ */
    const modalEl     = document.getElementById('event-modal');
    const modal       = new bootstrap.Modal(modalEl);
    const modalTitle  = document.getElementById('event-modal-title');
    const form        = document.getElementById('event-form');
    const fId         = document.getElementById('ev-id');
    const fTitle      = document.getElementById('ev-title');
    const fCategory   = document.getElementById('ev-category');
    const fStart      = document.getElementById('ev-start');
    const fEnd        = document.getElementById('ev-end');
    const fAllDay     = document.getElementById('ev-all-day');
    const fUrl        = document.getElementById('ev-url');
    const fDesc       = document.getElementById('ev-description');
    const btnDelete   = document.getElementById('ev-delete');
    const timeRow     = fStart.closest('.row');

    // All-day toggle: convert time inputs to date-only and back
    fAllDay.addEventListener('change', () => {
        const newType = fAllDay.checked ? 'date' : 'datetime-local';
        if (fStart.type !== newType) {
            fStart.value = fStart.value.split('T')[0];
            fEnd.value   = fEnd.value.split('T')[0];
        }
        fStart.type = newType;
        fEnd.type   = newType;
    });

    function snapToHour(d) {
        const out = new Date(d);
        out.setMinutes(0, 0, 0);
        if (out <= d) out.setHours(out.getHours() + 1);
        return out;
    }

    function openModalForCreate(startStr, endStr, allDay) {
        form.reset();
        fId.value       = '';
        modalTitle.textContent = TXT.addEvent;
        btnDelete.classList.add('d-none');
        fAllDay.checked = !!allDay;
        fStart.type = fAllDay.checked ? 'date' : 'datetime-local';
        fEnd.type   = fAllDay.checked ? 'date' : 'datetime-local';

        let start = startStr ? new Date(startStr) : snapToHour(new Date());
        let end   = endStr   ? new Date(endStr)   : new Date(start.getTime() + 60*60*1000);

        if (fAllDay.checked) {
            fStart.value = startStr ? startStr.split('T')[0] : toLocalInput(start.toISOString()).split('T')[0];
            fEnd.value   = endStr   ? endStr.split('T')[0]   : '';
        } else {
            fStart.value = toLocalInput(start.toISOString());
            fEnd.value   = toLocalInput(end.toISOString());
        }
        fCategory.value = 'etc';
        modal.show();
        setTimeout(() => fTitle.focus(), 250);
    }

    function openModalForEdit(ev) {
        form.reset();
        fId.value       = ev.id;
        modalTitle.textContent = TXT.editEvent;
        btnDelete.classList.remove('d-none');
        fTitle.value    = ev.title;
        fCategory.value = ev.extendedProps.category || 'etc';
        fAllDay.checked = !!ev.allDay;
        fStart.type = fAllDay.checked ? 'date' : 'datetime-local';
        fEnd.type   = fAllDay.checked ? 'date' : 'datetime-local';

        const startVal = ev.start ? toLocalInput(ev.start.toISOString()) : '';
        const endVal   = ev.end   ? toLocalInput(ev.end.toISOString())   : '';
        fStart.value = fAllDay.checked ? startVal.split('T')[0] : startVal;
        fEnd.value   = fAllDay.checked ? endVal.split('T')[0]   : endVal;

        fUrl.value      = ev.url || '';
        fDesc.value     = ev.extendedProps.description || '';
        modal.show();
        setTimeout(() => fTitle.focus(), 250);
    }

    document.getElementById('btn-add-event').addEventListener('click', () => {
        openModalForCreate(null, null, false);
    });

    function normalizeUrl(raw) {
        if (!raw) return null;
        const v = raw.trim();
        if (!v) return null;
        // If it already has a scheme like http://, https://, ftp://, mailto:, tel: → leave it.
        if (/^[a-z][a-z0-9+.-]*:/i.test(v)) return v;
        // Otherwise prepend https://
        return 'https://' + v;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = fId.value;
        const normalizedUrl = normalizeUrl(fUrl.value);
        if (normalizedUrl !== null) fUrl.value = normalizedUrl;   // reflect the fix in the field
        const payload = {
            title:       fTitle.value,
            category:    fCategory.value,
            start:       fStart.value,
            end:         fEnd.value || null,
            all_day:     fAllDay.checked,
            url:         normalizedUrl,
            description: fDesc.value || null,
        };
        const url    = id ? `/calendar/events/${id}` : `/calendar/events`;
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-TOKEN':     CSRF,
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
            if (!res.ok) {
                const j = await res.json().catch(() => ({}));
                const msg = j.errors ? Object.values(j.errors).flat().join('\n')
                                     : (j.message || ('HTTP ' + res.status));
                toast(TXT.saveFail + ': ' + msg, 'err');
                return;
            }
            modal.hide();
            calendar.refetchEvents();
            toast(TXT.saved);
        } catch (err) {
            toast(TXT.saveFail + ': ' + err.message, 'err');
        }
    });

    btnDelete.addEventListener('click', async () => {
        const id = fId.value;
        if (!id) return;
        if (!confirm(TXT.deleteQ)) return;
        try {
            const res = await fetch(`/calendar/events/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json',
                           'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            modal.hide();
            calendar.refetchEvents();
            toast(TXT.deleted);
        } catch (err) {
            toast(TXT.delFail + ': ' + err.message, 'err');
        }
    });

    async function saveEventChange(ev) {
        try {
            const payload = {
                title:       ev.title,
                category:    ev.extendedProps.category || 'etc',
                start:       ev.start.toISOString(),
                end:         ev.end ? ev.end.toISOString() : null,
                all_day:     ev.allDay,
                url:         ev.url || null,
                description: ev.extendedProps.description || null,
            };
            const res = await fetch(`/calendar/events/${ev.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-TOKEN':     CSRF,
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
            if (!res.ok) {
                calendar.refetchEvents();
                toast(TXT.saveFail, 'err');
            } else {
                toast(TXT.saved);
            }
        } catch (err) {
            calendar.refetchEvents();
            toast(TXT.saveFail + ': ' + err.message, 'err');
        }
    }

    console.log('[Calendar] Initialized successfully.');
}
</script>
@endpush
