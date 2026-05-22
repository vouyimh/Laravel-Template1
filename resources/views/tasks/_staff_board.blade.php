@php
    $columns = [
        'pending'     => ['label' => __('To Do'),       'class' => 'border-secondary'],
        'in_progress' => ['label' => __('In Progress'), 'class' => 'border-warning'],
        'completed'   => ['label' => __('Complete'),    'class' => 'border-success'],
    ];

    $grouped = [
        'pending'     => collect(),
        'in_progress' => collect(),
        'completed'   => collect(),
    ];
    foreach ($tasks as $t) {
        $bucket = $grouped[$t->status] ?? null;
        if ($bucket !== null) {
            $grouped[$t->status]->push($t);
        }
    }
@endphp

<div class="row g-3">
    @foreach ($columns as $statusKey => $col)
        <div class="col-md-4">
            <div class="card h-100 border-2 {{ $col['class'] }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $col['label'] }}</strong>
                    <span class="badge bg-label-secondary">{{ $grouped[$statusKey]->count() }}</span>
                </div>
                <div class="card-body p-2" style="min-height: 200px;">
                    @forelse ($grouped[$statusKey] as $task)
                        @include('tasks._staff_card', ['task' => $task])
                    @empty
                        <p class="text-muted text-center small my-3">{{ __('Nothing here.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

@if ($tasks->isEmpty())
    <div class="alert alert-info text-center mt-4">
        <h5>{{ __('No tasks assigned to you yet') }}</h5>
        <p class="mb-0">{{ __('Ask your admin to assign one.') }}</p>
    </div>
@endif

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value;

    async function postJson(url, body = null, method = 'POST') {
        const opts = {
            method,
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        };
        if (body instanceof FormData) {
            opts.body = body;
        } else if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(json.error || json.message || `HTTP ${res.status}`);
        }
        return json;
    }

    function toast(msg, ok = true) {
        const el = document.createElement('div');
        el.className = `alert ${ok ? 'alert-success' : 'alert-danger'} position-fixed top-0 end-0 m-3 shadow`;
        el.style.zIndex = 9999;
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    // START TASK — captures geolocation, reverse-geocodes via Nominatim, then POSTs
    document.querySelectorAll('[data-staff-start]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const taskId = btn.dataset.staffStart;
            const url = btn.dataset.url;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Locating...';

            const sendStart = async (payload) => {
                try {
                    await postJson(url, payload);
                    toast('Task started');
                    setTimeout(() => location.reload(), 600);
                } catch (e) {
                    btn.disabled = false;
                    btn.textContent = '🚀 Start Task';
                    toast(e.message, false);
                }
            };

            if (!navigator.geolocation) {
                return sendStart({});
            }

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    let address = null;
                    try {
                        const r = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
                            { headers: { 'Accept': 'application/json' } }
                        );
                        const j = await r.json();
                        const a = j.address || {};
                        address = a.city || a.town || a.village || a.county || a.state || j.display_name || null;
                    } catch (e) { /* offline / blocked — ignore */ }
                    sendStart({ lat, lng, address });
                },
                () => sendStart({}),
                { timeout: 10000 }
            );
        });
    });

    // UPLOAD PROOF
    document.querySelectorAll('[data-staff-upload]').forEach(input => {
        input.addEventListener('change', async () => {
            const file = input.files[0];
            if (!file) return;
            const url = input.dataset.url;
            const progress = input.parentElement.querySelector('.upload-progress');
            const bar = progress?.querySelector('.progress-bar');
            if (progress) progress.classList.remove('d-none');
            if (bar) bar.style.width = '0%';

            try {
                const fd = new FormData();
                fd.append('file', file);
                // fetch() doesn't expose upload progress without XHR — use XHR for the bar
                await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', url);
                    xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable && bar) {
                            bar.style.width = `${Math.round((e.loaded / e.total) * 100)}%`;
                        }
                    };
                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) resolve();
                        else {
                            let msg = `HTTP ${xhr.status}`;
                            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                            reject(new Error(msg));
                        }
                    };
                    xhr.onerror = () => reject(new Error('Upload failed'));
                    xhr.send(fd);
                });
                toast('Proof uploaded');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                toast(e.message, false);
                if (progress) progress.classList.add('d-none');
            }
        });
    });

    // REMOVE PROOF
    document.querySelectorAll('[data-staff-remove-file]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Remove this file?')) return;
            try {
                await postJson(btn.dataset.url, null, 'DELETE');
                btn.closest('[data-file-row]')?.remove();
            } catch (e) {
                toast(e.message, false);
            }
        });
    });

    // COMPLETE TASK
    document.querySelectorAll('[data-staff-complete]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Mark this task as complete?')) return;
            btn.disabled = true;
            try {
                await postJson(btn.dataset.url);
                toast('Task completed 🎉');
                setTimeout(() => location.reload(), 600);
            } catch (e) {
                btn.disabled = false;
                toast(e.message, false);
            }
        });
    });
})();
</script>
@endpush
