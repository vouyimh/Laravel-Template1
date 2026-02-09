@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * 🔁 USER SOURCE
 * ------------------------------------
 * TEST MODE (no login yet):
 *   fetch first admin from DB
 *
 * PRODUCTION (real login):
 *   change to: $navUser = Auth::user();
 */
$navUser = \App\Models\User::where('role', 'admin')->first();
// $navUser = Auth::user(); // ← use this later
@endphp

<!-- Brand -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
  <a href="{{ url('/') }}" class="app-brand-link gap-2">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
<span class="app-brand-text demo menu-text fw-bold text-heading">
  {{ config('variables.templateName') }}<span class="tm-symbol">™</span>
</span>

  </a>
</div>
@endif

<!-- Menu toggle -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ? 'd-xl-none' : '' }}">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    <i class="icon-base bx bx-menu icon-md"></i>
  </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  <ul class="navbar-nav flex-row align-items-center ms-auto">

    <!-- USER DROPDOWN -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="#" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img
            src="{{ $navUser && $navUser->avatar_path
                    ? asset('storage/'.$navUser->avatar_path)
                    : asset('assets/img/avatars/1.png') }}"
            alt="avatar"
            class="w-px-40 h-auto rounded-circle">
        </div>
      </a>

      <ul class="dropdown-menu dropdown-menu-end">
        <!-- PROFILE SUMMARY -->
        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <div class="d-flex">
              <div class="flex-shrink-0 me-3">
                <div class="avatar avatar-online">
                  <img
                    src="{{ $navUser && $navUser->avatar_path
                            ? asset('storage/'.$navUser->avatar_path)
                            : asset('assets/img/avatars/1.png') }}"
                    alt="avatar"
                    class="w-px-40 h-auto rounded-circle">
                </div>
              </div>

              <div class="flex-grow-1">
                <h6 class="mb-0">
                  {{ $navUser
                      ? (trim(($navUser->first_name ?? '').' '.($navUser->last_name ?? ''))
                          ?: ($navUser->name ?? 'User'))
                      : 'Guest' }}
                </h6>
                <small class="text-muted">
                  {{ $navUser ? ucfirst($navUser->role ?? 'user') : 'Guest' }}
                </small>
              </div>
            </div>
          </a>
        </li>

        <li><div class="dropdown-divider my-1"></div></li>

        <!-- MY PROFILE -->
        <li>
          <a class="dropdown-item" href="{{ route('pages-account-settings-account') }}">
            <i class="icon-base bx bx-user icon-md me-3"></i>
            <span>My Profile</span>
          </a>
        </li>

        <li><div class="dropdown-divider my-1"></div></li>

        <!-- LOGOUT -->
        {{-- For real login, replace with POST logout --}}
        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <i class="icon-base bx bx-power-off icon-md me-3"></i>
            <span>Log Out</span>
          </a>
        </li>

      </ul>
    </li>
    <!-- /USER DROPDOWN -->

  </ul>
</div>
