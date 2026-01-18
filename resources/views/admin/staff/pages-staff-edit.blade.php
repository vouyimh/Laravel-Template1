@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Staff')

@section('content')
<style>
  .required::after { content:" *"; color:#dc3545; font-weight:700; }
  .address-wrapper { position: relative; }
  #addressSuggestions {
    position: absolute; top: calc(100% + 4px); left:0; right:0;
    max-height:220px; overflow-y:auto; background:#fff;
    border:1px solid #dee2e6; border-radius:6px; z-index:2000;
    box-shadow:0 4px 12px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.06);
  }
  #addressSuggestions .item { padding:8px 12px; cursor:pointer; border-bottom:1px solid #f0f0f0; }
  #addressSuggestions .item:last-child { border-bottom:none; }
  #addressSuggestions .item:hover { background:#f5f6f8; }
  .address-space { margin-bottom:120px; }
</style>

@php
    $defaultAvatar = asset('assets/img/avatars/staff.jpg');
    $imgUrl = !empty($staff->ProfilePicture)
      ? asset('storage/'.$staff->ProfilePicture)
      : $defaultAvatar;

    $fullName = trim(($staff->FirstName ?? '').' '.($staff->LastName ?? ''));
    $savedWorkType = old('EmploymentType', $staff->EmploymentType ?? '');
    $savedAddress  = old('Address', $staff->Address ?? '');
@endphp

<input type="hidden" id="savedWorkType" value="{{ addslashes($savedWorkType) }}">
<input type="hidden" id="savedAddress" value="{{ addslashes($savedAddress) }}">

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1">Edit Staff</h4>
      <div class="text-muted small">Update staff information and profile photo.</div>
    </div>
    <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">
      <i class="bx bx-arrow-back me-1"></i> Back to List
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">Please fix the errors below:</div>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card mx-auto shadow-sm" style="max-width:900px;">
    <div class="card-body p-4 p-md-5">
      <form method="POST" action="{{ route('admin.staff.pages-staff-edit', $staff->StaffID) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
          {{-- LEFT PROFILE --}}
          <div class="col-md-4 text-center">
            <input id="ProfilePicture" name="ProfilePicture" type="file" class="d-none" accept="image/*">
            <div class="position-relative d-inline-block">
              <label for="ProfilePicture" style="cursor:pointer;">
                <div class="rounded-circle border overflow-hidden shadow-sm" style="width:160px;height:160px;">
                  <img id="profilePreview" src="{{ $imgUrl }}" alt="Preview" class="w-100 h-100" style="object-fit:cover;" onerror="this.src='{{ $defaultAvatar }}'">
                </div>
              </label>
              <label for="ProfilePicture" class="position-absolute bottom-0 end-0 translate-middle p-2 bg-primary border border-light rounded-circle shadow" style="cursor:pointer;" title="Upload photo">
                <i class="bx bx-camera text-white"></i>
              </label>
            </div>
            <div class="mt-3">
              <div class="fw-semibold">{{ $fullName ?: 'Staff' }}</div>
              <div class="text-muted small">JPG/PNG/WebP • Max 2MB</div>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <label for="ProfilePicture" class="btn btn-sm btn-primary mb-0"><i class="bx bx-upload me-1"></i> Change</label>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="removeBtn" style="display:none;"><i class="bx bx-trash me-1"></i> Remove</button>
            </div>
            <input type="hidden" name="RemoveProfilePicture" id="RemoveProfilePicture" value="0">
          </div>

          {{-- RIGHT FORM --}}
          <div class="col-md-8">
            <div class="row g-3">
              {{-- NAME --}}
              <div class="col-md-6">
                <label class="form-label required">First Name</label>
                <input name="FirstName" type="text" class="form-control" required value="{{ old('FirstName',$staff->FirstName) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label required">Last Name</label>
                <input name="LastName" type="text" class="form-control" required value="{{ old('LastName',$staff->LastName) }}">
              </div>

              {{-- EMAIL --}}
              <div class="col-md-6">
                <label class="form-label required">Email</label>
                <input name="Email" type="email" class="form-control" required value="{{ old('Email',$staff->Email) }}">
              </div>

              {{-- ROLE --}}
              <div class="col-md-6">
                <label class="form-label required">Role</label>
                <select name="Role" id="RoleSelect" class="form-select" required>
                  <option value="">-- Select Role --</option>
                  <option value="Temporary" {{ old('Role',$staff->Role)=='Temporary'?'selected':'' }}>Temporary</option>
                  <option value="Permanent" {{ old('Role',$staff->Role)=='Permanent'?'selected':'' }}>Permanent</option>
                  <option value="Company" {{ old('Role',$staff->Role)=='Company'?'selected':'' }}>Company</option>
                </select>
              </div>

              {{-- WORK TYPE --}}
              <div class="col-md-6">
                <label class="form-label required">Work Type</label>
                <select name="EmploymentType" id="EmploymentType" class="form-select" required></select>
              </div>

              {{-- USERNAME --}}
              <div class="col-md-6">
                <label class="form-label required">Username</label>
                <input name="Username" type="text" class="form-control" required value="{{ old('Username',$staff->Username) }}">
              </div>

              {{-- PHONE --}}
              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input name="PhoneNumber" type="text" class="form-control" value="{{ old('PhoneNumber',$staff->PhoneNumber) }}">
              </div>

              {{-- PASSWORD --}}
              <div class="col-md-12">
                <label class="form-label">New Password (optional)</label>
                <input name="Password" type="password" class="form-control" placeholder="Leave blank to keep current password">
              </div>

              {{-- ADDRESS --}}
              <div class="col-md-12 address-space">
                <label class="form-label required">Address</label>
                <div class="address-wrapper">
                  <input name="Address" id="addressInput" class="form-control" value="{{ $savedAddress }}" required>
                  <div id="addressSuggestions" style="display:none;"></div>
                </div>
              </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Update</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  // ===== IMAGE =====
  const input = document.getElementById('ProfilePicture');
  const preview = document.getElementById('profilePreview');
  const removeBtn = document.getElementById('removeBtn');
  const removeFlag = document.getElementById('RemoveProfilePicture');
  const defaultAvatar = "{{ $defaultAvatar }}";
  const maxSize = 2*1024*1024;

  if(preview.src && preview.src!==defaultAvatar) removeBtn.style.display='inline-flex';

  input.addEventListener('change', e=>{
    const file = e.target.files[0];
    if(!file) return;
    if(!file.type.startsWith('image/')) { alert('Choose image'); input.value=''; return; }
    if(file.size>maxSize) { alert('Image too large'); input.value=''; return; }
    removeFlag.value='0';
    preview.src = URL.createObjectURL(file);
    removeBtn.style.display='inline-flex';
  });

  removeBtn.addEventListener('click', ()=>{
    input.value=''; preview.src=defaultAvatar; removeFlag.value='1'; removeBtn.style.display='none';
  });

  // ===== ROLE → WORK TYPE =====
  const roleSelect = document.getElementById('RoleSelect');
  const workSelect = document.getElementById('EmploymentType');
  const savedWorkType = document.getElementById('savedWorkType').value.trim();

  const map = {
      temporary: ['Part Time','Full Time'],
      permanent: ['Part Time','Full Time'],
      company: ['Calculate Time','Contractor']
  };

  function renderWorkType() {
      const roleKey = (roleSelect.value || '').toLowerCase();
      const list = map[roleKey] || [];

      workSelect.innerHTML = '';
      workSelect.add(new Option('-- Select Work Type --',''));

      const normalize = str => str.toLowerCase().replace(/\s+/g,'');

      list.forEach(v => {
          const option = new Option(v, v);
          if(savedWorkType && normalize(v) === normalize(savedWorkType)) {
              option.selected = true;
          }
          workSelect.add(option);
      });

      if (!list.some(v => normalize(v) === normalize(savedWorkType))) {
          workSelect.value = '';
      }

      workSelect.disabled = list.length === 0;
  }

  // Initial render
  renderWorkType();
  roleSelect.addEventListener('change', renderWorkType);

  // ===== ADDRESS AUTOCOMPLETE =====
  const addressInput = document.getElementById('addressInput');
  const suggestionBox = document.getElementById('addressSuggestions');
  const savedAddress = document.getElementById('savedAddress').value.trim();
  addressInput.value = savedAddress;

  let timer;
  addressInput.addEventListener('input', function() {
      clearTimeout(timer);
      const q = this.value.trim();
      if(q.length < 2){ suggestionBox.style.display='none'; return; }
      timer = setTimeout(()=>searchAddress(q),300);
  });

  async function searchAddress(q){
      try{
          const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&q=${encodeURIComponent(q)}`, { headers:{'Accept-Language':'fr'} });
          const data = await res.json();
          suggestionBox.innerHTML='';

          data.forEach(item=>{
              const a=item.address;
              const parts=[];
              if(a.road) parts.push(a.road);
              if(a.postcode) parts.push(a.postcode);
              if(a.city) parts.push(a.city);
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
      } catch(e){ console.error(e); suggestionBox.style.display='none'; }
  }

  document.addEventListener('click', e=>{
      if(!addressInput.contains(e.target) && !suggestionBox.contains(e.target)) suggestionBox.style.display='none';
  });

})();
</script>
@endsection
