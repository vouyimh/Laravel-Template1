@extends('layouts/contentNavbarLayout')

@section('title', 'Add Staff')

@section('content')
<div class="px-4">
  <div class="container-fluid mx-auto">

    <h5 class="text-16 mb-4">Add Staff</h5>

    @if($errors->any())
      <div class="mb-4 px-4 py-3 rounded bg-red-50 text-red-700">
        <ul class="list-disc pl-5">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('pages-staff-add') }}">
          @csrf

          <div class="grid grid-cols-2 gap-4">
            <input name="FirstName" placeholder="First Name" class="form-input" required>
            <input name="LastName" placeholder="Last Name" class="form-input" required>

            <input name="Email" type="email" placeholder="Email" class="form-input" required>

            <select name="Role" class="form-input" required>
              <option value="">-- Select Role --</option>
              <option value="Temporary">Temporary</option>
              <option value="Permanent">Permanent</option>
              <option value="Company">Company</option>
            </select>

            <input name="Username" placeholder="Username" class="form-input" required>
            <input name="PhoneNumber" placeholder="Phone Number" class="form-input">

            <input name="Password" type="password" placeholder="Password" class="form-input" required>
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('pages-staff-list') }}" class="btn">Cancel</a>
            <button class="btn bg-custom-500 text-white">Save</button>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>
@endsection
