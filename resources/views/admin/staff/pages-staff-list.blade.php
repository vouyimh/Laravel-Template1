@extends('layouts/contentNavbarLayout')

@section('title','Staff List')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
/* keep table flush with card */
.table-responsive{ padding:0; }

/* ===== Role badge ===== */
.badge-role{
  font-weight: 600;
  padding: .35rem .65rem;
  font-size: .82rem;
  border-radius: .5rem;
}

/* ===== Filter bar ===== */
.filter-bar{ flex-wrap:wrap; gap:.75rem; }
#countBadge{
  padding: 6px 10px;
  border-radius: 10px;
  font-weight: 600;
  font-size: .8rem;
}

/* ===== Table header style ===== */
#staff-table thead th{
  background:#f5f6fa;
  color:#6c757d;
  font-size:.78rem;
  letter-spacing:.04em;
  text-transform:uppercase;
  border-bottom: 1px solid #e9ecef !important;
  padding: 12px 14px;
}

/* body spacing */
#staff-table tbody td{
  padding: 14px;
  vertical-align: middle;
}
#staff-table tbody tr:hover{ background:#f8f9fa; }

/* avatar */
.staff-avatar{
  width:42px;height:42px;border-radius:999px;
  display:flex;align-items:center;justify-content:center;
  font-weight:700;background:#696cff;color:#fff;
  box-shadow: 0 6px 16px rgba(105,108,255,.20);
  overflow:hidden;
  flex-shrink:0;
}
.staff-avatar img{
  width:100%;height:100%;object-fit:cover;
}

/* action buttons */
.btn-icon{
  width:34px;height:34px;padding:0;
  display:inline-flex;align-items:center;justify-content:center;
  border-radius:8px;
}

/* ===== DataTables bottom bar ===== */
.dt-bottom{
  padding: 14px 16px;
  border-top: 1px solid #eef0f4;
}

/* pagination more modern */
.dataTables_wrapper .pagination{ margin: 0; }
.dataTables_wrapper .page-link{ border-radius: 8px !important; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header -->
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Staff Management') }}</h4>
      <div class="text-muted small">{{ __('Search, filter by role, and manage staff accounts.') }}</div>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.staff.pages-staff-add') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bx bx-plus"></i> {{ __('Add New Staff') }}
      </a>
    </div>
  </div>

  @php
    $roles = $staff->pluck('Role')->filter()->unique()->values();
  @endphp

  <!-- Role Filter + Search (custom) -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 filter-bar">

      <!-- Left: Role filter + count -->
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted small">{{ __('Role') }}</span>
        <select id="roleFilter" class="form-select form-select-sm w-auto">
          <option value="">{{ __('All Roles') }}</option>
          @foreach($roles as $r)
            <option value="{{ $r }}">{{ $r }}</option>
          @endforeach
        </select>

        <span class="badge bg-label-primary ms-2" id="countBadge">0</span>
      </div>

      <!-- Right: Search + page size (custom) -->
      <div class="d-flex align-items-center gap-2 ms-md-auto flex-wrap">
        <span class="text-muted small">{{ __('Show') }}</span>
        <select id="pageSize" class="form-select form-select-sm w-auto">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
        <span class="text-muted small">{{ __('rows') }}</span>

        <span class="text-muted small ms-md-3">{{ __('Search') }}</span>
        <input id="staffSearch" type="text"
               class="form-control form-control-sm"
               style="width:260px;"
               placeholder="{{ __('Search staff...') }}">
      </div>

    </div>
  </div>

  <!-- Staff Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="staff-table">
          <thead>
            <tr>
              <th style="width:80px;">{{ __('ID') }}</th>
              <th>{{ __('Staff') }}</th>
              <th>{{ __('Email') }}</th>
              <th style="width:140px;">{{ __('Role') }}</th>
              <th style="width:160px;">{{ __('Phone') }}</th>
              <th class="text-end" style="width:140px;">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($staff as $s)
              @php
                $full = trim(($s->FirstName ?? '').' '.($s->LastName ?? ''));
                $role = $s->Role ?? '-';
                $badge = match(strtolower($role)) {
                  'temporary' => 'bg-label-warning text-warning',
                  'permanent' => 'bg-label-success text-success',
                  'company'   => 'bg-label-info text-info',
                  default     => 'bg-label-secondary text-secondary'
                };
                $initials = strtoupper(substr($s->FirstName ?? '',0,1).substr($s->LastName ?? '',0,1));
              @endphp

              <tr>
                <td class="fw-semibold">{{ $s->StaffID }}</td>

                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="staff-avatar">
                      @if (!empty($s->ProfilePicture))
                        <img src="{{ route('admin.staff.photo', $s->StaffID) }}"
                             alt="{{ $full }}"
                             onerror="this.parentNode.textContent='{{ $initials }}';">
                      @else
                        {{ $initials }}
                      @endif
                    </div>
                    <div>
                      <div class="fw-semibold">{{ $full }}</div>
                      <div class="small text-muted">{{ $s->Username }}</div>
                    </div>
                  </div>
                </td>

                <td class="text-muted">{{ $s->Email }}</td>

                <td><span class="badge badge-role {{ $badge }}">{{ $role }}</span></td>

                <td class="text-muted">{{ $s->PhoneNumber ?? '-' }}</td>

                <td class="text-end">
                  <a href="{{ route('admin.staff.pages-staff-edit', $s->StaffID) }}"
                     class="btn btn-sm btn-outline-primary btn-icon">
                    <i class="bx bx-pencil"></i>
                  </a>
                  <button class="delete-btn" data-url="{{ route('admin.staff.pages-staff-delete', $s->StaffID) }}">
                      <i class="bx bx-trash"></i>
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- DataTables bottom bar will render here via DOM -->
      <div id="dt-bottom-mount"></div>
    </div>
  </div>

</div>
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ✅ SweetAlert overlay must cover header + sidebar too */
.swal2-container{
  position: fixed !important;
  inset: 0 !important;
  z-index: 2147483647 !important; /* max to stay above navbar/sidebar */
}
.swal2-backdrop-show{
  background: rgba(0,0,0,.55) !important;
}
.swal2-popup{
  z-index: 2147483647 !important;
}
</style>
<script>
$(function () {
  const ROLE_COL = 3;   // Role column index (0-based)
  const ACTION_COL = 5; // Actions column index

  // ✅ Role filter (custom)
  $.fn.dataTable.ext.search.push(function (settings, data) {
    if (settings.nTable.id !== 'staff-table') return true;

    const selectedRole = ($('#roleFilter').val() || '').trim().toLowerCase();
    if (!selectedRole) return true;

    // Role column has HTML badge, so strip HTML
    const roleText = $('<div>').html(data[ROLE_COL]).text().trim().toLowerCase();
    return roleText === selectedRole;
  });

  // ✅ Init DataTables
  const dt = $('#staff-table').DataTable({
    pageLength: parseInt($('#pageSize').val() || '10', 10),
    lengthChange: false,
    searching: true,
    order: [[0, 'desc']],
    columnDefs: [{ orderable: false, targets: [ACTION_COL] }],
    responsive: false,
    dom:
      "tr" +
      "<'dt-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2'<'text-muted'i><'ms-auto'p>>",
    language: {
      info:        @json(__('Showing _START_ to _END_ of _TOTAL_ entries')),
      infoEmpty:   @json(__('Showing 0 to 0 of 0 entries')),
      zeroRecords: @json(__('No matching staff found')),
      paginate: {
        previous: @json(__('Previous')),
        next:     @json(__('Next')),
      },
    }
  });

  // ✅ Move bottom bar into mount
  $('#staff-table')
    .closest('.dataTables_wrapper')
    .find('.dt-bottom')
    .appendTo('#dt-bottom-mount');

  // ✅ Count badge
  const TXT_SHOWN = @json(__('shown'));
  const TXT_TOTAL = @json(__('Total'));
  function updateCount() {
    const shown = dt.rows({ filter: 'applied' }).count();
    const total = dt.rows().count();
    $('#countBadge').text(`${shown} ${TXT_SHOWN} (${TXT_TOTAL} ${total})`);
  }

  dt.on('draw', updateCount);
  updateCount();

  // ✅ Hooks
  $('#roleFilter').on('change', function () { dt.draw(); });
  $('#staffSearch').on('input', function () { dt.search(this.value).draw(); });
  $('#pageSize').on('change', function () { dt.page.len(parseInt(this.value, 10)).draw(); });

  // ✅ SweetAlert Delete (delegation because DataTables redraw replaces DOM)
$(document).on('click', '.delete-btn', async function () {
    const url = $(this).data('url');
    const rowEl = $(this).closest('tr');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!url) return Swal.fire(@json(__('Error')), @json(__('Missing delete URL.')), 'error');

    const result = await Swal.fire({
        title: '{{ __("Delete staff?") }}',
        text: '{{ __("This action cannot be undone.") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("Yes, delete") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    });

    if (!result.isConfirmed) return;

    Swal.fire({ title: @json(__('Deleting...')), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    console.log("URL", url);
      try {
          const res = await fetch(url, {
              method: 'DELETE',
              headers: {
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
              },
              credentials: 'same-origin'
          });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || @json(__('Delete failed.')));

        // Remove the row from DataTable
        $('#staff-table').DataTable().row(rowEl).remove().draw();

        Swal.fire({
            title: @json(__('Deleted!')),
            text:  data.message,
            icon:  'success',
            timer: 1200,
            showConfirmButton: false
        });

    } catch (err) {
        Swal.fire(@json(__('Error')), err.message || @json(__('Server error')), 'error');
    }
});

});
</script>

@endsection
