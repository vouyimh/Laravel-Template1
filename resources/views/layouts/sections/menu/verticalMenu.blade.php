@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">
                {{ config('variables.templateName') }}
            </span>
        </a>

        <a href="javascript:void(0);"
           class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        @foreach ($menuData[0]->menu as $menu)

            @php
                $userRole = Auth::user()->role;

                /*
                 |--------------------------------------------------
                 | Role check
                 |--------------------------------------------------
                 */
                if (isset($menu->roles) && !in_array($userRole, $menu->roles)) {
                    continue;
                }

                /*
                 |--------------------------------------------------
                 | Menu header
                 |--------------------------------------------------
                 */
                if (isset($menu->menuHeader)) {
                    echo '<li class="menu-header small text-uppercase">
                            <span class="menu-header-text">'.__($menu->menuHeader).'</span>
                          </li>';
                    continue;
                }

                /*
                 |--------------------------------------------------
                 | Active / Open class
                 |--------------------------------------------------
                 */
                $activeClass = '';
                $currentRouteName = Route::currentRouteName();

                if ($currentRouteName === ($menu->slug ?? null)) {
                    $activeClass = 'active';
                } elseif (isset($menu->submenu)) {
                    if (is_array($menu->slug ?? null)) {
                        foreach ($menu->slug as $slug) {
                            if (str_starts_with($currentRouteName, $slug)) {
                                $activeClass = 'active open';
                                break;
                            }
                        }
                    } elseif (isset($menu->slug) && str_starts_with($currentRouteName, $menu->slug)) {
                        $activeClass = 'active open';
                    }
                }

                /*
                 |--------------------------------------------------
                 | Dynamic URL
                 |--------------------------------------------------
                 */
                if ($menu->name === 'Manage Staff Work') {
                    if ($userRole === 'admin') {
                        $menuUrl = url('/admin/tasks');
                    } elseif ($userRole === 'staff') {
                        $menuUrl = url('/staff/tasks');
                    } else {
                        $menuUrl = url('/client/tasks');
                    }
                } else {
                    $menuUrl = isset($menu->url)
                        ? url($menu->url)
                        : 'javascript:void(0);';
                }
            @endphp

            <li class="menu-item {{ $activeClass }}">
                <a href="{{ $menuUrl }}"
                   class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                   @if (isset($menu->target)) target="_blank" @endif>

                    @isset($menu->icon)
                        <i class="{{ $menu->icon }}"></i>
                    @endisset

                    <div>{{ __($menu->name ?? '') }}</div>

                    @isset($menu->badge)
                        <div class="badge rounded-pill bg-{{ $menu->badge[0] }} ms-auto">
                            {{ $menu->badge[1] }}
                        </div>
                    @endisset
                </a>

                {{-- Submenu --}}
                @isset($menu->submenu)
                    @php
                        $filteredSubmenu = array_filter(
                            $menu->submenu,
                            fn ($sub) =>
                                !isset($sub->roles) || in_array($userRole, $sub->roles)
                        );
                    @endphp

                    @if (!empty($filteredSubmenu))
                        @include(
                            'layouts.sections.menu.submenu',
                            ['menu' => $filteredSubmenu]
                        )
                    @endif
                @endisset
            </li>

        @endforeach

    </ul>

</aside>
