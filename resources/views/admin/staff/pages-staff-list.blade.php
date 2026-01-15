@extends('layouts/contentNavbarLayout')

@section('title','Staff List')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
/* Custom styling for badges */
.badge-role {
    font-weight: 600;
    padding: 0.35em 0.65em;
    font-size: 0.85em;
}

/* Table row hover */
#staff-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Filter bar spacing */
.filter-bar {
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Table responsive padding */
.table-responsive {
    padding: 0.5rem 1rem;
}
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

  <!-- Role Filter -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 filter-bar">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted small">Role</span>
        <select id="roleFilter" class="form-select form-select-sm w-auto">
          <option value="">All Roles</option>
          @foreach($roles as $r)
            <option value="{{ $r }}">{{ $r }}</option>
          @endforeach
        </select>
        <span class="badge bg-label-primary ms-3" id="countBadge">0</span>
      </div>
    </div>
  </div>

  <!-- Staff Table -->
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
              $role = $s->Role ?? '-';
              $badge = match(strtolower($role)) {
                'temporary' => 'bg-label-warning text-warning',
                'permanent' => 'bg-label-success text-success',
                'company'   => 'bg-label-info text-info',
                default     => 'bg-label-secondary text-secondary'
              };
            @endphp
            <tr>
              <td class="fw-semibold">{{ $s->StaffID }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                       style="width:42px;height:42px;font-weight:700;">
                    {{ strtoupper(substr($s->FirstName ?? '',0,1).substr($s->LastName ?? '',0,1)) }}
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

@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    const ROLE_COL = 3;
    const ACTION_COL = 5;

    // Custom role filter
    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable.id !== 'staff-table') return true;
        const selectedRole = ($('#roleFilter').val() || '').trim().toLowerCase();
        if (!selectedRole) return true;
        const roleText = $('<div>').html(data[ROLE_COL]).text().trim().toLowerCase();
        return roleText === selectedRole;
    });

    // Initialize DataTable with native search and pagination
    const dt = $('#staff-table').DataTable({
        pageLength: 10,
        lengthChange: true,   // show native page size dropdown
        searching: true,      // show native search input
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [ACTION_COL] }],
        responsive: true,
        autoWidth: false
    });

    // Update badge count on draw
    function updateCount() {
        const shown = dt.rows({ filter: 'applied' }).count();
        const total = dt.rows().count();
        $('#countBadge').text(`${shown} shown (Total ${total})`);
    }
    dt.on('draw', updateCount);
    updateCount();

    // Hook role filter
    $('#roleFilter').on('change', function() {
        dt.draw();
    });
});
</script>
@endsection
