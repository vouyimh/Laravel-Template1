@extends('layouts/contentNavbarLayout')

@section('title', 'Add Staff')

@section('content')
<style>
  .required::after{
    content:" *";
    color:#dc3545;
    font-weight:700;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header (same style as Edit) --}}
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1">Add Staff</h4>
      <div class="text-muted small">Create a new staff account and upload a profile photo.</div>
    </div>

    <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">
      <i class="bx bx-arrow-back me-1"></i> Back to List
    </a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">Please fix the errors below:</div>
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    // ✅ Use staff.jpg like Edit (avoid default.png 404)
    $defaultAvatar = asset('assets/img/avatars/staff.jpg');
  @endphp

  <div class="card mx-auto shadow-sm" style="max-width: 900px;">
    <div class="card-body p-4 p-md-5">

      {{-- ✅ POST route for saving --}}
      <form method="POST" action="{{ route('admin.staff.pages-staff-add') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

          {{-- LEFT: Profile --}}
          <div class="col-md-4">
            <div class="text-center">

              <div class="mb-2">
                <span class="badge bg-label-primary">Profile</span>
              </div>

              <input id="ProfilePicture" name="ProfilePicture" type="file" class="d-none" accept="image/*">

              <div class="position-relative d-inline-block">
                <label for="ProfilePicture" class="d-block m-0 p-0" style="cursor:pointer;">
                  <div class="rounded-circle border overflow-hidden shadow-sm" style="width:160px; height:160px;">
                    <img id="profilePreview"
                         src="{{ $defaultAvatar }}"
                         alt="Preview"
                         class="w-100 h-100"
                         style="object-fit: cover;"
                         onerror="this.src='{{ $defaultAvatar }}'">
                  </div>
                </label>

                <label for="ProfilePicture"
                       class="position-absolute bottom-0 end-0 translate-middle p-2 bg-primary border border-light rounded-circle shadow"
                       style="cursor:pointer;"
                       title="Upload photo">
                  <i class="bx bx-camera text-white"></i>
                </label>
              </div>

              <div class="mt-3">
                <div class="fw-semibold">Profile Picture</div>
                <div class="text-muted small">JPG/PNG/WebP • Max 2MB</div>
              </div>

              <div id="fileMeta" class="mt-2 small text-muted" style="display:none;"></div>

              <div class="d-flex justify-content-center gap-2 mt-3">
                <label for="ProfilePicture" class="btn btn-sm btn-primary mb-0">
                  <i class="bx bx-upload me-1"></i> Change
                </label>

                <button type="button" class="btn btn-sm btn-outline-secondary" id="removeBtn" style="display:none;">
                  <i class="bx bx-trash me-1"></i> Remove
                </button>
              </div>

              @error('ProfilePicture')
                <div class="text-danger small mt-2">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- RIGHT: Form --}}
          <div class="col-md-8">
            <div class="mb-2">
              <span class="badge bg-label-secondary">Information</span>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required">First Name</label>
                <input name="FirstName" type="text"
                       class="form-control @error('FirstName') is-invalid @enderror"
                       required value="{{ old('FirstName') }}">
                @error('FirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label required">Last Name</label>
                <input name="LastName" type="text"
                       class="form-control @error('LastName') is-invalid @enderror"
                       required value="{{ old('LastName') }}">
                @error('LastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label required">Email</label>
                <input name="Email" type="email"
                       class="form-control @error('Email') is-invalid @enderror"
                       required value="{{ old('Email') }}">
                @error('Email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label required">Role</label>
                <select name="Role" id="RoleSelect"
                        class="form-select @error('Role') is-invalid @enderror"
                        required>
                  <option value="">-- Select Role --</option>
                  <option value="Temporary" {{ old('Role')=='Temporary' ? 'selected' : '' }}>Temporary</option>
                  <option value="Permanent" {{ old('Role')=='Permanent' ? 'selected' : '' }}>Permanent</option>
                  <option value="Company"   {{ old('Role')=='Company' ? 'selected' : '' }}>Company</option>
                </select>
                @error('Role') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- ✅ Work Type (same as Edit behavior) --}}
              <div class="col-md-6">
                <label class="form-label required">Work Type</label>
                <select name="EmploymentType" id="EmploymentType"
                        class="form-select @error('EmploymentType') is-invalid @enderror"
                        required>
                </select>
                @error('EmploymentType') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label required">Username</label>
                <input name="Username" type="text"
                       class="form-control @error('Username') is-invalid @enderror"
                       required value="{{ old('Username') }}">
                @error('Username') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input name="PhoneNumber" type="text"
                       class="form-control @error('PhoneNumber') is-invalid @enderror"
                       value="{{ old('PhoneNumber') }}">
                @error('PhoneNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-12">
                <label class="form-label required">Password</label>
                <input name="Password" type="password"
                       class="form-control @error('Password') is-invalid @enderror"
                       required>
                <div class="form-text">Use at least 6 characters.</div>
                @error('Password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> Save
              </button>
            </div>

          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
(function () {
  // ===== image preview/remove =====
  const input = document.getElementById('ProfilePicture');
  const preview = document.getElementById('profilePreview');
  const removeBtn = document.getElementById('removeBtn');
  const fileMeta = document.getElementById('fileMeta');

  const defaultAvatar = @json($defaultAvatar);
  const maxSize = 2 * 1024 * 1024;
  let objectUrl = null;

  function cleanup() {
    if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
  }
  function setMeta(file) {
    if (!fileMeta) return;
    fileMeta.style.display = 'block';
    fileMeta.innerText = `${file.name} • ${Math.round(file.size/1024)} KB`;
  }
  function clearMeta() {
    if (!fileMeta) return;
    fileMeta.style.display = 'none';
    fileMeta.innerText = '';
  }
  function showRemoveBtn() { if (removeBtn) removeBtn.style.display = 'inline-flex'; }
  function hideRemoveBtn() { if (removeBtn) removeBtn.style.display = 'none'; }

  function resetImage() {
    if (input) input.value = '';
    cleanup();
    if (preview) preview.src = defaultAvatar;
    clearMeta();
    hideRemoveBtn();
  }

  if (input && preview) {
    input.addEventListener('change', function (e) {
      const file = e.target.files && e.target.files[0];
      if (!file) return resetImage();

      if (!file.type.startsWith('image/')) {
        alert('Please choose an image file.');
        return resetImage();
      }
      if (file.size > maxSize) {
        alert('Image is too large. Max 2MB.');
        return resetImage();
      }

      cleanup();
      objectUrl = URL.createObjectURL(file);
      preview.src = objectUrl;

      setMeta(file);
      showRemoveBtn();
    });
  }

  if (removeBtn) removeBtn.addEventListener('click', resetImage);
  window.addEventListener('beforeunload', cleanup);

  // ===== Role -> Work Type (same as Edit) =====
  const roleSelect = document.getElementById('RoleSelect');
  const workTypeSelect = document.getElementById('EmploymentType');

  let savedWorkType = @json(old('EmploymentType', ''));

  const map = {
    temporary: ['Part Time', 'Full Time'],
    permanent: ['Part Time', 'Full Time'],
    company:   ['Calculate Time', 'Contractor']
  };

  function renderWorkType() {
    if (!roleSelect || !workTypeSelect) return;

    const roleKey = (roleSelect.value || '').toLowerCase();
    const list = map[roleKey] || [];

    workTypeSelect.innerHTML = '';
    workTypeSelect.add(new Option('-- Select Work Type --', ''));

    if (!list.length) {
      workTypeSelect.disabled = true;
      return;
    }

    workTypeSelect.disabled = false;
    list.forEach(v => workTypeSelect.add(new Option(v, v)));

    if (savedWorkType) workTypeSelect.value = savedWorkType;
  }

  if (roleSelect && workTypeSelect) {
    roleSelect.addEventListener('change', function () {
      savedWorkType = '';
      renderWorkType();
    });
    renderWorkType();
  }
})();
</script>
@endsection
