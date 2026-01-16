@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

<!-- Page Scripts -->
@section('script')
<!-- @vite(['resources/assets/js/pages-account-settings-account.js']) -->
<script>

    $(document).ready(function() {
        $('#add-client-form').submit(function(e) {
            e.preventDefault(); // prevent normal form submission

            // Build houses array
            let houses = [];
            $('#houses-container > div').each(function() {
                houses.push({
                    street_name: $(this).find('.house-street').val(),
                    local_code: $(this).find('.house-local').val(),
                    village: $(this).find('.house-village').val(),
                    house_number: $(this).find('.house-number').val(),
                    room: $(this).find('.house-room').val(),
                    size: $(this).find('.house-size').val(),
                    time: $(this).find('.house-time').val(),
                    tools: $(this).find('.house-tools').val(),
                    tasks: $(this).find('.house-tasks').val(),
                });
            });

            // Build main form data
            const formData = {
                company_name: $('input[name="company_name"]').val(),
                owner_name: $('input[name="owner_name"]').val(),
                email: $('input[name="email"]').val(),
                password: $('input[name="password"]').val(),
                phone_number: $('input[name="phone_number"]').val(),
                lockbox_number: $('input[name="lockbox_number"]').val(),
                company_type: $('select[name="company_type"]').val(),
                tax: $('#tax').is(':checked') ? true : false,
                company_address: {
                    street_name: $('input[name="company_address[street_name]"]').val(),
                    local_code: $('input[name="company_address[local_code]"]').val(),
                    village: $('input[name="company_address[village]"]').val(),
                    house_number: $('input[name="company_address[house_number]"]').val(),
                },
                houses: houses
            };

            $.ajax({
                url: 'api/add-client',
                type: 'POST',
                data: JSON.stringify(formData),
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val() // include CSRF token
                },
                success: function(response) {
                    alert(response.message);
                    $('#add-client-form')[0].reset();
                    $('#houses-container').html('');
                },
                error: function(xhr) {
                    if(xhr.status === 422){
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        let message = '';
                        for (let key in errors) {
                            message += errors[key].join(', ') + '\n';
                        }
                        alert(message);
                    } else {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                }
            });
        });
    });

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
            <div class="mb-3">
                <label class="form-label">Total Number of Rooms</label>
                <input type="number" name="houses[${index}][room]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Size</label>
                <input type="text" name="houses[${index}][size]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Total Time for Cleaning</label>
                <input type="text" name="houses[${index}][time]" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Total Tools</label>
                <textarea name="houses[${index}][tools]" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Task To Do</label>
                <textarea name="houses[${index}][tasks]" class="form-control" required></textarea>
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

            <form id="add-client-form" method="POST">
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

                <!-- Phone Number -->
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" required>
                </div>

                <!-- Lockbox -->
                <div class="mb-3">
                    <label class="form-label">Lockbox Number</label>
                    <input type="text" name="lockbox_number" class="form-control" required>
                </div>

                <!-- Company Type -->
                <div class="mb-6">
                    <label class="form-label">Company Type</label>
                    <select name="company_type" class="form-select " required="">
                        <option value="Personal">Personal</option>
                        <option value="Company">Company</option>
                    </select>
                </div>

                <!-- Tax -->
                <div class="form-check mb-9">
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

                <div class="text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection