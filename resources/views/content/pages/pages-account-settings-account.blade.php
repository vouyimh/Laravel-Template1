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
  .required::after{
    content:" *";
    color:#dc3545;
    font-weight:700;
  }
</style>

<div class="row">
  <div class="col-md-12">

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
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
              <input type="file" id="upload" name="avatar" class="account-file-input" hidden accept="image/png, image/jpeg" form="formAccountSettings" />
            </label>

            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
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

            {{-- Password Change --}}
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

            <div class="col-md-6">
              <label for="organization" class="form-label">Organization</label>
              <input type="text" class="form-control" id="organization" name="organization"
                     value="{{ old('organization', $u?->organization) }}" />
            </div>

            {{-- ✅ Phone with Country Code select --}}
            <div class="col-md-6">
              <label class="form-label required" for="phoneLocal">Phone Number</label>

              <div class="input-group">
                <select class="form-select" id="phoneCountry" style="max-width: 210px;">
                  <option value="+33"  data-country="France">France (+33)</option>
                  <option value="+855" data-country="Cambodia">Cambodia (+855)</option>
                  <option value="+44"  data-country="United Kingdom">United Kingdom (+44)</option>
                  <option value="+1"   data-country="United States">United States (+1)</option>
                  <option value="+82"  data-country="Korea, Republic of">Korea (+82)</option>
                  <option value="+84"  data-country="Vietnam">Vietnam (+84)</option>
                  <option value="+66"  data-country="Thailand">Thailand (+66)</option>
                </select>

                <input
                  type="tel"
                  id="phoneLocal"
                  class="form-control"
                  placeholder="Enter number"
                  inputmode="tel"
                  autocomplete="tel"
                  required
                />
              </div>

              {{-- ✅ This hidden input is what is saved to DB --}}
              <input type="hidden" id="phone" name="phone" value="{{ old('phone', $u?->phone) }}">
            </div>

            {{-- ✅ Address with Auto Suggestion (Google Places) --}}
            <div class="col-md-6">
            <label for="address" class="form-label">Address</label>
            <input
                type="text"
                class="form-control"
                id="address"
                name="address"
                value="{{ old('address', $u?->address) }}"
                placeholder="Start typing address..."
                autocomplete="off"
            />
            </div>

            <div class="col-md-6">
              <label for="state" class="form-label">State</label>
              <input class="form-control" type="text" id="state" name="state"
                     value="{{ old('state', $u?->state) }}" placeholder="State/Province" />
            </div>

            <div class="col-md-6">
              <label for="zipCode" class="form-label">Zip Code</label>
              <input type="text" class="form-control" id="zipCode" name="zip_code"
                     value="{{ old('zip_code', $u?->zip_code) }}" maxlength="20" placeholder="Zip code" />
            </div>

            <div class="col-md-6">
              <label class="form-label" for="country">Country</label>
              <select id="country" name="country" class="select2 form-select">
                @php($country = old('country', $u?->country))
                <option value="">Select</option>
                @foreach(["Cambodia","France","United Kingdom","United States","Korea, Republic of","Vietnam","Thailand"] as $c)
                  <option value="{{ $c }}" {{ $country === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="language" class="form-label">Language</label>
              <select id="language" name="language" class="select2 form-select">
                @php($lang = old('language', $u?->language))
                <option value="">Select Language</option>
                <option value="en" {{ $lang === 'en' ? 'selected' : '' }}>English</option>
                <option value="fr" {{ $lang === 'fr' ? 'selected' : '' }}>French</option>
                <option value="km" {{ $lang === 'km' ? 'selected' : '' }}>Khmer</option>
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

{{-- ✅ JS: avatar reset + phone combine + address autocomplete --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Avatar reset/preview
  const uploadInput = document.getElementById('upload');
  const avatarImg = document.getElementById('uploadedAvatar');
  const resetBtn = document.querySelector('.account-image-reset');
  const defaultAvatar = document.getElementById('defaultAvatar')?.value;

  if (uploadInput && avatarImg) {
    uploadInput.addEventListener('change', function () {
      const file = this.files && this.files[0];
      if (!file) return;
      avatarImg.src = URL.createObjectURL(file);
    });
  }

  if (resetBtn && avatarImg && uploadInput) {
    resetBtn.addEventListener('click', function () {
      uploadInput.value = '';
      if (defaultAvatar) avatarImg.src = defaultAvatar;
    });
  }

  // Phone combine: country code + local -> hidden "phone"
  const phoneCountry = document.getElementById('phoneCountry');
  const phoneLocal = document.getElementById('phoneLocal');
  const phoneHidden = document.getElementById('phone');
  const countrySelect = document.getElementById('country');

  function setPhoneHidden() {
    if (!phoneCountry || !phoneLocal || !phoneHidden) return;
    const code = phoneCountry.value || '';
    const local = (phoneLocal.value || '').replace(/\s+/g,'').replace(/^0+/, '');
    phoneHidden.value = code + local;
  }

  function syncCountryFromPhoneCountry() {
    if (!phoneCountry || !countrySelect) return;
    const selected = phoneCountry.options[phoneCountry.selectedIndex];
    const c = selected?.dataset?.country;
    if (c) countrySelect.value = c;
  }

  // If DB already has phone like +85512345678 -> split it
  const existingPhone = phoneHidden?.value || '';
  if (existingPhone.startsWith('+')) {
    const codes = Array.from(phoneCountry.options).map(o => o.value).sort((a,b)=>b.length-a.length);
    const match = codes.find(code => existingPhone.startsWith(code));
    if (match) {
      phoneCountry.value = match;
      phoneLocal.value = existingPhone.replace(match, '');
      syncCountryFromPhoneCountry();
    }
  }

  if (phoneCountry) phoneCountry.addEventListener('change', () => { setPhoneHidden(); syncCountryFromPhoneCountry(); });
  if (phoneLocal) phoneLocal.addEventListener('input', setPhoneHidden);
  setPhoneHidden();

});
</script>

{{-- ✅ Google Places Autocomplete (Address Suggestions)
     1) Create Google API key
     2) Enable: Places API
     3) Replace YOUR_GOOGLE_API_KEY below
--}}
<script
  src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_API_KEY&libraries=places&callback=initAddressAutocomplete"
  async defer></script>

<script>
function initAddressAutocomplete() {
  const input = document.getElementById('address');
  if (!input || !window.google || !google.maps || !google.maps.places) return;

  const autocomplete = new google.maps.places.Autocomplete(input, {
    types: ['geocode'],
  });

  autocomplete.addListener('place_changed', function () {
    const place = autocomplete.getPlace();
    // You can also parse place.address_components if you want to auto-fill state/zip/country.
  });
}
</script>

@endsection
