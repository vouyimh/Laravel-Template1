@extends('layouts/contentNavbarLayout')

@section('title', 'Add Staff')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <h5 class="fw-bold mb-4 text-center">Add Staff</h5>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mx-auto" style="max-width: 800px;">
    <div class="card-body">

      <form method="POST" action="{{ route('pages-staff-add') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">

          <!-- First Name / Last Name -->
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input name="FirstName" type="text" class="form-control" placeholder="First Name" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input name="LastName" type="text" class="form-control" placeholder="Last Name" required>
          </div>

          <!-- Email / Role -->
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input name="Email" type="email" class="form-control" placeholder="Email" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="Role" class="form-select" required>
              <option value="">-- Select Role --</option>
              <option value="Temporary">Temporary</option>
              <option value="Permanent">Permanent</option>
              <option value="Company">Company</option>
            </select>
          </div>

          <!-- Username / Phone -->
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input name="Username" type="text" class="form-control" placeholder="Username" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input name="PhoneNumber" type="text" class="form-control" placeholder="Phone Number">
          </div>

          <!-- Password -->
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input name="Password" type="password" class="form-control" placeholder="Password" required>
          </div>

          <!-- Empty column to align Profile Picture -->
          <div class="col-md-6"></div>

        </div>

        <!-- Profile Picture (Centered Below Form) -->
        <div class="d-flex flex-column align-items-center mt-4 mb-3">
          <label class="form-label">Profile Picture</label>
          <input name="ProfilePicture" type="file" class="form-control w-50 mb-3" accept="image/*" onchange="previewImage(event)">

          <div class="rounded-circle border overflow-hidden" style="width:150px; height:150px;">
            <img id="profilePreview" src="{{ asset('images/default-avatar.png') }}" alt="Preview" class="w-100 h-100" style="object-fit: cover;">
          </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="{{ route('pages-staff-list') }}" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>

      </form>

    </div>
  </div>

</div>
@endsection

@section('script')
<script>
  function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('profilePreview');

    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.src = "{{ asset('images/default-avatar.png') }}";
    }
  }
</script>
@endsection
