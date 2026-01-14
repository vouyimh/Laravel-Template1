@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

<!-- Page Scripts -->
@section('script')
<!-- @vite(['resources/assets/js/pages-account-settings-account.js']) -->
<script>
    // Function to add new house fields dynamically
    function addHouse() {
        const container = document.getElementById('houses-container');
        const index = container.children.length;

        const houseHTML = `
        <div class="card mb-3 p-3 border border-secondary">
            <h5>House #${index + 1}</h5>
            <div class="mb-3">
                <label class="form-label">Street Name</label>
                <input type="text" name="houses[${index}][street_name]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Local Code</label>
                <input type="text" name="houses[${index}][local_code]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Village</label>
                <input type="text" name="houses[${index}][village]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">House Number</label>
                <input type="text" name="houses[${index}][house_number]" class="form-control" required>
            </div>
            <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Remove House</button>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', houseHTML);
    }
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-6 p-3">
            <h1 class="text-center fw-bold mb-4">Add Client</h1>
        
                <form action="" method="POST">
                    @csrf

                    <!-- Company Name -->
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>

                    <!-- Owner Name -->
                    <div class="mb-3">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner_name" class="form-control" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <!-- Tax -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="tax" id="tax">
                        <label class="form-check-label" for="tax">Tax Registered</label>
                    </div>

                    <!-- Company Address -->
                    <h4>Company Address</h4>
                    <div class="mb-3">
                        <label class="form-label">Street Name</label>
                        <input type="text" name="company_address[street_name]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Local Code</label>
                        <input type="text" name="company_address[local_code]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Village</label>
                        <input type="text" name="company_address[village]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">House Number</label>
                        <input type="text" name="company_address[house_number]" class="form-control" required>
                    </div>

                    <!-- Houses -->
                    <h4>Houses</h4>
                    <div id="houses-container">
                        <!-- Initial house can be added here if needed -->
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" onclick="addHouse()">Add House</button>

                    <div>
                        <button type="submit" class="btn btn-primary">Save Client</button>
                    </div>
                </form>
            </div>
    </div>
</div>
@endsection