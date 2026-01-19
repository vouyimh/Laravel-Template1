@extends('layouts/contentNavbarLayout')

@section('title', 'Account settings - Account')

@section('page-script')
@vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
@php
  // TEST MODE (no login yet)
  $u = \App\Models\User::where('role','admin')->first();
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
            src="{{ $u && $u->avatar_path ? asset('storage/'.$u->avatar_path) : asset('assets/img/avatars/1.png') }}"
            alt="user-avatar"
            class="d-block w-px-100 h-px-100 rounded"
            id="uploadedAvatar"
          />

          <input type="hidden" id="defaultAvatar"
                 value="{{ $u && $u->avatar_path ? asset('storage/'.$u->avatar_path) : asset('assets/img/avatars/1.png') }}">

          <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="icon-base bx bx-upload d-block d-sm-none"></i>
              <input type="file" id="upload" name="avatar" class="account-file-input" hidden
                     accept="image/png, image/jpeg" form="formAccountSettings" />
            </label>

            <button type="button" class="btn btn-outline-secondary mb-4" id="resetAvatarBtn">
              <i class="icon-base bx bx-reset d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Reset</span>
            </button>

            <div>Allowed JPG, GIF or PNG. Max size of 800K</div>
          </div>

        </div>
      </div>

      <div class="card-body pt-4">
        <form id="formAccountSettings"
              method="POST"
              action="{{ route('pages-account-settings-account.update') }}"
              enctype="multipart/form-data">
          @csrf

          <div class="row g-6">

            <div class="col-md-6">
              <label for="firstName" class="form-label required">First Name</label>
              <input class="form-control" type="text" id="firstName" name="first_name"
                     value="{{ old('first_name', $u?->first_name) }}" required />
            </div>

            <div class="col-md-6">
              <label for="lastName" class="form-label required">Last Name</label>
              <input class="form-control" type="text" id="lastName" name="last_name"
                     value="{{ old('last_name', $u?->last_name) }}" required />
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label required">E-mail</label>
              <input class="form-control" type="email" id="email" name="email"
                     value="{{ old('email', $u?->email) }}" required />
            </div>

            <div class="col-md-6">
              <label for="organization" class="form-label">Organization</label>
              <input type="text" class="form-control" id="organization" name="organization"
                     value="{{ old('organization', $u?->organization) }}" />
            </div>

            {{-- Password Change (optional) --}}
            <div class="col-md-6">
              <label for="currentPassword" class="form-label">Current Password</label>
              <input class="form-control" type="password" id="currentPassword" name="current_password" autocomplete="current-password" />
            </div>

            <div class="col-md-6">
              <label for="newPassword" class="form-label">New Password</label>
              <input class="form-control" type="password" id="newPassword" name="password" autocomplete="new-password" />
            </div>

            <div class="col-md-6">
              <label for="confirmPassword" class="form-label">Confirm New Password</label>
              <input class="form-control" type="password" id="confirmPassword" name="password_confirmation" autocomplete="new-password" />
            </div>

            {{-- Phone (FR/UK format validation) --}}
            <div class="col-md-6">
            <label class="form-label required" for="phoneLocal">Phone Number</label>

            <div class="input-group">
                @php $savedPhone = old('phone', $u?->phone) ?? ''; @endphp

                <select class="form-select" id="phoneCountry" style="max-width:240px;">
                    <option value="+33" data-country="France">France (+33)</option>
                    <option value="+44" data-country="United Kingdom">United Kingdom (+44)</option>
                </select>

                <input
                type="text"
                id="phoneLocal"
                class="form-control"
                placeholder="Digits only"
                inputmode="numeric"
                autocomplete="tel"
                required
                />
                <input type="hidden" id="phone" name="phone" value="{{ old('phone', $u?->phone) }}">
            </div>

            <input type="hidden" id="phone" name="phone" value="{{ $savedPhone }}">
            </div>

            {{-- Address (French suggestion + fills state/zip/country) --}}
            <div class="col-md-6">
              <label class="form-label">Address</label>
              <div class="address-wrapper">
                <input
                  name="address"
                  id="addressInput"
                  class="form-control"
                  placeholder="Start typing address..."
                  autocomplete="off"
                  value="{{ old('address', $u?->address) }}"
                />
                <div id="addressSuggestions" style="display:none;"></div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="state" class="form-label">State / Region</label>
              <input class="form-control" type="text" id="state" name="state"
                     value="{{ old('state', $u?->state) }}" />
            </div>

            <div class="col-md-6">
              <label for="zipCode" class="form-label">Zip Code</label>
              <input class="form-control" type="text" id="zipCode" name="zip_code"
                     value="{{ old('zip_code', $u?->zip_code) }}" />
            </div>

            <div class="col-md-6">
              <label class="form-label" for="country">Country</label>
              @php($country = old('country', $u?->country) ?? 'France')
              <select id="country" name="country" class="form-select">
                <option value="">Select</option>
                @foreach(["France","Cambodia","United Kingdom","Korea"] as $c)
                  <option value="{{ $c }}" {{ $country === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
            </div>

            <!-- <div class="col-md-6">
              <label for="language" class="form-label">Language</label>
              @php($lang = old('language', $u?->language) ?? 'fr')
              <select id="language" name="language" class="form-select">
                <option value="">Select</option>
                <option value="fr" {{ $lang === 'fr' ? 'selected' : '' }}>French (fr)</option>
                <option value="en" {{ $lang === 'en' ? 'selected' : '' }}>English (en)</option>
              </select>
            </div> -->

            <!-- <div class="col-md-6">
              <label for="timezone" class="form-label">Timezone</label>
              @php($tz = old('timezone', $u?->timezone) ?? 'Europe/Paris')
              <select id="timezone" name="timezone" class="form-select">
                <option value="">Select</option>
                <option value="Europe/Paris" {{ $tz === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (France)</option>
                <option value="Asia/Phnom_Penh" {{ $tz === 'Asia/Phnom_Penh' ? 'selected' : '' }}>Asia/Phnom_Penh (Cambodia)</option>
                <option value="Europe/London" {{ $tz === 'Europe/London' ? 'selected' : '' }}>Europe/London (UK)</option>
                <option value="America/New_York" {{ $tz === 'America/New_York' ? 'selected' : '' }}>America/New_York (US)</option>
                <option value="Asia/Seoul" {{ $tz === 'Asia/Seoul' ? 'selected' : '' }}>Asia/Seoul (Korea)</option>
                <option value="Asia/Ho_Chi_Minh" {{ $tz === 'Asia/Ho_Chi_Minh' ? 'selected' : '' }}>Asia/Ho_Chi_Minh (Vietnam)</option>
              </select>
            </div> -->

            {{-- Currency (default EUR) --}}
            <div class="col-md-6">
              <label for="currency" class="form-label">Currency</label>
              @php($cur = old('currency', $u?->currency) ?? 'EUR')
              <select id="currency" name="currency" class="form-select">
                <option value="">Select</option>
                <option value="EUR" {{ $cur === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                <option value="USD" {{ $cur === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                <option value="KHR" {{ $cur === 'KHR' ? 'selected' : '' }}>KHR (Riel)</option>
                <option value="KRW" {{ $cur === 'KRW' ? 'selected' : '' }}>KRW (Won)</option>
              </select>
            </div>

          </div>

          <div class="mt-6">
            <button type="submit" class="btn btn-primary me-3">Save changes</button>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
  document.addEventListener('DOMContentLoaded', function () {

    // =========================
    // AVATAR PREVIEW + RESET
    // =========================
    const uploadInput   = document.getElementById('upload');
    const avatarImg     = document.getElementById('uploadedAvatar');
    const resetBtn      = document.getElementById('resetAvatarBtn');
    const defaultAvatar = document.getElementById('defaultAvatar')?.value;

    uploadInput?.addEventListener('change', function () {
      const file = this.files && this.files[0];
      if (!file) return;
      avatarImg.src = URL.createObjectURL(file);
    });

    resetBtn?.addEventListener('click', function () {
      if (uploadInput) uploadInput.value = '';
      if (defaultAvatar) avatarImg.src = defaultAvatar;
    });


    // =========================
    // PHONE: DIGITS ONLY + LENGTH RULES
    // Saves to hidden input: phone = +33XXXXXXXXX or +44XXXXXXXXXX
    // =========================
    const form          = document.getElementById('formAccountSettings');
    const phoneCountry  = document.getElementById('phoneCountry');
    const phoneLocal    = document.getElementById('phoneLocal');
    const phoneHidden   = document.getElementById('phone');
    const countrySelect = document.getElementById('country');

    function digitsOnly(v) {
      return (v || '').replace(/\D/g, '');
    }

    function getRule() {
      const code = phoneCountry?.value;
      if (code === '+33') return { min: 9,  max: 9,  country: 'France' };          // FR local digits only
      if (code === '+44') return { min: 10, max: 10, country: 'United Kingdom' };  // UK local digits only
      return { min: 7, max: 15, country: '' }; // fallback
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

      // enforce max length
      phoneLocal.maxLength = r.max;

      // sanitize + cut to max
      phoneLocal.value = digitsOnly(phoneLocal.value).slice(0, r.max);

      // save full value (+code + local digits)
      phoneHidden.value = phoneCountry.value + phoneLocal.value;
    }

    // Block non-digit key presses
    phoneLocal?.addEventListener('keydown', (e) => {
      const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
      if (allowed.includes(e.key)) return;
      if (e.ctrlKey || e.metaKey) return; // allow shortcuts

      if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    // Prevent pasting letters/symbols
    phoneLocal?.addEventListener('paste', (e) => {
      const pasted = (e.clipboardData || window.clipboardData).getData('text');
      if (/\D/.test(pasted)) e.preventDefault();
    });

    // Sanitize on any input
    phoneLocal?.addEventListener('input', applyPhone);

    phoneCountry?.addEventListener('change', () => {
      syncCountryFromPhoneCountry();
      applyPhone();
    });

    // Load existing phone from DB into select + local
    const existing = phoneHidden?.value || '';
    if (phoneCountry && phoneLocal) {
      if (existing.startsWith('+33')) {
        phoneCountry.value = '+33';
        phoneLocal.value = digitsOnly(existing.replace('+33', ''));
      } else if (existing.startsWith('+44')) {
        phoneCountry.value = '+44';
        phoneLocal.value = digitsOnly(existing.replace('+44', ''));
      } else {
        // default France
        phoneCountry.value = '+33';
        phoneLocal.value = digitsOnly(existing);
      }
    }

    syncCountryFromPhoneCountry();
    applyPhone();

    // Optional: block submit if wrong length
    form?.addEventListener('submit', (e) => {
      const r = getRule();
      const len = digitsOnly(phoneLocal?.value || '').length;

      // only strict check for FR/UK
      if ((phoneCountry.value === '+33' || phoneCountry.value === '+44') && len !== r.max) {
        e.preventDefault();
        alert(`Phone number must be exactly ${r.max} digits for ${r.country}.`);
        phoneLocal?.focus();
        return;
      }

      applyPhone();
    });


    // =========================
    // ADDRESS AUTOCOMPLETE (NOMINATIM) + FILL STATE/ZIP/COUNTRY
    // =========================
    const addressInput  = document.getElementById('addressInput');
    const suggestionBox = document.getElementById('addressSuggestions');
    const stateEl       = document.getElementById('state');
    const zipEl         = document.getElementById('zipCode');
    const countryEl     = document.getElementById('country');

    let timer;

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
        // ✅ French suggestions
        const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&accept-language=fr&q=${encodeURIComponent(q)}`;
        const res = await fetch(url);
        const data = await res.json();

        suggestionBox.innerHTML = '';

        data.forEach(item => {
          const a = item.address || {};
          const line = item.display_name || '';

          const div = document.createElement('div');
          div.className = 'item';
          div.textContent = line;

          div.addEventListener('click', () => {
            addressInput.value = line;
            suggestionBox.style.display = 'none';

            const region  = a.state || a.region || a.county || '';
            const zip     = a.postcode || '';
            const country = a.country || '';

            if (stateEl && region) stateEl.value = region;
            if (zipEl && zip) zipEl.value = zip;

            if (countryEl && country) {
              const opts = Array.from(countryEl.options).map(o => o.value);
              const found = opts.find(v => v.toLowerCase() === country.toLowerCase());
              if (found) countryEl.value = found;
            }
          });

          suggestionBox.appendChild(div);
        });

        suggestionBox.style.display = data.length ? 'block' : 'none';
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
