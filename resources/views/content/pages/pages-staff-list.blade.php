@extends('layouts/contentNavbarLayout')

@section('title','Staff List')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <h4 class="fw-bold mb-0">Staff Management</h4>

    <a href="{{ route('pages-staff-add') }}" class="btn btn-primary d-flex align-items-center gap-2">
      <i class="bx bx-plus"></i>
      Add New Staff
    </a>
  </div>

  {{-- Filter Bar --}}
  <div class="card mb-4">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

      {{-- Left: page size + role filter --}}
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">Show</span>
        <select id="pageSize" class="form-select form-select-sm w-auto">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
        </select>

        <span class="text-muted small ms-3">Role</span>
        <select id="roleFilter" class="form-select form-select-sm w-auto">
          <option value="">All Roles</option>
          <option value="admin">Admin</option>
          <option value="manager">Manager</option>
          <option value="full-time">Full-Time</option>
          <option value="part-time">Part-Time</option>
          <option value="temporary">Temporary</option>
          <option value="permanent">Permanent</option>
          <option value="company">Company</option>
        </select>
      </div>

      {{-- Right: search + export --}}
      <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
        <div class="position-relative">
          <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
          <input id="searchInput" type="text" class="form-control form-control-sm ps-5" placeholder="Search staff, email, phone...">
        </div>

        <button id="exportBtn" class="btn btn-success btn-sm d-flex align-items-center gap-1">
          <i class="bx bx-download"></i>
          Export
        </button>
      </div>

    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-body p-0 table-responsive">

      <table class="table table-hover table-sm mb-0" id="staffTable">
        <thead class="table-light text-uppercase text-muted">
          <tr>
            <th>ID</th>
            <th>Staff</th>
            <th>Email</th>
            <th>Role</th>
            <th>Phone</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody id="staffTbody">
          @foreach($staff as $s)
          @php
            $full = trim(($s->FirstName ?? '').' '.($s->LastName ?? ''));
            $initials = strtoupper(substr($s->FirstName,0,1).substr($s->LastName,0,1));
            $role = $s->Role ?? '-';
            $color = match(strtolower($role)) {
              'admin'=>'bg-danger text-white',
              'manager'=>'bg-purple text-white',
              'full-time'=>'bg-success text-white',
              'part-time'=>'bg-info text-white',
              default=>'bg-secondary text-white'
            };
          @endphp

          <tr class="staff-row" 
              data-name="{{ strtolower($full) }}" 
              data-email="{{ strtolower($s->Email) }}" 
              data-role="{{ strtolower($role) }}" 
              data-phone="{{ strtolower($s->PhoneNumber) }}">
            <td>{{ $s->StaffID }}</td>

            <td class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-weight:bold;">
                {{ $initials }}
              </div>
              <div>
                <div class="fw-semibold">{{ $full }}</div>
                <div class="small text-muted">{{ $s->Username }}</div>
              </div>
            </td>

            <td>{{ $s->Email }}</td>

            <td>
              <span class="badge {{ $color }}">
                {{ ucfirst($role) }}
              </span>
            </td>

            <td>{{ $s->PhoneNumber }}</td>

<td class="text-end">
  {{-- Edit Button --}}
  <a href="{{ route('pages-staff-edit', $s->StaffID) }}" class="btn btn-sm btn-outline-primary me-1">
    <i class="bx bx-pencil"></i>
  </a>

  {{-- Delete Button --}}
  <form action="{{ route('pages-staff-delete', $s->StaffID) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this staff?')">
      <i class="bx bx-trash"></i>
    </button>
  </form>
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
<script>
(function() {
  const pageSize = document.getElementById('pageSize');
  const search = document.getElementById('searchInput');
  const roleFilter = document.getElementById('roleFilter');
  const rows = [...document.querySelectorAll('.staff-row')];
  const exportBtn = document.getElementById('exportBtn');

  function applyFilter() {
    const q = search.value.toLowerCase();
    const roleVal = roleFilter.value.toLowerCase().trim();
    const size = parseInt(pageSize.value);

    const visibleRows = rows.filter(r => {
      const rowText = (r.dataset.name + r.dataset.email + r.dataset.phone).toLowerCase();
      const rowRole = r.dataset.role.toLowerCase().trim();
      const matchesSearch = rowText.includes(q);
      const matchesRole = roleVal === '' || rowRole === roleVal;
      return matchesSearch && matchesRole;
    });

    rows.forEach(r => r.classList.add('d-none'));
    visibleRows.slice(0, size).forEach(r => r.classList.remove('d-none'));
  }

  pageSize.addEventListener('change', applyFilter);
  search.addEventListener('input', applyFilter);
  roleFilter.addEventListener('change', applyFilter);
  applyFilter();

  // Export visible rows to CSV
  exportBtn.addEventListener('click', () => {
    const visibleRows = rows.filter(r => !r.classList.contains('d-none'));
    if(visibleRows.length === 0) {
      alert('No rows to export');
      return;
    }

    const csv = [];
    csv.push(['ID','Staff','Email','Role','Phone'].join(','));
    visibleRows.forEach(r => {
      const cols = r.querySelectorAll('td');
      const rowData = [
        cols[0].innerText,
        cols[1].querySelector('.fw-semibold').innerText,
        cols[2].innerText,
        cols[3].innerText,
        cols[4].innerText
      ];
      csv.push(rowData.join(','));
    });

    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', 'staff_list.csv');
    link.click();
  });

})();
</script>
@endsection
