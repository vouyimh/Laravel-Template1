@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Staff')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <h5 class="fw-bold mb-4">Edit Staff</h5>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">

      <form method="POST" action="{{ route('pages-staff-update', $staff->StaffID) }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input name="FirstName" type="text" class="form-control" value="{{ $staff->FirstName }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input name="LastName" type="text" class="form-control" value="{{ $staff->LastName }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input name="Email" type="email" class="form-control" value="{{ $staff->Email }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="Role" class="form-select" required>
              <option value="">-- Select Role --</option>
              <option value="Admin" {{ $staff->Role == 'Admin' ? 'selected' : '' }}>Admin</option>
              <option value="Manager" {{ $staff->Role == 'Manager' ? 'selected' : '' }}>Manager</option>
              <option value="Full-Time" {{ $staff->Role == 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
              <option value="Part-Time" {{ $staff->Role == 'Part-Time' ? 'selected' : '' }}>Part-Time</option>
              <option value="Temporary" {{ $staff->Role == 'Temporary' ? 'selected' : '' }}>Temporary</option>
              <option value="Permanent" {{ $staff->Role == 'Permanent' ? 'selected' : '' }}>Permanent</option>
              <option value="Company" {{ $staff->Role == 'Company' ? 'selected' : '' }}>Company</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input name="Username" type="text" class="form-control" value="{{ $staff->Username }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input name="PhoneNumber" type="text" class="form-control" value="{{ $staff->PhoneNumber }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Password (Leave blank to keep current)</label>
            <input name="Password" type="password" class="form-control" placeholder="New Password">
          </div>

          <div class="col-md-12 mt-3">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="ProfilePicture" class="form-control">
          </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="{{ route('pages-staff-list') }}" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>

      </form>

    </div>
  </div>

</div>
@endsection
