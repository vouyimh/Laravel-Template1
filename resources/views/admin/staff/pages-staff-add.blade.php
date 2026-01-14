@extends('layouts/contentNavbarLayout')

@section('title', 'Add Staff')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <div class="text-center mb-4">
    <h4 class="fw-bold mb-1">Add Staff</h4>
    <p class="text-muted mb-0">Create a new staff account and upload a profile photo.</p>
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

  <div class="card mx-auto shadow-sm" style="max-width: 900px;">
    <div class="card-body p-4 p-md-5">

      {{-- ✅ POST to your new route name --}}
      <form method="POST" action="{{ route('admin.staff.add') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

          {{-- LEFT: Profile --}}
          <div class="col-md-4">
            <div class="text-center">
              <div class="mb-2">
                <span class="badge bg-label-primary">Profile</span>
              </div>

              {{-- File input --}}
              <input id="ProfilePicture" name="ProfilePicture" type="file" class="d-none" accept="image/*">

              <div class="position-relative d-inline-block">
                {{-- click avatar --}}
                <label for="ProfilePicture" class="d-block m-0 p-0" style="cursor:pointer;">
                  <div class="rounded-circle border overflow-hidden shadow-sm" style="width:160px; height:160px;">
                    <img id="profilePreview"
                         src="{{ asset('assets/img/avatars/default.png') }}"
                         alt="Preview"
                         class="w-100 h-100"
                         style="object-fit: cover;">
                  </div>
                </label>

                {{-- camera button --}}
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
                  <i class="bx bx-upload me-1"></i> Upload
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
                <label class="form-label">First Name</label>
                <input name="FirstName" type="text"
                       class="form-control @error('FirstName') is-invalid @enderror"
                       placeholder="First Name" required
                       value="{{ old('FirstName') }}">
                @error('FirstName')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input name="LastName" type="text"
                       class="form-control @error('LastName') is-invalid @enderror"
                       placeholder="Last Name" required
                       value="{{ old('LastName') }}">
                @error('LastName')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input name="Email" type="email"
                       class="form-control @error('Email') is-invalid @enderror"
                       placeholder="Email" required
                       value="{{ old('Email') }}">
                @error('Email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="Role"
                        class="form-select @error('Role') is-invalid @enderror"
                        required>
                  <option value="">-- Select Role --</option>
                  <option value="Temporary" {{ old('Role')=='Temporary' ? 'selected' : '' }}>Temporary</option>
                  <option value="Permanent" {{ old('Role')=='Permanent' ? 'selected' : '' }}>Permanent</option>
                  <option value="Company" {{ old('Role')=='Company' ? 'selected' : '' }}>Company</option>
                </select>
                @error('Role')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Username</label>
                <input name="Username" type="text"
                       class="form-control @error('Username') is-invalid @enderror"
                       placeholder="Username" required
                       value="{{ old('Username') }}">
                @error('Username')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input name="PhoneNumber" type="text"
                       class="form-control @error('PhoneNumber') is-invalid @enderror"
                       placeholder="Phone Number"
                       value="{{ old('PhoneNumber') }}">
                @error('PhoneNumber')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-12">
                <label class="form-label">Password</label>
                <input name="Password" type="password"
                       class="form-control @error('Password') is-invalid @enderror"
                       placeholder="Password" required>
                <div class="form-text">Use at least 6 characters.</div>
                @error('Password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              {{-- ✅ Cancel should go to new staff list route --}}
              <a href="{{ route('admin.staff.pages-staff-list') }}" class="btn btn-outline-secondary">
                Cancel
              </a>

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
@endsection

@section('script')
<script>
(function () {
  const input = document.getElementById('ProfilePicture');
  const preview = document.getElementById('profilePreview');
  const removeBtn = document.getElementById('removeBtn');
  const fileMeta = document.getElementById('fileMeta');

  if (!input || !preview) return;

  const defaultSrc = preview.src;
  const maxSize = 2 * 1024 * 1024; // 2MB
  let objectUrl = null;

  function setMeta(file) {
    if (!fileMeta) return;
    const kb = Math.round(file.size / 1024);
    fileMeta.style.display = 'block';
    fileMeta.innerText = `${file.name} • ${kb} KB`;
  }

  function clearMeta() {
    if (!fileMeta) return;
    fileMeta.style.display = 'none';
    fileMeta.innerText = '';
  }

  function cleanupObjectUrl() {
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = null;
    }
  }

  function resetImage() {
    input.value = '';
    cleanupObjectUrl();
    preview.src = defaultSrc;
    if (removeBtn) removeBtn.style.display = 'none';
    clearMeta();
  }

  input.addEventListener('change', function (e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return resetImage();

    if (!file.type || !file.type.startsWith('image/')) {
      alert('Please choose an image file.');
      return resetImage();
    }

    if (file.size > maxSize) {
      alert('Image is too large. Please select an image under 2MB.');
      return resetImage();
    }

    cleanupObjectUrl();
    objectUrl = URL.createObjectURL(file);
    preview.src = objectUrl;

    if (removeBtn) removeBtn.style.display = 'inline-flex';
    setMeta(file);
  });

  if (removeBtn) removeBtn.addEventListener('click', resetImage);

  window.addEventListener('beforeunload', cleanupObjectUrl);
})();
</script>
@endsection
