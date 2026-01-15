@extends('layouts/contentNavbarLayout')

@section('title','Staff List')

{{-- ✅ CSS must be in HEAD (this section should be printed in <head> by your layout) --}}
@section('vendor-style')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

  <style>
    /* hide default datatable search box + length dropdown (we use custom UI) */
    div.dataTables_filter, div.dataTables_length { display:none !important; }
  </style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header -->
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1">Staff Management</h4>
      <div class="text-muted small">Search, filter by role, and manage staff accounts.</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('admin.staff.pages-staff-add') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bx bx-plus"></i> Add New Staff
      </a>
    </div>
  </div>

  @php
    $roles = $staff->pluck('Role')->filter()->unique()->values();
  @endphp

  <!-- Filter Bar -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="text-muted small">Show</span>
        <select id="pageSize" class="form-select form-select-sm w-auto">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="-1">All</option>
        </select>

        <span class="text-muted small ms-md-3">Role</span>
        <select id="roleFilter" class="form-select form-select-sm w-auto">
          <option value="">All Roles</option>
          @foreach($roles as $r)
            <option value="{{ $r }}">{{ $r }}</option>
          @endforeach
        </select>

        <span class="badge bg-label-primary ms-md-3" id="countBadge">0</span>
      </div>

      <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
        <div class="position-relative" style="min-width:280px;">
          <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
          <input id="txtSearch" type="text" class="form-control form-control-sm ps-5"
                 placeholder="Search name, username, email, phone...">
        </div>

        <div id="exportWrap"></div>
      </div>

    </div>
  </div>

  <!-- Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0 table-responsive">
      <table class="table table-hover align-middle mb-0" id="staff-table">
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

        <tbody>
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

              $imgUrl = !empty($s->ProfilePicture) ? asset('storage/'.ltrim($s->ProfilePicture,'/')) : null;
            @endphp

            <tr>
              <td class="fw-semibold">{{ $s->StaffID }}</td>

              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="rounded-circle overflow-hidden border bg-light d-flex align-items-center justify-content-center"
                       style="width:42px;height:42px;">
                    @if($imgUrl)
                      <img src="{{ $imgUrl }}" alt="avatar" class="w-100 h-100" style="object-fit:cover;"
                           onerror="this.remove(); this.nextElementSibling.classList.remove('d-none');">
                      <div class="w-100 h-100 d-none align-items-center justify-content-center bg-primary text-white"
                           style="font-weight:700;">
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

              <td><span class="badge {{ $badge }}">{{ $role }}</span></td>
              <td class="text-muted">{{ $s->PhoneNumber ?? '-' }}</td>

              <td class="text-end">
                <a href="{{ route('admin.staff.pages-staff-edit', $s->StaffID) }}" class="btn btn-sm btn-outline-primary">
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
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

{{-- ✅ EVERYTHING JS in one section to guarantee order --}}
@section('script')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- ✅ jQuery MUST load before DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- ✅ DataTables + Buttons --}}
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>

{{-- ✅ Required for csvHtml5 --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(function () {
  const ROLE_COL = 3;
  const ACTION_COL = 5;

  // role exact match filter (works with badge HTML)
  $.fn.dataTable.ext.search.push(function (settings, data) {
    if (settings.nTable.id !== 'staff-table') return true;

    const selectedRole = ($('#roleFilter').val() || '').trim().toLowerCase();
    if (!selectedRole) return true;

    const roleText = $('<div>').html(data[ROLE_COL]).text().trim().toLowerCase();
    return roleText === selectedRole;
  });

  const dt = $('#staff-table').DataTable({
    pageLength: parseInt($('#pageSize').val() || '10', 10),
    lengthChange: false,
    order: [[0, 'desc']],
    columnDefs: [{ orderable: false, targets: [ACTION_COL] }],
    dom: 'Brtip',
    buttons: [{
      extend: 'csvHtml5',
      text: '<i class="bx bx-download"></i> Export CSV',
      title: 'staff_list',
      exportOptions: { columns: [0,1,2,3,4] }
    }]
  });

  dt.buttons().container().appendTo('#exportWrap');

  function updateCount() {
    const shown = dt.rows({ filter: 'applied' }).count();
    const total = dt.rows().count();
    $('#countBadge').text(`${shown} shown (Total ${total})`);
  }

  dt.on('draw', updateCount);
  updateCount();

  $('#txtSearch').on('input', function () {
    dt.search(this.value).draw();
  });

  $('#pageSize').on('change', function () {
    dt.page.len(parseInt(this.value, 10)).draw();
  });

  $('#roleFilter').on('change', function () {
    dt.draw();
  });
});
</script>
@endsection
