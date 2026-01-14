@extends('layouts/contentNavbarLayout')

@section('title','Staff List')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1">Staff Management</h4>
      <div class="text-muted small">Search, filter by role, and manage staff accounts.</div>
    </div>

    <div class="d-flex gap-2">
      <button type="button" id="clearFilters" class="btn btn-outline-secondary">
        <i class="bx bx-refresh"></i> Reset
      </button>
      <a href="{{ route('pages-staff-add') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bx bx-plus"></i>
        Add New Staff
      </a>
    </div>
  </div>

  {{-- Filter Bar --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

      {{-- Left --}}
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="text-muted small">Show</span>
        <select id="pageSize" class="form-select form-select-sm w-auto">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="9999">All</option>
        </select>

        <span class="text-muted small ms-md-3">Role</span>
        <select id="roleFilter" class="form-select form-select-sm w-auto">
          <option value="">All Roles</option>
          <option value="temporary">Temporary</option>
          <option value="permanent">Permanent</option>
          <option value="company">Company</option>
        </select>

        <span class="badge bg-label-primary ms-md-3" id="resultCount">0 shown</span>
      </div>

      {{-- Right --}}
      <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
        <div class="position-relative" style="min-width:280px;">
          <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
          <input id="searchInput" type="text" class="form-control form-control-sm ps-5"
                 placeholder="Search name, username, email, phone...">
        </div>

        <button id="exportBtn" class="btn btn-success btn-sm d-flex align-items-center gap-1">
          <i class="bx bx-download"></i>
          Export CSV
        </button>
      </div>

    </div>
  </div>

  {{-- Table --}}
  <div class="card shadow-sm">
    <div class="card-body p-0 table-responsive">
      <table class="table table-hover align-middle mb-0" id="staffTable">
        <thead class="table-light text-uppercase text-muted">
          <tr>
            <th style="width:80px;">ID</th>
            <th>Staff</th>
            <th>Email</th>
            <th style="width:140px;">Role</th>
            <th style="width:160px;">Phone</th>
            <th class="text-end" style="width:140px;">Actions</th>
          </tr>
        </thead>

        <tbody id="staffTbody">
          @foreach($staff as $s)
            @php
              $full = trim(($s->FirstName ?? '').' '.($s->LastName ?? ''));
              $first = $s->FirstName ?? '';
              $last  = $s->LastName ?? '';
              $initials = strtoupper(substr($first,0,1).substr($last,0,1));
              $role = $s->Role ?? '-';

              $badge = match(strtolower($role)) {
                'temporary' => 'bg-label-warning text-warning',
                'permanent' => 'bg-label-success text-success',
                'company'   => 'bg-label-info text-info',
                default     => 'bg-label-secondary text-secondary'
              };

              $imgUrl = !empty($s->ProfilePicture) ? asset('storage/'.$s->ProfilePicture) : null;
            @endphp

            <tr class="staff-row"
                data-name="{{ strtolower($full) }}"
                data-username="{{ strtolower($s->Username ?? '') }}"
                data-email="{{ strtolower($s->Email ?? '') }}"
                data-role="{{ strtolower($role) }}"
                data-phone="{{ strtolower($s->PhoneNumber ?? '') }}">

              <td class="fw-semibold">{{ $s->StaffID }}</td>

              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="rounded-circle overflow-hidden border bg-light d-flex align-items-center justify-content-center"
                       style="width:42px;height:42px;">
                    @if($imgUrl)
                      <img src="{{ $imgUrl }}" alt="avatar" class="w-100 h-100" style="object-fit:cover;"
                           onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                      <div class="w-100 h-100 d-none align-items-center justify-content-center bg-primary text-white"
                           style="font-weight:700; display:none;">
                        {{ $initials }}
                      </div>
                    @else
                      <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary text-white"
                           style="font-weight:700;">
                        {{ $initials }}
                      </div>
                    @endif
                  </div>

                  <div>
                    <div class="fw-semibold">{{ $full }}</div>
                    <div class="small text-muted">{{ $s->Username }}</div>
                  </div>
                </div>
              </td>

              <td class="text-muted">{{ $s->Email }}</td>

              <td>
                <span class="badge {{ $badge }}">{{ ucfirst($role) }}</span>
              </td>

              <td class="text-muted">{{ $s->PhoneNumber ?? '-' }}</td>

              <td class="text-end">
                <a href="{{ route('pages-staff-edit', $s->StaffID) }}" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bx bx-pencil"></i>
                </a>

                <button type="button"
                        class="btn btn-sm btn-outline-danger delete-btn"
                        data-id="{{ $s->StaffID }}"
                        data-name="{{ $full }}">
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @endforeach

          <tr id="noResultsRow" class="d-none">
            <td colspan="6" class="text-center py-5">
              <div class="text-muted">
                <i class="bx bx-search-alt-2 fs-1 d-block mb-2"></i>
                No matching staff found.
              </div>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  /* ✅ Lock UI + no scroll */
  body.swal2-shown { overflow: hidden !important; }
  .swal2-container { z-index: 99999 !important; }
  .swal2-backdrop-show { background: rgba(0,0,0,.65) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const pageSizeEl  = document.getElementById('pageSize');
  const searchEl    = document.getElementById('searchInput');
  const roleEl      = document.getElementById('roleFilter');
  const exportBtn   = document.getElementById('exportBtn');
  const clearBtn    = document.getElementById('clearFilters');
  const countBadge  = document.getElementById('resultCount');
  const noRow       = document.getElementById('noResultsRow');

  function safe(v){ return (v || '').toString().toLowerCase(); }
  function getRows(){ return Array.from(document.querySelectorAll('.staff-row')); }

  function applyFilter() {
    const rows = getRows();
    const q = safe(searchEl.value).trim();
    const roleVal = safe(roleEl.value).trim();
    const size = parseInt(pageSizeEl.value || '10', 10);

    const matched = rows.filter(r => {
      const name = safe(r.dataset.name);
      const username = safe(r.dataset.username);
      const email = safe(r.dataset.email);
      const phone = safe(r.dataset.phone);
      const rowRole = safe(r.dataset.role);

      const matchesRole = (roleVal === '') || (rowRole === roleVal);
      const matchesSearch = (q === '') || (
        name.includes(q) || username.includes(q) || email.includes(q) || phone.includes(q)
      );
      return matchesRole && matchesSearch;
    });

    rows.forEach(r => r.classList.add('d-none'));

    const showList = (size >= 9999) ? matched : matched.slice(0, size);
    showList.forEach(r => r.classList.remove('d-none'));

    if (matched.length === 0) noRow.classList.remove('d-none');
    else noRow.classList.add('d-none');

    countBadge.textContent = `${showList.length} shown / ${matched.length} matched (Total ${rows.length})`;
  }

  // Export CSV
  function toCsvValue(text) {
    const s = (text ?? '').toString().replace(/"/g, '""');
    return `"${s}"`;
  }

  exportBtn.addEventListener('click', () => {
    const rows = getRows();
    const visible = rows.filter(r => !r.classList.contains('d-none'));
    if (visible.length === 0) {
      Swal.fire('No rows', 'No rows to export.', 'info');
      return;
    }

    const csv = [];
    csv.push(['ID','Staff','Username','Email','Role','Phone'].map(toCsvValue).join(','));

    visible.forEach(r => {
      const tds = r.querySelectorAll('td');
      const id = tds[0]?.innerText.trim() || '';
      const staffName = tds[1]?.querySelector('.fw-semibold')?.innerText.trim() || '';
      const username = tds[1]?.querySelector('.small')?.innerText.trim() || '';
      const email = tds[2]?.innerText.trim() || '';
      const role = tds[3]?.innerText.trim() || '';
      const phone = tds[4]?.innerText.trim() || '';
      csv.push([id, staffName, username, email, role, phone].map(toCsvValue).join(','));
    });

    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'staff_list.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  });

  // Reset
  clearBtn.addEventListener('click', () => {
    pageSizeEl.value = '10';
    roleEl.value = '';
    searchEl.value = '';
    applyFilter();
  });

  pageSizeEl.addEventListener('change', applyFilter);
  roleEl.addEventListener('change', applyFilter);
  searchEl.addEventListener('input', applyFilter);

  // ✅ Delete (WORKING + LOCK UI)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.delete-btn');
    if (!btn) return;

    if (!csrfToken) {
      Swal.fire('Error', 'CSRF token missing. Add <meta name="csrf-token" ...> in layout head.', 'error');
      return;
    }

    const staffId = btn.dataset.id;
    const staffName = btn.dataset.name || 'this staff';
    const row = btn.closest('tr');

    const result = await Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#696cff',
      cancelButtonColor: '#6c757d',

      // ✅ lock background UI
      allowOutsideClick: false,
      allowEscapeKey: false,
      heightAuto: false
    });

    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Deleting...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const res = await fetch(`/pages/staff-delete/${staffId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        throw new Error(data.message || `HTTP ${res.status} delete failed`);
      }

      if (!data.success) {
        throw new Error(data.message || 'Delete failed');
      }

      if (row) row.remove();
      applyFilter();

      Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: `${staffName} has been deleted.`,
        timer: 1200,
        showConfirmButton: false,
        heightAuto: false
      });

    } catch (err) {
      Swal.fire('Error', err.message || 'Something went wrong.', 'error');
    }
  });

  applyFilter();
});
</script>
@endsection
