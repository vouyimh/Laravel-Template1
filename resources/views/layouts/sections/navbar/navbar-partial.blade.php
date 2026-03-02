@php
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$locale = app()->getLocale();          // 'en' | 'ko'
$langLabel = $locale === 'fr' ? 'FR' : 'EN';
@endphp
<style>
/* Language collapse list inside dropdown */
.lang-submenu { display: none; padding-left: 0; margin: 0; }
.lang-submenu.show { display: block; }

.lang-submenu .dropdown-item {
  padding-left: 3rem; /* indent under Language */
}

.lang-toggle .lang-arrow {
  transition: transform .15s ease-in-out;
}

.lang-toggle.open .lang-arrow {
  transform: rotate(90deg);
}
</style>
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

    @if($user)
      <!-- USER DROPDOWN -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <div class="avatar avatar-online">
            <img
              src="{{ $user->avatar_path
                      ? asset('storage/'.$user->avatar_path)
                      : asset('assets/img/avatars/1.png') }}"
              alt="avatar"
              class="w-px-40 h-auto rounded-circle">
          </div>
        </a>

        <!-- DROPDOWN MENU -->
        <ul class="dropdown-menu dropdown-menu-end">

          <!-- PROFILE SUMMARY -->
          <li>
            <a class="dropdown-item" href="javascript:void(0);">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img
                      src="{{ $user->avatar_path
                              ? asset('storage/'.$user->avatar_path)
                              : asset('assets/img/avatars/1.png') }}"
                      alt="avatar"
                      class="w-px-40 h-auto rounded-circle">
                  </div>
                </div>

                <div class="flex-grow-1">
                  <h6 class="mb-0">
                    @php
                      $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                    @endphp
                    {{ $fullName !== '' ? $fullName : ($user->name ?? 'User') }}
                  </h6>

                  <small class="text-muted">
                    {{ $user->role ? ucfirst($user->role) : 'User' }}
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
              <span>{{ __('My Profile') }}</span>
            </a>
          </li>

          <!-- LANGUAGE (toggle EN <-> KO) -->
          <li><div class="dropdown-divider my-1"></div></li>

          <li>
            <a class="dropdown-item d-flex justify-content-between align-items-center lang-toggle"
              href="javascript:void(0);">
              <span>
                <i class="icon-base bx bx-world icon-md me-3"></i>
                {{ __('Language') }}
              </span>
              <i class="bx bx-chevron-right small lang-arrow"></i>
            </a>

            <ul class="lang-submenu {{ in_array($locale, ['en','fr']) ? '' : '' }}">
              <li>
                <a class="dropdown-item {{ $locale === 'en' ? 'active' : '' }}"
                  href="{{ route('lang.switch', 'en') }}">
                  English (EN)
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ $locale === 'fr' ? 'active' : '' }}"
                  href="{{ route('lang.switch', 'fr') }}">
                  Français (FR)
                </a>
              </li>
            </ul>
          </li>

          <li><div class="dropdown-divider my-1"></div></li>

          <!-- LOGOUT -->
          <li>
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
              @csrf
              <a class="dropdown-item" href="#"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="icon-base bx bx-power-off icon-md me-3"></i>
                <span>{{ __('Log Out') }}</span>
              </a>
            </form>
          </li>

        </ul>
      </li>
      <!-- /USER DROPDOWN -->
    @else
      <!-- If not logged in, show Login (optional) -->
      <li class="nav-item">
        <a class="nav-link" href="{{ route('auth-login-basic') }}">
          {{ __('Login') }}
        </a>
      </li>
    @endif

  </ul>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.lang-toggle');
  const submenu = document.querySelector('.lang-submenu');

  if (!toggle || !submenu) return;

  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();

    submenu.classList.toggle('show');
    toggle.classList.toggle('open');
  });

  // Prevent dropdown from closing when clicking inside submenu
  submenu.addEventListener('click', function(e){
    e.stopPropagation();
  });
});
</script>