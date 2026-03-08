@extends('layouts/contentNavbarLayout')

@section('title', __('Account settings - Account'))

@section('page-script')
  @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
@php
  $u = Auth::user();
@endphp

<style>
  .required::after{ content:" *"; color:#dc3545; font-weight:700; }
  .address-wrapper { position: relative; }

  #addressSuggestions{
    position:absolute;
    top:calc(100% + 4px);
    left:0;
    right:0;
    max-height:220px;
    overflow-y:auto;
    background:#fff;
    border:1px solid #dee2e6;
    border-radius:6px;
    z-index:2000;
    box-shadow:0 4px 12px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.06);
  }
  #addressSuggestions .item{ padding:8px 12px; cursor:pointer; border-bottom:1px solid #f0f0f0; }
  #addressSuggestions .item:last-child{ border-bottom:none; }
  #addressSuggestions .item:hover{ background:#f5f6f8; }
</style>

<div class="row">
  <div class="col-md-12">

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
        <img
          src="{{ $u && $u->avatar_path ? Storage::url($u->avatar_path) : asset('assets/img/avatars/1.png') }}"
          alt="user-avatar"
          class="d-block w-px-100 h-px-100 rounded"
          id="uploadedAvatar"
        />

          {{-- saved avatar url (maybe empty) --}}
          <input type="hidden" id="savedAvatar"
                 value="{{ $u && $u->avatar_path ? asset('storage/'.$u->avatar_path) : '' }}">

          {{-- fallback default avatar url --}}
          <input type="hidden" id="fallbackAvatar" value="{{ asset('assets/img/avatars/1.png') }}">

          <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
              <span class="d-none d-sm-block">{{ __('Upload new photo') }}</span>
              <i class="icon-base bx bx-upload d-block d-sm-none"></i>
              <input type="file" id="upload" name="avatar" class="account-file-input" hidden
                    accept="image/png, image/jpeg, image/gif" form="formAccountSettings" />
            </label>

            <button type="button" class="btn btn-outline-secondary mb-4" id="resetAvatarBtn">
              <i class="icon-base bx bx-reset d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('Reset') }}</span>
            </button>

            <div>{{ __('Allowed JPG, GIF or PNG. Max size of 800K') }}</div>
          </div>

        </div>
      </div>

      <div class="card-body pt-4">
        <form id="formAccountSettings"
              method="POST"
              action="{{ route('pages-account-settings-account.update') }}"
              enctype="multipart/form-data">
          @csrf

          {{-- tells backend to remove avatar when Reset --}}
          <input type="hidden" id="removeAvatar" name="remove_avatar" value="0">

          <div class="row g-6">

            <div class="col-md-6">
              <label class="form-label required">{{ __('First Name') }}</label>
              <input class="form-control" type="text" id="firstName" name="first_name"
                     value="{{ old('first_name', $u?->first_name) }}" required />
            </div>

            <div class="col-md-6">
              <label for="lastName" class="form-label required">{{ __('Last Name') }}</label>
              <input class="form-control" type="text" id="lastName" name="last_name"
                     value="{{ old('last_name', $u?->last_name) }}" required />
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label required">{{ __('E-mail') }}</label>
              <input class="form-control" type="email" id="email" name="email"
                     value="{{ old('email', $u?->email) }}" required />
            </div>

            <div class="col-md-6">
              <label for="organization" class="form-label">{{ __('Organization') }}</label>
              <input type="text" class="form-control" id="organization" name="organization"
                     value="{{ old('organization', $u?->organization) }}" />
            </div>

            {{-- Password Change (optional) --}}
            <div class="col-md-6">
              <label for="currentPassword" class="form-label">{{ __('Current Password') }}</label>
              <div class="input-group">
                <input class="form-control" type="password" id="currentPassword" name="current_password" autocomplete="current-password" />
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="currentPassword" aria-label="{{ __('Show/hide current password') }}">
                  <i class="bx bx-hide"></i>
                </button>
              </div>
            </div>

            <div class="col-md-6">
              <label for="newPassword" class="form-label">{{ __('New Password') }}</label>
              <div class="input-group">
                <input class="form-control" type="password" id="newPassword" name="password" autocomplete="new-password" />
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="newPassword" aria-label="{{ __('Show/hide new password') }}">
                  <i class="bx bx-hide"></i>
                </button>
              </div>
            </div>

            <div class="col-md-6">
              <label for="confirmPassword" class="form-label">{{ __('Confirm New Password') }}</label>
              <div class="input-group">
                <input class="form-control" type="password" id="confirmPassword" name="password_confirmation" autocomplete="new-password" />
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmPassword" aria-label="{{ __('Show/hide confirm password') }}">
                  <i class="bx bx-hide"></i>
                </button>
              </div>
            </div>

            {{-- Phone --}}
            <div class="col-md-6">
              <label class="form-label required" for="phoneLocal">{{ __('Phone Number') }}</label>

              @php
                $savedPhone = old('phone', $u?->phone) ?? '';
                $savedCountry = str_starts_with($savedPhone, '+44') ? '+44' : '+33';
                $savedLocal   = preg_replace('/^\+33|\+44/', '', $savedPhone);
                $savedLocal   = preg_replace('/\D/', '', $savedLocal);
              @endphp

              <div class="input-group">
                <select class="form-select" id="phoneCountry" name="phone_country" style="max-width:240px;">
                  <option value="+33" data-country="France" {{ $savedCountry === '+33' ? 'selected' : '' }}>{{ __('France') }} (+33)</option>
                  <option value="+44" data-country="United Kingdom" {{ $savedCountry === '+44' ? 'selected' : '' }}>{{ __('United Kingdom') }} (+44)</option>
                </select>

                <input
                  type="text"
                  id="phoneLocal"
                  name="phone_local"
                  class="form-control"
                  placeholder="{{ __('Digits only') }}"
                  inputmode="numeric"
                  autocomplete="tel"
                  required
                  value="{{ old('phone_local', $savedLocal) }}"
                />
              </div>

              <input type="hidden" id="phoneHidden" name="phone" value="{{ old('phone', $savedPhone) }}">
            </div>

            {{-- Address --}}
            <div class="col-md-6">
              <label class="form-label">{{ __('Address') }}</label>
              <div class="address-wrapper">
                <input
                  name="address"
                  id="addressInput"
                  class="form-control"
                  placeholder="{{ __('Start typing address...') }}"
                  autocomplete="off"
                  value="{{ old('address', $u?->address) }}"
                />
                <div id="addressSuggestions" style="display:none;"></div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="state" class="form-label">{{ __('State / Region') }}</label>
              <input class="form-control" type="text" id="state" name="state"
                     value="{{ old('state', $u?->state) }}" />
            </div>

            <div class="col-md-6">
              <label for="zipCode" class="form-label">{{ __('Zip Code') }}</label>
              <input class="form-control" type="text" id="zipCode" name="zip_code"
                     value="{{ old('zip_code', $u?->zip_code) }}" />
            </div>

            <div class="col-md-6">
              <label class="form-label" for="country">{{ __('Country') }}</label>
              @php($country = old('country', $u?->country) ?? 'France')
              <select id="country" name="country" class="form-select">
                <option value="">{{ __('Select') }}</option>
                @foreach(["France","Cambodia","United Kingdom","Korea"] as $c)
                  <option value="{{ $c }}" {{ $country === $c ? 'selected' : '' }}>{{ __($c) }}</option>
                @endforeach
              </select>
            </div>

            {{-- Currency --}}
            <div class="col-md-6">
              <label for="currency" class="form-label">{{ __('Currency') }}</label>
              @php($cur = old('currency', $u?->currency) ?? 'EUR')
              <select id="currency" name="currency" class="form-select">
                <option value="">{{ __('Select') }}</option>
                <option value="EUR" {{ $cur === 'EUR' ? 'selected' : '' }}>EUR ({{ __('Euro') }})</option>
                <option value="USD" {{ $cur === 'USD' ? 'selected' : '' }}>USD ({{ __('US Dollar') }})</option>
                <option value="KHR" {{ $cur === 'KHR' ? 'selected' : '' }}>KHR ({{ __('Riel') }})</option>
                <option value="KRW" {{ $cur === 'KRW' ? 'selected' : '' }}>KRW ({{ __('Won') }})</option>
              </select>
            </div>

          </div>

          <div class="mt-6">
            <button type="submit" class="btn btn-primary me-3">{{ __('Save changes') }}</button>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
  document.addEventListener('DOMContentLoaded', function () {

    // PASSWORD TOGGLE
    document.querySelectorAll('.toggle-password').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (!input) return;

        const icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon?.classList.remove('bx-hide');
          icon?.classList.add('bx-show');
        } else {
          input.type = 'password';
          icon?.classList.remove('bx-show');
          icon?.classList.add('bx-hide');
        }
      });
    });

    // AVATAR PREVIEW + RESET
    const uploadInput    = document.getElementById('upload');
    const avatarImg      = document.getElementById('uploadedAvatar');
    const resetBtn       = document.getElementById('resetAvatarBtn');
    const fallbackAvatar = document.getElementById('fallbackAvatar')?.value || '';
    const removeAvatar   = document.getElementById('removeAvatar');

    uploadInput?.addEventListener('change', function () {
      const file = this.files && this.files[0];
      if (!file) return;
      if (removeAvatar) removeAvatar.value = '0';
      avatarImg.src = URL.createObjectURL(file);
    });

    resetBtn?.addEventListener('click', function () {
      if (uploadInput) uploadInput.value = '';
      if (fallbackAvatar) avatarImg.src = fallbackAvatar;
      if (removeAvatar) removeAvatar.value = '1';
    });

    // PHONE VALIDATION
    const form          = document.getElementById('formAccountSettings');
    const phoneCountry  = document.getElementById('phoneCountry');
    const phoneLocal    = document.getElementById('phoneLocal');
    const phoneHidden   = document.getElementById('phoneHidden');
    const countrySelect = document.getElementById('country');

    function digitsOnly(v) { return (v || '').replace(/\D/g, ''); }

    function getRule() {
      const code = phoneCountry?.value;
      if (code === '+33') return { max: 9,  country: 'France' };
      if (code === '+44') return { max: 10, country: 'United Kingdom' };
      return { max: 15, country: '' };
    }

    function syncCountryFromPhoneCountry() {
      if (!countrySelect || !phoneCountry) return;
      const opt = phoneCountry.options[phoneCountry.selectedIndex];
      const c = opt?.dataset?.country;
      if (c) countrySelect.value = c;
    }

    function applyPhone() {
      if (!phoneCountry || !phoneLocal || !phoneHidden) return;
      const r = getRule();
      phoneLocal.maxLength = r.max;
      phoneLocal.value = digitsOnly(phoneLocal.value).slice(0, r.max);
      phoneHidden.value = phoneCountry.value + phoneLocal.value;
    }

    phoneLocal?.addEventListener('keydown', (e) => {
      const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
      if (allowed.includes(e.key)) return;
      if (e.ctrlKey || e.metaKey) return;
      if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    phoneLocal?.addEventListener('paste', (e) => {
      const pasted = (e.clipboardData || window.clipboardData).getData('text');
      if (/\D/.test(pasted)) e.preventDefault();
    });

    phoneLocal?.addEventListener('input', applyPhone);

    phoneCountry?.addEventListener('change', () => {
      syncCountryFromPhoneCountry();
      applyPhone();
    });

    const existing = phoneHidden?.value || '';
    if (phoneCountry && phoneLocal) {
      if (existing.startsWith('+33')) {
        phoneCountry.value = '+33';
        phoneLocal.value = digitsOnly(existing.replace('+33', ''));
      } else if (existing.startsWith('+44')) {
        phoneCountry.value = '+44';
        phoneLocal.value = digitsOnly(existing.replace('+44', ''));
      } else {
        phoneCountry.value = '+33';
        phoneLocal.value = digitsOnly(existing);
      }
    }

    syncCountryFromPhoneCountry();
    applyPhone();

    form?.addEventListener('submit', (e) => {
      const r = getRule();
      const len = digitsOnly(phoneLocal?.value || '').length;

      if ((phoneCountry.value === '+33' || phoneCountry.value === '+44') && len !== r.max) {
        e.preventDefault();
        alert(`Phone number must be exactly ${r.max} digits for ${r.country}.`);
        phoneLocal?.focus();
        return;
      }
      applyPhone();
    });

    // ADDRESS AUTOCOMPLETE (NOMINATIM)
    const addressInput  = document.getElementById('addressInput');
    const suggestionBox = document.getElementById('addressSuggestions');
    const stateEl       = document.getElementById('state');
    const zipEl         = document.getElementById('zipCode');
    const countryEl     = document.getElementById('country');

    let timer;

    function pickCity(a) {
      return (a.city || a.town || a.village || a.municipality || a.hamlet || a.suburb || a.city_district || '');
    }

    function buildShortAddress(a, fallback) {
      return ([a.house_number, a.road].filter(Boolean).join(' ') || a.road || a.name || fallback || '');
    }

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    addressInput?.addEventListener('input', function () {
      clearTimeout(timer);
      const q = this.value.trim();
      if (q.length < 2) {
        if (suggestionBox) suggestionBox.style.display = 'none';
        return;
      }
      timer = setTimeout(() => searchAddress(q), 250);
    });

    async function searchAddress(q) {
      if (!suggestionBox) return;

      try {
        const url =
          `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&accept-language=fr&countrycodes=fr&q=${encodeURIComponent(q)}`;

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        suggestionBox.innerHTML = '';

        (Array.isArray(data) ? data : []).forEach(item => {
          const a = item.address || {};
          const title = buildShortAddress(a, item.display_name);
          const city = pickCity(a);
          const zip  = a.postcode || '';
          const sub  = [city, zip].filter(Boolean).join('  ');
          if (!title) return;

          const div = document.createElement('div');
          div.className = 'item';
          div.innerHTML = `
            <div style="font-weight:600;">${escapeHtml(title)}</div>
            ${sub ? `<div style="font-size:12px;color:#6c757d;">${escapeHtml(sub)}</div>` : ''}
          `;

          div.addEventListener('click', () => {
            addressInput.value = title;
            suggestionBox.style.display = 'none';
            if (stateEl && city) stateEl.value = city;
            if (zipEl && zip) zipEl.value = zip;

            const country = (a.country || '').trim();
            if (countryEl && country) {
              const opts = Array.from(countryEl.options).map(o => o.value);
              const found = opts.find(v => v.toLowerCase() === country.toLowerCase());
              if (found) countryEl.value = found;
            }
          });

          suggestionBox.appendChild(div);
        });

        suggestionBox.style.display = suggestionBox.childElementCount ? 'block' : 'none';
      } catch (e) {
        console.error(e);
        suggestionBox.style.display = 'none';
      }
    }

    document.addEventListener('click', (e) => {
      if (!addressInput || !suggestionBox) return;
      if (!addressInput.contains(e.target) && !suggestionBox.contains(e.target)) {
        suggestionBox.style.display = 'none';
      }
    });

  });
})();
</script>

@endsection