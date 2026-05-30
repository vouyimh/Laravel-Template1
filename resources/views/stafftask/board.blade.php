@extends('layouts/contentNavbarLayout')

@php
    $user        = auth()->user();
    $role        = $user->role ?? 'staff';
    $isAdmin     = $role === 'admin';
    $isStaff     = $role === 'staff';
    $totalCount  = $tasks->count();
    $todoCount   = $tasks->where('status', 'pending')->count();
    $progCount   = $tasks->where('status', 'in_progress')->count();
    $doneCount   = $tasks->where('status', 'completed')->count();
@endphp

@section('title', 'StaffTask Board')

@section('content')
<style>
    /* === StaffTask light theme === */
    :root {
        --st-bg:        #f5f5f9;
        --st-bg-2:      #ffffff;
        --st-panel:     #ffffff;
        --st-panel-2:   #fafafc;
        --st-border:    #e7e7eb;
        --st-text:      #2b2c40;
        --st-muted:     #6f7180;
        --st-primary:   #696cff;
        --st-cyan:      #03c3ec;
        --st-orange:    #ffab00;
        --st-red:       #ff3e1d;
        --st-green:     #71dd37;
    }
    /* No html/body override - the Laravel template handles page bg */
    .st-wrap { padding: 0; max-width: 100%; margin: 0; }

    .st-header { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .st-logo { display: flex; align-items: center; gap: .5rem; font-size: 1.5rem; font-weight: 700;
        color: var(--st-primary); }
    .st-logo .ico { width: 36px; height: 36px; border-radius: 8px; background: var(--st-primary);
        color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; }

    .st-pill { display: inline-flex; align-items: center; gap: .4rem; background: var(--st-panel);
        border: 1px solid var(--st-border); color: var(--st-text); padding: .45rem .85rem;
        border-radius: 999px; font-size: .85rem; font-weight: 500;
        box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .st-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--st-green); box-shadow: 0 0 6px var(--st-green); }

    .st-btn { display: inline-flex; align-items: center; gap: .4rem; background: var(--st-panel);
        border: 1px solid var(--st-border); color: var(--st-text); padding: .5rem .9rem;
        border-radius: 8px; font-size: .85rem; cursor: pointer; transition: all .15s;
        text-decoration: none; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .st-btn:hover { background: var(--st-panel-2); color: var(--st-primary); border-color: var(--st-primary); }
    .st-btn-primary { background: var(--st-primary); color: #fff; border: 0; font-weight: 600;
        box-shadow: 0 4px 12px -4px rgba(105,108,255,.5); }
    .st-btn-primary:hover { background: #5d61f5; color: #fff; }
    .st-btn-danger  { background: #fdebe8; color: var(--st-red);    border-color: #fbd5cd; }
    .st-btn-success { background: #e8f9d9; color: #4caf17;          border-color: #d4f0bd; }
    .st-btn-warning { background: #fff2cc; color: #b58200;          border-color: #ffe39d; }
    .st-btn:disabled { opacity: .5; cursor: not-allowed; }

    .st-stats { display: flex; gap: .5rem; margin-left: auto; flex-wrap: wrap; }
    .st-stat { background: var(--st-panel); border: 1px solid var(--st-border);
        padding: .5rem 1rem; border-radius: 999px; font-size: .85rem; color: var(--st-muted);
        box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .st-stat b { color: var(--st-primary); margin-left: .25rem; font-weight: 700; }

    .st-filters { background: var(--st-panel); border: 1px solid var(--st-border);
        border-radius: 14px; padding: 1rem; margin-bottom: 1.25rem; display: grid; gap: .75rem;
        grid-template-columns: 2fr 1fr 1fr 1fr auto; align-items: end;
        box-shadow: 0 2px 6px rgba(0,0,0,.04); }
    @media (max-width: 992px) { .st-filters { grid-template-columns: 1fr 1fr; } }
    .st-field label { display: block; font-size: .75rem; font-weight: 600; color: var(--st-muted);
        text-transform: uppercase; margin-bottom: .35rem; letter-spacing: .04em; }
    .st-input, .st-select { width: 100%; background: #fff; border: 1px solid var(--st-border);
        color: var(--st-text); border-radius: 8px; padding: .55rem .75rem; font-size: .9rem;
        outline: none; transition: border-color .15s, box-shadow .15s; }
    .st-input:focus, .st-select:focus { border-color: var(--st-primary); box-shadow: 0 0 0 3px rgba(105,108,255,.12); }
    .st-input::placeholder { color: #adb0bd; }

    .st-board { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    @media (max-width: 992px) { .st-board { grid-template-columns: 1fr; } }
    .st-col { background: var(--st-panel); border: 1px solid var(--st-border);
        border-radius: 14px; padding: 1rem; min-height: 500px;
        box-shadow: 0 2px 6px rgba(0,0,0,.04); }
    .st-col-head { display: flex; align-items: center; justify-content: space-between;
        padding-bottom: .75rem; margin-bottom: .75rem; border-bottom: 1px solid var(--st-border);
        font-weight: 700; font-size: 1.05rem; color: var(--st-text); }
    .st-col-count { background: var(--st-primary); color: #fff; padding: .15rem .65rem;
        border-radius: 999px; font-size: .8rem; font-weight: 700; }
    .st-col[data-col="in_progress"] .st-col-count { background: var(--st-orange); }
    .st-col[data-col="completed"]   .st-col-count { background: #4caf17; }

    .st-card { background: #fff; border: 1px solid var(--st-border);
        border-left: 4px solid var(--st-primary); border-radius: 10px; padding: .9rem;
        margin-bottom: .75rem; transition: transform .12s, box-shadow .12s;
        box-shadow: 0 1px 3px rgba(0,0,0,.05); }
    .st-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px -6px rgba(0,0,0,.15); }
    .st-card.prio-high   { border-left-color: var(--st-red); }
    .st-card.prio-medium { border-left-color: var(--st-orange); }
    .st-card.prio-low    { border-left-color: #4caf17; }
    .st-card-head { display: flex; justify-content: space-between; align-items: start; gap: .5rem; margin-bottom: .5rem; }
    .st-card-title { font-weight: 700; font-size: 1rem; color: var(--st-text); }
    .st-card-desc  { color: var(--st-muted); font-size: .85rem; margin-bottom: .5rem; line-height: 1.45; }
    .st-badge { display: inline-block; padding: .2rem .55rem; border-radius: 5px;
        font-size: .68rem; font-weight: 800; letter-spacing: .06em; }
    .st-badge-high   { background: #fdebe8; color: var(--st-red); }
    .st-badge-medium { background: #fff2cc; color: #b58200; }
    .st-badge-low    { background: #e8f9d9; color: #4caf17; }

    .st-meta { display: flex; flex-wrap: wrap; gap: .5rem 1rem; font-size: .8rem;
        color: var(--st-muted); margin: .35rem 0 .5rem; }

    .st-assignee { display: inline-flex; align-items: center; gap: .4rem; margin: .35rem 0 .5rem; }
    .st-avatar { width: 26px; height: 26px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; color: #fff; }

    .st-location { background: #ecf6ff; border: 1px solid #c8e3fb;
        color: #1b6caa; padding: .4rem .6rem; border-radius: 6px; font-size: .8rem; margin: .35rem 0; }

    .st-files { display: flex; flex-wrap: wrap; gap: .35rem; margin: .5rem 0; }
    .st-file { position: relative; width: 56px; height: 56px; border-radius: 6px;
        overflow: hidden; border: 1px solid var(--st-border); background: #f5f5f9; }
    .st-file img, .st-file video { width: 100%; height: 100%; object-fit: cover; }
    .st-file .vid { display: flex; flex-direction: column; align-items: center; justify-content: center;
        width: 100%; height: 100%; color: var(--st-primary); font-size: .65rem; }
    .st-file .rm { position: absolute; top: 2px; right: 2px; width: 18px; height: 18px;
        border-radius: 50%; background: var(--st-red); color: #fff; border: 0; font-size: 11px;
        line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .st-file .dl { position: absolute; bottom: 2px; right: 2px; width: 20px; height: 20px;
        border-radius: 4px; background: rgba(0,0,0,.65); color: #fff; font-size: 12px; line-height: 1;
        text-decoration: none; display: flex; align-items: center; justify-content: center;
        transition: background .15s; }
    .st-file .dl:hover { background: var(--st-primary); color: #fff; }

    .st-actions { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .6rem; }
    .st-actions .st-btn { flex: 1 1 100px; justify-content: center; }

    .st-progress { height: 4px; background: #eef0f5; border-radius: 999px;
        overflow: hidden; margin-top: .35rem; }
    .st-progress > div { height: 100%; background: var(--st-primary); width: 0%; transition: width .15s; }

    .st-empty { text-align: center; color: var(--st-muted); padding: 2.5rem 1rem; font-size: .9rem; }
    .st-empty .em { font-size: 2.5rem; display: block; margin-bottom: .5rem; }

    .st-toast-wrap { position: fixed; top: 1rem; right: 1rem; z-index: 9999;
        display: flex; flex-direction: column; gap: .5rem; }
    .st-toast { padding: .7rem 1rem; border-radius: 8px; font-size: .9rem; font-weight: 500;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,.2); animation: stIn .25s ease-out; }
    .st-toast.ok  { background: #4caf17; color: #fff; }
    .st-toast.err { background: var(--st-red);  color: #fff; }
    @keyframes stIn { from { transform: translateX(20px); opacity: 0; } to { transform: none; opacity: 1; } }

    .st-hide { display: none !important; }
    .st-back { color: var(--st-muted); font-size: .85rem; text-decoration: none; }
    .st-back:hover { color: var(--st-primary); }

    /* drag & drop */
    [data-st-draggable] { cursor: grab; }
    [data-st-draggable]:active { cursor: grabbing; }
    .st-card.st-dragging { opacity: .5; transform: scale(.98); }
    .st-col.st-drag-over { background: #eef0ff; border-color: var(--st-primary); }

    /* preview modal */
    .st-modal { position: fixed; inset: 0; background: rgba(0,0,0,.85); z-index: 10000;
        display: none; align-items: center; justify-content: center; padding: 2rem; }
    .st-modal.open { display: flex; }
    .st-modal-body { max-width: 95vw; max-height: 90vh; position: relative;
        display: flex; flex-direction: column; align-items: center; gap: .5rem; }
    .st-modal-body img, .st-modal-body video {
        max-width: 95vw; max-height: 80vh; border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0,0,0,.5); }
    .st-modal-caption { color: #fff; font-size: .9rem; }
    .st-modal-actions { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
        justify-content: center; margin-top: .5rem; }
    .st-modal-close { position: absolute; top: -2.5rem; right: -.25rem;
        width: 36px; height: 36px; border-radius: 50%; border: 0; background: #fff;
        color: #222; font-size: 1.4rem; cursor: pointer; line-height: 1;
        display: flex; align-items: center; justify-content: center; }
</style>

<div class="st-wrap">

    {{-- ============================ HEADER ============================ --}}
    <div class="st-header">
        <div class="st-logo"><span class="ico">📋</span> {{ __('StaffTask Board') }}</div>

        <div class="st-stats">
            <span class="st-stat">{{ __('Total') }}: <b id="stat-total">{{ $totalCount }}</b></span>
            <span class="st-stat">{{ __('To Do') }}: <b id="stat-todo">{{ $todoCount }}</b></span>
            <span class="st-stat">{{ __('In Progress') }}: <b id="stat-prog">{{ $progCount }}</b></span>
            <span class="st-stat">{{ __('Complete') }}: <b id="stat-done">{{ $doneCount }}</b></span>
        </div>
    </div>

    {{-- ============================ FILTERS ============================ --}}
    <div class="st-filters">
        <div class="st-field">
            <label>🔍 {{ __('Search') }}</label>
            <input id="f-search" class="st-input" placeholder="{{ __('Search by title or assignee...') }}">
        </div>
        <div class="st-field">
            <label>{{ __('Priority') }}</label>
            <select id="f-prio" class="st-select">
                <option value="">{{ __('All') }}</option>
                <option value="high">{{ __('High') }}</option>
                <option value="medium">{{ __('Medium') }}</option>
                <option value="low">{{ __('Low') }}</option>
            </select>
        </div>
        <div class="st-field">
            <label>{{ __('Status') }}</label>
            <select id="f-status" class="st-select">
                <option value="">{{ __('All') }}</option>
                <option value="pending">{{ __('To Do') }}</option>
                <option value="in_progress">{{ __('In Progress') }}</option>
                <option value="completed">{{ __('Complete') }}</option>
            </select>
        </div>
        <div class="st-field">
            <label>{{ __('Assignee') }}</label>
            <input id="f-assignee" class="st-input" placeholder="{{ __('Filter by staff name...') }}">
        </div>
        @if ($isAdmin)
            <a href="{{ route('stafftask.create') }}" class="st-btn st-btn-primary">＋ {{ __('New Task') }}</a>
        @endif
    </div>

    {{-- ============================ KANBAN ============================ --}}
    <div class="st-board">
        @foreach ([
            'pending'     => ['label' => __('To Do'),       'ico' => '📋'],
            'in_progress' => ['label' => __('In Progress'), 'ico' => '⚙️'],
            'completed'   => ['label' => __('Complete'),    'ico' => '✅'],
        ] as $statusKey => $col)
            @php $colTasks = $tasks->where('status', $statusKey)->values(); @endphp
            <div class="st-col" data-col="{{ $statusKey }}">
                <div class="st-col-head">
                    <span><span class="ico">{{ $col['ico'] }}</span> {{ $col['label'] }}</span>
                    <span class="st-col-count" data-col-count="{{ $statusKey }}">{{ $colTasks->count() }}</span>
                </div>
                <div class="st-col-body" data-col-body="{{ $statusKey }}">
                    @forelse ($colTasks as $task)
                        @include('stafftask._card', ['task' => $task, 'isAdmin' => $isAdmin, 'isStaff' => $isStaff])
                    @empty
                        <div class="st-empty" data-empty>
                            <span class="em">🎉</span>
                            {{ __('No tasks yet') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="st-toast-wrap" id="st-toasts"></div>

{{-- ============================ PREVIEW MODAL ============================ --}}
<div class="st-modal" id="st-preview-modal" role="dialog" aria-modal="true">
    <div class="st-modal-body">
        <button type="button" class="st-modal-close" data-st-preview-close aria-label="Close">×</button>
        <div data-st-preview-slot></div>
        <div class="st-modal-actions">
            <a href="#" data-st-preview-download
               class="st-btn st-btn-primary"
               style="background:#fff;color:#222;border:0;"
               target="_blank" rel="noopener">
                ⬇ {{ __('Download') }}
            </a>
            <span class="st-modal-caption" data-st-preview-caption></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function toast(msg, ok = true) {
        const wrap = document.getElementById('st-toasts');
        const el = document.createElement('div');
        el.className = 'st-toast ' + (ok ? 'ok' : 'err');
        el.textContent = msg;
        wrap.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    async function send(url, body = null, method = 'POST') {
        const opts = { method, headers: {
            'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'
        } };
        if (body instanceof FormData) opts.body = body;
        else if (body !== null) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(json.message || json.error || ('HTTP ' + res.status));
        return json;
    }

    console.log('[StaffTask] Board JS initialized. CSRF token:', CSRF ? 'present' : 'MISSING');
    console.log('[StaffTask] Start buttons found:', document.querySelectorAll('[data-st-start]').length);

    /* START TASK + GPS */
    document.querySelectorAll('[data-st-start]').forEach(btn => {
        btn.addEventListener('click', () => {
            const url = btn.dataset.url;
            console.log('[StaffTask] Start clicked. URL:', url);
            btn.disabled = true; btn.textContent = '⏳ ' + @json(__('Locating...'));

            const post = (payload) => {
                console.log('[StaffTask] POST', url, payload);
                return send(url, payload)
                    .then(res => {
                        console.log('[StaffTask] Server replied:', res);
                        toast(@json(__('Task started')));
                        setTimeout(() => location.reload(), 800);
                    })
                    .catch(e => {
                        console.error('[StaffTask] Server error:', e);
                        btn.disabled = false; btn.textContent = '🚀 ' + @json(__('Start Task'));
                        toast(@json(__('Failed')) + ': ' + e.message, false);
                    });
            };

            if (!navigator.geolocation) {
                console.warn('[StaffTask] navigator.geolocation not available');
                return post({});
            }
            navigator.geolocation.getCurrentPosition(async pos => {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                console.log('[StaffTask] GPS captured:', lat, lng);
                let address = null;
                try {
                    const r = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
                        { headers: { 'Accept': 'application/json' } });
                    const j = await r.json();
                    const a = j.address || {};
                    address = a.city || a.town || a.village || a.county || a.state || j.display_name || null;
                    console.log('[StaffTask] Reverse-geocoded address:', address);
                } catch (e) { console.warn('[StaffTask] Nominatim failed:', e); }
                post({ lat, lng, address });
            }, err => {
                console.warn('[StaffTask] GPS error:', err);
                post({});
            }, { timeout: 10000 });
        });
    });

    /* UPLOAD */
    document.querySelectorAll('[data-st-upload]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0]; if (!file) return;
            const url = input.dataset.url;
            const bar  = input.closest('.st-upload-wrap').querySelector('.st-progress');
            const fill = bar?.querySelector('div');
            if (bar) bar.style.display = 'block';

            const xhr = new XMLHttpRequest();
            const fd = new FormData(); fd.append('file', file);
            xhr.open('POST', url);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.onprogress = e => { if (e.lengthComputable && fill) fill.style.width = Math.round(e.loaded/e.total*100)+'%'; };
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    toast(@json(__('Proof uploaded'))); setTimeout(() => location.reload(), 500);
                } else {
                    let m = 'HTTP ' + xhr.status;
                    try { m = JSON.parse(xhr.responseText).message || m; } catch (e) {}
                    toast(m, false); if (bar) bar.style.display = 'none';
                }
            };
            xhr.onerror = () => { toast(@json(__('Upload failed')), false); if (bar) bar.style.display = 'none'; };
            xhr.send(fd);
        });
    });

    /* REMOVE FILE */
    document.querySelectorAll('[data-st-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm(@json(__('Remove this file?')))) return;
            try { await send(btn.dataset.url, null, 'DELETE'); btn.closest('.st-file').remove(); }
            catch (e) { toast(e.message, false); }
        });
    });

    /* COMPLETE */
    document.querySelectorAll('[data-st-complete]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm(@json(__('Mark this task as complete?')))) return;
            btn.disabled = true;
            try { await send(btn.dataset.url); toast(@json(__('Task completed')) + ' 🎉'); setTimeout(() => location.reload(), 600); }
            catch (e) { btn.disabled = false; toast(e.message, false); }
        });
    });

    /* ============================ DRAG & DROP (admin) ============================ */
    let dragging = null;

    document.querySelectorAll('[data-st-draggable]').forEach(card => {
        card.addEventListener('dragstart', (e) => {
            dragging = card;
            card.classList.add('st-dragging');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', card.dataset.taskId || ''); } catch (err) {}
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('st-dragging');
            dragging = null;
            document.querySelectorAll('.st-col.st-drag-over').forEach(c => c.classList.remove('st-drag-over'));
        });
    });

    document.querySelectorAll('.st-col[data-col]').forEach(col => {
        col.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('st-drag-over');
        });
        col.addEventListener('dragleave', (e) => {
            if (!col.contains(e.relatedTarget)) col.classList.remove('st-drag-over');
        });
        col.addEventListener('drop', async (e) => {
            e.preventDefault();
            col.classList.remove('st-drag-over');
            if (!dragging) return;

            const taskId = dragging.dataset.taskId;
            const newStatus = col.dataset.col;
            const oldStatus = dragging.dataset.status;
            if (newStatus === oldStatus) return;

            // optimistic move
            const body = col.querySelector('[data-col-body]');
            if (body) body.appendChild(dragging);
            dragging.dataset.status = newStatus;

            try {
                await send(`/stafftask/${taskId}/move`, { status: newStatus });
                toast(@json(__('Task moved')));
                setTimeout(() => location.reload(), 400);
            } catch (err) {
                toast(@json(__('Move failed')) + ': ' + err.message, false);
                setTimeout(() => location.reload(), 600);
            }
        });
    });

    /* ============================ IMAGE / VIDEO PREVIEW ============================ */
    const modal         = document.getElementById('st-preview-modal');
    const modalSlot     = modal?.querySelector('[data-st-preview-slot]');
    const modalCap      = modal?.querySelector('[data-st-preview-caption]');
    const modalClose    = modal?.querySelector('[data-st-preview-close]');
    const modalDownload = modal?.querySelector('[data-st-preview-download]');

    function openPreview(kind, url, name, downloadUrl) {
        if (!modal) return;
        modalSlot.innerHTML = '';
        if (kind === 'image') {
            const img = document.createElement('img');
            img.src = url;
            img.alt = name || '';
            modalSlot.appendChild(img);
        } else {
            const v = document.createElement('video');
            v.src = url;
            v.controls = true;
            v.autoplay = true;
            v.style.maxWidth = '95vw';
            v.style.maxHeight = '80vh';
            modalSlot.appendChild(v);
        }
        modalCap.textContent = name || '';
        if (modalDownload) {
            modalDownload.href = downloadUrl || url;
            modalDownload.setAttribute('download', name || '');
        }
        modal.classList.add('open');
    }
    function closePreview() {
        if (!modal) return;
        modal.classList.remove('open');
        modalSlot.innerHTML = '';
    }

    document.querySelectorAll('[data-st-preview]').forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            openPreview(el.dataset.stPreview, el.dataset.url, el.dataset.name, el.dataset.download);
        });
    });

    modalClose?.addEventListener('click', closePreview);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closePreview(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePreview(); });

    /* FILTERS */
    const fS = document.getElementById('f-search');
    const fP = document.getElementById('f-prio');
    const fT = document.getElementById('f-status');
    const fA = document.getElementById('f-assignee');

    function applyFilters() {
        const q  = (fS.value || '').toLowerCase().trim();
        const p  = fP.value;
        const t  = fT.value;
        const an = (fA.value || '').toLowerCase().trim();
        const counts = { pending: 0, in_progress: 0, completed: 0 };

        document.querySelectorAll('[data-task]').forEach(card => {
            const title    = (card.dataset.title || '').toLowerCase();
            const prio     = card.dataset.prio || '';
            const status   = card.dataset.status || '';
            const assignee = (card.dataset.assignee || '').toLowerCase();
            const matches =
                (!q  || title.includes(q) || assignee.includes(q)) &&
                (!p  || prio === p) &&
                (!t  || status === t) &&
                (!an || assignee.includes(an));
            card.classList.toggle('st-hide', !matches);
            if (matches) counts[status] = (counts[status] || 0) + 1;
        });

        Object.keys(counts).forEach(k => {
            const el = document.querySelector(`[data-col-count="${k}"]`);
            if (el) el.textContent = counts[k];
            const body = document.querySelector(`[data-col-body="${k}"]`);
            if (body) {
                const visible = body.querySelectorAll('[data-task]:not(.st-hide)').length;
                let empty = body.querySelector('[data-empty]');
                if (visible === 0 && !empty) {
                    empty = document.createElement('div');
                    empty.className = 'st-empty';
                    empty.dataset.empty = '';
                    empty.innerHTML = '<span class="em">🎉</span>' + @json(__('No tasks yet'));
                    body.appendChild(empty);
                }
                if (empty) empty.style.display = visible === 0 ? '' : 'none';
            }
        });

        const visTotal = document.querySelectorAll('[data-task]:not(.st-hide)').length;
        document.getElementById('stat-total').textContent = visTotal;
        document.getElementById('stat-todo').textContent  = counts.pending     || 0;
        document.getElementById('stat-prog').textContent  = counts.in_progress || 0;
        document.getElementById('stat-done').textContent  = counts.completed   || 0;
    }
    [fS, fP, fT, fA].forEach(el => el?.addEventListener('input', applyFilters));
})();
</script>
@endpush

@endsection
