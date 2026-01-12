@extends('layouts/contentNavbarLayout')

@section('title', 'Staff List')

@section('content')
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu
            group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md
            group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm
            pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4">
  <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

    <div class="flex items-center justify-between py-4">
      <h5 class="text-16 font-semibold text-slate-800 dark:text-zink-100">Staff List</h5>

      <a href="{{ route('pages-staff-add') }}"
         class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600">
        + Add Staff
      </a>
    </div>

    @if(session('success'))
      <div class="mb-4 px-4 py-3 rounded bg-green-50 text-green-700 border border-green-100">
        {{ session('success') }}
      </div>
    @endif

    <div class="card">
      <div class="card-body">

        {{-- Search Filter header --}}
        <div class="flex items-center justify-between mb-5">
          <h6 class="text-15 font-semibold text-slate-800 dark:text-zink-100">Search Filter</h6>
        </div>

        {{-- Filter row --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-5">

          <div class="flex items-center gap-2">
            <select id="pageSize"
              class="w-24 form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 dark:bg-zink-700 dark:text-zink-100">
              <option value="5">5</option>
              <option value="7">7</option>
              <option value="10" selected>10</option>
              <option value="25">25</option>
            </select>

            <input id="searchInput" type="text" placeholder="Search User"
              class="w-64 form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 dark:bg-zink-700 dark:text-zink-100 placeholder:text-slate-400 dark:placeholder:text-zink-200">
          </div>

          <div class="flex items-center gap-2 md:justify-end">
            <button type="button"
              class="btn bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200 dark:bg-zink-700 dark:text-zink-100 dark:border-zink-500">
              Export
            </button>
          </div>

        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-md border border-slate-200 dark:border-zink-500">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-zink-700/50 border-b border-slate-200 dark:border-zink-500">
              <tr class="text-slate-600 dark:text-zink-200">
                <th class="py-3 px-4 text-left w-24">ID</th>
                <th class="py-3 px-4 text-left">USER</th>
                <th class="py-3 px-4 text-left">EMAIL</th>
                <th class="py-3 px-4 text-left w-40">ROLE</th>
                <th class="py-3 px-4 text-left w-40">PHONE</th>
                <th class="py-3 px-4 text-right w-40">ACTIONS</th>
              </tr>
            </thead>

            <tbody id="staffTbody">
              @forelse(($staff ?? []) as $s)
                @php
                  $first = $s->FirstName ?? '';
                  $last  = $s->LastName ?? '';
                  $full  = trim($first.' '.$last);
                  $initials = strtoupper(substr($first,0,1).substr($last,0,1));
                  if ($initials === '') $initials = 'NA';
                  $email = $s->Email ?? '';
                  $role  = $s->Role ?? '-';
                  $phone = $s->PhoneNumber ?? '-';
                  $username = $s->Username ?? '';
                @endphp

                <tr class="border-b border-slate-200 dark:border-zink-500 staff-row"
                    data-name="{{ strtolower($full.' '.$username) }}"
                    data-email="{{ strtolower($email) }}"
                    data-role="{{ strtolower($role) }}"
                    data-phone="{{ strtolower($phone) }}">
                  <td class="py-4 px-4 align-middle text-slate-700 dark:text-zink-100">
                    {{ $s->StaffID }}
                  </td>

                  <td class="py-4 px-4 align-middle">
                    <div class="flex items-center gap-3">
                      <div class="flex items-center justify-center font-semibold rounded-full size-10 shrink-0 bg-slate-100 text-slate-700 dark:bg-zink-600 dark:text-zink-100">
                        {{ $initials }}
                      </div>
                      <div class="min-w-0">
                        <div class="font-semibold text-slate-800 dark:text-zink-100 truncate">
                          {{ $full ?: $username }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-zink-200 truncate">
                          {{ $username }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="py-4 px-4 align-middle text-slate-600 dark:text-zink-200">
                    {{ $email }}
                  </td>

                  <td class="py-4 px-4 align-middle">
                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-700 dark:bg-zink-600 dark:text-zink-100">
                      {{ $role }}
                    </span>
                  </td>

                  <td class="py-4 px-4 align-middle text-slate-600 dark:text-zink-200">
                    {{ $phone }}
                  </td>

                  <td class="py-4 px-4 align-middle">
                    <div class="flex items-center justify-end gap-2">
                      <button type="button"
                        class="flex items-center justify-center rounded-md size-9 bg-slate-100 text-slate-500 hover:text-custom-500 hover:bg-custom-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-custom-500/20 dark:hover:text-custom-500"
                        title="Edit">
                        <i data-lucide="pencil" class="size-4"></i>
                      </button>

                      <button type="button"
                        class="flex items-center justify-center rounded-md size-9 bg-slate-100 text-slate-500 hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:text-red-500 dark:hover:bg-red-500/20"
                        title="Delete">
                        <i data-lucide="trash-2" class="size-4"></i>
                      </button>

                      <button type="button"
                        class="flex items-center justify-center rounded-md size-9 bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-zink-500"
                        title="More">
                        <i data-lucide="more-vertical" class="size-4"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-500 dark:text-zink-200">
                    No staff found. Click <span class="font-semibold">“Add Staff”</span> to create one.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mt-4 text-slate-500 dark:text-zink-200 text-sm">
          <div id="showingText">
            Showing 1 to {{ min(count($staff ?? []), 10) }} of {{ count($staff ?? []) }} entries
          </div>

          <div class="flex items-center gap-2">
            <button type="button" class="btn bg-slate-100 dark:bg-zink-700 dark:text-zink-100" disabled>«</button>
            <button type="button" class="btn bg-slate-100 dark:bg-zink-700 dark:text-zink-100" disabled>‹</button>
            <button type="button" class="btn bg-custom-500 text-white">1</button>
            <button type="button" class="btn bg-slate-100 dark:bg-zink-700 dark:text-zink-100" disabled>›</button>
            <button type="button" class="btn bg-slate-100 dark:bg-zink-700 dark:text-zink-100" disabled>»</button>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
@endsection

@section('script')
<script>
(function () {
  const pageSizeEl = document.getElementById('pageSize');
  const searchEl   = document.getElementById('searchInput');
  const tbody      = document.getElementById('staffTbody');
  const showingEl  = document.getElementById('showingText');

  if (!tbody) return;

  function applyFilterAndLimit() {
    const size = parseInt(pageSizeEl?.value || '10', 10);
    const q = (searchEl?.value || '').trim().toLowerCase();

    const rows = Array.from(tbody.querySelectorAll('.staff-row'));

    // if no data rows, keep the empty row as-is
    if (rows.length === 0) {
      if (showingEl) showingEl.textContent = 'Showing 0 to 0 of 0 entries';
      return;
    }

    const matched = rows.filter(r => {
      if (!q) return true;
      const hay =
        (r.dataset.name || '') + ' ' +
        (r.dataset.email || '') + ' ' +
        (r.dataset.role || '') + ' ' +
        (r.dataset.phone || '');
      return hay.includes(q);
    });

    rows.forEach(r => r.classList.add('hidden'));
    matched.slice(0, size).forEach(r => r.classList.remove('hidden'));

    const total = matched.length;
    const shown = Math.min(total, size);
    if (showingEl) showingEl.textContent = `Showing 1 to ${shown} of ${total} entries`;
  }

  pageSizeEl?.addEventListener('change', applyFilterAndLimit);
  searchEl?.addEventListener('input', applyFilterAndLimit);

  applyFilterAndLimit();
})();
</script>
@endsection
