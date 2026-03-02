@extends('layouts/contentNavbarLayout')

@section('title', 'Add Staff')

@section('content')
<style>
  .required::after { content:" *"; color:#dc3545; font-weight:700; }

  .address-wrapper { position: relative; }

  #addressSuggestions {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    z-index: 2000;
    box-shadow: 0 4px 12px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.06);
  }

  #addressSuggestions .item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
  #addressSuggestions .item:last-child { border-bottom:none; }
  #addressSuggestions .item:hover { background:#f5f6f8; }

  .address-space { margin-bottom: 120px; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Add Staff') }}</h4>
      <div class="text-muted small"> {{ __('Create a new staff account and upload a profile photo.') }}</div>
    </div>
    <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">
      <i class="bx bx-arrow-back me-1"></i>  {{ __('Back to List') }}
    </a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">  {{ __('Please fix the errors below:') }}</div>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php $defaultAvatar = asset('assets/img/avatars/staff.jpg'); @endphp

  <div class="card mx-auto shadow-sm" style="max-width:900px;">
    <div class="card-body p-4 p-md-5">
      <form method="POST" action="{{ route('admin.staff.pages-staff-add') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

          {{-- LEFT PROFILE --}}
          <div class="col-md-4 text-center">
            <input id="ProfilePicture" name="ProfilePicture" type="file" class="d-none" accept="image/*">
            <div class="position-relative d-inline-block">
              <label for="ProfilePicture" style="cursor:pointer;">
                <div class="rounded-circle border overflow-hidden" style="width:160px;height:160px;">
                  <img id="profilePreview" src="{{ $defaultAvatar }}" class="w-100 h-100" style="object-fit:cover;">
                </div>
              </label>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <label for="ProfilePicture" class="btn btn-sm btn-primary mb-0"><i class="bx bx-upload me-1"></i>{{ __('Change') }}</label>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="removeBtn" style="display:none;"><i class="bx bx-trash me-1"></i>{{ __('Remove') }}</button>
            </div>
          </div>

          {{-- RIGHT FORM --}}
          <div class="col-md-8">
            <div class="row g-3">

              {{-- NAME --}}
              <div class="col-md-6">
                <label class="form-label required">{{ __('First Name') }}</label>
                <input name="FirstName" class="form-control" required value="{{ old('FirstName') }}">
              </div>
              <div class="col-md-6">
                <label class="form-label required">{{ __('Last Name') }}</label>
                <input name="LastName" class="form-control" required value="{{ old('LastName') }}">
              </div>

              {{-- EMAIL --}}
              <div class="col-md-6">
                <label class="form-label required">{{ __('Email') }}</label>
                <input name="Email" type="email" class="form-control" required value="{{ old('Email') }}">
              </div>

              {{-- ROLE --}}
              <div class="col-md-6">
                <label class="form-label required">{{ __('Role') }}</label>
                <select name="Role" id="RoleSelect" class="form-select" required>
                  <option value="">{{ __('-- Select Role --') }}</option>
                  <option value="Temporary">{{ __('Temporary') }}</option>
                  <option value="Permanent">{{ __('Permanent') }}</option>
                  <option value="Company">{{ __('Company') }}</option>
                </select>
              </div>

              {{-- WORK TYPE --}}
              <div class="col-md-6">
                <label class="form-label required">{{ __('Work Type') }}</label>
                <select name="EmploymentType" id="EmploymentType" class="form-select" required>
                  <option value="">{{ __('-- Select Work Type --') }}</option>
                </select>
              </div>

              {{-- USERNAME --}}
              <div class="col-md-6">
                <label class="form-label required">{{ __('Username') }}</label>
                <input name="Username" class="form-control" required value="{{ old('Username') }}">
              </div>

              {{-- PHONE --}}
              <div class="col-md-6">
                <label class="form-label">{{ __('Phone Number') }}</label>
                <input name="PhoneNumber" class="form-control" value="{{ old('PhoneNumber') }}">
              </div>

              {{-- PASSWORD --}}
              <div class="col-md-6">
                <label class="form-label required">{{ __('Password') }}</label>
                <input name="Password" type="password" class="form-control" required>
              </div>

              {{-- ADDRESS --}}
              <div class="col-md-12 address-space">
                <label class="form-label required">{{ __('Address') }}</label>
                <div class="address-wrapper">
                  <input name="Address" id="addressInput" class="form-control" placeholder="{{ __('Start typing address...') }}" autocomplete="off" required>
                  <div id="addressSuggestions" style="display:none;"></div>
                </div>
              </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
              <button class="btn btn-primary"><i class="bx bx-save me-1"></i> {{ __('Save') }}</button>
            </div>

          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  // ===== IMAGE PREVIEW =====
  const input = document.getElementById('ProfilePicture');
  const preview = document.getElementById('profilePreview');
  const removeBtn = document.getElementById('removeBtn');
  const defaultAvatar = "{{ $defaultAvatar }}";

  input.addEventListener('change', e=>{
    const file = e.target.files[0];
    if(!file) return;
    preview.src = URL.createObjectURL(file);
    removeBtn.style.display = 'inline-flex';
  });

  removeBtn.addEventListener('click', ()=>{
    input.value=''; preview.src = defaultAvatar; removeBtn.style.display='none';
  });

  // ===== ROLE → WORK TYPE =====
  const map = {
    temporary: ['Part Time','Full Time'],
    permanent: ['Part Time','Full Time'],
    company: ['Calculate Time','Contractor']
  };

  const roleSelect = document.getElementById('RoleSelect');
  const workSelect = document.getElementById('EmploymentType');

  function renderWorkType() {
    const roleKey = (roleSelect.value || '').toLowerCase();
    const list = map[roleKey] || [];
    workSelect.innerHTML = '';
    workSelect.add(new Option('-- Select Work Type --',''));
    list.forEach(v=>workSelect.add(new Option(v,v)));
  }

  roleSelect.addEventListener('change', renderWorkType);
  renderWorkType(); // initial render

  // ===== ADDRESS AUTOCOMPLETE =====
  const addressInput = document.getElementById('addressInput');
  const suggestionBox = document.getElementById('addressSuggestions');
  let timer;

  addressInput.addEventListener('input', function() {
    clearTimeout(timer);
    const q = this.value.trim();
    if(q.length<2){ suggestionBox.style.display='none'; return; }
    timer = setTimeout(()=>searchAddress(q),300);
  });

  async function searchAddress(q){
    try{
      const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&q=${encodeURIComponent(q)}`;
      const res = await fetch(url);
      const data = await res.json();

      suggestionBox.innerHTML = '';
      data.forEach(item=>{
        const a = item.address;
        const parts = [];
        if(a.road) parts.push(a.road);
        if(a.postcode) parts.push(a.postcode);
        if(a.city) parts.push(a.city);
        if(a.town) parts.push(a.town);
        if(a.district) parts.push(a.district);
        if(a.state) parts.push(a.state);
        if(a.country) parts.push(a.country);
        const text = parts.join(', ') || item.display_name;
        const div = document.createElement('div');
        div.className='item';
        div.innerText=text;
        div.onclick=()=>{ addressInput.value=text; suggestionBox.style.display='none'; };
        suggestionBox.appendChild(div);
      });

      suggestionBox.style.display = data.length ? 'block' : 'none';
    }catch(e){ console.error(e); suggestionBox.style.display='none'; }
  }

  document.addEventListener('click', e=>{
    if(!addressInput.contains(e.target) && !suggestionBox.contains(e.target)) suggestionBox.style.display='none';
  });

})();
</script>

@endsection
